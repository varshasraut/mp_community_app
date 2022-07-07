<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Device_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->deviceVersion = 'ems_comu_app_device_version';
        $this->appVersion = 'ems_comu_app_device_version_info';
    }
    function insertDeviceVersion($data){
        $this->db->insert($this->deviceVersion,$data);
        return $this->db->insert_id();
    }
    function updateDeviceVersion($data,$uniqueId){
        $checkId = $this->db->where('unique_id',$uniqueId)->get($this->deviceVersion)->result_array();
        if(empty($checkId)){
            return 1;
        }else{
            $this->db->where('unique_id',$uniqueId)->update($this->deviceVersion,$data);
        }
    }
    function getCurrentversion($osName){
        return $this->db->where('osName',$osName)->get($this->appVersion)->result_array();
    }
}