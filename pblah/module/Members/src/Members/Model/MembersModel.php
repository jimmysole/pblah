<?php

namespace Members\Model;

use Members\Model\Classes\Members;


class MembersModel extends Members
{
    public function postCurrentStatus(array $data)
    {
       return parent::postStatus($data['status']);
    }
}