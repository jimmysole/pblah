<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class EditPhotoAlbumName 
{
    final const EDIT_PHOTO_ALBUM_NAME_SUCCESS  = 'Your photo album\'s name was edited successfully.';
    
    final const EDIT_PHOTO_ALBUM_NAME_FAILURE = 'Error editing your photo album\'s name.';
    
    
    /**
     * @var mixed
     */
    public $current_album_name;
    
    
    /**
     * @var mixed
     */
    public $new_album_name;
    
    
    /**
     * Constructor
     * @param mixed $current_album_name
     * @param mixed $new_album_name
     * @throws PhotoAlbumException
     */
    public function __construct($current_album_name, $new_album_name)
    {
        $this->current_album_name = $current_album_name;
        
        try {
            if (!empty($album_name)) {
                $this->new_album_name = $new_album_name;
            } else {
                throw new PhotoAlbumException("In order to edit your photo album name, the new name cannot be left empty.");
            }
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }
    
    
    /**
     * Edits the photo album's name
     * @return string
     */
    public function editName()
    {
        // rename the photo album directory
        if (rename(getcwd() . '/public/images/profile/' . Profile::getUser() . '/' . $this->current_album_name . '/', 
            getcwd() . '/public/images/profile/' . Profile::getUser() . '/' . $this->new_album_name)) {
            return self::EDIT_PHOTO_ALBUM_NAME_SUCCESS; 
        }
        
        return self::EDIT_PHOTO_ALBUM_NAME_FAILURE;
    }
}