<?php
namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Model\Exceptions\ProfileException;
use Members\Model\Exceptions\PhotoAlbumException;

use Members\Form\CreateAlbumForm;
use Members\Form\AddPhotosForm;
use Members\Form\RemovePhotosForm;
use Members\Form\EditPhotosForm;



class ProfileController extends AbstractActionController
{

    public $profile_service;

    public $edit_profile_service;



    public function indexAction()
    {

        if (!$this->getProfileService()->checkIfProfileSet()) {
            return $this->redirect()->toRoute('members/profile', array('action' => 'create-profile'));
        }

        
        $params = $this->identity();

        $layout = $this->layout();
        
        $dir = @array_diff(scandir('./data/images/profile/' . $params . '/', 1), array('.', '..', 'current', '.htaccess', 'albums', 'edited_photos', 'videos'));

        if (count($dir) > 0) {
            $images = array();

            foreach ($dir as $value) {
                $images[] = "<img src=\"/images/profile/$params/$value\" class=\"w3-margin-bottom w3-round w3-border\" style=\"width: 100%; height: 88px;\">";
            }

            

            natsort($images);

            $layout->setVariable('my_images', $images);
        } else {
            $images[] = "<img src=\"/images/profile/avatar2.png\" class=\"w3-margin-bottom w3-round w3-border\" style=\"width: 100%; height: 88px;\">";
            
            $layout = $this->layout();
            
            $layout->setVariable('my_images', $images);
        }
        
        $video_dir = @array_diff(scandir('./data/images/profile/' . $params . '/videos/', 1), array('.', '..', 'current', '.htaccess', 'albums', 'edited_photos'));
        
        if (count($video_dir) > 0) {
            $videos = array();
            
            foreach ($video_dir as $video) {
                $videos[] = "<video style=\"width: 100%; height: 100%;\" class=\"w3-margin-bottom w3-round w3-border\">
                <source src=\"/images/profile/$params/videos/$video\" type=\"video/mp4\">
                </video>";
            }
            
            natsort($videos);
            
            $layout->setVariable('my_videos', $videos);
        }
    }


