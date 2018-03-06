<?php

namespace Members\Model\Interfaces;

interface StatusInterface
{
    /**
     * Posts the current status for the user
     * 
     * @param string $status
     * @param array $image
     * @throws StatusException
     * @return bool
     */
    public function postStatus($status, array $image = array());
    
    
    /**
     * Gets the current status of the user
     *
     * @throws StatusException
     * @return array
     */
    public function getStatus();
}