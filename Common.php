<?php

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

		// 抛出 运行 异常
		throw new \RuntimeException($message, $status_code);
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

		throw new \RuntimeException($message, 404);
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
	 * @param	Exception	$exception
	 * @return	void
	 */
	function _exception_handler($exception)
	{
		log_message('error', 'Severity: Error --> '.$exception->getMessage().' '.$exception->getFile().' '.$exception->getLine());

		// // 不是 自定义 异常类
		// if (! ($exception instanceof BaseException)) {
		// 	$exception = new HttpException(
		// 		$message = '',
		// 		$exception->getCode() ?: 500,
		// 		$exception
		// 	);
		// }

		// // 加载 输出类
		// $output =& load_class('Output', 'core');

		// $output->response($exception)->_display();
		
        // $response = $this->renderException($request, $e);
		

		exit();
	}
}

// ------------------------------------------------------------------------

if (! function_exists('class_basename')) {
    /**
     * 获取 对象或类的 basename
     * 
     * @param string|object
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}
