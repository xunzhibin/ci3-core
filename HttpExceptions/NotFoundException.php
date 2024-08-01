<?php

// 命名空间
namespace Xzb\Ci3\Core\HttpExceptions;

/**
 * 未找到 异常类
 */
class NotFoundException extends HttpException
{
	/**
	 * HTTP 状态码
	 * 
	 * @var int
	 */
	protected $httpStatusCode = 404;

	/**
	 * 错误消息
	 * 
	 * @var string
	 */
	protected $errorMessage = 'Not found';

}
