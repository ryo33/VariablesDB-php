<?php

class VarDB{

    private $con;
    private $prefix;
    private $space;

    //variable's type
    const T_INTEGER = 0, T_FLOAT = 1, T_STRING = 2, T_BOOLEAN = 3, T_ARRAY = 4;

    function __construct($con, $space, $prefix='vardb_'){
        $this->con = $con;
        $this->prefix = $prefix;
        $this->space = $space;
    }

    public function setup($ddb){
        $ddb->addTable($this->prefix . 'variable', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'space' => 'VARCHAR(32) NOT NULL',
            'name' => 'VARCHAR(32) NOT NULL',
            'type' => 'SMALLINT UNSIGNED',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'integer', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'space' => 'VARCHAR(32) NOT NULL',
            'variable_id' => 'INTEGER',
            'value' => 'INTEGER NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'float', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'space' => 'VARCHAR(32) NOT NULL',
            'variable_id' => 'INTEGER',
            'value' => 'FLOAT NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'string', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'space' => 'VARCHAR(32) NOT NULL',
            'variable_id' => 'INTEGER',
            'value' => 'VARCHAR(256) NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'boolean', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'space' => 'VARCHAR(32) NOT NULL',
            'variable_id' => 'INTEGER',
            'value' => 'BOOLEAN NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->updateDB(['drop'=>true, 'truncate'=>true]);
    }

    private function get_database($type){
        switch($type){
        case self::T_INTEGER:
            return 'integer';
        case self::T_FLOAT:
            return 'float';
        case self::T_STRING:
            return 'string';
        case self::T_BOOLEAN:
            return 'boolean';
        }
    }

    private function cast($type, $value){
        switch($type){
        case self::T_INTEGER:
            return (int)$value;
        case self::T_FLOAT:
            return (float)$value;
        case self::T_STRING:
            return $value;
        case self::T_BOOLEAN:
            return (bool)$value;
        }
    }

    private function get_type($value){
        if(is_int($value)) return self::T_INTEGER;
        else if(is_float($value)) return self::T_FLOAT;
        else if(is_bool($value)) return self::T_BOOLEAN;
        else return self::T_STRING;
    }

    private function get_variable($name){
        $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `type` FROM `' . $this->prefix . 'variable` WHERE `space` = ? AND `name` = ?', [$this->space, $name]);
        if($result['COUNT(`id`)'] === '1'){
            return $result;
        }
        return null;
    }

    function set_space($space){
        $this->space = $space;
    }

    function set($name, $value){
        $variable = $this->get_variable($name);
        $type = $this->get_type($value);
        if($variable !== null){
            if($type === (int)$variable['type']){
                $this->con->update($this->prefix . $this->get_database((int)$variable['type']), ['value' => $value],
                    '`space` = ? AND `variable_id` = ?', [$this->space, $variable['id']]);
            }else{
                $this->con->execute('DELETE FROM `' . $this->prefix . $this->get_database((int)$variable['type']) . '` WHERE `space` = ? AND `variable_id` = ?',
                    [$this->space, $variable['id']]);
                $this->con->update($this->prefix . 'variable', ['type' => $type], '`id` = ?', [$variable['id']]);
                $this->con->insert($this->prefix . $this->get_database($type),
                    ['space' => $this->space, 'variable_id' => $variable['id'], 'value' => $value]);
            }
        }else{
            $variable_id = $this->con->insert($this->prefix . 'variable', ['space' => $this->space, 'name' => $name, 'type' => $type], true);
            $this->con->insert($this->prefix . $this->get_database($type),
                ['space' => $this->space, 'variable_id' => $variable_id, 'value' => $value]);
        }
    }

    function get($name, $default=null){
        $variable = $this->get_variable($name);
        if($variable !== null){
            return $this->cast((int)$variable['type'],
                $this->con->fetchColumn('SELECT `value` FROM `' . $this->prefix . $this->get_database((int)$variable['type']) . '` WHERE `space` = ? AND `variable_id` = ?',
                [$this->space, $variable['id']]));
        }else{
            return $default;
        }
    }

    function delete($name){
        $variable = $this->get_variable($name);
        if($variable !== null){
            $this->con->execute('DELETE FROM `' . $this->prefix . 'variable` WHERE `space` = ? AND `name` = ?', [$this->space, $name]);
            $this->con->execute('DELETE FROM `' . $this->prefix . $this->get_database($variable['type']) . '` WHERE `space` = ? AND `variable_id` = ?',
                [$this->space, $variable['id']]);
            return true;
        }else{
            return false;
        }
    }

}
