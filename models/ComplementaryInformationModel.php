<?php

require_once 'BaseModel.php';

class ComplementaryInformationModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'complementary_information';
    }
}
