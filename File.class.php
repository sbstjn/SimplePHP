<?php

/**
 * SiFile handles file based actions
 * @package SimplePHPTools
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/hazelcode/SimplePHPTools
 */

class SiFile {

	/**
	 * Write data to file
	 * @param string $file
	 * @param string $content
	 */
	static function put($file, $content = '') {
		$handle = fopen($name,	"w+");
		fwrite($handle, $data);
		fclose($handle);
	}
	
	/**
	 * Write data to file
	 * @param string $file
	 * @param string $content
	 */
	static function write($file, $content = '') {
		return self::put($file, $content);
	}

	/**
	 * Write data to file
	 * @param string $file
	 * @param string $content
	 */
	static function save($file, $content = '') {
		return self::put($file, $content);
	}

	/**
	 * Get content from file
	 * @param string $file
	 * @param int $length
	 * @return string
	 */
	static function getContent($file, $length = 512) {
		$handle  = fopen ($file, "r");
		return fread ($handle, $length);
	}

	/**
	 * Get content from file
	 * @param string $file
	 * @param int $length
	 * @return string
	 */
	static function load($file, $length = 512) {
		$handle  = fopen ($file, "r");
		return fread ($handle, $length);
	}

}