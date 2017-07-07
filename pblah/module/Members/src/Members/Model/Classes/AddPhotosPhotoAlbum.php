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
    
    
    /**
     * Adds photo(s) to an album
     * @throws PhotoAlbumException
     * @return string
     */
    public function addPhotos()
    {
        if (is_uploaded_file($this->photos['file']['tmp_name'])) {
            foreach ($this->photos['file']['error'] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $file_name = basename($this->photos['file']['name'][$key]);
                    
                    if (move_uploaded_file($this->photos['file']['tmp_name'], getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->album_name . '/' . $file_name)) {
                        return self::ADD_PHOTO_ALBUM_SUCCESS;
                    } else {
                        throw new PhotoAlbumException(self::ADD_PHOTO_ALBUM_FAILURE);
                    }
                } else {
                    throw new PhotoAlbumException($this->photos['file']['error']);
                }
            }
        } else {
            throw new PhotoAlbumException(self::ADD_PHOTO_ALBUM_FAILURE);
        }
    }
}