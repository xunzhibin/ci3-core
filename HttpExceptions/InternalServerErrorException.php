<?php

// 命名空间
namespace Xzb\Ci3\Core\HttpExceptions;

/**
 * 内部服务器错误 异常类
 */
class InternalServerErrorException extends HttpException
{
	/**
	 * HTTP 状态码
	 * 
	 * @var int
	 */
	protected $httpStatusCode = 500;

	// /**
	//  * 错误消息
	//  * 
	//  * @var string
	//  */
	// protected $errorMessage = 'Server Error';

}
