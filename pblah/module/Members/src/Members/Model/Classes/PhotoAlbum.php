<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class PhotoAlbum extends Profile
{
    /**
     * @var mixed
     */
    protected $album_name;
    
    
    /**
     * @var string
     */
    protected $album_created_date;
    
    
    /** 
     * @var int
     */
    protected $album_photo_count;
    
    
    /**
     * @var array
     */
    protected $album_photos = array();
    
    
    /**
     * @var Closure
     */
    protected $album_photo_holder;
    
    
    /**
     * @var array
     */
    protected $album_edits = array();
    
    
    /**
     * Constructor
     * @param mixed $album_name
     * @param unknown $album_created_date
     * @param array $album_photos
     */
    public function __construct($album_name, $album_created_date, array $album_photos)
    {
        $this->album_name = !empty($album_name) ? $album_name : null;
        
        $this->album_created_date = !empty($album_created_date) ? $album_created_date : date('Y-m-d', strtotime('now'));
        
        // this closure will bind to $_FILES
        $this->album_photo_holder = function() use ($album_photos) {
            if (count($album_photos) > 0) {
                foreach ($album_photos as $key => $value) {
                    $this->album_photos[$key] = $value;
                }
                
                return $this->album_photos;
            } else {
                return false;
            }
        };
    }
    
    
    /**
     * Creates a photo album 
     * @throws PhotoAlbumException
     * @return string
     */
    protected function createAlbum()
    {
        $file_info = array();
        
        // create the album 
        // first, check if the user has already uploaded images
        // if so, create a directory with the album name
        // and upload the photos
        // if not, create the directory with the user's username
        // and then create a directory with the album name
        // and upload the photos
        if (is_dir(getcwd() . '/public/images/profile/' . parent::getUser())) {
            mkdir(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name, 0777);
            
            // write the htaccess file
            $server_name = str_replace(array('https', 'http', 'www'), '', $_SERVER['SERVER_NAME']); // only need the actual server name, not the protocols or www
            
            $data = " 
                Options -Indexes
                RewriteEngine on 
                RewriteCond %{HTTP_REFERER} !^$ 
                RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$server_name [NC]
                RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]";
            
            file_put_contents(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . '/.htaccess', $data);
            
            // handle the photos now
            if (count($this->album_photo_holder(), 1) > 1) {
                // multiple photos
                $this->album_photo_count = count($this->album_photo_holder());
                
                for ($i=0; $i<count($this->album_photo_count); $i++) {
                    $file = $this->album_photo_holder()['name'][$i];
                    
                    $file_info[$file] = getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . $file;
                    
                    move_uploaded_file($this->album_photo_holder()['tmp_name'], 
                        getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . '/' . $this->album_photo_holder()['name']);
                }
                
                // return the file information in json format
                return json_encode($file_info);
            } else if (count($this->album_photo_holder(), 1) == 1) {
                // single photo
                $file_name = $this->album_photo_holder()['name'];
                
                $file_info[$file_name] = getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . $file_name;
                
                move_uploaded_file($this->album_photo_holder()['tmp_name'],
                    getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . '/' . $this->album_photo_holder()['name']);
                
                // return the file information in json format
                return json_encode($file_info);
            } else {
                throw new PhotoAlbumException("Error processing uploaded photos, please make sure you chose one or more to be uploaded.");
            }
        } else {
            mkdir(getcwd() . '/public/images/profile/' . parent::getUser(), 0777);
            mkdir(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name, 0777);
            
            // write the htaccess file
            $server_name = str_replace(array('https', 'http', 'www'), '', $_SERVER['SERVER_NAME']); // only need the actual server name, not the protocols or www
            
            $data = "
            Options -Indexes
            RewriteEngine on
            RewriteCond %{HTTP_REFERER} !^$
            RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$server_name [NC]
            RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]";
            
            file_put_contents(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . '/.htaccess', $data);
            
            // handle the photos now
            if (count($this->album_photo_holder(), 1) > 1) {
                // multiple photos
                $this->album_photo_count = count($this->album_photo_holder());
                
                for ($i=0; $i<count($this->album_photo_count); $i++) {
                    $file = $this->album_photo_holder()['name'][$i];
                    
                    $file_info[$file] = getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . $file;
                    
                    move_uploaded_file($this->album_photo_holder()['tmp_name'],
                        getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . '/' . $this->album_photo_holder()['name']);
                }
                
                // return the file information in json format
                return json_encode($file_info);
            } else if (count($this->album_photo_holder(), 1) == 1) {
                // single photo
                $file_name = $this->album_photo_holder()['name'];
                
                move_uploaded_file($this->album_photo_holder()['tmp_name'],
                    getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name . '/' . $this->album_photo_holder()['name']);
                
                // return the file information in json format
                return json_encode($file_info);
            } else {
                throw new PhotoAlbumException("Error processing uploaded photos, please make sure you chose one or more to be uploaded.");
            }
        }
    }
    
    
    /**
     * Allows various edits of the Photo Album
     * @param array $edits
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function editAlbum(array $edits) 
    {
        if (count($edits, 1) > 0) {
            foreach ($edits as $key => $value) {
                $this->album_edits[$key] = $value;
            }
            
            // determine which edit option was passed
            // supported options are:
            // 1) edit album name
            // 2) edit album location (for tagging)
            // 3) edit album photos (photo editor)
            // 4) add photos to album
            // 5) remove photos from album
            if ($this->album_edits['edit_name']) {
                $edit_name = (new EditPhotoAlbumName($this->album_name, $this->album_edits['edit_name']['new_album_name']))->editName();
                
                if (is_string($edit_name)) {
                    if ($edit_name == EditPhotoAlbumName::EDIT_PHOTO_ALBUM_SUCCESS) {
                        return true; // photo album was renamed successfully
                    } else if ($edit_name == EditPhotoAlbumName::EDIT_PHOTO_ALBUM_FAILURE) {
                        throw new PhotoAlbumException(EditPhotoAlbumName::EDIT_PHOTO_ALBUM_FAILURE); // error renaming photo album
                    } else {
                        throw new PhotoAlbumException("A unknown error occurred, please try again.");
                    }
                }
            }
        } else {
            throw new PhotoAlbumException("Please provide a edit option for your photo album.");
        }
    }
    
    
    /**
     * Deletes a photo album
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function deleteAlbum()
    {
        if (is_dir(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name)) {
            // remove the files inside the directory
            // since PHP won't allow rmdir() to remove a directory unless it is empty
            foreach (array_diff(scandir(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name, 1), array('.', '..')) as $values) {
                unlink(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name, $values);
            }
            
            // now remove the directory
            if (rmdir(getcwd() . '/public/images/profile/' . parent::getUser() . '/' . $this->album_name)) {
                // directory removed
                return true;
            } else {
                throw new PhotoAlbumException("Error deleting your photo album, please try again.");
            }
        } else {
            throw new PhotoAlbumException("Photo album does not exist.");
        }
    }
}