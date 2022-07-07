<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Call_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function insertCall($data){
        $this->db->select('*');
        $this->db->from('ems_comu_app_call');
        $this->db->where('mobile_no',$data['mobile_no']);
        $checkRec = $this->db->get();
        if ($checkRec->num_rows() > 0 )
        {
            $this->db->where('mobile_no',$data['mobile_no'])->update('ems_comu_app_call',$data);
        }else{
            $this->db->insert('ems_comu_app_call',$data);
        }
        $data1['mobile_no'] = $data['mobile_no'];
        $data1['lat'] = $data['lat'];
        $data1['lng'] = $data['lng'];
        $data1['added_date'] = date('Y-m-d H:i:s');
        $data1['call_status'] = "Call Dial";
        $this->db->insert('ems_comu_app_call_details',$data1);
        return $this->db->insert_id();
        //return 1;
    }
    public function saveImg($data){
        $this->db->insert('ems_comu_app_call_imgvideo',$data);
    }
//function for fetch link
    public function getmaplink($mobile){
        $this->db->select('*');
        $this->db->from('ems_incident_tracklink');
        $this->db->where('mobile_no',$mobile);
        $this->db->order_by("added_date", "ASC");
        $query = $this->db->get();
        if ($query->num_rows() > 0 )
        {
            return $query->result_array();
        }
    }
}