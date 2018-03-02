<?php

namespace Members\Model\Interfaces;


interface StatusInterface
{
    /**
     * Posts the current status for the user
     *
     * @param string $status
     * @throws StatusException
     * @return boolean
     */
    public function postStatus($status);
    
    
    /**
     * Gets the current status of the user
     *
     * @throws StatusException
     * @return array
     */
    public function getStatus();
}