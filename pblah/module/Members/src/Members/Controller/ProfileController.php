<?php
namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Members\Model\Classes\Exceptions\ProfileException;
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

        $dir = @array_diff(scandir(getcwd() . '/public/images/profile/' . $params . '/', 1), array('.', '..', 'current', '.htaccess'));

        if (count($dir) > 0) {
            $images = array();

            foreach ($dir as $value) {
                $images[] = "<img src=\"/images/profile/$params/$value\" class=\"w3-margin-bottom w3-round w3-border\" style=\"width: 100%; height: 88px;\">";
            }

            $layout = $this->layout();

            natsort($images);

            $layout->setVariable('my_images', $images);
        }
    }




    public function uploadfileAction()
    {
        if ($this->request->isPost()) {
            if (!empty($_FILES[$this->identity()])) {
                $file_info = array();
                
                // make directory if it doesnt exist
                if (!is_dir(getcwd() . '/public/images/profile/' . $this->identity())) {
                    mkdir(getcwd() . '/public/images/profile/' . $this->identity(), 0777);
                    mkdir(getcwd() . '/public/images/profile/' . $this->identity() . '/current');
                    
                    // make the htaccess file
                    $server = str_replace(array('https', 'http', 'www'), '', $_SERVER['SERVER_NAME']);
                    $data = "
                        Options -Indexes
                        RewriteEngine on 
                        RewriteCond %{HTTP_REFERER} !^$ 
                        RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?$server [NC]
                        RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]";
                    
                    file_put_contents(getcwd() . '/public/images/profile/' . $this->identity() . '/.htaccess', $data);
                   
                    
                    
                    if (!is_array($_FILES[$this->identity()]['name'])) {
                        // single file
                        $file_name = $_FILES[$this->identity()]['name'];
                        
                        move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                            getcwd() . '/public/images/profile/' . $this->identity() . '/' . $_FILES[$this->identity()]['name']);
                        
                        $file_info[$file_name] = getcwd() . '/public/images/profile/' . $this->identity() . '/' . $file_name;
                    } else {
                        // multiple files
                        $file_count = count($_FILES[$this->identity()]['name']);
                        
                        for ($i=0; $i<count($file_count); $i++) {
                            $file_name = $_FILES[$this->identity()]['name'][$i];
                            
                            $file_info[$file_name] = getcwd() . '/public/images/profile/' . $this->identity() . '/' . $file_name;
                            
                            move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                                getcwd() . '/public/images/profile/' . $this->identity() . '/' . $_FILES[$this->identity()]['name']);
                        }
                    }
                    
                    echo json_encode($file_info);
                } else {
                    if (!is_array($_FILES[$this->identity()]['name'])) {
                        // single file
                        $file_name = $_FILES[$this->identity()]['name'];

                        move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                            getcwd() . '/public/images/profile/' . $this->identity() . '/' . $_FILES[$this->identity()]['name']);

                        $file_info[$file_name] = getcwd() . '/public/images/profile/' . $this->identity() . '/' . $file_name;
                    } else {
                        // multiple files
                        $file_count = count($_FILES[$this->identity()]['name']);

                        for ($i=0; $i<count($file_count); $i++) {
                            $file_name = $_FILES[$this->identity()]['name'][$i];

                            $file_info[$file_name] = getcwd() . '/public/images/profile/' . $this->identity() . '/' . $file_name;

                            move_uploaded_file($_FILES[$this->identity()]['tmp_name'],
                                getcwd() . '/public/images/profile/' . $this->identity() . '/' . $_FILES[$this->identity()]['name']);
                        }
                    }

                    echo json_encode($file_info);
                }
            }
        }
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

                $this->getProfileService()->makeProfile(array(
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


            if ($mime_check->file(getcwd() . '/public/images/profile/' . $identity . '/' . basename($src)) == "image/jpg" || $mime_check->file(getcwd() . '/public/images/profile/'. $identity . '/' . basename($src)) == 'image/jpeg') {
                if (!$img = @imagecreatefromjpeg(getcwd() . '/public/images/profile/' . $identity . '/' . basename($src))) {
                    $this->flashMessenger()->addErrorMessage("Image is not a valid jpeg image!");
                    return $this->redirect()->toRoute('members/profile', array('action' => 'crop-profile-image-failure'));
                } else {
                    $img = imagecreatefromjpeg(getcwd() . '/public/images/profile/' . $identity . '/' . basename($src));
                    $true_color = imagecreatetruecolor($tw, $th);

                    imagecopyresampled($true_color, $img, 0, 0, $x, $y, $tw, $th, $w, $h);

                    $dest_source = getcwd() . '/public/images/profile/' . $this->identity() . '/current/' . basename($src);

                    imagejpeg($true_color, $dest_source, $img_quality);

                    // cropped okay
                    return $this->redirect()->toRoute('members', array('action' => 'index'));
                }
            } else if ($mime_check->file(getcwd() . '/public/images/profile/' . $identity . '/' . basename($src)) == "image/png") {
                if (!$img = @imagecreatefrompng(getcwd() . '/public/images/profile/' . $identity . '/' . basename($src))) {
                    $this->flashMessenger()->addErrorMessage("Image is not a valid png image!");
                    return $this->redirect()->toRoute('members/profile', array('action' => 'crop-profile-image-failure'));
                } else {
                    $img = imagecreatefrompng(getcwd() . '/public/images/profile/' . $identity . '/' . basename($src));
                    $true_color = imagecreatetruecolor($tw, $th);

                    imagecopyresampled($true_color, $img, 0, 0, $x, $y, $tw, $th, $w, $h);

                    $dest_source = getcwd() . '/public/images/profile/' . $this->identity() . '/current/' . basename($src);

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
            foreach (array_diff(scandir(getcwd() . '/public/images/profile/' . $this->identity() . '/current/', 1), array('.', '..')) as $values) {
                unlink(getcwd() . '/public/images/profile/' . $this->identity() . '/current/' . $values);
            }

            $img_quality = 100;

            $width  = 200;
            $height = 200;

            list($w, $h) = getimagesize(getcwd() . '/public/' . $params['image']);

            $ratio = $w / $h;

            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $true_color = imagecreatetruecolor($width, $height);

            $img = imagecreatefromjpeg(getcwd() . '/public/images/profile/' . $this->identity() . '/' . basename($params['image']));

            imagecopyresampled($true_color, $img, 0, 0, 0, 0, $width, $height, $w, $h);

            $dest_source = getcwd() . '/public/images/profile/' . $this->identity() . '/current/' . basename($params['image']);

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

        $dir = @array_diff(@scandir(getcwd() . '/public/images/profile/' . $params . '/current/', 1), array('.', '..'));

        if (!$dir) {
            // set default avatar
            $string_img = "<img src=\"/images/profile/defaults/avatar2.png\" class=\"w3-round w3-border\"
            style=\"width: 200px; height: 200px;\" alt=\"Avatar\" id=\"avatar\">";
        } else {
            $string_img = "<img src=\"/images/profile/$params/current/$dir[0]\" class=\"w3-round w3-border\"
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


    public function getEditProfileService()
    {
        if (!$this->edit_profile_service) {
            $this->edit_profile_service = $this->getServiceLocator()->get('Members\Model\EditProfileModel');
        }

        return $this->edit_profile_service;
    }
}
