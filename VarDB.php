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

    function setup(){
        $ddb = new DiffDB($this->con);
        $ddb->addTable($this->prefix . 'variable', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'name' => 'VARCHAR(32) NOT NULL',
            'type' => 'SMALLINT UNSIGNED',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'integer', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'variable_id' => 'INTEGER',
            'integer' => 'INTEGER NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'float', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'variable_id' => 'INTEGER',
            'float' => 'FLOAT NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'string', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'variable_id' => 'INTEGER',
            'string' => 'VARCHAR(256) NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->addTable($this->prefix . 'boolean', [
            'id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
            'variable_id' => 'INTEGER',
            'boolean' => 'BOOLEAN NULL',
            'created' => 'INTEGER'
        ]);
        $ddb->updateDB(['drop'=>true, 'truncate'=>true]);
    }

}
