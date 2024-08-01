<?php

// 命名空间
namespace Xzb\Ci3\Core;

/**
 * 语言 抽象类
 */
abstract class Lang extends \CI_Lang
{
	/**
	 * 基础语言
	 * 
	 * @var string
	 */
	public $baseLanguage = 'english';

	/**
	 * Load a language file
	 *
	 * 重写  加载语言文件
	 * 
	 * @param array|string $langFile
	 * @param string $idiom
	 * @param bool $return
	 * @param bool $addSuffix
	 * @param string $altPath
	 * @return mixed
	 */
	public function load($langFile, $idiom = '', $return = FALSE, $addSuffix = TRUE, $altPath = '')
	{
		// 数组
		if (is_array($langFile)) {
			foreach ($langFile as $file) {
				$this->load($file, $idiom, $return, $addSuffix, $altPath);
			}
			return;
		}

		$langFile = str_replace('.php', '', $langFile);

		if ($addSuffix === TRUE) {
			$langFile = preg_replace('/_lang$/', '', $langFile) . '_lang';
		}

		$langFile .= '.php';

		if (empty($idiom) OR ! preg_match('/^[a-z_-]+$/i', $idiom)) {
			$config = & get_config();
			$idiom = empty($config['language']) ? $this->baseLanguage : $config['language'];
		}

		if ($return === FALSE && isset($this->is_loaded[$langFile]) && $this->is_loaded[$langFile] === $idiom) {
			return;
		}

		$basepath = SYSDIR . 'language/' . $this->baseLanguage . '/' . $langFile;
		if (($found = file_exists($basepath)) === TRUE) {
			include($basepath);
		}

		$basepath = BASEPATH . 'language/' . $idiom . '/' . $langFile;
		if (($found = file_exists($basepath)) === TRUE) {
			include($basepath);
		}

		if ($altPath !== '') {
			$altPath .= 'language/' . $idiom . '/' . $langFile;
			if (file_exists($altPath)) {
				include($altPath);
				$found = TRUE;
			}
		}
		else {
			if (get_instance() ?? false) {
				foreach (get_instance()->load->get_package_paths(TRUE) as $packagePath) {
					$packagePath .= 'language/' . $idiom . '/' . $langFile;
					if ($basepath !== $packagePath && file_exists($packagePath)) {
						include($packagePath);
						$found = TRUE;
						break;
					}
				}
			}
		}

		if ($found !== TRUE) {
			log_message('error', 'Unable to load the requested language file: language/' . $idiom . '/' . $langFile);
			return ;
		}

		if (!isset($lang) OR ! is_array($lang)) {
			log_message('error', 'Language file contains no data: language/' . $idiom . '/' . $langFile);

			if ($return === TRUE) {
				return array();
			}

			return;
		}

		if ($return === TRUE) {
			return $lang;
		}

		$this->is_loaded[$langFile] = $idiom;
		$this->language = array_merge($this->language, $lang);

		log_message('info', 'Language file loaded: language/' . $idiom . '/' . $langFile);
		return TRUE;
	}


}
