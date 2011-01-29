<?php

/**
 * Handle MySQL actions
 * @package SimplePHP
 * @author Sebastian Müller
 * @version 0.2
 * @link http://github.com/semu/SimplePHP
 */
class SQL {
    
    /**
     * construct is not used
     */
    function __construct() {
        return null;
    }
    
    /**
     * Handle SQL query
     * edit this for custom sql handling
     * @param string $query
     * @return sql result
     */
    static function __handleQuery($query){
        if (defined('SQL_DEBUG')) {
            G::debug($query);
        }
        
        return mysql_query($query);
    }
    
    /**
     * Escape data
     * @param array $data
     * @return array
     */
    static function __escapeData($data) {
        $keys = array_map('mysql_real_escape_string', array_keys($data));
        $data = array_map('mysql_real_escape_string', $data);
        return array('keys' => $keys, 'data' => $data);
    }
    
    /**
     * Escape string
     * @param string $string
     * @return string
     */
    static function __escapeString($string) {
        $string = mysql_real_escape_string($string);
        return $string;
    }
    
    /**
     * Parse where
     * @param string $query
     * @param array $where
     * @return string query
     */
    static function __parseWhere($query, $where = array()) {
        if (count($where) == 0) {
            return $query;
        }
        
        $whereOptions = array();
        
        foreach ($where as $key => $value) {
            $tmpKey = self::__escapeTableField($key);
            
            if (substr($value, 0, 1) == '!' && stristr($value, '%')) {
                $whereOptions[] = $tmpKey . ' NOT LIKE \'' . substr($value, 1) . '\'';
            } elseif (substr($value, 0, 1) == '!') {
                $whereOptions[] = $tmpKey . ' != ' . (int)substr($value, 1);
            } elseif ($value === null || $value == 'NULL') {
                $whereOptions[] = $tmpKey . ' IS NULL';
            } elseif (substr($value, 0, 3) == 'IN ') {
                $whereOptions[] = $tmpKey . ' ' . $value;
            } elseif (substr($value, 0, 2) == '>=') {
                $whereOptions[] = $tmpKey . ' >= ' . (int)substr($value, 2);
            } elseif (substr($value, 0, 1) == '>') {
                $whereOptions[] = $tmpKey . ' > ' . (int)substr($value, 1);
            } elseif (substr($value, 0, 2) == '<=') {
                $whereOptions[] = $tmpKey . ' <= ' . (int)substr($value, 2);
            } elseif (substr($value, 0, 1) == '<' && is_int(substr($value, 1))) {
                $whereOptions[] = $tmpKey . ' < ' . substr($value, 1);
            } elseif (stristr($value, '*')) {
                $whereOptions[] = $tmpKey . ' LIKE \'' . self::__escapeString(str_replace('*', '%', $value)) . '\'';
            } else {
                $whereOptions[] = $tmpKey . ' = \'' . self::__escapeString($value) . '\'';
            }
        }
        
        return $query . '
        WHERE ' . implode(' AND ', $whereOptions);
    }
    
    /**
     * Try to escape table field as good as possible
     * @param string $key
     * @return string
     */
    static function __escapeTableField($key) {
        if (stristr($key, '.') && stristr($key, '(')) {
            return str_replace(array('.', '(', ')'), array('`.`', '(`', '`)'), $key);
        } elseif (stristr($key, '(') && !stristr($key, '.')) {
            return str_replace(array('(', ')'), array('(`', '`)'), $key);
        } elseif (stristr($key, '.')) {
            return '`' . str_replace('.', '`.`', $key) . '`';
        }
        
        return '`' . $key . '`';
    }
    
    /**
     * Create select statement
     * @param string $key
     * @param string $alias
     * @return string
     */
    static function __parseSelectItem($key, $alias) {
        return self::__escapeTableField(self::__escapeString($key)) . ' as \'' . self::__escapeString($alias) . '\'';
    }
    
    /**
     * Parse ORDER BY input
     * @param array $order
     * @return string
     */
    static function __parseOrder($order) {
        if (!is_array($order) || count($order) == 0) {
            return null;
        }
        
        $str = ' ORDER BY ';
        $orderArray = array();
        
        foreach ($order as $key => $direction) {
            $orderArray[] = self::__escapeString($key) . ' ' . self::__parseOrderDirection($direction);
        }
        
        return $str . implode(', ', $orderArray);
    }
    
