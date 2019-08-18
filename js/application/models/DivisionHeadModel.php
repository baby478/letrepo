<?php
class DivisionHeadModel extends CI_Model {
    /**
     * Date : 07-01-2019
     */
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getMandalPs($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l1', 'tm.location = l1.id'); //mandal
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); // village
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->where('tm.user_id', $id);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDivisionInchargeByDM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDivisionInchargePS($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('tp.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothObserverByDM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        } 
    }

    public function getBoothObserverPS($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('tp.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothPresidentByDM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role, p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_ps as tp', 'tm.user_id = tp.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSPCountByBP($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    /**
     * Date : 08-01-2019
     */
    public function getSheetPresidentByBP($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersCountBySP($id) {
        $this->db->select('c.citizen_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->where('c.user_id', $id);
        //$this->db->where('c.user_role', 17);
        return $this->db->get()->num_rows();
    }

    public function getFamilyHeadBySP($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->where('cm.user_id', $id);
        $this->db->where('cm.parent_id', $id);
        $this->db->where('cm.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersCountByFH($id) {
        $this->db->select('cm.id');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.parent_id', $id);
        $this->db->where('cm.user_role', 17);
        return $this->db->get()->num_rows();
    }

    public function getVotersBySP($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.lfname, v.llastname, v.f_name, v.lrname, v.gender, v.photo, v.age, v.voter_id, v.hno, v.mobile');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'v.id = c.citizen_id');
        $this->db->where('c.user_id', $id);
        $this->db->where('c.parent_id', $id);
        $this->db->where('c.user_role', 17);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersByFH($fh_id) {
        $this->db->select('v.firstname, v.lastname, v.mobile, v.gender, v.photo, v.voter_id, c.citizen_id, p.ps_no, p.ps_name');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_ps as p', 'v.ps_no = p.id');
        $this->db->where('c.parent_id', $fh_id);
        $this->db->where('c.group_id', 40);
        $this->db->where('c.user_role', 17);
        // $this->db->where('c.local_status', 15);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function diExistsByPs($id) {
        $this->db->select('id, user_id, ps_id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('tp.status', 1);
        $tp_result = $this->db->get();
        if($tp_result->num_rows() > 0) {
            $this->db->select('p.ps_no');
            $this->db->from('tbl_team_ps as tp');
            $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->where('u.user_role', 2);
            $this->db->where('u.status', 1);
            $this->db->where('tp.status', 1);
            $this->db->where('tp.ps_id', $id);
            $u_result = $this->db->get();
            if($u_result->num_rows() > 0) {
                return $u_result->row();
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

    public function mobileVerify($mobile) {
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

    public function addDivisionIncharge($data) {
        $this->db->trans_begin();
        //users table
        $user_d = array(
            'first_name' => $data['firstname'],
            'gender' => $data['gender'],
            'mobile' => $data['mobile'],
            'photo' => $data['photo'],
            'user_role' => 2,
            'status' => 1,
            'created_by' => $data['user_id']
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
        if($this->input->post('voter_id')) {
            $user_d['voter_id'] = $this->input->post('voter_id');
        }
        
        $this->db->insert('tbl_users', $user_d);
        $uid = $this->db->insert_id();

        //get mandal location
        $this->db->select('location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->where('user_id', $data['user_id']);
        $res = $this->db->get()->row();
        $loc = $res->location;

        //team mng
        $tm_d = array(
            'user_id' => $uid,
            'parent_id' => $data['user_id'],
            'location' => $loc,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $data['user_id']
        );
        $this->db->insert('tbl_team_mng', $tm_d);
        
        // if(is_array($data['psid'])) {
        //     foreach($data['psid'] as $ps) {
        //         //team ps table
        //         $tm_p = array(
        //             'user_id' => $uid,
        //             'ps_id' => $ps,
        //             'status' => 1,
        //             'created_by' => $data['user_id']
        //         );
        //         $this->db->insert('tbl_team_ps', $tm_p);
        //     }
        // }

        if(is_string($data['psid'])) {
            $psid = explode(',', $data['psid']);
            foreach($psid as $ps) {
                //team ps table
                $tm_p = array(
                    'user_id' => $uid,
                    'ps_id' => $ps,
                    'status' => 1,
                    'created_by' => $data['user_id']
                );
                $this->db->insert('tbl_team_ps', $tm_p);
            }
        }
        
        //address table
        $ad_d = array(
            'id' => $uid,
            // 'street' => $data['street'],
            'location' => $loc,
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
    }

    public function getDICount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getBCCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getBPCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getTCCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 138);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getSPCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getFHCount($id) {
        $this->db->select('cm.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.user_role', 46);
        return $this->db->get()->num_rows();
    }

    public function getVotersCount($id, $filters = array()) {
        $this->db->select('cm.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        
        return $this->db->get()->num_rows();
    }

    public function getAppVersion($id) {
        $this->_sdb->select('app_id, version, version_date, status');
        $this->_sdb->from('tbl_app_version');
        $this->_sdb->where('app_id', $id);
        $this->_sdb->where('status', 1);
        $this->_sdb->order_by('version_date', 'desc');
        $this->_sdb->limit(1);
        $result = $this->_sdb->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveSms($data) {
        $this->db->trans_begin();
        $msg_t = array(
            'sms_type' => $data['sms_type'],
            'receiver_user_role' => $data['user_role'],
            'text_message' => $data['message'],
            'language' => $data['language'],
            'created_by' => $data['user_id']
        );
        $this->db->insert('tbl_sms', $msg_t);
        $msg_id = $this->db->insert_id();

        $msg_g = array(
            'sms_id' => $msg_id,
            'receiver_id' => $data['receiver_group'],
            'msg_count' => $data['msg_count']
        );
        $this->db->insert('tbl_sms_mng', $msg_g);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function getBoothCoordinatorByDH($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        } 
    }

    public function getBoothPresidentByDH($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        } 
    }

    public function getSheetPresidentByDH($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        } 
    }

    public function getFamilyHeadByDH($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSentSms($id) {
        $outbox = array();
        
        //group sms
        $this->db->select('s.id, s.sms_type, s.text_message, s.receiver_user_role, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_lookup as lu', 'sm.receiver_id = lu.id');
        $this->db->where('s.created_by', $id);
        $this->db->where('s.sms_type', 62);
        $this->db->order_by('s.created_at', 'desc');
        $result_g = $this->db->get();
        if($result_g->num_rows() > 0) {
            $group = $result_g->result();
            foreach($group as $g) {
                $g->title = 'Group sms to ' . $g->value;
            }
        }else {
            $group = false;
        }

        //single sms
        $this->db->select('s.id, s.sms_type, s.text_message, s.receiver_user_role, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_lookup as lu', 's.receiver_user_role = lu.id');
        $this->db->where('s.created_by', $id);
        $this->db->where('s.sms_type', 63);
        $this->db->order_by('s.created_at', 'desc');
        $result_s = $this->db->get();
        if($result_s->num_rows() > 0) {
            $single = $result_s->result();
            foreach($single as $s) {
                if($s->receiver_user_role == 46) {
                    $this->db->select('v.firstname, v.mobile');
                    $this->db->from('tbl_voters as v');
                    $this->db->where('v.id', $s->receiver_id);
                    $result = $this->db->get();
                    if($result->num_rows() > 0) {
                        $rec_d = $result->row();
                        $s->firstname = $rec_d->firstname;
                        $s->mobile = $rec_d->mobile;
                        $s->title = $rec_d->firstname . ' ('. $s->value . ')';
                    }
                }else {
                    $this->db->select('u.first_name, u.mobile');
                    $this->db->from('tbl_users as u');
                    $this->db->where('u.id', $s->receiver_id);
                    $result = $this->db->get();
                    if($result->num_rows() > 0) {
                        $rec_d = $result->row();
                        $s->firstname = $rec_d->first_name;
                        $s->mobile = $rec_d->mobile;
                        $s->title = $rec_d->first_name . ' ('. $s->value . ')';
                    }
                }
            }
        }else {
            $single = false;
        }

        if($group && $single) {
            $outbox = array_merge($group, $single);
            usort($outbox, function($a, $b) {
                $t1 = strtotime($a->created_at);
                $t2 = strtotime($b->created_at);
                return $t2 - $t1;
            });
        }elseif($group && !$single) {
            $outbox = $group;
        }elseif(!$group && $single) {
            $outbox = $single;
        }else {
            $outbox =  false;
        }

        if($outbox) {
            return $outbox;
        }else {
            return false;
        }
    }

    public function getSms($id) {
        $inbox = array();

        $sup_id = array();
        $this->db->select('t2.user_id as sm_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id[] = $result->sm_id;

        $this->db->select('t3.user_id as gmng_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_const_location as cl', 't2.location = cl.location_id');
        $this->db->join('tbl_team_mng as t3', 't3.location = cl.parent_id');
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
        $this->db->where('t.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 57);
        $result = $this->db->get()->row();
        $sup_id[] = $result->gmng_id;

        //group inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 62);
        $this->db->where('sm.receiver_id', 141);
        $this->db->where_in('s.created_by', $sup_id);
        $this->db->order_by('s.created_at', 'desc');
        $result_g = $this->db->get();
        if($result_g->num_rows() > 0) {
            $group = $result_g->result();
            foreach($group as $g) {
                $g->title = $g->first_name . ' (' . $g->sender . ')';
                $this->db->select('sr.id, sr.read');
                $this->db->from('tbl_sms_read as sr');
                $this->db->where('sr.sms_id', $g->id);
                $this->db->where('sr.user_id', $id);
                $this->db->where('sr.read', 1);
                $result_r = $this->db->get();
                if($result_r->num_rows() > 0) {
                    $g->read = 1;
                }else {
                    $g->read = 0;
                }
            }
        }else {
            $group = false;
        }

        //single inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 63);
        $this->db->where('sm.receiver_id', $id);
        $this->db->order_by('s.created_at', 'desc');
        $result_s = $this->db->get();
        if($result_s->num_rows() > 0) {
            $single = $result_s->result();
            foreach($single as $s) {
                $s->title = $s->first_name . ' (' . $s->sender . ')';
                $this->db->select('sr.id, sr.read');
                $this->db->from('tbl_sms_read as sr');
                $this->db->where('sr.sms_id', $s->id);
                $this->db->where('sr.user_id', $id);
                $this->db->where('sr.read', 1);
                $result_r = $this->db->get();
                if($result_r->num_rows() > 0) {
                    $s->read = 1;
                }else {
                    $s->read = 0;
                }
            }
        }else {
            $single = false;
        }

        if($group && $single) {
            $inbox = array_merge($group, $single);
            usort($inbox, function($a, $b) {
                $t1 = strtotime($a->created_at);
                $t2 = strtotime($b->created_at);
                return $t2 - $t1;
            });
        }elseif($group && !$single) {
            $inbox = $group;
        }elseif(!$group && $single) {
            $inbox = $single;
        }else {
            $inbox =  false;
        }

        if($inbox) {
            return $inbox;
        }else {
            return false;
        }
    }

    public function smsexists($msg_id) {
        $this->db->select('s.id');
        $this->db->from('tbl_sms as s');
        $this->db->where('s.id', $msg_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function smsread($msg_id, $flag, $user_id) {
        $exists = $this->smsexists($msg_id);
        if($exists) {
            $read_d = array(
                'sms_id' => $msg_id,
                'user_id' => $user_id,
                'read' => $flag
            );
            $result = $this->db->insert('tbl_sms_read', $read_d);
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

    public function saveHelpQuery($data) {
        $q_data = array(
            'app_id' => $data['app_id'],
            'app_version' => $data['app_version'],
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'attach_file' => $data['attach']
        );
        if($this->input->post('description')) {
            $q_data['description'] = $data['description'];
        }
        $result = $this->_sdb->insert('tbl_app_help', $q_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getSmartMedia() {
        $this->db->select('id, media_path, publish_date');
        $this->db->from('tbl_smart_media');
        $this->db->where('publish_date <= CURRENT_DATE', NULL, FALSE);
        $this->db->where('status', 1);
        $this->db->order_by('publish_date', 'desc');
        $this->db->limit(10);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getPostLikes($id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('post_id', $id);
        $this->db->where('post_like', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getPostLikeByUser($id, $user_id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('post_id', $id);
        $this->db->where('user_id', $user_id);
        $this->db->where('post_like', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return 1;
        }else {
            return 0;
        }
    }

    public function postLikeExists($id, $user_id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('post_id', $id);
        $this->db->where('user_id', $user_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }
    
    public function savePostLike($data) {
        $user_id = $data['user_id'];
        $post_id = $data['post_id'];
        $like_exists = $this->postLikeExists($post_id, $user_id);
        if($like_exists == false) {
            $like_save = array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'post_like' => $data['like']
            );
            $result = $this->db->insert('tbl_sm_likes', $like_save);
        }elseif($like_exists == true) {
            if($data['like'] == 1) {
                $like = 1;
            }else {
                $like = 0;
            }
            $like_update = array(
                'post_like' => $like
            );
            $this->db->where('post_id', $post_id);
            $this->db->where('user_id', $user_id);
            $result = $this->db->update('tbl_sm_likes', $like_update);
        }

        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getXpartyByPS($psid, $party) {
        $this->db->select('x.id, x.name, x.age, x.designation, x.mobile, x.followers');
        $this->db->from('tbl_xparty_info as x');
        $this->db->join('tbl_team_ps as tp', 'x.user_id = tp.user_id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $psid);
        $this->db->where('x.party_id', $party);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
}