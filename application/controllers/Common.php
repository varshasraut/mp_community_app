<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Calcutta");
require APPPATH . '/libraries/REST_Controller.php';
class Common extends REST_Controller {
    public function __construct() { 
        parent::__construct();
        $this->load->model(array('Common_model'));
    }
  public function bloodgrp_post(){
    
    $rec = $this->Common_model->getbloodgrp();
    $this->response([
        'data' => $rec,
        'error' => null
    ],REST_Controller::HTTP_OK);
  }

           

}