<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Calcutta");
require APPPATH . '/libraries/REST_Controller.php';
class Call extends REST_Controller {
    public function __construct() { 
        parent::__construct();
        $this->load->model(array('Call_model','Common_model','Login_model'));
        $this->load->library('encryption');
        $this->load->helper(array('cookie', 'url'));
        $this->load->library('upload');
        $this->load->helper('string');
    }
    public function index_post(){
        $chkLoging['login_secret_key'] =  $this->post('loginSecretKey');
        $chkLoging['uniqueId'] =  $this->post('uniqueId');
        $chkLoging['mobile'] =  $this->post('mobile');
        $auth = $this->Login_model->checkLoginUserforAuth($chkLoging);
        if($auth == 1){
            $mobile = $this->post('mobile');
            $lat = $this->post('lat');
            $lng = $this->post('lng');
            $data['mobile_no'] = $mobile;
            $data['lat'] = $lat;
            $data['lng'] = $lng;
            $data['added_date'] = date('Y-m-d H:i:s');
            $callSaveId = $this->Call_model->insertCall($data);
            $this->response([
                'data' => ([
                    'callId' => $callSaveId,
                    'code' => 1,
                    'message' => "Inserted Successfully"
                ]),
                'error' => null
            ],REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'data' => ([]),
                'error' => null
            ],REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
    public function imgVideo_post(){
        $chkLoging['login_secret_key'] =  $this->post('loginSecretKey');
        $chkLoging['uniqueId'] =  $this->post('uniqueId');
        $chkLoging['mobile'] =  $this->post('mobile');
        $auth = $this->Login_model->checkLoginUserforAuth($chkLoging);
        $callId = $this->post('callId');
        $chkUploadImg = $this->post('chkUploadImg');
        $chkUploadVideo = $this->post('chkUploadVideo');
        if($auth == 1){
            $configVideo['upload_path'] = FCPATH . 'videozip';
            $configVideo['max_size'] = '20000000';
            $configVideo['allowed_types'] = 'zip';
            $configVideo['overwrite'] = FALSE;
            $configVideo['remove_spaces'] = TRUE;
            $video_name = random_string('numeric', 5);
            $configVideo['file_name'] = $video_name;
            $configVideo['client_name'] = $video_name;
            $this->upload->initialize($configVideo);
        
            $extractpath = FCPATH . "calldata";
            //Create directory
        
            $date = date("Y_m_d");
            if (is_dir($extractpath)) {
                if (!is_dir($extractpath . '/' . $date)) {
                $mainDir = mkdir($extractpath . '/' . $date);
                } else {
                $mainDir = $extractpath . '/' . $date;
                }
        
                if (is_dir($extractpath . '/' . $date)) {
                // create a dir with incident number
                    if (!empty($callId)) {
                        if(!is_dir($extractpath . '/' . $date . '/' . $callId)){
                        $dir = mkdir($extractpath . '/' . $date . '/' . $callId);
                        $dir = $extractpath . '/' . $date . '/' . $callId;
                        } else {
                        $dir = $extractpath . '/' . $date . '/' . $callId;
                        }
                    }
                }
            }
            if($chkUploadImg == "true") {
                $imageFlag = false;
                $imageData =  $this->processImagesData($mainDir,$dir,$callId);
                if (strpos($imageData, "UploadError" !== false)) {
                  $errorString = explode("@#@", $imageData);
                  $aaa =  explode("##", $errorString[1]);
                  return $this->response([
                    'data' => $aaa,
                    'error' => null
                  ],REST_Controller::HTTP_OK);
                } else {
                  $imageFlag = true;
                }
            }
            if($chkUploadVideo == "true") {
                $videoData = $this->processVideoData($mainDir,$dir, $this->upload->data(),$callId);
                $videoFlag = false;
                if (!empty($videoData)) {
                    // check is it string contain error message
                    if (strpos($videoData, "UploadError" !== false)) {
                        $errorString = explode("@#@", $videoData);
                        return $this->response([
                            'data' => $errorString[1],
                            'error' => null
                        ],REST_Controller::HTTP_OK);
                    } else {
                        $videoFlag = true;
                    }
                    
                }
            }else{
                $videoFlag = false;
            }
            
            if ($videoFlag && $imageFlag) {
                return $this->response([
                  'data' => 'Success',
                  'error' => null
                ],REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'data' => ([]),
                    'error' => null
                ],REST_Controller::HTTP_UNAUTHORIZED);
            }
        }else{
            $this->response([
                'data' => ([]),
                'error' => null
            ],REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
    public function processImagesData($mainDir,$dir,$callId) {
        if(isset($mainDir) && isset($dir)){
          // if($chkUploadImg == "true"){
            $number_of_files = sizeof(($_FILES['uploadedimages']['tmp_name']));
            $files = $_FILES['uploadedimages'];
            $errors = array();
            $errorArr = array();
            
            for($i=0;$i<$number_of_files;$i++)
            {
              if($_FILES['uploadedimages']['error'][$i] != 0) $errors[$i][] = 'Couldn\'t upload file '.$_FILES['uploadedimages']['name'][$i];
            }
            if(sizeof($errors)==0)
            {
              $imgSize = 0;
              $config['upload_path'] =  $dir;
              $config['allowed_types'] = '*';
              $image1 = array();
              
              for ($i = 0; $i < $number_of_files; $i++) {
                $_FILES['uploadedimage']['name'] = $files['name'][$i];
                $_FILES['uploadedimage']['type'] = $files['type'][$i];
                $_FILES['uploadedimage']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['uploadedimage']['error'] = $files['error'][$i];
                $_FILES['uploadedimage']['size'] = $files['size'][$i];
                // $imgSizeCal = $imgSize + $_FILES['uploadedimage']['size'];
                // array_push($imgessize,$imgSizeCal);
                
                $ext = explode(".", $files['name'][$i]);
                $_FILES['uploadedimage']['name'] = date('Y_m_d_H_i_s_') . $i . "." . $ext[1];
                $image = date("Y_m_d")."/".$callId."/".date('Y_m_d_H_i_s_') . $i . "." . $ext[1];
                array_push($image1,$image);
                $this->upload->initialize($config);
                if (! $this->upload->do_upload('uploadedimage')) {
                  $data['upload_errors'][$i] = $this->upload->display_errors();
                  $errorArr[] = $data['upload_errors'][$i];
                }
                
                unset($ext, $imageFileName);
              }
              
              if (empty($errorArr)) {
                $str = '';
                $data['imgvideo_path'] = implode(",",$image1);
                $data['call_id'] = $callId;
                $this->Call_model->saveImg($data);
                // echo "sucess". $str;
                  $this->response([
                    'data' => [
                        "code" => 1,
                        "message" => "Sucessfully Uploaded"
                      ],
                    'error' => null
                  ],REST_Controller::HTTP_OK);
                //  echo "uploadSuccess";
                // exit;
              }else {
                $str = '';
                for($j=0;$j<=count($errorArr);$j++){
                  $str .= $errorArr[$j]."##";
                }
                echo "UploadError@#@". $str;
                exit;
              }
   
            }
            else{
              return $this->response([
                'data' => $errors,
                'error' => null
              ],REST_Controller::HTTP_OK);
            }
        }
    }
    public function processVideoData($mainDir,$dir,$uploadData,$callId) {
        if(isset($mainDir) && isset($dir) && isset($uploadData)){
            if (!$this->upload->do_upload('video')) 
            {
                echo "UploadError";
                echo "@#@";
                echo $this->upload->display_errors();
                exit;
            } else {
                $data = $this->upload->data();
                $zip_file  = new ZipArchive;
                $full_path = $data['full_path']; 
                $extractpath = FCPATH . "callvideo";
                $this->upload->initialize($data);
                if ($zip_file->open($full_path) === TRUE)
                {
                    // create a folder datewise
                    $zip_file->extractTo($dir);
                    $files1 = scandir($dir,1);
                    if(!empty($files1[0])) {
                        $filearray = explode('.', $files1[0]);
                        if($filearray[1]){
                            $fileName = rename($dir . '/'. $files1[0], $dir .'/'. date('Y_m_d_H_i_s') . '.' .$filearray[1]);
                            $VideoName = date('Y_m_d_H_i_s') . '.' .$filearray[1];
                        }
                    }
                    $zip_file->close();
                    $url = date("Y_m_d")."/".$callId."/".$files1[1];
                    $data1['imgvideo_path'] = $url;
                    $data1['call_id'] = $callId;
                    $this->Call_model->saveImg($data1);
                    $str = '';
                    // echo "sucess". $str;
                    $this->response([
                        'data' => [
                            "code" => 1,
                            "message" => "Sucessfully Uploaded"
                        ],
                        'error' => null
                    ],REST_Controller::HTTP_OK);
                    exit;
                }  
            }
        }
      }
    public function maplink_post() {

        $chkLoging['login_secret_key'] =  $this->post('loginSecretKey');
        $chkLoging['uniqueId'] =  $this->post('uniqueId');
        $chkLoging['mobile'] =  $this->post('mobile');
        $auth = $this->Login_model->checkLoginUserforAuth($chkLoging);
        if($auth == 1){
            $mobile = $this->post('mobile');
            $linkdetails = $this->Call_model->getmaplink($mobile);
            if(empty($linkdetails)){
                $this->response([
                    'data' => [],
                    'error' => null
                ],REST_Controller::HTTP_OK);
            }else{
                $data1 = array();
                foreach($linkdetails as $linkdetails1){
                    $data = array(
                        "link" => $linkdetails1['track_link']
                    
                    );
                    
                }
                $this->response([
                    'data' => $data,
                    'error' => null
                ],REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'data' => ([]),
                'error' => null
            ],REST_Controller::HTTP_UNAUTHORIZED);
        }
            
    }

}