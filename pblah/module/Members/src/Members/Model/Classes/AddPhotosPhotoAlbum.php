<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class AddPhotosPhotoAlbum
{
    const ADD_PHOTO_ALBUM_SUCCESS = 'Your photo album was successfully added to.';
    
    const ADD_PHOTO_ALBUM_FAILURE = 'Error adding photos to your photo album.';
    
    
    /**
     * @var mixed
     */
    public $album_name;
    
    
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
    public function __construct($album_name, array $photos)
    {
        try {
            if (!empty($album_name) && count($photos, 1) > 0) {
                $this->album_name = $album_name;
                
                foreach ($photos as $key => $value) {
                    $this->photos[$key] = $value;
                }
            } else {
                throw new PhotoAlbumException("Invalid photo album and/or photos provided, please fix this and try again.");
            }
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }
}