<?php

namespace Members\Model\Interfaces;


interface PhotoAlbumInterface
{
    /**
     * Creates a photo album
     * 
     * @param string $album_name
     * @param array $album_photos
     * @param string $location
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function createAlbum($album_name, array $album_photos, $location = "");
    
    
    /**
     * Adds photos to an album
     * 
     * @param string $first_album
     * @param array $files
     * @param string $other_album
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function addPhotosToAlbum($first_album, $files, $other_album = false);

    
    /**
     * Gets all the photos from a specified album
     * 
     * @return string
     */
    public function photosFromAlbum($album);
    
    
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
     * @return boolean
     */
    public function deletePhotosFromAlbum(array $images);
    
    
    /**
     * Allows for various edits of the photo album's photos
     * @param array $edits
     * @throws PhotoAlbumException
     * @return boolean|string
     */
    public function editAlbum(array $edits);
    
    
    /**
     * Deletes a photo album
     * 
     * @param array $album
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function deleteAlbum(array $album);
    
    
    /**
     * Gets a list of all the user's photo albums
     * 
     * @throws PhotoAlbumException
     * @return array
     */
    public function getAlbums();
    
    
    /**
     * Gets the list of all the edited photos for a user
     * 
     * @throws PhotoAlbumException
     * @return array
     */
    public function viewEditedPhotos();
}