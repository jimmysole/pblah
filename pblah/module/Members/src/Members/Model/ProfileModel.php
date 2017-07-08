<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;

use Members\Model\Classes\Profile;
use Members\Model\Classes\PhotoAlbum;



class ProfileModel extends Profile
{

    /**
     * @var TableGateway
     */
    protected $gateway;


    /**
     * Constructor method for ProfileModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;

        parent::getTableGateway($this->gateway);
        parent::getSQLClass($this->gateway->getAdapter());
        parent::setUser($user);
    }


    /**
     * Checks if a profile has been set
     * @return boolean
     */
    public function checkIfProfileSet()
    {
        // check if a user has set a profile
        // used only if a member has just completed the verification process
        $get_user = parent::getUserId();

        if ($get_user['new'] == 1) {
            // new member, hasn't set up profile yet
            // return false
            return false;
        }

        return true;
    }


    /**
     * Creates the profile for the user
     * @param array $data
     * @return boolean
     */
    public function makeProfile(array $data)
    {

        if (parent::createProfile($data) !== false) {
            return true;
        }

        return false;
    }


    /**
     * Gets the user's display name
     * @return string
     */
    public function getUserDisplayName()
    {
        return parent::getDisplayName();
    }


    /**
     * Gets the user's location
     * @return string
     */
    public function getUserLocation()
    {
        return parent::getLocation();
    }


    /**
     * Gets the user's age
     * @return number
     */
    public function getUserAge()
    {
        return parent::getAge();
    }


    /**
     * Gets the user profile
     * @return ArrayObject|NULL
     */
    public function getUserProfile()
    {
        return parent::getProfile();
    }


    /**
     * Gets the user's bio
     * @return string
     */
    public function getUserBio()
    {
        return parent::getBio();
    }


    /**
     * Performs any edits on the user's profile
     * @param array $changes
     * @return boolean
     */
    public function editUserProfile(array $changes)
    {
        if (parent::editProfile($changes) !== false) {
            return true;
        }

        return false;
    }
    
    
    /**
     * Makes a photo album
     * @param mixed $album_name
     * @param array $album_photos
     * @param string $location
     * @return boolean
     */
    public function makePhotoAlbum($album_name, array $album_photos, $location = "")
    {
        $photo_album = new PhotoAlbum($album_name, $album_photos, $location);
        
        return $photo_album->createAlbum();
    }
    
    
    /**
     * Deletes a photo album 
     * @param mixed $album_name
     * @return boolean
     */
    public function deletePhotoAlbum($album_name)
    {
        $photo_album = new PhotoAlbum($album_name, array());
        
        return $photo_album->deleteAlbum();
    }
    
    
    public function getPhotoAlbums($album_name)
    {
        $photo_album = new PhotoAlbum($album_name, array());
        
        return $photo_album->getAlbums();
    }
}