<?php

// 命名空间
namespace Xzb\Ci3\Core;

/**
 * 语言类
 */
class Lang extends \CI_Lang
{
	/**
	 * 基础语言
	 * 
	 * @var string
	 */
	public $baseLanguage = 'english';

	/**
	 * 已加载 文件
	 * 
	 * @var array
	 */
	protected $isLoadedFiles = [];

	/**
	 * 加载 语言文件
	 * 
	 * @return void
	 */
	public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
	{
		if (is_array($langfile)) {
			foreach ($langfile as $value) {
				$this->load($value, $idiom, $return, $add_suffix, $alt_path);
			}

			return ;
		}

		// 文件名
		$langfile = str_replace('.php', '', $langfile);
		// 文件名 后缀
		if ($add_suffix === TRUE) {
			$langfile = preg_replace('/_lang$/', '', $langfile) . '_lang';
		}
		// 文件 扩展名
		$langfile .= '.php';

		// 语言
		if (empty($idiom) OR !preg_match('/^[a-z_-]+$/i', $idiom)) {
			// 获取 配置文件中 语言
			$idiom = config_item('language') ?: $this->baseLanguage;
		}

		// 已加载
		if ($return === FALSE && isset($this->is_loaded[$langfile]) && $this->is_loaded[$langfile] === $idiom) {
			return ;
		}

		// 加载 系统目录(system/language) 默认语言 文件
		$systemDefalutLanguage = $this->getSystemDefaultLanguage($langfile);

		// 加载 系统目录(system/language) 基础语言 文件
		$systemBaseLanguage = $this->getSystemBaseLanguage($idiom, $langfile);

		// 加载 指定路径 基础语言 文件
		$specificLanguage = $alt_path
							? $this->getAltPathLanguage($alt_path, $idiom, $langfile)
							: $this->getPackageLanguage($idiom, $langfile);

		// 合并
		$lang = array_merge($systemDefalutLanguage, $systemBaseLanguage, $specificLanguage);

		// 语言文件 未找到
		// if (! $lang) {
		// 	show_error('Unable to load the requested language file: language/' . $idiom . '/' . $langfile);
		// }

		if ($return === TRUE) {
			return $lang;
		}

		// 设置 加载列表
		$this->is_loaded[$langfile] = $idiom;

		// 设置 翻译列表
		$this->language = array_merge($this->language, $lang);

		log_message('info', 'Language file loaded: language/' . $idiom . '/' . $langfile);
		return TRUE;
	}

	/**
	 * 加载 系统 默认语言 文件
	 * 
	 * @param string $langfile
	 * @return array
	 */
	protected function getSystemDefaultLanguage(string $langfile): array
	{
    	$filePath = replace_dir_separator(BASEPATH . 'language/' . $this->baseLanguage . '/' . $langfile);
		if (! in_array($filePath, $this->isLoadedFiles) && file_exists($filePath)) {
			include($filePath);

			array_push($this->isLoadedFiles, $filePath);
		}

		// 语言文件 无内容
		if ( ! isset($lang) OR !is_array($lang)) {
			$lang = [];
			// log_message('error', 'Language file contains no data: ' . $filePath);
		}

		return $lang;
	}

	/**
	 * 加载 系统 基础语言 文件
	 * 
	 * @param string $idiom
	 * @param string $langfile
	 * @return array
	 */
	protected function getSystemBaseLanguage(string $idiom, string $langfile): array
	{
    	$filePath = replace_dir_separator(BASEPATH . 'language/' . $idiom . '/' . $langfile);
		if (! in_array($filePath, $this->isLoadedFiles) && file_exists($filePath)) {
			include($filePath);

			array_push($this->isLoadedFiles, $filePath);
		}

		// 语言文件 无内容
		if ( ! isset($lang) OR !is_array($lang)) {
			$lang = [];
			// log_message('error', 'Language file contains no data: ' . $filePath);
		}

		return $lang;
	}

	/**
	 * 加载 指定路径 基础语言 文件
	 * 
	 * @param string $altPath
	 * @param string $idiom
	 * @param string $langfile
	 * @return array
	 */
	protected function getAltPathLanguage(string $altPath, string $idiom, string $langfile): array
	{
    	$filePath = replace_dir_separator($altPath . 'language/' . $idiom . '/' . $langfile);
		if (in_array($filePath, $this->isLoadedFiles) && file_exists($filePath)) {
			include($filePath);

			array_push($this->isLoadedFiles, $filePath);
		}

		// 语言文件 无内容
		if ( ! isset($lang) OR !is_array($lang)) {
			$lang = [];
			// log_message('error', 'Language file contains no data: ' . $filePath);
		}

		return $lang;
	}

	/**
	 * 加载 程序包 基础语言 文件
	 * 
	 * @param string $idiom
	 * @param string $langfile
	 * @return array
	 */
	protected function getPackageLanguage(string $idiom, string $langfile)
	{
		$lang = [];

		// 程序包
		$load =& load_class('Loader', 'core');

		foreach ($load->get_package_paths(TRUE) as $packagePath) {
			$filePath = replace_dir_separator($packagePath . 'language/' . $idiom . '/' . $langfile);
			if (! in_array($filePath, $this->isLoadedFiles) && file_exists($filePath)) {
				include($filePath);

				array_push($this->isLoadedFiles, $filePath);

				// 语言文件 无内容
				if ( !isset($lang) OR !is_array($lang)) {
					$lang = [];
					// log_message('error', 'Language file contains no data: ' . $filePath);
				}

				break;
			}
		}
	
		return $lang;
	}

}
