<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Calcutta");
// Load the Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';
// Authentication
class Device extends REST_Controller {

    public function __construct() { 
        parent::__construct();
        // Load the user model
        $this->load->model('Device_model');
        $this->load->helper('string');
        $this->load->helper('number');
    }
    public function index_post(){
        $uniqueId = $this->post('uniqueId');
        $deviceId = $this->post('deviceId');
        $osVersion = $this->post('osVersion');
        $osName = $this->post('osName');
        $devicePlatform = $this->post('devicePlatform');
        $appVersion = $this->post('appVersion');
        $deviceTimezone = $this->post('deviceTimezone');
        $deviceCurrentTimestamp = $this->post('deviceCurrentTimestamp');
        $token = $this->post('token');
        $modelName = $this->post('modelName');  
        $data = array(
            'device_id' =>$deviceId,
            'os_version' => $osVersion,
            'os_name' => $osName,
            'device_platform' => $devicePlatform,
            'app_version' => $appVersion,
            'device_timezone' => $deviceTimezone,
            'date_time' => $deviceCurrentTimestamp,
            'token' => $token,
            'model_name' =>$modelName
        ); 
        $version = $this->Device_model->getCurrentversion($osName);
        $currentversion = $version[0]['osVersion'];
        $locationpath = $version[0]['location_path'];
        $previousversion = $version[0]['currentversion'];
        if($uniqueId==0)
	    {
            if($osVersion == '' || $osName == '' || $devicePlatform == '' || $appVersion == '' || $deviceTimezone == '' || $deviceCurrentTimestamp == '' || $modelName == '')
            {
                $this->response([
                    'data' => ([]),
                    'error' => null
                ],REST_Controller::HTTP_OK);
            }
            else
            {
                $currentId = $this->Device_model->insertDeviceVersion($data);
                $this->response([
                    'data' => ([
                        'uniqueId' => (int) $currentId,
                    
                    'versionInfo' => ([
                        'id' => (int)$version[0]['device_version_id'],
                        'deviceId' => $deviceId,
                        'devicePlatform' => $version[0]['osName'],
                        'currentVersion' => (int)$previousversion,
                        'lastCompulsoryVersion' => (int)$currentversion,
                        'locationPath'=> $locationpath
                    ]),
                    ]),
                    'error' => null
                ], REST_Controller::HTTP_OK);
            }
        }else{
            if($osVersion == '' || $osName == '' || $devicePlatform == '' || $appVersion == '' || $deviceTimezone == '' || $deviceCurrentTimestamp == '' || $modelName == '')
            {
                $this->response("Please provide data", REST_Controller::HTTP_BAD_REQUEST);
            }
            else
            {
                $checkDeviceId = $this->Device_model->updateDeviceVersion($data,$uniqueId);
                if($checkDeviceId == 1){
                    $this->response([
                        'data' => null,
                        'error' => ([
                            'code' => 1,
                            'message' => 'Device ID Do not exist'
                        ])
                    ],REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'data' => ([
                            'uniqueId' => (int) $uniqueId,
                        
                        'versionInfo' => ([
                            'id' => (int)$version[0]['device_version_id'],
                            'deviceId' => $deviceId,
                            'devicePlatform' => $version[0]['osName'],
                            'currentVersion' => (int)$previousversion,
                            'lastCompulsoryVersion' => (int)$currentversion,
                            'locationPath'=> $locationpath
                        ]),
                        ]),
                        'error' => null
                    ], REST_Controller::HTTP_OK);
                }
            }
        }

    }
}