    /**
     * Parse ORDER BY direction
     * @param string $str
     * @return string
     */
    static function __parseOrderDirection($str) {
        if ($str == 'D' || $str == 'DESC') {
            return 'DESC';
        }
        
        return 'ASC';
    }
    
    /**
     * Select all lines as array by query
     * @param string $q
     * @return array
     */
    static function __allLinesAsArray($q) {
        $result = self::__handleQuery($q);
        $return = array();
        
        while ($item = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $return[] = $item;
        }
        
        return $return;
    }
    
    /**
     * Check if table is known
     * @param string $table
     * @return bool
     */
    public static function hasTable($table) {
        $result = self::__handleQuery('SHOW TABLES LIKE \'' . $table . '\' ');
        
        if (mysql_num_rws($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Check if table has given column
     * @param string $table
     * @param string $column
     * @return bool
     */
    public static function hasColumn($table, $column) {
        $result = self::__handleQuery('SHOW COLUMNS
        FROM \'' . $table . '\' ');
        
        while ($item = mysql_fetch_array($result, MYSQL_ASSOC)) {
            if($item['Field'] == $column) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if table has Index
     * @param string $table
     * @param string $name
     * @return bool
     **/
    public static function hasIndex($table, $name) {
        $result = mysql_query('SHOW INDEX
        FROM \'' . $table . '\' ');
        
        while ($item = mysql_fetch_array($result, MYSQL_ASSOC)) {
            if ($item['Column_name'] == $name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get available values for enumeration column
     * @param string $table
     * @param string $column
     * @return array
     */
    static function getEnumValues($table, $column) {
        $return = array();
        $result = self::__handleQuery('SHOW COLUMNS
        FROM `' . mysql_real_escape_string($table) . '` LIKE \'' . mysql_real_escape_string($col) . '\' ');
        $data = mysql_fetch_array($result);
        $enum = $data['Type'];
        preg_match_all('/\'(.*?)\'/', $enum, $pregData);
        return $pregData[1];
    }
    
    /**
     * Insert row and replace if needed
     * @param string $table
     * @param array $data
     * @param array $keys
     */
    static function insertAndReplace($table, $data, $keys) {
        $d = self::__escapeData($data);
        $k = self::__escapeData($keys);
        $keys = array();
        
        foreach ($k['data'] as $key => $data) {
            $keys[] = '`' . $key . '` = \'' . $data . '\'';
        }
        
        self::__handleQuery('INSERT INTO `' . $table . '` (`' . implode('`, `', $d['keys']) . '`)
        VALUES (\'' . implode('\', \'', $d['data']) . '\') ON DUPLICATE KEY UPDATE ' . implode(', ', $keys));
    }
    
    /**
     * Replaces data in table - data must contain all columns of table
     * @param string $table
     * @param array $data
     */
    static function replaceInto($table, $data) {
        $updates = array();
        
        foreach (array_keys($data) as $fieldname) {
            $updates[] = '`' . mysql_real_escape_string($fieldname) . '` = \'' . mysql_real_escape_string($data[$fieldname]) . '\'';
        }
        
        return self::__handleQuery('REPLACE INTO `' . $table . '` SET ' . implode(', ', $updates));
    }
    
    /**
     * Update rows in table
     * @param string $table
     * @param array $data update
     * @param array $where
     * @return mixed
     */
    static function update($table, $data, $where = array()) {
        $update = array();
        
        foreach (array_keys($data) as $fieldname) {
            $update[] = '`' . self::__escapeString($fieldname) . '` = \'' . self::__escapeString($data[$fieldname]) . '\'';
        }
        
        self::__handleQuery(self::__parseWhere('UPDATE `' . $table . '` SET ' . implode(', ', $update), $where));
        return true;
    }
    
    /**
     * Get column names from table
     * @param string $table
     * @return array
     */
    static function getColumns($table) {
        $result = self::__handleQuery('SHOW COLUMNS
        FROM `' . mysql_real_escape_string($table) . '`');
        $return = array();
        
        while ($item = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $return[] = $item['Field'];
        }
        
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
        
        foreach ($d['data'] as $key => $data) {
            $keys[] = '`' . $key . '` LIKE \'' . $data . '\'';
        }
        
        return self::__allLinesAsArray('SELECT  *
        FROM `' . self::__escapeString($table) . '`
        WHERE ' . implode(' OR ', $keys));
    }
    
    /**
     * Get single field from table
     * @param string $table
     * @param string $field
     * @param array $where
     * @param array $order
     * @return string
     */
    static function getField($table, $field, $where = array(), $order = array()) {
        $result = self::__handleQuery(self::__parseWhere('SELECT `' . self::__escapeString($field) . '`
        FROM `' . $table . '` ', $where) . self::__parseOrder($order));
        
        if (mysql_num_rows($result) == 0) {
            return null;
        }
        
        return mysql_result($result, 0, $field);
    }
    
    /**
     * Get all values for single column
     * @param string $table
     * @param string $columnname
     * @param array $where
     * @param array $order
     * @return array
     */
    static function getColumn($table, $columnname, $where = array(), $order = array()) {
        $data = self::__allLinesAsArray(self::__parseWhere('SELECT ' . $columnname . '
        FROM `' . $table . '` ', $where) . self::__parseOrder($order));
        $result = array();
        
        foreach ($data as $item) {
            $result[$item[$columnname]] = $item[$columnname];
        }
        
        return $result;
    }
    
    /**
     * Wrapper for @self::getColumn
     *
     * Get all values for single column
     * @param string $table
     * @param string $columnname
     * @param array $where
     * @param array $order
     * @return array
     */
    static function getCol($table, $columnname, $where = array(), $order = array()) {
        return self::getColumn($table, $columnname, $where, $order);
    }
    
    /**
     * Insert line into table
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function newLine($table, $data) {
        $d = self::__escapeData($data);
        self::__handleQuery('INSERT INTO `' . $table . '` (`' . implode('`, `', $d['keys']) . '`)
        VALUES (\'' . implode('\', \'', $d['data']) . '\') ');
        return mysql_insert_id();
    }
    
    /**
     * Insert line into table (wrapper for newLine)
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function addLine($table, $data) {
        return self::newLine($table, $data);
    }
    
    /**
     * Insert line into table (wrapper for newLine)
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function newRow($table, $data) {
        return self::newLine($table, $data);
    }
    
    /**
     * Insert line into table  (wrapper for newLine)
     * @param string $table
     * @param array $data
     * @return mixed
     */
    static function addRow($table, $data) {
        return self::newLine($table, $data);
    }
    
    /**
     * Insert array of lines into table
     * @param string $table
     * @param array $lines
     */
    static function addArrayOfLines($table, $lines) {
        // TODO: stop sending each line as a single query
        foreach ($lines as $line) {
            self::newLine($table, $line);
        }
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
     * Get simple from table by where
     * @param string $func
     * @param string $table
     * @param array $where
     * @param array $order
     * @return strin
     */
    static function getFunction($func, $table, $where = array(), $order = array()) {
        return self::getField($table, $func, $where, $order);
    }
    
    /**
     * Get single line from table
     * @param string $table
     * @param array $where
     * @param array $order
     * @return array
     */
    static function getLine($table, $where = array(), $order = array()) {
        $query = self::__parseWhere('SELECT  *
        FROM `' . $table . '` ', $where) . self::__parseOrder($order);
        return mysql_fetch_array(self::__handleQuery($query), MYSQL_ASSOC);
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
        $result = self::__handleQuery(self::__parseWhere('SELECT count(' . self::getFirstColumn($table) . ')
        FROM `' . $table . '` ', $where));
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
     * @param array $order
     * @return array
     */
    static function getLines($table, $where = array(), $order = array()) {
        return self::__allLinesAsArray(self::__parseWhere('SELECT  *
        FROM `' . $table . '` ', $where) . self::__parseOrder($order));
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
        return self::__handleQuery(self::__parseWhere('DELETE
        FROM `' . $table . '` ', $where));
    }
    
    /**
     * Select rows from table with given offset
     * @param string $table
     * @param array $where
     * @param int $offset
     * @param int $limit
     */
    static function getLinesWithOffset($table, $where, $offset = 0, $limit = 50000) {
        return self::__allLinesAsArray(self::__parseWhere('SELECT  *
        FROM `' . $table . '` ', $where) . '
        LIMIT ' . (int)$offset . ',' . (int)$limit);
    }
    
    /**
     * Get all lines from table where field is in array of data
     * @param string $table
     * @param string $field
     * @param array $inArray
     * @return array
     */
    static function getLinesWhereIn($table, $field, $inArray) {
        return self::__allLinesAsArray('SELECT  *
        FROM `' . mysql_real_escape_string($table) . '`
        WHERE `' . mysql_real_escape_string($field) . '` IN (\'' . implode('\', \'', $inArray) . '\') ');
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