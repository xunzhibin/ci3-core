<?php

// 命名空间
namespace Xzb\Ci3\Core\HttpExceptions;

/**
 * HTTP 异常类
 */
class HttpException extends \RuntimeException implements HttpExceptionInterface
{
// ---------------------- HTTP 状态码----------------------
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
	/**
	 * 错误码
	 * 
	 * @var string
	 */
	protected $errorCode;

	/**
	 * 设置 错误码
	 * 
	 * @param string $code
	 * @return $this
	 */
	public function setErrorCode(string $code)
	{
		$this->errorCode = $code;

		return $this;
	}

	/**
	 * 获取 错误码
	 * 
	 * @return string
	 */
	public function getErrorCode(): string
	{
		return $this->errorCode ?: class_basename($this->getPrevious() ?: static::class);
	}

// ---------------------- 自定义 错误消息 ----------------------
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

// ---------------------- debug 信息 ----------------------
	/**
	 * 数据库操作 debug
	 * 
	 * @return array
	 */
	protected function getDatabaseQueriesDebug()
	{
		$dbs = array();
		foreach (get_object_vars(get_instance()) as $name => $cobject) {
			if (is_object($cobject)) {
				if ($cobject instanceof \CI_DB) {
					$dbs[get_class(get_instance()).':$'.$name] = $cobject;
				}
				elseif ($cobject instanceof \CI_Model) {
					foreach (get_object_vars($cobject) as $mname => $mobject) {
						if ($mobject instanceof \CI_DB) {
							$dbs[get_class($cobject).':$'.$mname] = $mobject;
						}
					}
				}
			}
		}

		$queries = [];
		if (count($dbs)) {
			foreach ($dbs as $name => $db) {
				$row = [];
				$row['database'] = $db->database.' ('.$name.')';
				$row['queries_count'] = count($db->queries);
				$row['queries_total_time'] = 0;
				$row['queries_list'] = [];

				$totalTime = 0;
				foreach ($db->queries as $key => $sql) {
					$time = number_format($db->query_times[$key], 4);
					$row['queries_total_time'] += $time;

					$row['queries_list'][] = [
						'sql' => $sql,
						'sql' => str_replace(["\n"], ' ', $sql),
						// 'sql' => str_replace(["\n"], '', $sql),
						'time' => $time . ' 秒'
					];
				}

				// var_dump($row);exit;
				$queries[] = $row;
			}
		}

		return $queries;
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
            // 异常信息
            $backtrace[] = [
                'message'   => $exception->getMessage(), // 错误文言
                'file'      => $exception->getFile(), // 文件
                'line'      => $exception->getLine(), // 行号
				// 'trace'		=> array_map(function ($row) {
				// 	unset($row['args']);
				// 	return $row;
				// }, $exception->getTrace())
            ];

            // 前一个 Throwable
            $exception = $exception->getPrevious();
        } while($exception);

		return $backtrace;
	}

// ---------------------- 数据转换 ----------------------
	/**
	 * 模型 转换为 数组
	 * 
	 * @return array
	 */
	public function toArray(): array
	{
		$body = [
			// HTTP 状态码
			'status_code' => $this->getHttpStatusCode(),
			// API 业务处理 状态
			'status' => false,
			// API 错误异常码
			'errcode' => $this->getErrorCode(),
			// API 错误描述
			'message' => $this->getErrorMessage(),
			// 服务器 当前时间戳
			'server_timestamp' => time(),
		];

		// debug 信息，非生产环境提供
		if (defined('ENVIRONMENT') && in_array(ENVIRONMENT, ['development', 'testing'])) {
			// 数据库操作 debug
			$body['debug']['db'] = [];
			if (get_instance() ?? false) {
				$body['debug']['db'] = $this->getDatabaseQueriesDebug();
			}

			// 回溯跟踪 debug
			$body['debug']['backtrace'] = $this->getBacktraceDebug();
		}

		return $body;
	}

	/**
	 * 转换为 JSON字符串
	 * 
	 * @return string
	 * 
	 * @throws \Xzb\Ci3\Database\Eloquent\JsonEncodingException
	 */
	public function toJson(): string
	{
		return json_encode($this->toArray());
	}

// ---------------------- 魔术方法 ----------------------
	/**
	 * 模型属性 转换为 字符串
	 * 
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}

}