    public function uploadfileAction()
    {
        if ($this->request->isPost()) {
            if (!empty($_FILES[$this->identity()])) {
                $file_info = array();
                
                // make directory if it doesn't exist
                if (!is_dir('./data/images/profile/' . $this->identity())) {
                    mkdir('./data/images/profile/' . $this->identity(), 0777);
                    mkdir('./data/images/profile/' . $this->identity() . '/current');
                    mkdir('./data/images/profile/' . $this->identity() . '/videos');
                    
                    // make the htaccess file
                    $server = str_replace(array('https', 'http', 'www'), '', $_SERVER['SERVER_NAME']);
                    $data = "
                        Options -Indexes
                        RewriteEngine on 
                        RewriteCond %{HTTP_REFERER} !^$ 
                        RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$server [NC]
                        RewriteRule \.(jpg|jpeg|png|gif|mp4)$ - [NC,F,L]";
                    
                    file_put_contents('./data/images/profile/' . $this->identity() . '/.htaccess', $data);
                   
                    if (!is_array($_FILES[$this->identity()]['name'])) {
                        $file_name = $_FILES[$this->identity()]['name'];
                        
                        move_uploaded_file(trim($_FILES[$this->identity()]['tmp_name']),
                            './data/images/profile/' . $this->identity() . '/' . str_replace(' ', '', $_FILES[$this->identity()]['name']));
                        
                        $file_info[$file_name] = './data/images/profile/' . $this->identity() . '/' . $file_name;
                        
                        foreach (@array_diff(scandir('./data/images/profile/' . $this->identity() . '/', 1), array('.', '..', 'current', '.htaccess', 'albums', 'edited_photos', 'videos')) as $files) {
                            if (preg_match("/(.mp4)$/", $files)) {
                                rename( './data/images/profile/' . $this->identity() . '/' . $files,
                                    './data/images/profile/' . $this->identity() . '/videos/' . $files);
                            }
                        }
                    } else {
                        // multiple files
                        // disallow for multiple video uploads at a time
                        $file_count = count($_FILES[$this->identity()]['name']);
                        
                        for ($i=0; $i<count($file_count); $i++) {
                            $file_name = $_FILES[$this->identity()]['name'][$i];
                            
                            $file_info[$file_name] = './data/images/profile/' . $this->identity() . '/' . $file_name;
                            
                            move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                                getcwd() . './data/images/profile/' . $this->identity() . '/' . $_FILES[$this->identity()]['name']);
                        }
                    }
                    
                    echo json_encode($file_info);
                } else {
                    if (!is_array($_FILES[$this->identity()]['name'])) {
                        $file_name = $_FILES[$this->identity()]['name'];
                        
                        move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                            './data/images/profile/' . $this->identity() . '/' . str_replace(' ', '', $_FILES[$this->identity()]['name']));

                        $file_info[$file_name] = './data/images/profile/' . $this->identity() . '/' . $file_name;
                        
                        foreach (@array_diff(scandir('./data/images/profile/' . $this->identity() . '/', 1), array('.', '..', 'current', '.htaccess', 'albums', 'edited_photos', 'videos')) as $files) {
                            if (preg_match("/(.mp4)$/", $files)) {
                                rename('./data/images/profile/' . $this->identity() . '/' . $files,
                                    './data/images/profile/' . $this->identity() . '/videos/' . $files);
                            }
                        }
                    } else {
                        // multiple files
                        // disallow for multiple video uploads at a time
                        $file_count = count($_FILES[$this->identity()]['name']);

                        for ($i=0; $i<count($file_count); $i++) {
                            $file_name = $_FILES[$this->identity()]['name'][$i];

                            $file_info[$file_name] = './data/images/profile/' . $this->identity() . '/' . $file_name;

                            move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                                './data/images/profile/' . $this->identity() . '/' . $_FILES[$this->identity()]['name']);
                        }
                    }

                    echo json_encode($file_info);
                }
            }
        }
    }
    
    
    
    public function vieweditedimagesAction()
    {
        return new ViewModel(array('files' => $this->getProfileService()->viewEditedPhotos()));
    }
    
    
    
    
    public function makephotoalbumAction()
    {
        $form = new CreateAlbumForm();
        
        return new ViewModel(array('form' => $form));
    }
    
    
    public function mphotoalbumAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            try {
               $params = $this->params()->fromPost();
               $files = $this->params()->fromFiles();
               
               if ($this->getProfileService()->createAlbum($params['album-name'], $files, $params['location'])) {
                   $this->flashMessenger()->addSuccessMessage("Photo album was created successfully!");
                   
                   return $this->redirect()->toUrl('photo-album-created-success');
               } 
            } catch (PhotoAlbumException $e) {
                $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
                    
                return $this->redirect()->toUrl('photo-album-created-failure');
            }
        }
    }
    
    
    public function photoalbumcreatedsuccessAction()
    {
        return;
    }
    
    
    public function photoalbumcreatedfailureAction()
    {
        return;
    }
    
    
    public function viewphotoalbumsAction()
    {
        $identity = $this->identity();
        $files = array();
        $album_name = array();
        
        foreach (glob('./data/images/profile/' . $identity . '/albums/*', GLOB_ONLYDIR) as $dir) {
            $album_name = basename($dir);
            
            $files[$album_name] = glob($dir . '/*.{jpg,png,gif,JPG,PNG,GIF}', GLOB_BRACE);
        }
        
        return new ViewModel(array('files' => $files));
    }
    
    
    public function addphotosAction()
    {
       $form = $this->getServiceLocator()
        ->get('FormElementManager')
        ->get(AddPhotosForm::class);
        
        return new ViewModel(array('form' => $form)); 
    }
    
    
    public function photostoalbumAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        
        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost();
                $files = $this->params()->fromFiles();
                
                
                if ($params['copy-from-album'] != "") {
                    $from_album = $params['copy-from-album'];
                } else if ($params['copy-from-album'] == "") {
                    $from_album = false;
                }
                
                if (false !== $this->getProfileService()->addPhotosToAlbum($params['album-name'], $files, $from_album)) {
                    $this->flashMessenger()->addSuccessMessage("Photos added to album successfully!");
                    
                    return $this->redirect()->toUrl('add-photos-to-album-success');
                } 
            } catch (PhotoAlbumException $e) {
                $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
                
                return $this->redirect()->toUrl('add-photos-to-album-failure');
            }
        }
        
        return $view_model;
    }
    
    
    public function addphotostoalbumsuccessAction()
    {
        return;
    }
    
    
    public function addphotostoalbumfailureAction()
    {
        return;
    }
  
    
    public function removephotoalbumAction()
    {
        $identity = $this->identity();
        $files = array();
        $album_name = array();
        
        foreach (glob('./data/images/profile/' . $identity . '/albums/*', GLOB_ONLYDIR) as $dir) {
            $album_name = basename($dir);
            
            $files[$album_name] = glob($dir . '/*.{jpg,png,gif,JPG,PNG,GIF}', GLOB_BRACE);
        }
        
        return new ViewModel(array('photo_albums' => $files));
    }

    
    public function removealbumAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        if ($this->request->isPost()) {
            try {
                $params = $this->getRequest()->getPost()->toArray();
                
                $this->getProfileService()->deleteAlbum($params);
            } catch (PhotoAlbumException $e) {
                echo $e->getMessage();
            }
        }
        
        return $view_model;
    }
    
    
    public function removephotosAction()
    {
        $form = $this->getServiceLocator()
        ->get('FormElementManager')
        ->get(RemovePhotosForm::class);
        
        return new ViewModel(array('form' => $form));
    }
    
    
    public function handlephotodeleteAction()
    {
        $form = new RemovePhotosForm();
        
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        if ($this->request->isPost()) { 
            try {
                $params = $this->getRequest()->getPost()->toArray();
                
                if ($this->getProfileService()->removePhotosFromAlbum($params['album'], $params['images'])) {
                    echo "Image(s) deleted from " . $params['album'];
                }
            } catch (PhotoAlbumException $e) {
                echo $e->getMessage();
            }
        }
        
        return $view_model;
    }
    
  
    public function getphotosfromalbumAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            
            echo $this->getProfileService()->photosFromAlbum($params['album_name']);
        }
        
        return $view_model;
    }
    
    
    public function getphotosizeAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        if ($this->request->isPost()) {
            $params = $this->getRequest()->getPost()->toArray();
            
            $viewsize = $this->getProfileService()->getPhotoSize($params['image_name']);
            
            $arr = array();
            
            foreach ($viewsize as $v) {
                $arr[] = $v;
            }
            
            echo json_encode(array('width' => $arr[0], 'height' => $arr[1]));
        }
        
        return $view_model;
    }
    
    
    public function editprofileAction()
    {
        if (!$this->getProfileService()->checkIfProfileSet()) {
            return $this->redirect()->toRoute('members/profile', array('action' => 'create-profile'));
        }


        $layout = $this->layout();

        $layout->setVariable('display_name', $this->getProfileService()->getUserDisplayName());
        $layout->setVariable('location', $this->getProfileService()->getUserLocation());
        $layout->setVariable('age', $this->getProfileService()->getUserAge());
        $layout->setVariable('bio', $this->getProfileService()->getUserBio());
    }


    public function changenameAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost();

                $this->getProfileService()->editProfile(array('display_name' => ltrim($params['display_name'])));
            } catch (ProfileException $e) {
                echo $e->getMessage();
            }
        }

        return $view_model;
    }


    public function changelocationAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost();

                $this->getProfileService()->editProfile(array('location' => ltrim($params['location'])));
            } catch (ProfileException $e) {
                echo $e->getMessage();
            }
        }

        return $view_model;
    }


    public function changeageAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost();

                $this->getProfileService()->editProfile(array('age' => ltrim($params['user_age'])));
            } catch (ProfileException $e) {
                echo $e->getMessage();
            }
        }

        return $view_model;
    }


    public function changebioAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost();

                $this->getProfileService()->editProfile(array('bio' => ltrim($params['user_bio'])));
            } catch (ProfileException $e) {
                echo $e->getMessage();
            }
        }

        return $view_model;
    }


    public function changefailureAction()
    {
        return;
    }


    public function createprofileAction()
    {
        if ($this->getProfileService()->checkIfProfileSet()) {
            return $this->redirect()->toRoute('members/profile', array('action' => 'index'));
        }

        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost();

                $this->getProfileService()->createProfile(array(
                    'display_name'  => $params['display_name'],
                    'email_address' => $params['email_address'],
                    'age'           => $params['age'],
                    'location'      => $params['location'],
                    'bio'           => $params['bio'],
                ));
            } catch (ProfileException $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());

                return $this->redirect()->toRoute('members/profile', array('action' => 'create-profile-failure'));
            }
        }
    }


    public function createprofilefailureAction()
    {
        return;
    }


    public function removeprofileAction()
    {

    }


    public function profilesettingsAction()
    {

    }


    public function profileviewsAction()
    {

    }
    
    
    public function editphotosAction()
    {
        $form = $this->getServiceLocator()
        ->get('FormElementManager')
        ->get(EditPhotosForm::class);
        
        return new ViewModel(array('form' => $form));
    }
    
    
    public function handlephotoeditAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        $params = $this->params()->fromPost();
        
        if (@(int)$params['crop_image'] == 1) {
            try {
                if ($this->getProfileService()->editPhoto($params['album_name'], $params['photo'], array('crop_image' => $params['crop_image'], 
                    'width' => $params['width'], 'height' => $params['height'], 'x' => $params['x'], 'y' => $params['y']))) {
                    echo json_encode(array('success' => 'Photo was cropped successfully.'));
                }
            } catch (\ImagickException $e) {
                echo json_encode(array('fail' => $e->getMessage()));
            } catch (PhotoAlbumException $e) {
                echo json_encode(array('fail' => $e->getMessage()));
            }
        } 
        
        
        if (@(int)$params['blur_image'] == 1) {
            try {
                if ($this->getProfileService()->editPhoto($params['album_name'], $params['photo'], array('blur_image' => $params['blur_image'],
                    'radius' => $params['radius'], 'sigma' => $params['sigma']))) {
                    echo json_encode(array('success_blur' => 'Adaptive blur applied to photo successfully.'));
                }
            } catch (\ImagickException $e) {
                echo json_encode(array('fail_blur' => $e->getMessage()));
            } catch (PhotoAlbumException $e) {
                echo json_encode(array('fail_blur' => $e->getMessage()));
            }
        }
        
        if (@(int)$params['enhance_image'] == 1) {
            try {
                if ($this->getProfileService()->editPhoto($params['album_name'], $params['photo'], array('enhance_image' => $params['enhance_image']))) {
                    echo json_encode(array('success_enhance' => 'Photo was enhanced successfully.'));
                }
            } catch (\ImagickException $e) {
                echo json_encode(array('fail_enhance' => $e->getMessage()));
            } catch (PhotoAlbumException $e) {
                echo json_encode(array('fail_enhance' => $e->getMessage()));
            }
        }
        
        if (@(int)$params['thumbnail_image'] == 1) {
            try {
                if ($this->getProfileService()->editPhoto($params['album_name'], $params['photo'], array('make_thumbnail' => $params['thumbnail_image'],
                    't_width' => $params['tx'], 't_height' => $params['ty']))) {
                    echo json_encode(array('success_thumbnail' => 'Thumbnail applied to photo successfully.'));
                }
            } catch (\ImagickException $e) {
                echo json_encode(array('fail_thumbnail' => $e->getMessage()));
            } catch (PhotoAlbumException $e) {
                echo json_encode(array('fail_thumbnail' => $e->getMessage()));
            }
        }
        
        if (@(int)$params['sepia_image'] == 1) {
            try {
                if ($this->getProfileService()->editPhoto($params['album_name'], $params['photo'], array('sepia_image' => $params['sepia_image'],
                    'sepia_threshold' => $params['sepia_threshold']))) {
                    echo json_encode(array('success_sepia' => 'Sepia tone edit applied to photo successfully.'));
                }
            } catch (\ImagickException $e) {
                echo json_encode(array('fail_sepia' => $e->getMessage()));
            } catch (PhotoAlbumException $e) {
                echo json_encode(array('fail_sepia' => $e->getMessage()));
            }
        }
        
        if (@(int)$params['bw_image'] == 1) {
            try {
                if ($this->getProfileService()->editPhoto($params['album_name'], $params['photo'], array('bw_image' => $params['bw_image'],
                    'colorspace' => $params['colorspace'], 'channel' => $params['channel']))) {
                    echo json_encode(array('success_bw' => 'B&W edit applied to photo successfully.'));
                }
            } catch (\ImagickException $e) {
                echo json_encode(array('fail_bw' => $e->getMessage()));
            } catch (PhotoAlbumException $e) {
                echo json_encode(array('fail_bw' => $e->getMessage()));
            }
        }
        
        return $view_model;
    }



    public function getprofileAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        try {
            $data = array(
                'display_name' => $this->getProfileService()->getUserProfile()['display_name'],
                'location'     => $this->getProfileService()->getUserProfile()['location'],
                'age'          => $this->getProfileService()->getUserProfile()['age'],
            );

            echo json_encode($data);
        } catch (ProfileException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }

        return $view_model;
    }


    public function getuserdisplaynameAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        try {
            $user = $this->getProfileService()->getUserDisplayName();

            echo $user;
        } catch (ProfileException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }

        return $view_model;
    }


    public function getuserlocationAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        try {
            $location = $this->getProfileService()->getUserLocation();

            echo $location;
        } catch (ProfileException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }

        return $view_model;
    }


    public function getuserageAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        try {
            $age = $this->getProfileService()->getUserAge();

            echo $age;
        } catch (ProfileException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }

        return $view_model;
    }


    public function getuserbioAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        try {
            $bio = $this->getProfileService()->getUserBio();

            echo $bio;
        } catch (ProfileException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }

        return $view_model;
    }


    public function cropprofileimageAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();


            $x = $params['x'];
            $y = $params['y'];
            $w = $params['w'];
            $h = $params['h'];
            $src = $params['fname'];

            $tw = $th = 150;
            $img_quality = 90;

            $mime_check = new \finfo(\FILEINFO_MIME_TYPE);

            $identity = $this->identity();


            if ($mime_check->file('./data/images/profile/' . $identity . '/' . basename($src)) == "image/jpg" ||
                $mime_check->file('/.data/images/profile/'. $identity . '/' . basename($src)) == 'image/jpeg') {
                if (!$img = @imagecreatefromjpeg('./data/images/profile/' . $identity . '/' . basename($src))) {
                    $this->flashMessenger()->addErrorMessage("Image is not a valid jpeg image!");
                    return $this->redirect()->toRoute('members/profile', array('action' => 'crop-profile-image-failure'));
                } else {
                    $img = imagecreatefromjpeg('./data/images/profile/' . $identity . '/' . basename($src));
                    $true_color = imagecreatetruecolor($tw, $th);

                    imagecopyresampled($true_color, $img, 0, 0, $x, $y, $tw, $th, $w, $h);

                    $dest_source = './data/images/profile/' . $this->identity() . '/current/' . basename($src);

                    imagejpeg($true_color, $dest_source, $img_quality);

                    // cropped okay
                    return $this->redirect()->toRoute('members', array('action' => 'index'));
                }
            } else if ($mime_check->file('./data/images/profile/' . $identity . '/' . basename($src)) == "image/png") {
                if (!$img = @imagecreatefrompng( './data/images/profile/' . $identity . '/' . basename($src))) {
                    $this->flashMessenger()->addErrorMessage("Image is not a valid png image!");
                    return $this->redirect()->toRoute('members/profile', array('action' => 'crop-profile-image-failure'));
                } else {
                    $img = imagecreatefrompng( './data/images/profile/' . $identity . '/' . basename($src));
                    $true_color = imagecreatetruecolor($tw, $th);

                    imagecopyresampled($true_color, $img, 0, 0, $x, $y, $tw, $th, $w, $h);

                    $dest_source = './data/images/profile/' . $this->identity() . '/current/' . basename($src);

                    imagepng($true_color, $dest_source, $img_quality);
                }
            } else {
                $this->flashMessenger()->addErrorMessage("Invalid image type given, only jpg and png files are supported.");
                return $this->redirect()->toRoute('members/profile', array('action' => 'crop-profile-image-failure'));
            }
        }

        return $view_model;
    }


    public function cropprofileimagefailureAction()
    {
        return;
    }


    public function changeprofilepictureAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);


        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            // remove all previous images
            foreach (array_diff(scandir('./data/images/profile/' . $this->identity() . '/current/', 1), array('.', '..')) as $values) {
                unlink('./data/images/profile/' . $this->identity() . '/current/' . $values);
            }

            $img_quality = 100;

            $width  = 200;
            $height = 200;

            list($w, $h) = getimagesize('./data/' . $params['image']);

            $ratio = $w / $h;

            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $true_color = imagecreatetruecolor($width, $height);

            $img = imagecreatefromjpeg('./data/images/profile/' . $this->identity() . '/' . basename($params['image']));

            imagecopyresampled($true_color, $img, 0, 0, 0, 0, $width, $height, $w, $h);

            $dest_source = './data/images/profile/' . $this->identity() . '/current/' . basename($params['image']);

            imagejpeg($true_color, $dest_source, $img_quality);
        }

        return $view_model;
    }


    public function getprofileimageAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        $params = $this->identity();

        
        $dir = @array_diff(@scandir('./data/images/profile/' . $params . '/current/', 1), array('.', '..'));

        
        if (!$dir) {
            // set default avatar
            $string_img = "<img src=\"/images/profile/defaults/avatar2.png\" class=\"w3-round w3-border\"
            style=\"width: 200px; height: 200px;\" alt=\"Avatar\" id=\"avatar\">";
        } else {
            $img = $dir[0];
            
            $string_img = "<img src=\"/images/profile/$params/current/$img\" class=\"w3-round w3-border\"
            style=\"width: 200px; height: 200px;\" alt=\"Avatar\" id=\"avatar\">";
        }

        $data = array(
            'profile_photo' => $string_img
        );

        echo $data['profile_photo'];

        return $view_model;
    }
    
    
   




    public function getProfileService()
    {
        if (!$this->profile_service) {
            $this->profile_service = $this->getServiceLocator()->get('Members\Model\ProfileModel');
        }

        return $this->profile_service;
    }
}
