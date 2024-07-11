<?php

// HTTP 异常类
use Xzb\Ci3\Exception\HttpExceptionInterface;
use Xzb\Ci3\Exception\InternalServerErrorException;
use Xzb\Ci3\Exception\NotFoundException;
// 模型 异常类
use Xzb\Ci3\Database\RecordsNotFoundException;
// CI框架扩展 异常类
use Xzb\Ci3\Core\Exceptions;

// ------------------------------------------------------------------------

if ( ! function_exists('show_error')) {
	/**
	 * 自定义 错误 处理
	 * 
	 * 重新 CI3 核心 公共函数
	 * 所有 错误 抛出异常 统一处理
	 *
	 * @param	string
	 * @param	int
	 * @param	string
	 * @return	void
	 */
	function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
	{
		$status_code = abs($status_code);

		// 错误文言
		if (is_array($message)) {
			$message = implode(', ', $message);
		}
		$message = $heading . ': ' . $message;

		throw new InternalServerErrorException($message);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('show_404')) {
	/**
	 * 自定义 404 处理
	 *
	 * 重新 CI3 核心 公共函数
	 * 所有 404 抛出异常 统一处理
	 *
	 * @param	string
	 * @param	bool
	 * @return	void
	 */
	function show_404($page = '', $log_error = TRUE)
	{
		// 异常消息内容
		$message = 'Not Found: ' . $page;

		throw new NotFoundException($message);
	}
}
// --------------------------------------------------------------------

if ( ! function_exists('_error_handler')) {
	/**
	 * 自定义 PHP函数 set_error_handler 错误处理
	 * 
	 * 重新 CI3 核心 公共函数
	 * 所有 错误 抛出异常 统一处理
	 *
	 * @param	int	$severity
	 * @param	string	$message
	 * @param	string	$filepath
	 * @param	int	$line
	 * @return	void
	 */
	function _error_handler($severity, $message, $filepath, $line)
	{
		if (($severity & error_reporting()) !== $severity) {
			return;
		}

		// 抛出 错误异常
		throw new \ErrorException($message, $code = 500, $severity, $filepath, $line);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('_exception_handler')) {
	/**
	 * 自定义 PHP函数 set_exception_handler 异常类处理
	 *
	 * @param \Exception $exception
	 * @return void
	 */
	function _exception_handler($exception)
	{
		log_message('error', 'Severity: Error --> '.$exception->getMessage().' '.$exception->getFile().' '.$exception->getLine());

		if (! $exception instanceof HttpExceptionInterface) {
			// 记录未找到 异常类
			if ($exception instanceof RecordsNotFoundException) {
				$exception = new NotFoundException($exception->getMessage(), $exception->getCode(), $exception);
			}
			else {
				$exception = new InternalServerErrorException($exception->getMessage(), $exception->getCode(), $exception);
			}
		}

		// 加载 CI框架 异常类
		load_class('Exceptions', 'core');

		// 加载 输出类
		load_class('Output', 'core')
			// 设置 HTTP状态码
			->set_status_header($exception->getHttpStatusCode())
			// 设置 内容类型
			->set_content_type('json')
			// 显示输出
			->_display(Exceptions::parseExceptionResponse($exception));

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

		exit();
	}
}

// ------------------------------------------------------------------------
