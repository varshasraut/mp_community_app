<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Calcutta");
require APPPATH . '/libraries/REST_Controller.php';
class Login extends REST_Controller {
    public function __construct() { 
        parent::__construct();
        $this->load->model(array('Login_model','Common_model'));
        $this->load->library('encryption');
        $this->load->helper(array('cookie', 'url'));
    }
    public function index_post(){
         
    }
    public function usrregister_post(){
        $data['f_name'] = $this->post('fName');
        $data['l_name'] = $this->post('lName');
        $data['email'] = $this->post('email');
        $data['mobile'] = $this->post('mobile');
        $data['gender'] = $this->post('gender');
        $data['address'] = $this->post('address');
        $data['age'] = $this->post('age');
        $data['blood_grp'] = $this->post('bloodGrp');
        $data['uer_passward'] = MD5($this->post('password'));
        $otp = rand(1000, 9999);
        // $mobileNo = $data['mobile'];
        // $form_url = "http://www.unicel.in/SendSMS/sendmsg.php";
        // $txtMsg='';
        // $txtMsg .= "OTP password is : $otp. OTP is valid for 30 Minutes";
        
        // $data_to_post = array();
        // $data_to_post['uname'] = 'bvgmems';
        // $data_to_post['pass'] = 'm2v5c2';
        // $data_to_post['send'] = 'SperocHL';
        // $data_to_post['dest'] = $mobileNo; 
        // $data_to_post['msg'] = $txtMsg;
    
        // $curl = curl_init();
        // curl_setopt($curl,CURLOPT_URL, $form_url);
        // curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl,CURLOPT_POSTFIELDS, $data_to_post);
        // $result = curl_exec($curl);
        // curl_close($curl);
        $current_time = date('Y-m-d H:i:s');
        $OTP_timestamp = strtotime($current_time) + 30*60;
        $otp_expiry_time = date('Y-m-d H:i:s', $OTP_timestamp);
        $data['otp'] = $otp;
        $data['otp_expire_time'] = $otp_expiry_time;
        $usrReg = $this->Login_model->chkMobExist($data);
        if($usrReg == 1){
            $this->response([
                'data' => ([
                    'code' => 1,
                    'message' => 'Mobile number already exist'
                ]),
                'error' => null
            ],REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'data' => ([
                    'code' => 2,
                    'message' => 'User Registration Sucessfully',
                    'otp' => $otp
                ]),
                'error' => null
            ],REST_Controller::HTTP_OK);
        }
    }
    public function chkotp_post(){
        $data['mobile'] = $this->post('mobile');
        $otp = $this->post('otp');
        $password = md5($this->post('password'));
        $deviceId = $this->post('deviceId');
        $otpPass = $this->post('otpPass'); //otp :1 and pass: 2

        $current_time = date('Y-m-d H:i:s');
        $usrData = $this->Common_model->getUsrData($data);
        $usrLoginData = $this->Login_model->getUsrLogin($data);
        $loginSecretKey = md5($usrData[0]['f_name'].$usrData[0]['email'].date('Y-m-d H:i:s'));
        $data1['usr_id'] = $usrData[0]['usr_id'];
        $data1['mobile'] = $data['mobile'];
        $data1['login_time'] = date('Y-m-d H:i:s');
        $data1['unique_id'] = $deviceId;
        $data1['login_status'] = "1";
        $data1['login_secret_key'] = $loginSecretKey;

        if($usrLoginData == 2){
            if($otpPass == 1){
                if(($usrData[0]['otp'] == $otp)){
                    if(($usrData[0]['otp_expire_time'] >= $current_time)){
                        $this->Login_model->usrLogin($data1);
                        $this->response([
                            'data' => ([
                                'code' => 1,
                                'message' => 'Sucessfully Login'
                            ]),
                            'error' => null
                        ],REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'data' => null,
                            'error' => ([
                                'code' => 2,
                                'message' => 'OTP expired'
                            ])
                        ],REST_Controller::HTTP_OK);
                    }
                }else{
                    $this->response([
                        'data' => null,
                        'error' => ([
                            'code' => 3,
                            'message' => 'Invalid OTP'
                        ])
                    ],REST_Controller::HTTP_OK);
                }
            }else{
                if(($usrData[0]['uer_passward'] == $password)){
                    $this->Login_model->usrLogin($data1);
                    $this->response([
                        'data' => ([
                            'code' => 1,
                            'message' => 'Sucessfully Login'
                        ]),
                        'error' => null
                    ],REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'data' => null,
                        'error' => ([
                            'code' => 3,
                            'message' => 'Invalid Passward'
                        ])
                    ],REST_Controller::HTTP_OK);
                }
            }
        }else{
            $this->response([
                'data' => null,
                'error' => ([
                    'code' => 4,
                    'message' => 'Already User Login'
                ])
            ],REST_Controller::HTTP_OK);
        }
    }
    public function signin_post(){
        $data['mobile'] = $this->post('mobile');
        $otpPass = $this->post('otpPass'); //otp :1 and pass: 2
        $sms_autofill = $this->post('sms_autofill');
        $usrReg = $this->Common_model->getUsrData($data);
        if($otpPass == 1){
            if(!empty($usrReg)){
                $otp = rand(1000, 9999);
                $mobileNo = $data['mobile'];
                // $form_url = "http://www.unicel.in/SendSMS/sendmsg.php";
                // $txtMsg='';
                // $txtMsg .= "OTP password is : $otp. OTP is valid for 30 Minutes";
                
                // $data_to_post = array();
                // $data_to_post['uname'] = 'bvgmems';
                // $data_to_post['pass'] = 'm2v5c2';
                // $data_to_post['send'] = 'SperocHL';
                // $data_to_post['dest'] = $mobileNo; 
                // $data_to_post['msg'] = $txtMsg;
            
                // $curl = curl_init();
                // curl_setopt($curl,CURLOPT_URL, $form_url);
                // curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
                // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($curl,CURLOPT_POSTFIELDS, $data_to_post);
                // $result = curl_exec($curl);
                // curl_close($curl);

                $form_url = "http://www.mgage.solutions/SendSMS/sendmsg.php";
                $txtMsg='';
                $txtMsg .= "BVG,"." \n";
                $txtMsg .=  "OTP for your login is : ".$otp.""." \n";
                $txtMsg .=  "OTP is valid for 15 Minutes"." \n";
                $txtMsg .=  "MEMS";
                $data_to_post = array();
                $data_to_post['uname'] = 'BVGMEMS';
                $data_to_post['pass'] = 'Mems@108';//s1M$t~I)';
                $data_to_post['send'] = 'BVGMEM';
                $data_to_post['dest'] = $mobileNo; 
                $data_to_post['msg'] = $txtMsg;
                $curl = curl_init();
                curl_setopt($curl,CURLOPT_URL, $form_url);
                curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl,CURLOPT_POSTFIELDS, $data_to_post);
                $result = curl_exec($curl);
                curl_close($curl);

                $current_time = date('Y-m-d H:i:s');
                $OTP_timestamp = strtotime($current_time) + 30*60;
                $otp_expiry_time = date('Y-m-d H:i:s', $OTP_timestamp);
                $update['otp'] = $otp;
                $update['otp_expire_time'] = $otp_expiry_time;
                $updateOtp = $this->Login_model->updateOtp($data,$update);
                $this->response([
                    'data' => ([
                        'otp' => $otp
                    ]),
                    'error' => null
                ],REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'data' => null,
                    'error' => ([
                        'code' => 2,
                        'message' => 'Please first register'
                    ])
                ],REST_Controller::HTTP_OK);
            }
        }else{
            if(!empty($usrReg)){
                $this->response([
                    'data' => ([
                        'code' => 1,
                        'message' => 'Sucess'
                    ]),
                    'error' => null
                ],REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'data' => null,
                    'error' => ([
                        'code' => 2,
                        'message' => 'Please first register'
                    ])
                ],REST_Controller::HTTP_OK);
            }
        }
    }
    public function logout_post(){
        $data['login_secret_key'] =  $this->post('loginSecretKey');
        $data['uniqueId'] =  $this->post('uniqueId');
        $data['mobile'] =  $this->post('mobile');
        $auth = $this->Login_model->checkLoginUserforAuth($data);
        if($auth == 1){
            $log = $this->Login_model->logout($data);
            if($log == 1){
                $this->response([
                    'data' => ([
                        'code' => 1,
                        'message' => 'Sucessfully Logout'
                    ]),
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
     public function resendOtp_post(){
        $mobile = $this->post('mobile');
        $otp = rand(1000, 9999);
        // $mobileNo = $mobile;
        // $form_url = "http://www.unicel.in/SendSMS/sendmsg.php";
        // $txtMsg='';
        // $txtMsg .= "OTP password is : $otp. OTP is valid for 30 Minutes";
        
        // $data_to_post = array();
        // $data_to_post['uname'] = 'bvgmems';
        // $data_to_post['pass'] = 'm2v5c2';
        // $data_to_post['send'] = 'SperocHL';
        // $data_to_post['dest'] = $mobileNo; 
        // $data_to_post['msg'] = $txtMsg;
    
        // $curl = curl_init();
        // curl_setopt($curl,CURLOPT_URL, $form_url);
        // curl_setopt($curl,CURLOPT_POST, sizeof($data_to_post));
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl,CURLOPT_POSTFIELDS, $data_to_post);
        // $result = curl_exec($curl);
        // curl_close($curl);
        $current_time = date('Y-m-d H:i:s');
        $OTP_timestamp = strtotime($current_time) + 30*60;
        $otp_expiry_time = date('Y-m-d H:i:s', $OTP_timestamp);
        $data['otp'] = $otp;
        $data['otp_expire_time'] = $otp_expiry_time;
        $usrReg = $this->Login_model->resendOtpUpdate($data,$mobile);
        if($usrReg == 1){
            $this->response([
                'data' => ([
                    'code' => 1,
                    'message' => 'OTP sent on mobile no',
                    'otp' => $otp
                ]),
                'error' => null
            ],REST_Controller::HTTP_OK);
        }
    }
}