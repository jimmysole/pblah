<?php

namespace Members\Model\Interfaces;


interface EditPhotoAlbumInterface
{
    /**
     * Changes the location for a photo album (for geotagging)
     * @param string $new_location
     * @throws PhotoAlbumException
     * @return string
     */
    public function changeLocation($new_location);
    
    
    /**
     * Edits the photo album's name
     * @return string
     */
    public function editName();
}