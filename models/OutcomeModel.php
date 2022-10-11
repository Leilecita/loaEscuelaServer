<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 25/11/2019
 * Time: 12:19
 */

require_once 'BaseModel.php';
class OutcomeModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = 'outcomes';
    }



    function getOutcomes($filters=array(),$paginator=array()){
        $conditions = join(' AND ',$filters);
        // $query = 'SELECT *, op.created as operation_created , op.id as op_id , i.id as item_operation_id FROM items_operation i JOIN operations op ON i.operation_id = op.id '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' group by op.id  ORDER BY operation_created DESC
        $query = 'SELECT * FROM outcomes '.( empty($filters) ?  '' : ' WHERE '.$conditions ).' ORDER BY created DESC
        LIMIT '.$paginator['limit'].' OFFSET '.$paginator['offset'];
        return $this->getDb()->fetch_all($query);

    }
}