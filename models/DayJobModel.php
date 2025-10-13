<?php

require_once 'BaseModel.php';

class DayJobModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'day_jobs';
    }

    function findAllAll($filters=array()){
        $conditions = join(' AND ',$filters);
        $query = 'SELECT * FROM '.$this->tableName .( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY category ASC';
        return $this->getDb()->fetch_all($query);
    }
}
