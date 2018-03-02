<?php


namespace Members\Model\Filters;


class Groups
{
    public $id;
    
    public $group_name;
    
    public $group_creator;
    
    public $group_description;
    
    
    public function exchangeArray($data)
    {
        $this->id                = (!empty($data['id']))                ? $data['id']                : null;
        $this->group_name        = (!empty($data['group_name']))        ? $data['group_name']        : null;
        $this->group_creator     = (!empty($data['group_creator']))     ? $data['group_creator']     : null;
        $this->group_description = (!empty($data['group_description'])) ? $data['group_description'] : null;
    }
}