<?php
namespace Application\Model\Storage;

use Zend\Authentication\Storage\Session;


class LoginAuthStorage extends Session
{
    /**
     * Sets the time for the user to be remembered after login
     * @param number $default
     * @param number $time
     * @return void
     */
    public function rememberUser($default = 0, $time = 1209600)
    {
        if ($default == 1) {
            $this->session->getManager()->rememberMe($time);
        } else if ($default == 0) {
            $this->session->getManager()->rememberMe(0);
        }
    }


    /**
     * Destroys the session information
     * @return void
     */
    public function forgetUser()
    {
        $this->session->getManager()->forgetMe();
    }
}