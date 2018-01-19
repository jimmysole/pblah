<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Adapter;


use Members\Model\Interfaces\ProfileInterface;
use Members\Model\Exceptions\ProfileException;


class ProfileModel implements ProfileInterface
{

    
    /**
     * @var TableGateway
     */
    public $gateway;

    /**
     * @var string
     */
    public $user;
    
    /**
     * @var Select
     */
    public $select;
    
    /**
     * @var Insert
     */
    public $insert;
    
    /**
     * @var Delete
     */
    public $delete;
    
    /**
     * @var Update
     */
    public $update;
    
    /**
     * @var Sql
     */
    public $sql;
    
    
    /**
     * @var array
     */
    private $profile_changes = array();
    

    /**
     * Constructor method for ProfileModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->select = new Select();
        
        $this->insert = new Insert();
        
        $this->delete = new Delete();
        
        $this->update = new Update();
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->user =  $user;
    }

    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::getUserId()
     */
    public function getUserId()
    {
        $this->select->columns(array('*'))
        ->from('members')
        ->where(array('username' => $this->user));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::getDisplayName()
     */
    public function getDisplayName()
    {
        return $this->getProfile()['display_name'];
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::getLocation()
     */
    public function getLocation()
    {
        return $this->getProfile()['location'];
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::getAge()
     */
    public function getAge()
    {
        return $this->getProfile()['age'];
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::getBio()
     */
    public function getBio()
    {
        return $this->getProfile()['bio'];
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::editProfile()
     */
    public function editProfile(array $changes)
    {
        if (count($changes, 1) > 0) {
            foreach ($changes as $key => $value) {
                $this->profile_changes[$key] = $value;
            }
            
            // proceed to update the profile information
            // that resides in the profiles table
            // locate the id in the profiles table 
            $select = $this->gateway->select(array('profile_id' => $this->getUserId()['id']));
            
            if ($select->count() > 0) {
                $rowset = array();
                
                foreach ($select as $row) {
                    $rowset[] = $row;
                }
                
                // profile found
                // update the changes now
                $updated_data = array(
                    'display_name'  => array_key_exists('display_name', $this->profile_changes)  ? rtrim($this->profile_changes['display_name'])  : $rowset['display_name'],
                    'email_address' => array_key_exists('email_address', $this->profile_changes) ? rtrim($this->profile_changes['email_address']) : $rowset['email_address'],
                    'age'           => array_key_exists('age', $this->profile_changes)           ? rtrim($this->profile_changes['age'])           : $rowset['age'],
                    'location'      => array_key_exists('location', $this->profile_changes)      ? rtrim($this->profile_changes['location'])      : $rowset['location'],
                    'bio'           => array_key_exists('bio', $this->profile_changes)           ? rtrim($this->profile_changes['bio'])           : $rowset['bio'],
                ); 
                
                $update = $this->gateway->update($updated_data, array('profile_id' => $rowset['profile_id']));
                
                if ($update > 0) {
                    return true;
                } else {
                    throw new ProfileException("Error updating your profile, please try again.");
                }
            } else {
                throw new ProfileException("User was not found.");
            }
        } else {
            throw new ProfileException("Profile changes cannot be left empty if you wish to edit your profile.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::removeProfile()
     */
    public function removeProfile()
    {
        $delete = $this->gateway->delete(array('profile_id' => $this->getUserId()['id']));
        
        if ($delete > 0) {
            return true;
        } else {
            throw new ProfileException("Error removing your profile, please try again.");
        }
    }
    
    
    public function profileSettings(array $settings)
    {
        
    }
    
    
    public function profileViews()
    {
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::createProfile()
     */
    public function createProfile(array $data)
    {
        if (count($data) > 0) {
            // assign the data to a array
            // then insert the data into the profiles table
            $profile_data = array();
            
            foreach ($data as $key => $value) {
                $profile_data[$key] = $value;
            }
            
            $insert_data = array(
                'profile_id'    => $this->getUserId()['id'],
                'display_name'  => $profile_data['display_name'],
                'email_address' => $profile_data['email_address'],
                'age'           => $profile_data['age'],
                'location'      => $profile_data['location'],
                'bio'           => $profile_data['bio'],
            );
            
            $insert = $this->gateway->insert($insert_data);
            
            if ($insert > 0) {
                // set the member table field new to zero
                $this->update->table('members')
                ->set(array('new' => 0))
                ->where(array('id' => $this->getUserId()['id']));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->update),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($query->count() > 0) {
                    // make the profile dir (for images)
                    mkdir(getcwd() . '/public/images/profile/' . $this->user);
                    mkdir(getcwd() . '/public/images/profile/' . $this->user . '/current');
                    
                    // make the htaccess file
                    // used to prevent hotlinking of images
                    $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                    
                    $file_data = "RewriteEngine on
                                  RewriteCond %{HTTP_REFERER} !^$
                                  RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$domain [NC]
                                  RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]";
                    
                    file_put_contents(getcwd() . '/public/images/profile/' . $this->user . '/.htaccess', $file_data);
                    
                    return true;
                } else {
                    throw new ProfileException("Error finalizing creation of profile...");
                }
            } else {
                throw new ProfileException("Error inserting profile data, please try again.");
            }
        } else {
            throw new ProfileException("Profile data cannot be left empty.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ProfileInterface::getProfile()
     */
    public function getProfile()
    {
        $row = $this->gateway->select(array('profile_id' => $this->getUserId()['id']));
        
        if ($row->count() > 0) {
            $rowset = array();
            
            foreach ($row as $result) {
                $rowset[] = $result;
            }
            
            return $rowset;
        } else {
            throw new ProfileException("It looks like you haven't set up a profile yet.");
        }
        
        return false;
    }
    
    
    public function makeProfilePrivate()
    {
        
    }
    
    
    public function makeProfilePublic()
    {
        
    }
}