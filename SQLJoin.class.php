<?php

/**
 * Handle basic MySQL Join actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/hazelcode/SimplePHP
 */
 
class SQLJoin {

    var $base;
    var $tableName;
    var $joinedTables;
    var $returnAllFlag;
    var $returnList;
    
    /**
     * Define base table
     * @param string $tableName
     */
    function __construct($tableName) {
        $this->base = $tableName;
        $this->joinedTables = array();
        
        $this->addTable($tableName);
    }
    
    /**
     * Join $tableName ON $index = $onField
     * @param string $tableName
     * @param string $index
     * @param string $onField
     */
    function addTable($tableName, $index = '', $onField = '') {
        $this->joinedTables[] = array(
            'name'     => $tableName,
            'hash'     => substr(md5(count($this->joinedTables) . $tableName), 0, 8),
            'index'    => $index,
            'onField'  => $onField);
    }
    
    /**
     * Set values for selection
     * ->select('*');
     * ->select('*', array('id' => 'newId'));
     * @param func_get_args() is called
     */
    function select() {
        $args = func_get_args();
        
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $key   = array_shift(array_keys($arg));
                $value = array_shift($arg);
                
                $key   = $this->__parseTableFieldKey($key);
                $this->returnList[$key] = $value;
            } elseif (stristr($arg, '*')) {
                $this->returnAllList[] = $this->__parseTableFieldKey($arg);
            }
        }

    }
    
    /**
     * Set values for where condition
     * @param array $array
     */
    function where($array) {
        foreach ($array as $key => $value)
            $this->where[$this->__parseTableFieldKey($key)] = $value;
    }
    
    /**
     * Parse strings like 0#id
     * @param string $key
     */
    function __parseTableFieldKey($key) {
        $pattern = '/[(0-9)*]\#/sU';
        preg_match($pattern, $key, $return);
        $tableIndex = substr($return[0], 0, 1);

        return str_replace($return[0], $this->joinedTables[$tableIndex]['hash'] . '.', $key);
    }
    
    /**
     * Convert selection settings to simple array for implosing
     * @return array
     */
    function __getSelection() { 
        $items = array();

        foreach ($this->returnAllList as $table)
            $items[] = $table;

        foreach ($this->returnList as $key => $value)
            $items[] = SQL::__parseSelectItem($this->__parseTableFieldKey($key, true), $value);

        return $items;
    }
    
    /**
     * Parse single join line for table
     * @paran array $data
     * @return string
     */
    function __parseJoinTable(&$data) {
        return 'JOIN `' . $data['name'] . '` AS `' . $data['hash'] . '` ON `' . $data['hash'] . '`.`' . $data['index'] . '` = `'. $this->joinedTables[0]['hash'] . '`.`' . $data['onField'] . '`';
    }
    
    /**
     * Build query and return
     * @return string
     */
    function getQuery() {
        $q = 'SELECT ' . implode(', ', $this->__getSelection()) . " FROM `" . $this->base . '`';
        $q = $q . ' AS `' . $this->joinedTables[0]['hash'] . "` ";
        
        $join = array();
        foreach ($this->joinedTables as $key => &$t) {
            if (0 == $key)
                continue;
                
            $join[] = $this->__parseJoinTable($t);
        }
        $q = $q . implode(" ", $join);
        $q = SQL::__parseWhere($q, $this->where);
        
        return $q;
    }
    
    /**
     * Get result of query 
     * @return arrau
     */
    function getResult() {
        $q = $this->getQuery();
        $r = mysql_query($q);
        
        $return = array();
        while ($i = mysql_fetch_array($r, MYSQL_ASSOC))
            $return[] = $i;
            
        return $return;
    }

}