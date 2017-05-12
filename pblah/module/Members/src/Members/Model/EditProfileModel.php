<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;

use Members\Model\Classes\Profile;


class EditProfileModel
{
    public $table_gateway;


    public function __construct(TableGateway $gateway)
    {
        $this->table_gateway = $gateway instanceof TableGateway ? $this->table_gateway = $gateway : null;
    }


    public function checkIfProfileEmpty()
    {
        try {
           Profile::getUserId();
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }
    }
}