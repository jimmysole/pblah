<?php
namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\PhotoAlbumException;

class EditPhotos
{
    
    const IMAGICK_LOADED_FAILURE = 'Imagick is not enabled/installed on your system, some features will not be available.';

    
    /**
     *
     * @var mixed
     */
    public $album_name;

    
    /**
     *
     * @var string
     */
    public $photo;

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
     * @param string $photos            
     * @param array $edits            
     * @throws PhotoAlbumException
     */
    public function __construct($album_name, $photo, array $edits)
    {
        if (extension_loaded('imagick')) {
            $this->imagick_loaded = true;
        } else {
            $this->imagick_loaded = false;
        }
        
        try {
            if (empty($album_name)) {
                throw new PhotoAlbumException("Invalid album name provided, please fix this and try again.");
            } else {
                $this->album_name = $album_name;
                $this->photo = $photo;
                
                
                // get the edit(s) passed
                if (count($edits, 1) > 0) {
                    foreach ($edits as $key => $value) {
                        $this->edits[$key] = $value;
                    }
                } else {
                    throw new PhotoAlbumException("Invalid edit options passed, please fix this and try again.");
                }
                
                
                if ($this->imagick_loaded !== false) {
                    $this->imagick = new \Imagick(getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->album_name . '/' . $this->photo);
                } else {
                    throw new PhotoAlbumException(self::IMAGICK_LOADED_FAILURE);
                }
            }
        } catch (PhotoAlbumException $e) {
            echo $e->getMessage();
        }
    }

    
    /**
     * Crops the supplied image
     * @return \Members\Model\Classes\EditPhotoAlbumPhotos
     * @throws \ImagickException
     */
    public function cropImage()
    {
        // set the crop values
        // if the values are null
        // assign zero to them
        $crop_width = is_int($this->edits['crop']['width']) ? $this->edits['crop']['width'] : intval($this->edits['crop']['width']);
        $crop_height = is_int($this->edits['crop']['height']) ? $this->edits['crop']['height'] : intval($this->edits['crop']['height']);
        $x = is_int($this->edits['crop']['x']) ? $this->edits['crop']['x'] : intval($this->edits['crop']['x']);
        $y = is_int($this->edits['crop']['y']) ? $this->edits['crop']['y'] : intval($this->edits['crop']['y']);
        
        // crop the image
        $this->imagick->cropImage($crop_width, $crop_height, $x, $y);
        
        return $this;
    }

   
    /**
     * Performs an adaptive blur on the supplied image
     * @return \Members\Model\Classes\EditPhotoAlbumPhotos
     * @throws \ImagickException
     */
    public function blurImage()
    {
        // check to see if a value was supplied for the radius, if not, use zero and let the radius be chosen automatically
        // if the sigma was left empty, default to zero
        $radius = is_float($this->edits['blur']['radius']) ? $this->edits['blur']['radius'] : floatval($this->edits['blur']['radius']);
        $sigma = is_float($this->edits['blur']['sigma']) ? $this->edits['blur']['sigma'] : floatval($this->edits['blur']['sigma']);
        
        // perform the adaptive blur on the image
        $this->imagick->adaptiveBlurImage($radius, $sigma);
        
        return $this;
    }
    
    
    /**
     * Enhances the image
     * @return \Members\Model\Classes\EditPhotoAlbumPhotos
     * @throws \ImagickException
     */
    public function enhanceImage()
    {
        if ($this->edits['enhance'] !== false) {
            // use Imagick::enhanceImage to enhance the image
            $this->imagick->enhanceImage();
        
            return $this;
        }
    }
    
    
    /**
     * Crops the image to make a thumbnail
     * @return \Members\Model\Classes\EditPhotoAlbumPhotos
     * @throws \ImagickException
     */
    public function makeThumbnail()
    {
        // set the thumbnail width + height
        $crop_thumbnail_width  = is_int($this->edits['crop']['t_width'])  ? $this->edits['crop']['t_width']  : intval($this->edits['crop']['t_width']);
        $crop_thumbnail_height = is_int($this->edits['crop']['t_height']) ? $this->edits['crop']['t_height'] : intval($this->edits['crop']['t_height']);
        
        $this->imagick->cropThumbnailImage($crop_thumbnail_width, $crop_thumbnail_height);
        
        return $this;
    }
    
    
    /**
     * Adds a sepia tone edit to the image
     * @return \Members\Model\Classes\EditPhotos
     * @throws \ImagickException
     */
    public function sepiaImage()
    {
        // set the threshold of the sepia edit
        $sepia_threshold = is_float($this->edits['sepia']['threshold']) ? $this->edits['sepia']['threshold'] : floatval($this->edits['sepia']['threshold']);
        
        $this->imagick->sepiaToneImage($sepia_threshold);
        
        return $this;
    }
    
    
    /**
     * Adds black white edit to the image
     * @return \Members\Model\Classes\EditPhotos
     */
    public function blackWhiteImage()
    {
        $this->imagick->transformImageColorSpace($this->edits['colorspace']['value']);
        $this->imagick->separateImageChannel($this->edits['colorspace']['channel']);
        
        return $this;
    }
    
    
    /**
     * Saves the image
     * @return bool
     * @throws \ImagickException
     */
    public function saveImage()
    {
        $this->imagick->setImageFormat('jpeg'); // set the format of the image to jpeg
        
        if (!is_dir(getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->album_name . '/edited_photos')) {
            mkdir(getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/' . $this->album_name . '/edited_photos', 0777);
        }
        
        // save the image
        $this->imagick->writeImageFile(fopen(getcwd() . '/public/images/profile/' . Profile::getUser() . '/albums/'
           . $this->album_name . '/edited_photos/' . date('Y-m-d') . '_' . $this->photo, 'w'));
       
        
        
        return true;
    }
}