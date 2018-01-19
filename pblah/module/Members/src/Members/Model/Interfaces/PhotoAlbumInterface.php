<?php

namespace Members\Model\Interfaces;


interface PhotoAlbumInterface
{
    /**
     * Creates a photo album
     * 
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function createAlbum();
    
    
    /**
     * Adds photos to an album
     * 
     * @param string $first_album
     * @param string $other_album
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function addPhotosToAlbum($first_album, $other_album = false);

    
    /**
     * Gets all the photos from a specified album
     * 
     * @return string
     */
    public function photosFromAlbum();
    
    
    /**
     * Gets image size of a photo
     * 
     * @param string $photo
     * @return array
     */
    public function getImageSize($photo);
    
    
    /**
     * Deletes photo(s) from an album
     * 
     * @param array $images
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function deletePhotosFromAlbum(array $images);
    
    
    /**
     * Allows for various edits of the photo album's photos
     * @param array $edits
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function editAlbum(array $edits);
    
    
    /**
     * Deletes a photo album
     * 
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function deleteAlbum();
    
    
    /**
     * Gets a list of all the user's photo albums
     * 
     * @throws PhotoAlbumException
     * @return array
     */
    public function getAlbums();
}