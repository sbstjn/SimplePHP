<?php

/**
 * Handle MySQL actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.1
 * @link http://github.com/hazelcode/SimplePHP
 */
 
class SQL {

    /**
     * construct is not used
     */
    public function __construct() {
    
    }
    
    /**
     * Handle SQL query
     * edit this for custom sql handling
     * @param string $query
     * @return mixed sql result
     */
    private static function __handleQuery($query) {
        return mysql_query($query);
    }
    
    /**
     * Escape data
     * @param array $data
     * @return array
     */
    private static function __escapeData($data) {
        $keys = array_map('mysql_real_escape_string', array_keys($data));
        $data = array_map('mysql_real_escape_string', $data);
        
        return array('keys' => $keys, 'data' => $data);    
    }
    
    /**
     * Escape string
     * @param string $string
     * @return string
     */
    private static function __escapeString($string) {
        $string = mysql_real_escape_string($string);
        
        return $string;
    }
    
    /**
     * Parse where
     * @param string $query
     * @param array $where key => value
     * @return string query
     */
    private static function __parseWhere($query, $where = array()) {
        if (count($where) == 0)
            return $query;
            
        $whereOptions = array();
        
        foreach ($where as $key => $value) {
            if (stristr($value, '*')) {
                $whereOptions[] = '`' . self::__escapeString($key) . '` LIKE \'' . self::__escapeString(str_replace('*', '%', $value)) . '\'';
            } else {
                $whereOptions[] = '`' . self::__escapeString($key) . '` = \'' . self::__escapeString($value) . '\'';
            }
        }
        
        return $query . ' WHERE ' . implode(' AND ', $whereOptions);
    }    
    
    /**
     * Select all lines as array by query
     * @param string $q
     * @return array
     */
    private static function __allLinesAsArray($q) {
        $result = self::__handleQuery($q);        
        $return = array();
        
        while ($item = mysql_fetch_array($result, MYSQL_ASSOC))
            $return[] = $item;
            
        return $return;
    }
    
    /**
     * Insert row and replace if needed
     * @param string $table
     * @param array $data
     */
    static function insertAndReplace($table, $data) {
        $d = self::__escapeData($data);        
        
        $keys = array();
        foreach ($d['data'] as $key => $data) 
            $keys[] = '`'.$key.'` = \''.$data.'\'';
        
        self::__handleQuery('INSERT INTO `' . $table . '` (`' . implode('`, `', $d['keys']) . '`) VALUES (\'' . implode("', '", $d['data']) . '\') ON DUPLICATE KEY UPDATE ' . implode(', ', $keys) . ';');
    }    
    
    /**
     * Insert array of lines into table (wrapper for addArrayOfLines)
     * @param string $table
     * @param array $lines
     */	
    static function addArrayOfRows($table, $lines) {
        return self::AddArrayOfLines($table, $lines);
    }
    
    /**
     * Update rows in table
     * @param string $table
     * @param array $update
     * @param array $where
     * @return mixed
     */
    static function update($table, $data, $where = array()) {
        $update = array();

        foreach(array_keys($data) as $fieldname)
            $update[] = '`' . self::__escapeString($fieldname) . '` = \'' . self::__escapeString($data[$fieldname]) . '\'';
            
        self::__handleQuery(self::__parseWhere('UPDATE `' . $table . '` SET ' . implode(', ', $update), $where));
        return true;
    }    
    
    /**
     * Get column names from table
     * @param string $table
     * @return array
     */
    static function getColumns($table) {
        $result = self::__handleQuery('SHOW COLUMNS FROM `' . mysql_real_escape_string($table) . '`');
        $return = array();
        
        while ($item = mysql_fetch_array($result, MYSQL_ASSOC))
            $return[] = $item['Field'];
        
        return $return;
    }

    /**
     * Get first column name from table
     * @param string $table
     * @return array
     */
    static function getFirstColumn($table) {
        return array_shift(self::getColumns($table));
    }

    /**
     * Search in table 
     * @param string $table
     * @param array $search
     * @return array
     */
    static function search($table, $search) {
        $d = self::__escapeData($search);        
        
        $keys = array();
        foreach ($d['data'] as $key => $data) 
            $keys[] = '`'.$key.'` LIKE \''.$data.'\'';
        
        return self::__allLinesAsArray('SELECT * FROM `' . self::__escapeString($table) . '` WHERE ' . implode(' OR ', $keys));
    }
    
    /**
     * Get single field from table
     * @param string $table
     * @param string $field
     * @param array $where
     * @return string
     */
    static function getField($table, $field, $where = array()) {
        $result = self::__handleQuery(self::__parseWhere('SELECT `' . self::__escapeString($field) . '` FROM `' . $table . '` ', $where));
        
        if (mysql_num_rows($result) == 0)
            return null;
            
        return mysql_result($result, 0, $field);
    }
    
