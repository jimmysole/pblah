<?php

namespace Members\Model\Interfaces;


interface EditPhotoAlbumInterface
{
   
    /**
     * Changes the location for a photo album (for geotagging)
     * 
     * @param string $new_location
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function changeLocation($new_location);
    
    
    /**
     * Edits the photo album's name
     * 
     * @param string $current_album_name
     * @param string $album_new_name
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function editName($current_album_name, $album_new_name);
    
    
    /**
     * Crops the supplied image
     * 
     * @param mixed $photo
     * @param array $crop_values
     * @throws \ImagickException
     * @return EditPhotoAlbumInterface
     */
    public function cropImage($photo, array $crop_values);
    
    
    /**
     * Performs an adaptive blur on the supplied image
     * 
     * @param mixed $photo
     * @param array $blur_values
     * @throws \ImagickException
     * @return EditPhotoAlbumInterface
     */
    public function blurImage($photo, array $blur_values);
    
    
    /**
     * Enhances the image
     * 
     * @param mixed $photo
     * @throws \ImagickException
     * @return EditPhotoAlbumInterface
     */
    public function enhanceImage($photo);
    
    
    /**
     * Crops the image to make a thumbnail
     * 
     * @param mixed $photo
     * @param array $thumbnail_values
     * @throws \ImagickException
     * @return EditPhotoAlbumInterface
     */
    public function makeThumbnail($photo, array $thumbnail_values);
    
    
    /**
     * Adds a sepia tone edit to the image
     * 
     * @param mixed $photo
     * @param array $sepia_values
     * @throws \ImagickException
     * @return EditPhotoAlbumInterface
     */
    public function sepiaImage($photo, array $sepia_values);
    
    
    /**
     * Adds black white edit to the image
     * 
     * @param mixed $photo
     * @param array $bw_values
     * @throws \ImagickException
     * @return EditPhotoAlbumInterface
     */
    public function blackWhiteImage($photo, array $bw_values);
    
    
    /**
     * Saves the image
     * 
     * @throws \ImagickException
     * @return bool
     */
    public function saveImage();
}