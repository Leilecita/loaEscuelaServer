<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 30/10/2019
 * Time: 10:14
 */

include __DIR__ . '/../config/config.php';
require __DIR__ . '/../libs/dbhelper.php';

use vielhuber\dbhelper\dbhelper;

abstract class BaseModel
{
    protected $tableName  = '';
    private $db;

    function __construct(){
        global $DBCONFIG;
        $this->db = new dbhelper();
        //$this->db->connect('pdo', 'mysql', '127.0.0.1', 'root', null, 'loa', 3306);
        $this->db->connect('pdo', 'mysql', $DBCONFIG['HOST'], $DBCONFIG['USERNAME'], $DBCONFIG['PASSWORD'],$DBCONFIG['DATABASE'],$DBCONFIG['PORT']);
    }

    function findById($id){
        return $this->db->fetch_row('SELECT * FROM '.$this->tableName.' WHERE id = ?',$id);
    }

    function findAll($filters=array(),$paginator=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->db->fetch_all($query);
    }

    function findAllTop($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC LIMIT 10';
        return $this->db->fetch_all($query);
    }

    function find($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC';
        return $this->db->fetch_row($query);
    }

    public function getDb(){
        return $this->db;
    }

    function save($data){
        return $this->db->insert($this->tableName, $data );
    }

    function update($id, $data){
        return  $this->db->update($this->tableName, $data,['id' => "$id"]);
    }

    function delete($id){
        return ($this->db->delete($this->tableName, ['id' => $id]) == 1);
    }
}