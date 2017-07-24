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
     * @var string
     */
    protected $location;
    
    
    /**
     * @var array
     */
    protected $album_photo_holder;
    
    
    /**
     * @var array
     */
    protected $album_edits = array();
    
    
    /**
     * @var string
     */
    protected $filtered_album_name;
    
    
    /**
     * Constructor
     * @param mixed $album_name
     * @param array $album_photos
     * @param string $location
     */
    public function __construct($album_name, array $album_photos, $location = "")
    {
        if (is_array($album_name) && count($album_name, 1) > 0) {
            foreach ($album_name as $key => $value) {
                $this->album_name[$key] = $value;
            }
        } else {
            // assume string
            $this->album_name = $album_name;
        }
        
        $this->album_created_date =  date('Y-m-d', strtotime('now'));
        
        $this->location = !empty($location) ? $location : null;
        
        foreach ($album_photos as $key => $values) {
            $this->album_photo_holder[$key] = $values;
        }
        
        $this->filtered_album_name = str_replace("_", " ", $this->album_name);
    }
    
    
    /**
     * Creates a photo album 
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function createAlbum()
    {
        //var_dump($this->album_photo_holder['photos']); exit;
        // first check if a location was provided
        $write_location = function() {
            if (null !== $this->location) {
                file_put_contents(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . '/location.txt', $this->location);
                
                return true;
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
            @mkdir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date, 0777);
            
            // write the htaccess file
            $server_name = str_replace(array('https', 'http', 'www'), '', $_SERVER['SERVER_NAME']); // only need the actual server name, not the protocols or www
            
            $data = " 
                Options -Indexes
                RewriteEngine on 
                RewriteCond %{HTTP_REFERER} !^$ 
                RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$server_name [NC]
                RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]";
            
            file_put_contents(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . '/.htaccess', $data);
            
            
            // handle the photos now
            if (count($this->album_photo_holder, 1) > 1) {
                // location tagging of album (if provided)
                $write_location();
                
                // multiple photos
                foreach ($this->album_photo_holder['photos'] as $key => $value) {
                    $file = $value['name']; 
                    $temp = $value['tmp_name']; 
                    
                    $file_info[$file] = getcwd() . '/public/images/profile/' . parent::getUser() . '/albums' . $this->filtered_album_name . '_' . $this->album_created_date . $file;
                    
                    move_uploaded_file($temp, 
                        getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name .  '_' . $this->album_created_date . '/' . $file);
                }
                
                return true;
            } else if (count($this->album_photo_holder, 1) == 1) {
                // location tagging of album (if provided)
                $write_location();
                
                // single photo
                $file_name = $this->album_photo_holder['photos'][0]['name'];
                
                $file_info[$file_name] = getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . $file_name;
                
                move_uploaded_file($this->album_photo_holder['photos'][0]['tmp_name'],
                    getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . '/' . $file_name);
                
                return true;
            } else {
                throw new PhotoAlbumException("Error processing uploaded photos, please make sure you chose one or more to be uploaded.");
            }
        } else {
            mkdir(getcwd() . '/public/images/profile/' . parent::getUser(), 0777);
            mkdir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date, 0777);
            
            // write the htaccess file
            $server_name = str_replace(array('https', 'http', 'www'), '', $_SERVER['SERVER_NAME']); // only need the actual server name, not the protocols or www
            
            $data = "
            Options -Indexes
            RewriteEngine on
            RewriteCond %{HTTP_REFERER} !^$
            RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$server_name [NC]
            RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]";
            
            file_put_contents(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . '/.htaccess', $data);
            
            
            // handle the photos now
            if (count($this->album_photo_holder, 1) > 1) {
                // location tagging of album (if provided)
                $write_location();
                
                // multiple photos
                foreach ($this->album_photo_holder['photos'] as $key => $value) {
                    $file = $value['name'];
                    $temp = $value['tmp_name'];
                    
                    $file_info[$file] = getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . $file;
                    
                    move_uploaded_file($temp,
                        getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name .  '_' . $this->album_created_date . '/' . $file);
                }
                
                return true;
            } else if (count($this->album_photo_holder, 1) == 1) {
                // location tagging of album (if provided)
                $write_location();
                
                // single photo
                $file_name = $this->album_photo_holder['photos'][0]['name'];
                
                move_uploaded_file($this->album_photo_holder['photos'][0]['tmp_name'],
                    getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->filtered_album_name . '_' . $this->album_created_date . '/' . $file_name);
                
                return true;
            } else {
                throw new PhotoAlbumException("Error processing uploaded photos, please make sure you chose one or more to be uploaded.");
            }
        }
    }
    
    
    /**
     * Adds photos to an album
     * @param string $first_album
     * @param string $other_album
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function addPhotosToAlbum($first_album, $other_album = false)
    {
        $file_info = array();
        
        if ($other_album !== false) {
            $replace = array();
            
            // check whether the other album exists
            // if it does, copy the files from one album to the other album
            foreach (glob(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/*', GLOB_ONLYDIR) as $dir) {
                $replace[] = basename($dir);
            }
            
            if (in_array($other_album, $replace)) {
                foreach (glob(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $other_album. '/*.{jpg,png,gif,JPG,PNG,GIF}', GLOB_BRACE) as $files) {
                    copy($files, getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $first_album . '/' . substr(strrchr($files, '/'), 1));
                }
                
                return true;
            } else {
                throw new PhotoAlbumException("Photo album was not found, please make sure it exists.");
            }
        } else {
            // just copy to the one album
            foreach ($this->album_photo_holder['photos'] as $key => $value) {
                $file = $value['name'];
                $temp = $value['tmp_name'];
                
                $file_info[$file] = getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $first_album . '/' . $file;
            
                move_uploaded_file($temp, $file_info[$file]);
            }
            
            return true;
        }
    }
    
    
    /**
     * Grabs all the photos from a specified album
     * @return string
     */
    public function photosFromAlbum()
    {
        $photos_holder = array();
        
        foreach (array_diff(scandir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . $this->album_name, 1), array('.', '..' , '.htaccess', 'location.txt')) as $photos) {
            
            $photos_holder[] = $photos;
        }
        
        return json_encode(array('photos' => $photos_holder));
    }
       
    
    /**
     * Deletes photo(s) from an album
     * @param array $images
     * @throws PhotoAlbumException
     * @return boolean
     */
    public function deletePhotosFromAlbum(array $images)
    {
        foreach (array_diff(scandir($this->album_name['album'], 1), array('.', '..')) as $value) {
            // retrieve the files selected from the album
            if (in_array($value, $images)) {
                // remove the file(s) from the album
                unlink($value);
            } else {
                throw new PhotoAlbumException("No images were found for the album " . $this->album_name['album']);
            }
        }
        
        return true;
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
                $edit_name = (new EditPhotoAlbumName($this->album_name . '_' . $this->album_created_date, $this->album_edits['edit_name']['new_album_name']))->editName();
 
                if ($edit_name == EditPhotoAlbumName::EDIT_PHOTO_ALBUM_NAME_SUCCESS) {
                    return true; // photo album was renamed successfully
                } else if ($edit_name == EditPhotoAlbumName::EDIT_PHOTO_ALBUM_NAME_FAILURE) {
                    throw new PhotoAlbumException(EditPhotoAlbumName::EDIT_PHOTO_ALBUM_NAME_FAILURE); // error renaming photo album
                } else {
                    throw new PhotoAlbumException("A unknown error occurred, please try again.");
                }
            } else if ($this->album_edits['edit_location']) {
                $edit_location = (new EditPhotoAlbumLocation($this->album_name . '_' . $this->album_created_date, $this->album_edits['edit_location']['new_location']))->changeLocation();
                
                if ($edit_location == EditPhotoAlbumLocation::EDIT_PHOTO_ALBUM_LOCATION_SUCCESS) {
                    return true; // photo album location was edited successfully
                } else if ($edit_location == EditPhotoAlbumLocation::EDIT_PHOTO_ALBUM_LOCATION_FAILURE) {
                    throw new PhotoAlbumException(EditPhotoAlbumLocation::EDIT_PHOTO_ALBUM_LOCATION_FAILURE); // error changing location
                } else {
                    throw new PhotoAlbumException("A unknown error occurred, please try again.");
                }
            } else if ($this->album_edits['edit_photo_album']) {
                if ($this->album_edits['edit_photo_album']['crop']) {
                    // crop the image, then save it
                    try {
                        $crop_image = (new EditPhotos($this->album_name . '_' . $this->album_created_date,
                            array('files' => $this->album_photo_holder()['name']), $this->album_edits['crop_image']))
                        ->cropImage()
                        ->saveImage();
                    } catch (\ImagickException $e) {
                        echo $e->getMessage();
                        return false;
                    } catch (PhotoAlbumException $e) {
                        echo $e->getMessage();
                        return false;
                    }
                } 
                
                if ($this->album_edits['edit_photo_album']['blur']) {
                    // blur the image, then save it
                    try {
                        $blur_image = (new EditPhotos($this->album_name . '_' . $this->album_created_date,
                            array('files' => $this->album_photo_holder()['name']), $this->album_edits['blur_image']))
                        ->blurImage()
                        ->saveImage();
                    } catch (\ImagickException $e) { 
                        echo $e->getMessage();
                        return false;
                    } catch (PhotoAlbumException $e) {
                        echo $e->getMessage();
                        return false;
                    }
                }
                
                if ($this->album_edits['edit_photo_album']['enhance_image']) {
                    // enhance the image, then save it
                    try {
                        $enhance_image = (new EditPhotos($this->album_name . '_' . $this->album_created_date,
                            array('files' => $this->album_photo_holder()['name']), $this->album_edits['enhance_image']))
                        ->enhanceImage()
                        ->saveImage();
                    } catch (\ImagickException $e) {
                        echo $e->getMessage();
                        return false;
                    } catch (PhotoAlbumException $e) {
                        echo $e->getMessage();
                        return false;
                    }
                }
                
                if ($this->album_edits['edit_photo_album']['make_thumbnail']) {
                    // scale the image to thumbnail size, then save it
                    try {
                        $make_thumbnail = (new EditPhotos($this->album_name . '_' . $this->album_created_date, 
                            array('files' => $this->album_photo_holder()['name']), $this->album_edits['crop']))
                        ->makeThumbnail()
                        ->saveImage();
                    } catch (\ImagickException $e) {
                        echo $e->getMessage();
                        return false;
                    } catch (PhotoAlbumException $e) {
                        echo $e->getMessage();
                        return false;
                    }
                }
            } else if ($this->album_edits['add_photos']) {
                
            } else if ($this->album_edits['remove_photos']) {
                
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
        $album_as_array = array_key_exists('album', $this->album_name) ? true : false;
        
        // delete albums
        if (false !== $album_as_array) {
            foreach ($this->album_name['album'] as $key => $values) {
                if (is_dir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . str_replace('remove_', '', $values) . '/')) {
                    // remove the files inside the directory
                    // since PHP won't allow rmdir() to remove a directory unless it is empty
                    foreach (array_diff(scandir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . str_replace('remove_', '', $values) . '/', 1), array('.', '..')) as $files) {
                        unlink(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . str_replace('remove_', '', $values) . '/' . $files);
                    }
                    
                    // now remove the directory
                    if (rmdir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/' . str_replace('remove_', '', $values) . '/')) {
                        // directory removed
                        // continue until all albums are gone
                        continue;
                    } else {
                        throw new PhotoAlbumException("Error deleting your photo album, please try again.");
                    }
                } else {
                    throw new PhotoAlbumException("Photo album does not exist.");
                }
            }
            
            return true;
        }
    }
    
    
    /**
     * Gets a list of all albums
     * @throws PhotoAlbumException
     * @return array
     */
    public function getAlbums()
    {
        if (is_dir(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/')) {
            // scan the albums directory
            $files = array();
            $album_name = array();
            
            foreach (glob(getcwd() . '/public/images/profile/' . parent::getUser() . '/albums/*', GLOB_ONLYDIR) as $dir) {
                $album_name = basename($dir);
                
                $files[$album_name] = glob($dir . '/*.{jpg,png,gif,JPG,PNG,GIF}', GLOB_BRACE);
            }
            
            // make sure there are values returned
            if (count($dir) > 0) {
                // directories found
                // return them
                return $files[$album_name];
            } else {
                throw new PhotoAlbumException("Error locating your photo album, please try again.");
            }
        } else {
            throw new PhotoAlbumException("Photo album does not exist.");
        }
    }

}