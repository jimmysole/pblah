<?php

namespace Members\Model\Interfaces;


interface EditPhotoAlbumInterface
{
   
    /**
     * Changes the location for a photo album (for geotagging)
     * 
     * @param string $album_name
     * @param string $new_location
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function changeLocation($album_name, $new_location);
    
    
    /**
     * Edits the photo album's name
     * 
     * @param string $current_album_name
     * @param string $album_new_name
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function editName($current_album_name, $album_new_name);
    
}