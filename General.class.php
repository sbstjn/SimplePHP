<?php

/**
 * Handle general actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/semu/SimplePHP
 */
class G {
    
    /**
     * Debug an object or an array
     * @param mixed $array or an object
     */
    static function debug($array) {
        $string = '<pre>';
        
        foreach (debug_backtrace() as $item) {
            $string = $string . $item['function'] . ' - ' . basename($item['file']) . ':' . $item['line'] . ' - ' . dirname($item['file']) . "\n";
        }
        
        $string = $string . print_r($array, true) . '</pre>';
        echo $string;
    }
    
    static function urlTitle($site, $page, $url) {
        return $site . ' - ' . $page . ' | ' . $url;
    }
    
    /**
     * Check if cookie is found and return value
     * @param string $name
     * @return string null if not found
     */
    static function getCookie($name) {
        if (!isset($_COOKIE[$name])) {
            return null;
        }
        
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
        setcookie($name, $value, time() + $time, '/', $domain);
    }
    
    /**
     * Create a php redirect to url
     * @param string $url
     */
    static function redirectTo($url) {
        header('location: ' . $url);
        die();
    }
    
    /**
     * Perform various empty checks on a string
     * @param string $str
     * @return bool
     */
    static function isEmpty($str) {
        if (!$str || $str == null || $str === null || trim($str) == '') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if index is set in $_POST
     * @param string $key
     * @return mixed
     */
    static function inPost($key) {
        return isset($_POST[$key]);
    }
    
    /**
     * Check if index is set in $_GET
     * @param string $key
     * @return mixed
     */
    static function inGet($key) {
        return isset($_GET[$key]);
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

