<?php

// 命名空间
namespace Xzb\Ci3\Core;

// HTTP 异常类
use Xzb\Ci3\Core\HttpExceptions\{
	HttpExceptionInterface,
	InternalServerErrorException,
	NotFoundException
};
// 模型 异常类
use Xzb\Ci3\Database\Exception\{
	ModelExceptionInterface,
	RecordsNotFoundException
};

// 字符串 辅助函数
use Xzb\Ci3\Helpers\Str;

// PHP 异常类
use ErrorException;
use Throwable;

/**
 * 异常类 扩展
 */
abstract class Exceptions extends \CI_Exceptions
{
    /**
     * HTTP 状态码
     * 
     * @var int
     */
    protected $httpStatusCode;

    /**
     * 异常 响应 状态码
     * 
     * @param int $code
     * @return $this
     */
    public function httpStatusCode(int $code)
    {
        $this->httpStatusCode = $code;

        return $this;
    }

// ------------------------------- 重构 父 -------------------------------
	/**
	 * 404 Error Handler
	 * 
	 * 重写 404 错误处理
	 * CI 辅助函数 system\core\Common.php 文件中 show_404 方法调用
	 * 
	 * @param string $page
	 * @param bool $logError
	 * @return void
	 * 
	 * @throws \Xzb\Ci3\Exception\NotFoundException
	 */
	public function show_404($page = '', $logError = TRUE)
	{
		throw new NotFoundException(
			'Not Found: ' . $page
		);
	}

	/**
	 * General Error Page
	 * 
	 * 重写 错误页面显示
	 * CI 辅助函数 system\core\Common.php 文件中 show_error 方法调用
	 * CI 核心类 system\core\Exceptions.php 文件中 show_404 方法调用(当前父类已重写, 不在调用)
	 * CI DB驱动类 system\database\DB_driver.php 文件中 display_error 方法调用(需要在 application\config\database.php 配置文件中 开启 db_debug, 一般生产环境关闭的)
	 * 
	 * @param string $heading
	 * @param string|string[] $message
	 * @param string $template
	 * @param int $statusCode
	 * @return void
	 * 
	 * @throws \Xzb\Ci3\Exception\InternalServerErrorException
	 */
	public function show_error($heading, $message, $template = '', $statusCode = 500)
	{
		// 错误文言
		if (is_array($message)) {
			$message = implode(' -> ', $message);
		}
		// $message = $heading . ' -> ' . $message;

		// 抛出 异常
		throw new InternalServerErrorException($message);
	}

	/**
	 * Native PHP error handler
	 * 
	 * 重写 PHP错误处理
	 * CI 辅助函数 system\core\Common.php 文件中 _error_handler 方法调用
	 * 
	 * @param int $severity
	 * @param string $message
	 * @param string $filePath
	 * @param int $line
	 * @param int $statusCode
	 * @return void
	 * 
	 * @throws \ErrorException
	 */
	public function show_php_error($severity, $message, $filePath, $line, $statusCode = 500)
	{
		// 抛出 错误异常
		throw new ErrorException($message, $code = 500, $severity, $filePath, $line);
	}

	/**
	 * Exception Handler
	 * 
	 * 重写 异常错误处理
	 * CI 辅助函数 system\core\Common.php 文件中 _exception_handler 方法调用
	 * 
	 * @param \Throwable $e
	 * @return void
	 */
	public function show_exception($e)
	{
		$e = $this->prepareException($e);

		// 加载 输出类
		load_class('Output', 'core')
			// 设置 HTTP状态码
			->set_status_header($this->httpStatusCode ?: $e->getHttpStatusCode())
			// 设置 内容类型
			->set_content_type('json')
			// 显示输出
			->_display($this->parseExceptionResponse($e));

        /*
            1. PHP 运行异常 ---> 500
            2. CI框架抛出异常 ---> 500
            3. 模型异常
                未找到 ---> 404
                其它 ---> 500
            4. 业务异常(ServiceException) --> 500
            5. 验证异常(ValidationException) --> 422
                缺少必要的参数 --> 必填
                参数格式错误 --> 非数字、非字母、非字母数字、非手机号、非整形、非数组 等等
                参数值不合法 --> 长度不符合、大小不符合、不在指定值内 等等
            6. 身份验证异常(AuthenticationException) --> 401
            7. 令牌不匹配异常(TokenMismatchException) --> 419
        */
	}

// ------------------------------- 异常解析扩展 -------------------------------
	/**
	 * 准备渲染异常
	 * 
	 * @param \Throwable $e
	 * @return \Throwable
	 */
	protected function prepareException(Throwable $e)
	{
		if ($e instanceof HttpExceptionInterface) {
			return $e;
		}

		// 记录未找到 异常类
		if ($e instanceof RecordsNotFoundException) {
			return new NotFoundException($e->getMessage(), $e->getCode(), $e);
		}

		return new InternalServerErrorException($e->getMessage(), $e->getCode(), $e);
	}

	/**
	 * 解析 异常 响应
	 * 
	 * @param \Throwable $e
	 * @return string
	 */
	protected function parseExceptionResponse(Throwable $e)
	{
		// 解析 响应错误信息
		if ($message = $this->parseResponseErrorMessage($e)) {
			$e->setErrorMessage($message);
		}

		return (string)$e;
	}

	/**
	 * 解析 响应错误信息
	 * 
	 * @param \Throwable $e
	 * @return string
	 */
	protected function parseResponseErrorMessage(Throwable $e): string
	{
		// 模型 异常
		if ($e->getPrevious() instanceof ModelExceptionInterface) {
			return $this->parseModelExceptionErrorMessage($e->getPrevious());
		}

		return $this->parseHttpExceptionErrorMessage($e);
	}

	/**
	 * 解析 模型异常 错误信息
	 * 
	 * @param \Xzb\Ci3\Database\Exception\ModelExceptionInterface
	 * @return string
	 */
	protected function parseModelExceptionErrorMessage(ModelExceptionInterface $e): string
	{
		$lineKey = 'database_' . str_replace('_exception', '', Str::snake(class_baseName($e)));
		$message = $this->parseErrorMessage($lineKey, ['database', 'custom_error_messages']);

		$lineKey = 'database_label_' . $e->getModel();
		$replaceData[] = $this->parseErrorMessage($lineKey);

		$search = [ '{field}', '{param}' ];

		return str_replace($search, $replaceData, $message);
	}

	/**
	 * 解析 HTTP 异常 错误信息
	 * 
	 * @param \Throwable $e
	 * @return string
	 */
	protected function parseHttpExceptionErrorMessage(Throwable $e): string
	{
		$lineKey = 'http_status_code_' . $e->getHttpStatusCode();

		return $this->parseErrorMessage($lineKey, ['http_status_code', 'custom_error_messages']);
	}

	/**
	 * 解析 错误信息
	 * 
	 * @param string $lineKey
	 * @param array|string|null $file
	 * @return string
	 */
	protected function parseErrorMessage(string $lineKey, $file = null): string
	{
		$lang =& load_class('Lang', 'core');

		// 加载 语言文件
		if (! is_null($file)) {
			$lang->load($file);
		}

		// 错误信息
		return $lang->line($lineKey, false) ?: '';
	}

}
