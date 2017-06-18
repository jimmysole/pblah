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
     * @var string
     */
    public $location;
    
    
    /**
     * @var string
     */
    public $new_location;
    
    
    /**
     * Constructor
     * @param mixed $album_name
     * @param string $location
     * @throws PhotoAlbumException
     */
    public function __construct($album_name, $location)
    {
        try {
            if (!empty($album_name) && !empty($location)) {
                $this->album_name = $album_name;
                $this->location   = $location;
            } else {
                throw new PhotoAlbumException("Invalid photo album and/or location provided, please choose a valid one and try again.");
            } 
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }
    
    
    /**
     * Changes the location for a photo album
     * @param string $new_location
     * @throws PhotoAlbumException
     * @return string
     */
    public function changeLocation($new_location)
    {
        if (!empty($new_location)) {
            $this->new_location = $new_location;
            
            // scan for the file ($this->location as the filename)
            // if found, rename to $this->new_location and insert the new location into the file
            // if not found, throw PhotoAlbumException
            if (file_exists($this->location)) {
                $fp = @fopen($this->location, "w");
                
                if (is_resource($fp)) {
                    // write the value of $this->new_location
                    // into the file
                    if (@fwrite($fp, $this->new_location) > 0) {
                        @fclose($fp);
                        
                        return self::EDIT_PHOTO_ALBUM_LOCATION_SUCCESS;
                    } else {
                        // couldn't write the location to the file :(
                        @fclose($fp);
                        throw new PhotoAlbumException(self::EDIT_PHOTO_ALBUM_LOCATION_FAILURE);
                    }
                } else {
                    throw new PhotoAlbumException("Error locating the location tagged, perhaps it was removed/changed already?");
                }
            }
        } else {
            throw new PhotoAlbumException("Cannot change your photo album's location - no value provided.");
        }
    }
}