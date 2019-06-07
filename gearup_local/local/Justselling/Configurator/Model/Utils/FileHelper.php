<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */

class Justselling_Configurator_Model_Utils_FileHelper {

	/**
	 * Returns all filenames (absolute path) of a folder as a simple array. Does not work recursively!
	 * @param $path string valid path name (must be directory)
	 * @param $regexInclFilter regex pattern for include-filter
	 * @return array array of string with absolute paths, may return empty array
	 */
	public static function getFilesOfFolder($path, $regexInclFilter=null, $regexOptions='') {
		$files = array();
		if (empty($path)) return $files;
		if (!is_dir($path)) {
			Js_Log::log(sprintf("Path is not a folder: %s", $path, 'configurator', Zend_Log::ERR));
			return $files;
		}
		$it = new DirectoryIterator($path);
		if (!$it->isReadable()) {
			Js_Log::log(sprintf("Cannot read from path %s", $it->getPath()), 'configurator', Zend_Log::ERR);
			return $files;
		}
		/* @var $fileInfo SplFileInfo */
		foreach ($it as $fileInfo) {
			if ($fileInfo->isDir() || $fileInfo->isLink() || $fileInfo->getFilename() == '.'
				|| $fileInfo->getFilename() == '..' || $fileInfo->getFilename() == '.DS_Store') {
				continue; // ignore
			}
			if (!empty($regexInclFilter)) {
				if (preg_match('/'.$regexInclFilter.'/'.$regexOptions, $fileInfo->getFilename())) {
					$files[] = $path.DS.$fileInfo->getFileName();
				}
			} elseif ($fileInfo->isFile()) {
				$files[] = $path.DS.$fileInfo->getFileName();
			}
		}
		return $files;
	}

    /**
     * @param $string
     * @param int $length
     * @return bool
     */
    public static function getLenHeader($string, $length = 40) {
        $res = false;
        if (strlen($string) >= $length) {
            $res = true;
        }

        return $res;
    }
}