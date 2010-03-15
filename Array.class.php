<?php

/**
 * Handle array actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/hazelcode/SimplePHP
 */
class A {

    /**
     * construct is not used
     */
    public function __construct() {
    
    }
    
    /**
     * Get first item
     * @param array $array
     * @return mixed
     */
    static function first($array) {
        return array_shift($array);
    }

    /**
     * Convert array to CSV string
     * @param array $array
     * @return string
     */
    static function asCSV($array) {
        $return = implode(',', array_keys(A::first($array))) . ";\n";
        
        foreach ($array as $line)
            $return = $return . implode(',', array_keys($line)) . ";\n";
    }

}

?>