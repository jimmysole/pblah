<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class AddPhotos
{
    const ADD_PHOTO_ALBUM_SUCCESS = 'Your photo album was successfully added to.';
    
    const ADD_PHOTO_ALBUM_FAILURE = 'Error adding photos to your photo album.';
    
    
    /**
     * @var string
     */
    public $album_name;
    
    
    /**
     * 
     * @var string
     */
    public $other_album;
    
    
    /**
     * @var array
     */
    public $photos = array();
    
    
    /**
     * Constructor
     * @param mixed $album_name
     * @param array $photos
     * @throws PhotoAlbumException
     */
    public function __construct($album_name, array $photos, $other_album = "")
    {
        try {
            if (!empty($album_name) && count($photos, 1) > 0) {
                $this->album_name = $album_name;
                
                foreach ($photos as $key => $value) {
                    $this->photos[$key] = $value;
                }
                
                if ($other_album != "") {
                    $this->other_album = $other_album;
                }
            } else {
                throw new PhotoAlbumException("Invalid photo album and/or photos provided, please fix this and try again.");
            }
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }
    
    
    /**
     * Adds photo(s) to an album
     * @throws PhotoAlbumException
     * @return string
     */
    public function addPhotos()
    {
        // first check to see if the album selected to add photos to is another album
        // for example, a user wants to add photos from album 1 to album 2
        if (@is_dir(getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->other_album)) {
            // copy the selected files to the other directory
            $files = explode(" ", implode(" ", $this->photos));
            
            foreach ($files as $copy) {
                if (!copy(getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->album_name . '/' . $copy, 
                    getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->other_album . '/' . $copy)) {
                    // error. move on to next element
                    continue;
                } 
            }
            
            return self::ADD_PHOTO_ALBUM_SUCCESS;
        } else {
            // no other album was selected
            // continue with adding photos to the selected album only
            foreach ($this->photos as $value) {
                if (is_uploaded_file($value[Profile::getUser()]['tmp_name'])) {
                    move_uploaded_file($value[Profile::getUser()]['tmp_name'],
                        getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->album_name . '/' . $value[Profile::getUser()]['name']);
                } else {
                    throw new PhotoAlbumException(self::ADD_PHOTO_ALBUM_FAILURE);
                }
            }
            
            return self::ADD_PHOTO_ALBUM_SUCCESS;
        }
    }
}