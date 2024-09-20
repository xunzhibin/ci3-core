<?php

// 命名空间
namespace Xzb\Ci3\Core\HttpExceptions;

// 异常资源响应类
use Xzb\Ci3\Core\Http\Resources\Json\ExceptionResourceResponse;

/**
 * HTTP 异常类
 */
class HttpException extends \RuntimeException implements HttpExceptionInterface
{
	/**
	 * HTTP 状态码
	 * 
	 * @var int
	 */
	protected $httpStatusCode;

	/**
	 * 获取 HTTP 状态码
	 * 
	 * @return int
	 */
	public function getHttpStatusCode() :int
	{
		return $this->httpStatusCode;
	}

	/**
	 * 设置 HTTP 状态码
	 * 
	 * @param int $code
	 * @return $this
	 */
	public function setHttpStatusCode(int $code)
	{
		$this->httpStatusCode = $code;

		return $this;
	}

// ---------------------- 自定义 错误码 ----------------------
	// /**
	//  * 错误码
	//  * 
	//  * @var string
	//  */
	// protected $errorCode;

	// /**
	//  * 设置 错误码
	//  * 
	//  * @param string $code
	//  * @return $this
	//  */
	// public function setErrorCode(string $code)
	// {
	// 	$this->errorCode = $code;

	// 	return $this;
	// }

	// /**
	//  * 获取 错误码
	//  * 
	//  * @return string
	//  */
	// public function getErrorCode(): string
	// {
	// 	return $this->errorCode ?: class_basename($this->getPrevious() ?: static::class);
	// }

	/**
	 * 错误消息
	 * 
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * 设置 错误消息
	 * 
	 * @param string $message
	 * @return $this
	 */
	public function setErrorMessage(string $message)
	{
		$this->errorMessage = $message;

		return $this;
	}

	/**
	 * 获取 错误消息
	 * 
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		return $this->errorMessage;
	}

// ---------------------- 数据转换 ----------------------
	/**
	 * 创建 HTTP响应对象
	 * 
	 * @return array
	 */
	public function toResponse()
	{
		$body = [
			// // HTTP 状态码
			// 'status_code' => $this->getHttpStatusCode(),
			// API 错误异常码
			'errcode' => class_basename($this->getPrevious() ?: $this),
			// API 错误描述
			'message' => $this->getErrorMessage(),
		];

		// debug 信息，非生产环境提供
		if (defined('ENVIRONMENT') && in_array(ENVIRONMENT, ['development', 'testing'])) {
			// 回溯跟踪 debug
			$body['debug_backtrace'] = $this->getBacktraceDebug();
		}

		return $body;
	}

	/**
	 * 回溯跟踪 debug
	 * 
	 * @return array
	 */
	protected function getBacktraceDebug(): array
	{
		$backtrace = [];
		$exception = $this;
		do {
			$isHasPrevious = false;
			if ($exception->getPrevious()) {
				$exception = $exception->getPrevious();
				$isHasPrevious = true;
			}

		} while($isHasPrevious);

		$backtrace[] = [
			'message'   => $exception->getMessage(), // 错误文言
			'file'      => $exception->getFile(), // 文件
			'line'      => $exception->getLine(), // 行号
		];
		foreach($exception->getTrace() as $trace) {
			unset($trace['args'], $trace['type']);
			$backtrace[] = $trace;
		}

        // do {
        //     // 异常信息
        //     $backtrace[] = [
        //         'message'   => $exception->getMessage(), // 错误文言
        //         'file'      => $exception->getFile(), // 文件
        //         'line'      => $exception->getLine(), // 行号
		// 		'trace'		=> array_map(function ($row) {
		// 			unset($row['args'], $row['type']);
		// 			return $row;
		// 		}, $exception->getTrace())
        //     ];

        //     // 前一个 Throwable
        //     $exception = $exception->getPrevious();
        // } while($exception);

		return $backtrace;
	}

}
