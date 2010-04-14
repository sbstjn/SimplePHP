<?php

/**
 * Handle general actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/hazelcode/SimplePHP
 */

class G {

	/**
	 * Check if cookie is found and return value 
	 * @param string $name
	 * @return string null if not found
	 */
	static function getCookie($name) {
		if (!isset($_COOKIE[$name]))
			return null;
		return $_COOKIE[$name];
	}
	
	/**
	 * Set cookie
	 * @param string $name
	 * @param string $value
	 * @param int $time
	 * @param string $domain
	 */
	static function setCookie($name, $value, $time, $domain) {
		setcookie($name, $value, time() + $time, "/", "." . $domain);
	}
	
	/**
	 * Debug an object or an array
	 * @param mixed $array or an object
	 */
	static function debug($array) {
		$string = '<pre>';
		foreach (debug_backtrace() as $item)
		    $string = $string . $item['function'] . ' - ' . basename($item['file']) . ':' . $item['line'] . ' - '.dirname($item['file']) . "\n";
		$string = $string . print_r($array, true) . '</pre>';
		
	    echo $string;
	}
	
	/**
	 * Check if string is utf8
	 * @param string $string
	 * @retun bool
	 */
	static function isUTF8($string) {
		return seems_utf8($string);
	}
	
	/**
	 * Create an url compatible string 
	 * @param string $str
	 * @return string
	 */
	static function toSlug($str) {
		return sanitize_title_with_dashes($str);
	}

}