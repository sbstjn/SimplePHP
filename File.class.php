<?php

/**
 * Handle file actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/hazelcode/SimplePHP
 */

class F {

    /**
     * construct is not used
     */
    public function __construct() {
    
    }
    
    /**
     * Check file
     * @param string $file
     */
    static function __checkFile($file) {
    	if (!file_exists($file))
            throw new Exception('File not found: ' . $file);
    		
    	return true;
    }
    
    /**
     * Write data to file
     * @param string $file
     * @param string $content
     */
    static function put($file, $content = '', $fileOption = 'w+') {
    	self::__checkFile($file);
    
        $handle = fopen($name, $fileOption);
        fwrite($handle, $data);
        fclose($handle);
    }
    
    /**
     * Write data to file (wrapper for put)
     * @param string $file
     * @param string $content
     */
    static function write($file, $content = '') {
        return self::put($file, $content);
    }

    /**
     * Write data to file (wrapper for put)
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
    static function get($file, $length = FILE_SIZE_FULL) {
    	self::__checkFile($file);
    	
        $handle = fopen($file, (FILE_SIZE_FULL == $length ? filesize($file) : $length));
        return fread ($handle, $length);
    }

    /**
     * Get content from file (wrapper for get)
     * @param string $file
     * @param int $length
     * @return string
     */
    static function load($file, $length = FILE_SIZE_FULL) {
        return self::get($file, $length);
    }

    /**
     * Get content from file (wrapper for get)
     * @param string $file
     * @param int $length
     * @return string
     */
    static function getContent($file, $length = FILE_SIZE_FULL) {
        return self::get($file, $length);
    }

}