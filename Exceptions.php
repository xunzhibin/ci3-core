<?php

// 命名空间
namespace Xzb\Ci3\Core;

/**
 * 异常类
 */
abstract class Exceptions extends \CI_Exceptions
{
// --------------------------------------------------------------------
	/**
	 * 常规 错误
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

		// 抛出 运行 异常
		throw new \RuntimeException($message, $status_code);
	}
}
