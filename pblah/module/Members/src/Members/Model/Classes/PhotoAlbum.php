<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;


class PhotoAlbum extends Profile
{
    protected $album_name;
    
    protected $album_created_date;
    
    protected $album_photo_count;
    
    protected $album_photos = array();
    
    protected $album_photo_holder;
    
    
    protected static function createAlbum($album_name, $album_created_date, array $album_photos)
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
    
    
    public static function editAlbum($album_name, array $edits) 
    {
         
    }
    
    
    public static function deleteAlbum($album_name)
    {
        
    }
}