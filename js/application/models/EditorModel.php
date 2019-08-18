<?php
class EditorModel extends CI_Model {
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getMandalsByConstituence($location) {
        $this->db->select('l.id, l.name, l.level_id');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('cl.parent_id', $location);
        $this->db->where('cl.level_id', 45);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }	
    }

    public function getPollingStationByMandal($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_locations as l'); //mandal
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id'); //village
        $this->db->join('tbl_ps as p', 'p.village_id = l1.id');
        $this->db->where('l.id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }	
    }

    public function mobileVerify($mobile) {
        if($mobile == '0000000000' && $this->input->post('email')) {
            return false;
        }else {
            $this->db->select('id');
            $this->db->from('tbl_users');
            $this->db->where('mobile', $mobile);
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                return $result->row();
            }else {
                return false;
            }
        }
        
    }

    public function emailVerify($email) {
        $this->db->select('id');
        $this->db->from('tbl_users');
        $this->db->where('email', $email);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function addUser(array $data) {
        //Booth President exists
        $user_info = $this->getUserInfoByPS($data['psid']);
        if($user_info) {
            
            $this->db->trans_begin();
            //users table
            $user_d = array(
                'first_name' => $data['firstname'],
                // 'last_name' => $data['lastname'],
                // 'f_name' => $data['fathername'],
                'gender' => $data['gender'],
                // 'dob'   => date('Y-m-d', strtotime($data['dob'])),
                'mobile' => $data['mobile'],
                'photo' => $data['photo'],
                // 'caste' => $data['caste'],
                // 'category' => $data['category'],
                'user_role' => 3,
                'status' => 1,
                'created_by' => 83
            );
            if($this->input->post('email')) {
                $user_d['email'] = $this->input->post('email');
            }
            if($this->input->post('lastname')) {
                $user_d['last_name'] = $this->input->post('lastname');
            }
            if($this->input->post('fathername')) {
                $user_d['f_name'] = $this->input->post('fathername');
            }
            if($this->input->post('dob')) {
                $user_d['dob'] = date('Y-m-d', strtotime($data['dob']));
            }
            if($this->input->post('caste')) {
                $user_d['caste'] = $this->input->post('caste');
            }
            if($this->input->post('category')) {
                $user_d['category'] = $this->input->post('category');
            }
            //booth observer as sheet president
            $bpregister = false;
            if($this->input->post('email')) {
                $email = $data['email'];
                $em_array = explode('@', $email);
                $phone = $em_array[0];
                if(is_numeric($phone) && strlen($phone) == 10) {
                    $exists = $this->isUserBO($phone);
                    if($exists) {
                        
                        unset($user_d['mobile']);
                        $bpregister = true;
                    } 
                }
            }
            $this->db->insert('tbl_users', $user_d);
            $uid = $this->db->insert_id();
            
            if($bpregister && $exists) {
                
                $activate = $this->activateBPasSP($uid, $exists->id);
            }
            //family size table
            if($this->input->post('family_size')) {
                $fs_d = array(
                    'user_id' => $uid,
                    'family_size' => $data['family_size']
                );
                $this->db->insert('tbl_family_size', $fs_d);
            }

            //team mng
            $tm_d = array(
                'user_id' => $uid,
                'parent_id' => $user_info->id,
                'location' => $user_info->location_id,
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => 83
            );
            $this->db->insert('tbl_team_mng', $tm_d);

            //team ps table
            $tm_p = array(
                'user_id' => $uid,
                'ps_id' => $data['psid'],
                'status' => 1,
                'created_by' => 83
            );
            $this->db->insert('tbl_team_ps', $tm_p);

            //address table
            $ad_d = array(
                'id' => $uid,
                // 'street' => $data['street'],
                'location' => $user_info->location_id,
            );
            if($this->input->post('street')) {
                $ad_d['street'] = $this->input->post('street');
            }
            if($this->input->post('h_no')) {
                $ad_d['house_no'] = $this->input->post('h_no');
            }
            if($this->input->post('pincode')) {
                $ad_d['pincode'] = $this->input->post('pincode');
            }
            $this->db->insert('tbl_address', $ad_d);

            
            
            if($this->db->trans_status() === FALSE)  {
                $this->db->trans_rollback();
                return false;
            }else {
                $this->db->trans_commit();
                return $uid;
            }
        }else {
            return false;
        }    
    }

    public function getUserInfoByPS($id) {
        $this->db->select('u.id, u.first_name, u.last_name, lc.name as location, lc.id as location_id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id');
        $this->db->join('tbl_locations as lc', 't.location = lc.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function isUserBO($phone) {
        $this->db->select('u.id');
        $this->db->from('tbl_users as u');
        $this->db->where('u.mobile', $phone);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function activateBPasSP($spid, $boid) {
        $this->db->trans_begin();
        //download table
        $down_d = array(
            'user_id' => $spid,
            'app_id' => 78
        );
        $this->_sdb->insert('tbl_download', $down_d);
        
        //device table
        $this->_sdb->select('dv.app_id, dv.device_id, dv.primary_no, dv.secondary_no, dv.serial, dv.model, dv.manufacture, dv.brand, dv.sdk, dv.version_code, dv.account_info');
        $this->_sdb->from('tbl_device as dv');
        $this->_sdb->where('dv.user_id', $boid);
        $this->_sdb->where('dv.app_id', 81);
        $dv_result = $this->_sdb->get();
        if($dv_result->num_rows() > 0) {
            $device = $dv_result->row();
            $sp_dv = array(
                'user_id' => $spid,
                'app_id' => 78,
                'device_id' => $device->device_id,
                'primary_no' => $device->primary_no,
                'secondary_no' => $device->secondary_no,
                'serial' => $device->serial,
                'model' => $device->model,
                'manufacture' => $device->manufacture,
                'brand' => $device->brand,
                'sdk' => $device->sdk,
                'version_code' => $device->version_code,
                'account_info' => $device->account_info,
                'status' => 1
            );
            $this->_sdb->insert('tbl_device', $sp_dv);
        }

        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return $uid;
        }
    }
}