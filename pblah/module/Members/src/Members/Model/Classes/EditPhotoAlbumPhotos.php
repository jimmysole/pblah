<?php
namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;

class EditPhotoAlbumPhotos
{

    const EDIT_PHOTO_ALBUM_SUCCESS = 'Your photo album was successfully edited.';

    const EDIT_PHOTO_ALBUM_FAILURE = 'Error editing your photo album.';

    const IMAGICK_LOADED_FAILURE = 'Imagick is not enabled/installed on your system, some features will not be available.';

    
    /**
     *
     * @var mixed
     */
    public $album_name;

    
    /**
     *
     * @var array
     */
    public $photos = array();

    /**
     *
     * @var array
     */
    public $edits = array();

    
    /**
     *
     * @var bool
     */
    private $imagick_loaded;

    
    /**
     * @var \Imagick
     */
    private $imagick;

    
    /**
     * Constructor
     *
     * @param mixed $album_name            
     * @param array $edits            
     * @throws PhotoAlbumException
     */
    public function __construct($album_name, array $photos, array $edits)
    {
        if (extension_loaded('imagick')) {
            $this->imagick_loaded = true;
        } else {
            $this->imagick_loaded = false;
        }
        
        try {
            if (!empty($album_name) && count($photos, 1) > 0 && count($edits, 1) > 0) {
                $this->album_name = $album_name;
                
                foreach ($photos as $k => $v) {
                    $this->photos[$k] = $v;
                }
                
                foreach ($edits as $key => $value) {
                    $this->edits[$key] = $value;
                }
                
                if ($this->imagick_loaded !== false) {
                    $this->imagick = new \Imagick($this->photos['files']);
                } else {
                    throw new PhotoAlbumException(self::IMAGICK_LOADED_FAILURE);
                }
            } else {
                throw new PhotoAlbumException("Invalid photo album and/or edits provided, please fix this and try again.");
            }
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }

    
    /**
     * Crops the supplied image
     * @return void
     * @throws \ImagickException
     */
    public function cropImage()
    {
        // set the crop values
        // if the values are null
        // assign zero to them
        $crop_width = is_int($this->edits['crop']['width']) ? $this->edits['crop']['width'] : 0;
        $crop_height = is_int($this->edits['crop']['height']) ? $this->edits['crop']['height'] : 0;
        $x = is_int($this->edits['crop']['x']) ? $this->edits['crop']['x'] : 0;
        $y = is_int($this->edits['crop']['y']) ? $this->edits['crop']['y'] : 0;
        
        // crop the image
        $this->imagick->cropImage($crop_width, $crop_height, $x, $y);
    }

   
    /**
     * Performs an adaptive blur on the supplied image
     * @return void
     * @throws \ImagickException
     */
    public function blurImage()
    {
        // check to see if a value was supplied for the radius, if not, use zero and let the radius be chosen automatically
        // if the sigma was left empty, default to zero
        $radius = is_float($this->edits['blur']['radius']) ? $this->edits['blur']['radius'] : 0;
        $sigma = is_float($this->edits['blur']['sigma']) ? $this->edits['blur']['sigma'] : 0;
        
        // perform the adaptive blur on the image
        $this->imagick->adaptiveBlurImage($radius, $sigma);
    }
    
    
    /**
     * Enhances the image
     * @return void
     * @throws \ImagickException
     */
    public function enhanceImage()
    {
        // use Imagick::enhanceImage to enhance the image
        $this->imagick->enhanceImage();
    }
    
    
    /**
     * Crops the image to make a thumbnail
     * @return void
     * @throws \ImagickException
     */
    public function makeThumbnail()
    {
        // set the thumbnail width + height
        // if empty, default to zero
        $crop_thumbnail_width  = is_int($this->edits['crop']['t_width'])  ? $this->edits['crop']['t_width']  : 0;
        $crop_thumbnail_height = is_int($this->edits['crop']['t_height']) ? $this->edits['crop']['t_height'] : 0;
        
        $this->imagick->cropThumbnailImage($crop_thumbnail_width, $crop_thumbnail_height);
    }
    
    
    /**
     * Saves the image
     * @return void
     * @throws \ImagickException
     */
    public function saveImage()
    {
        $this->imagick->setImageFormat('jpeg'); // set the format of the image to jpeg
        
        // save the image
        $this->imagick->writeImageFile(@fopen(getcwd() . '/public/images/profile/' . Profile::getUser() . '/'
            . $this->album_name . '/' . $this->photos['files']));
    }
}