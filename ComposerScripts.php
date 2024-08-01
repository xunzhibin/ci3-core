<?php

// 命名空间
namespace Xzb\Ci3\Core;

use Composer\Script\Event;

/**
 * composer 脚本事件
 */
class ComposerScripts
{
    /**
	 * 处理 composer事件 post-install-cmd
	 * 
	 * @param \Composer\Script\Event $event
	 * @return void
	 */
	public static function postInstallCmd(Event $event)
    {
		$venderDir = $event->getComposer()->getConfig()->get('vendor-dir');
		$rootDir = dirname($venderDir);
		$appDirName = 'application';

		$isRoot = false;
		do {
			if (! file_exists($rootDir . '/' . $appDirName)) {
				$isRoot = true;
				$rootDir = dirname($rootDir);
			}
		} while (! $isRoot);

        $files = scandir($fromDir = dirname(__FILE__) . '/core');
        $files = array_diff($files, array('.', '..'));

        foreach ($files as $file) {
		    $fromPath = $fromDir . '/' . $file;
            $toPath = $rootDir . '/' . $appDirName . '/core/' . $file;

            if (! file_exists($toPath)) {
                copy($fromPath, $toPath);
            }
        }

		$langFromPath = dirname(__FILE__) . '/' . $langDirName . '/' . $langFileName;
		$langToPath = $rootDir . '/' . $appDirName . '/' . $langDirName . '/' . $langFileName;

		if (! file_exists($langToPath)) {
			copy($langFromPath, $langToPath);
		}
    }

}
