<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class User_family_info_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function storeUserFamilyInfo($data)
    {
        $this->db->insert('ems_comu_app_family_info',$data);
        return 1;
    }
    public function checkUserId($usrMobNo){
        $this->db->select('usr_id,mobile');
        $this->db->from('ems_comu_app_user_reg');
        $this->db->where('mobile',$usrMobNo);
        $checkRec = $this->db->get();
        if ($checkRec->num_rows() > 0 )
        {
            return $checkRec->result();
        }else{
            return 1;
        }
    }
    public function delete_user_fam_records($id){
        $data['fam_is_deleted'] = '1';
        $this->db->where('fam_id', $id);
        $this->db->update('ems_comu_app_family_info',$data);
        return 1;
    }
    public function update_user_fam_records($id,$data){
        $this->db->where('fam_id', $id);
        $this->db->update('ems_comu_app_family_info',$data);
        return 1;
    }

    public function get_user_all_fam_info($usrMobNo){
        $this->db->select('usr.mobile','usr.usr_id','fam.fam_f_name','fam.fam_l_name','fam.fam_age','fam.fam_blood_grp','fam.fam_email','fam.fam_mobile','fam.fam_gender','fam.fam_address');
        $this->db->from('ems_comu_app_user_reg usr');
        $this->db->join("ems_comu_app_family_info fam","usr.usr_id = fam.usr_id","left");
        $this->db->where('fam.fam_is_deleted','0');
        $this->db->where('usr.mobile',$usrMobNo);
        $query = $this->db->get();
        if ($query->num_rows() > 0 )
        {
            return $query->result_array();
        }
        
    }

}