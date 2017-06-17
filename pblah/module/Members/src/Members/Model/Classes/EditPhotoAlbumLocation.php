<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class EditPhotoAlbumLocation
{
    const EDIT_PHOTO_ALBUM_LOCATION_SUCCESS = 'Your photo album\'s location was edited successfully.';
    
    const EDIT_PHOTO_ALBUM_LOCATION_FAILURE = 'Error editing your photo album\'s location.';
    
    
    /**
     * @var mixed
     */
    public $album_name;
    
    
    /**
     * Constructor
     * @param mixed $album_name
     * @throws PhotoAlbumException
     */
    public function __construct($album_name)
    {
        try {
            if (!empty($album_name)) {
                $this->album_name = $album_name;
            } else {
                throw new PhotoAlbumException("Invalid photo album provided, please choose a valid one and try again.");
            } 
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }
}