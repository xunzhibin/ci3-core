<?php

// 命名空间
namespace Xzb\Ci3\Core;

// PHP 异常
use Throwable;
// 模型 异常
use Xzb\Ci3\Database\Exception\ModelExceptionInterface;
// HTTP 异常
use Xzb\Ci3\Exception\InternalServerErrorException;

// 字符串 辅助函数
use Xzb\Ci3\Helpers\Str;

/**
 * 异常类
 */
class Exceptions extends \CI_Exceptions
{
// --------------------------------------------------------------------
	/**
	 * 重写 常规 错误
	 * 
	 * 此方法 只限于 CI 框架调用，自定义请使用异常类
	 *
	 * CI 辅助函数 system\core\Common.php 文件中 show_error 方法调用
	 * CI 核心类 system\core\Exceptions.php 文件中 show_404 方法调用(已在父类重写，不在调用)
	 * CI DB驱动类  system\database\DB_driver.php 文件中 display_error 方法调用(需要在 application\config\database.php 配置文件中 开启 db_debug，一般生产环境时关闭的)
	 *
	 * @param	string		$heading	Page heading
	 * @param	string|string[]	$message	Error message
	 * @param	string		$template	Template name
	 * @param 	int		$status_code	(default: 500)
	 *
	 * @return	string	Error page output
	 */
	public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		// 错误文言
		if (is_array($message)) {
			$message = implode(' -> ', $message);
		}
		$message = $heading . ' -> ' . $message;

		// 抛出 异常
		throw new InternalServerErrorException($message);
	}

// --------------------------------------------------------------------
	/**
	 * 解析 异常 响应
	 * 
	 * @param \Throwable $e
	 * @return string
	 */
	public static function parseExceptionResponse(Throwable $e)
	{
		// 解析 响应错误信息
		if ($message = static::parseResponseErrorMessage($e)) {
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
	public static function parseResponseErrorMessage(Throwable $e): string
	{
		// 模型 异常
		if ($e->getPrevious() instanceof ModelExceptionInterface) {
			$message = static::parseModelExceptionErrorMessage($e->getPrevious());
		}
		else {
			$message = static::parseHttpExceptionErrorMessage($e);
		}

		return $message;
	}

	/**
	 * 解析 模型异常 错误信息
	 * 
	 * @param \Xzb\Ci3\Database\Exception\ModelExceptionInterface
	 * @return string
	 */
	public static function parseModelExceptionErrorMessage(ModelExceptionInterface $e): string
	{
		// 错误行 键名
		$errorLineKey = str_replace('_exception', '', Str::snake(class_baseName($e)));

		// 模型名
		$modelName = $e->getModel();

		// 实例化 语言类
		$lang = new Lang;

		// 加载 语言文件
		$lang->load('database');

		// 错误信息
		$message = $lang->line('database_' . $errorLineKey, false) ?: '';

		// 模型 名称
		$models = $lang->line('database_models', false);
		$customModelName = $models[$modelName] ?? $modelName;

		return str_replace([ '{field}' ], [ $customModelName ], $message);
	}

	/**
	 * 解析 HTTP 异常 错误信息
	 * 
	 * @param \Throwable $e
	 * @return string
	 */
	public static function parseHttpExceptionErrorMessage(Throwable $e): string
	{
		// 实例化 语言类
		$lang = new Lang;

		// 加载 语言文件
		$lang->load('http_status_code');

		return $lang->line('http_status_code_' . $e->getHttpStatusCode(), false) ?: '';
	}


}
