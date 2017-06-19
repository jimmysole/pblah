<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class EditPhotoAlbumPhotos
{
    const EDIT_PHOTO_ALBUM_SUCCESS = 'Your photo album was successfully edited.';
    
    const EDIT_PHOTO_ALBUM_FAILURE = 'Error editing your photo album.';
    
    
    /**
     * @var mixed
     */
    public $album_name;
    
    
    /**
     * @var array
     */
    public $edits = array();
    
    
    /**
     * Constructor
     * @param mixed $album_name
     * @param array $edits
     * @throws PhotoAlbumException
     */
    public function __construct($album_name, array $edits)
    {
        try {
            if (!empty($album_name) && count($edits, 1) > 0) {
                $this->album_name = $album_name;
                
                foreach ($edits as $key => $value) {
                    $this->edits[$key] = $value;
                }
            } else {
                throw new PhotoAlbumException("Invalid photo album and/or edits provided, please fix this and try again.");
            }
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }
}