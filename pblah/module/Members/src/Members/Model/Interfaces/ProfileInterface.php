<?php

namespace Members\Model\Interfaces;



interface ProfileInterface
{
    /**
     * Gets the user's id from the database
     * 
     * @return ResultSet|boolean
     */
    public function getUserId();
    
    
    /**
     * Gets display name for the user
     * 
     * @return string
     */
    public function getUserDisplayName();
    
    
    /**
     * Gets the location of the user
     * 
     * @return string
     */
    public function getUserLocation();
    
    
    /**
     * Gets the age of the user
     * 
     * @return integer
     */
    public function getUserAge();
    
    
    /**
     * Gets the user's bio
     * 
     * @return string
     */
    public function getUserBio();
    
    
    /**
     * Handles the editing of a user profile
     * 
     * @param array $changes
     * @throws ProfileException
     * @return boolean
     */
    public function editProfile(array $changes);
    
    
    /**
     * Removes a user profile
     * 
     * @throws ProfileException
     * @return boolean
     */
    public function removeProfile();
    
    
    /**
     * Sets the profile settings (visibility, etc)
     * 
     * @param array $settings
     * @throws ProfileException
     * @return boolean
     */
    public function profileSettings(array $settings);
    
    
    /**
     * Gets the number of profile views
     * 
     * @throws ProfileException
     * @return integer
     */
    public function profileViews();
    
    
    /**
     * Create a profile for the user
     * 
     * @param array $data
     * @throws ProfileException
     * @return boolean
     */
    public function createProfile(array $data);
    
    
    /**
     * Gets the user's profile
     * 
     * @throws ProfileException
     * @return array|boolean
     */
    public function getUserProfile();
  
    
    /**
     * Sets the user's profile to private
     * 
     * @throws ProfileException
     * @return boolean
     */
    public function makeProfilePrivate();
    
    
    /**
     * Sets the user's profile to public
     * 
     * @throws ProfileException
     * @return boolean
     */
    public function makeProfilePublic();
}