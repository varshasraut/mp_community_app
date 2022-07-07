<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function getUsrData($data){
        if($data['mobile'] != ''){
            $this->db->select('*');
            $this->db->from('ems_comu_app_user_reg');
            $this->db->where('mobile',$data['mobile']);
            $query = $this->db->get();
            if ($query->num_rows() > 0 )
            {
                return $query->result_array();
            }
        }
    }
    public function getUserLogs($mobile1){
        $this->db->select('*');
        $this->db->from('ems_comu_app_call_details');
        $this->db->where('mobile_no',$mobile1);
        $this->db->order_by("added_date", "DESC");
        $query = $this->db->get();
        if ($query->num_rows() > 0 )
        {
            return $query->result_array();
        }
    }
}