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

}