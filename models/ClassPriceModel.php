<?php


require_once 'BaseModel.php';

class ClassPriceModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'classes_price';
    }
}