    /**
     * Insert line into table
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function newLine($table, $data) {
        $d = self::__escapeData($data);        
        
        self::__handleQuery('INSERT INTO `' . $table . '` (`' . implode('`, `', $d['keys']) . '`) VALUES (\'' . implode('\', \'', $d['data']).'\') ');
        return mysql_insert_id();
    }
    
    /**
     * Insert line into table (wrapper for newLine)
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function addLine($table, $data) {
        return self::newLine($table, $data, $return);
    }
    
    /**
     * Insert line into table (wrapper for newLine)
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function newRow($table, $data) {
        return self::newLine($table, $data, $return);
    }
    
    /**
     * Insert line into table  (wrapper for newLine)
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function addRow($table, $data) {
        return self::newLine($table, $data, $return);
    }	
    
    /**
     * Insert array of lines into table
     * @param string $table
     * @param array $lines
     */	
    static function addArrayOfLines($table, $lines) {
        // TODO: stop sending each line as a single query
        
        foreach ($table as $line)
            self::newLine($table, $line);
    }
    
    /**
     * Get single line from table
     * @param string $table
     * @param array $where
     * @return array
     */
    static function getLine($table, $where = array()) {
        return mysql_fetch_array(self::__handleQuery(self::__parseWhere('SELECT * FROM `' . $table . '` ', $where)), MYSQL_ASSOC);
    }
    
    /**
     * Get single line from table (wrapper for getLine)
     * @param string $table
     * @param array $where
     * @return array
     */    
    static function getSingleLine($table, $where = array()) {
        return self::getLine($table, $where);
    } 

    /**
     * Get single line from table (wrapper for getLine)
     * @param string $table
     * @param array $where
     * @return array
     */        
    static function getRow($table, $where = array()) {
        return self::getLine($table, $where);
    }
       
    /**
     * Get single line from table (wrapper for getLine)
     * @param string $table
     * @param array $where
     * @return array
     */    
    static function getSingleRow($table, $where = array()) {
        return self::getLine($table, $where);
    }
    
    /**
     * Count rows in table
     * @param string $table
     * @param array $where
     * @return int
     */
    static function rows($table, $where = array()) {
        $result = self::__handleQuery(self::__parseWhere('SELECT count(' . self::getFirstColumn($table) . ') FROM `' . $table . '` ', $where));
        return mysql_result($result, 0, 0);
    }
    
    /**
     * Count rows in table (wrapper for rows)
     * @param string $table
     * @param array $where
     * @return int
     */
    static function lines($table, $where = array()) {
        return self::rows($table, $where);
    }
    
    /**
     * Count rows in table (wrapper for rows)
     * @param string $table
     * @param array $where
     * @return int
     */
    static function countRows($table, $where = array()) {
        return self::rows($table, $where);
    }
    
    /**
     * Count rows in table (wrapper for rows)
     * @param string $table
     * @param array $where
     * @return int
     */
    static function countLines($table, $where = array()) {
        return self::rows($table, $where);
    }  
    
    /**
     * Get all lines from table (wrapper for getLines)
     * @param string $table
     * @param array $where
     * @return array
     */   
    static function getLines($table, $where = array()) {
        return self::__allLinesAsArray(self::__parseWhere('SELECT * FROM `' . $table . '` ', $where));
    }
    
    /**
     * Get all lines from table (wrapper for getLines)
     * @param string $table
     * @param array $where
     * @return array
     */
    static function getAllLines($table, $where = array()) {
        return self::getLines($table, $where);
    }
    
    /**
     * Get all lines from table (wrapper for getLines)
     * @param string $table
     * @param array $where
     * @return array
     */
    static function getRows($table, $where = array()) {
        return self::getLines($table, $where);
    }
    
    /**
     * Get all lines from table (wrapper for getLines)
     * @param string $table
     * @param array $where
     * @return array
     */
    static function getAllRows($table, $where = array()) {
        return self::getLines($table, $where);
    }    
    
    /**
     * Delete rows from table
     * @param string $table
     * @param array $where
     */
    static function remove($table, $where = array()) {
        return self::__handleQuery(self::__parseWhere('DELETE FROM `' . $table . '` ', $where));
    }    

    /**
     * Delete rows from table
     * @param string $table
     * @param array $where
     */
    static function removeRows($table, $where = array()) {
        self::remove($table, $where);
    }
        
    /**
     * Delete rows from table
     * @param string $table
     * @param array $where
     */
    static function removeLines($table, $where = array()) {
        self::remove($table, $where);
    }
    
    /**
     * Delete rows from table
     * @param string $table
     * @param array $where
     */
    static function delete($table, $where = array()) {
        self::remove($table, $where);
    }
    
    /**
     * Delete rows from table
     * @param string $table
     * @param array $where
     */
    static function deleteRows($table, $where = array()) {
        self::remove($table, $where);
    }
    
    /**
     * Delete rows from table
     * @param string $table
     * @param array $where
     */
    static function deleteLines($table, $where = array()) {
        self::remove($table, $where);
    }
    
}

?>