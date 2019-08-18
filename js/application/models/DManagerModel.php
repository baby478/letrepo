<?php
class DManagerModel extends CI_Model {

    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getBPresidentCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getSPresidentCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getVotersCount($id, $filters = array()) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->where('u.user_role', 3);
        $this->db->where('tm.parent_id', $id);
        return $this->db->get()->num_rows();
    }

    public function getMandalPs($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'p.id = tp.ps_id');
        $this->db->where('tp.user_id', $id);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothPresidentByPs($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
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

    public function getVotersByPS($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.lfname, v.llastname, v.f_name, v.lrname, v.gender, v.photo, v.age, v.voter_id, v.hno, v.mobile');
        $this->db->from('tbl_voters as v');
        $this->db->where('v.ps_no', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
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

    public function saveMessage($data) {
        if(is_array($data['mobile'])) {
            foreach($data['mobile'] as $m) {
                $sms_data = array(
                    'user_id' => $data['user_id'],
                    'receiver_id' => $data['receiver_id'],
                    'mobile'    => $m,
                    'text_message' => $data['message']
                );
        
                $result = $this->db->insert('tbl_sms_details', $sms_data); 
            }
        }else {
            $sms_data = array(
                'user_id' => $data['user_id'],
                'receiver_id' => $data['receiver_id'],
                'mobile'    => $data['mobile'],
                'text_message' => $data['message']
            );
    
            $result = $this->db->insert('tbl_sms_details', $sms_data);
        }
        
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getVisitsCount($id) {
        //voters registerd
        $this->db->select('c.citizen_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t1', 't1.parent_id = t.user_id');
        $this->db->join('tbl_citizen_mng as c', 't1.user_id = c.user_id');
        $this->db->where('t.parent_id', $id);
        $this->db->where('c.user_role', 17);
        $data = $this->db->get();
        if($data->num_rows() > 0) {
            $citizen = $data->result();
            $ctz = array();
            foreach($citizen as $c) {
                $ctz[] = $c->citizen_id;
            }
            
            //visit 1
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_27 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v1 = $res->result();
            }

            //visit 2
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_28 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v2 = $res->result();
            }

            //visit 3
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_29 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v3 = $res->result();
            }

            //visit 4
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_30 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v4 = $res->result();
            }

            //visit 5
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_33 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v5 = $res->result();
            }

            //visit 6
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_34 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v6 = $res->result();
            }

            //visit 6
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_35 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where_in('c.citizen_id', $ctz);
            $this->db->where('c.user_role', 17);
            $this->db->where('c.local_status', 15);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v7 = $res->result();
            }

            $result = array_merge($res_v1, $res_v2,  $res_v3,  $res_v4, $res_v5, $res_v6, $res_v7);
            
            return $result;
        }else {
            return false;
        }
    }

    public function bpExistsByPs($id) {
        $this->db->select('id, user_id, ps_id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('tp.status', 1);
        $tp_result = $this->db->get();
        if($tp_result->num_rows() > 0) {
            $this->db->select('u.id');
            $this->db->from('tbl_team_ps as tp');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->where('u.user_role', 18);
            $this->db->where('u.status', 1);
            $this->db->where('tp.status', 1);
            $this->db->where('tp.ps_id', $id);
            $u_result = $this->db->get();
            if($u_result->num_rows() > 0) {
                return true;
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

    public function addBoothPresident($data) {
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
            'user_role' => 18,
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

        //get village location
        $this->db->select('village_id');
        $this->db->from('tbl_ps');
        $this->db->where('id', $data['psid']);
        $res = $this->db->get()->row();
        $loc = $res->village_id;

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

        //team ps table
        $tm_p = array(
            'user_id' => $uid,
            'ps_id' => $data['psid'],
            'status' => 1,
            'created_by' => $data['user_id']
        );
        $this->db->insert('tbl_team_ps', $tm_p);

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

    public function boExistsByPs($id) {
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
            $this->db->where('u.user_role', 55);
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

    public function addBoothObserver($data) {
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
            'user_role' => 55,
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

        //get village location
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

        if(is_array($data['psid'])) {
            foreach($data['psid'] as $ps) {
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

    public function userExists($id, $role) {
        if($role == 46 || $role == 17) {
            $this->db->select('cm.citizen_id');
            $this->db->from('tbl_citizen_mng as cm');
            $this->db->where('cm.citizen_id', $id);
            $this->db->where('cm.user_role', $role);
            $this->db->where('cm.status', 1);
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }elseif($role == 3 || $role == 18 || $role == 55) {
            $this->db->select();
            $this->db->from('tbl_users as u');
            $this->db->where('u.id', $id);
            $this->db->where('u.user_role', $role);
            $this->db->where('u.status', 1);
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }
    }

    public function validationExists($user_id, $role) {
        $this->db->select('v.id, v.user_id, v.user_role, v.profession, v.party_participation, v.personal_status, v.family_voters, v.vote_commitment, v.status');
        $this->db->from('tbl_validation as v');
        $this->db->where('v.user_id', $user_id);
        $this->db->where('v.user_role', $role);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveValidation($data, $option) {
        $vid = $data['val_id'];
        $role = $data['user_role'];
        $this->db->trans_begin();

        //if validation exists update
        $update = $this->validationExists($vid, $role);
        
        if($update) {
            $this->db->set($option, $data[$option]);
            $this->db->where('id', $update->id);
            $this->db->where('user_id', $vid);
            $this->db->where('user_role', $role);
            $this->db->update('tbl_validation');
        }else {
            $i_data = array(
                'user_id' => $vid,
                'user_role' => $role,
                $option => $data[$option],
                'created_by' => $data['user_id']
            );
            $this->db->insert('tbl_validation', $i_data);
        }

        //update status
        $val = $this->validationExists($vid, $role);

        if($val) {
            if($val->status == 0) {
                if($val->profession != 0 && $val->party_participation != 0 && $val->personal_status != 0 && $val->family_voters != 0 &&  $val->vote_commitment != 0) {
                    $this->db->set('status', 1);
                    $this->db->where('id', $val->id);
                    $this->db->update('tbl_validation');
                }
            }
        }
        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function saveGovtSchemeValidation($data) {
        $user_id = $data['val_id'];
        $user_role = $data['user_role'];
        $created_by = $data['user_id'];
        $o_values = $data['govt_scheme'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'user_id' => $user_id,
                'user_role' => $user_role,
                'created_by' => $created_by,
                'status' => 1
            );
            $this->db->insert('tbl_validation_1', $v_data);
            $validation_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'validation_id' => $validation_id,
                    'option_id' => $v,
                    'status' => 1
                );
                $this->db->insert('tbl_validation1_options', $o_data);
            }
            if($this->db->trans_status() === FALSE)  {
                $this->db->trans_rollback();
                return false;
            }else {
                $this->db->trans_commit();
                return true;
            }
        }else {
            return false;
        }
    }

    public function validationOneStatus($id, $role) {
        $this->db->select('id, status, created_at, updated_at');
        $this->db->from('tbl_validation_1');
        $this->db->where('user_id', $id);
        $this->db->where('user_role', $role);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveYSRSchemeValidation($data) {
        $user_id = $data['val_id'];
        $user_role = $data['user_role'];
        $created_by = $data['user_id'];
        $o_values = $data['ysr_scheme'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'user_id' => $user_id,
                'user_role' => $user_role,
                'created_by' => $created_by,
                'status' => 1
            );
            $this->db->insert('tbl_validation_2', $v_data);
            $validation_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'validation_id' => $validation_id,
                    'option_id' => $v,
                    'status' => 1
                );
                $this->db->insert('tbl_validation2_options', $o_data);
            }
            if($this->db->trans_status() === FALSE)  {
                $this->db->trans_rollback();
                return false;
            }else {
                $this->db->trans_commit();
                return true;
            }
        }else {
            return false;
        }
    }

    public function validationTwoStatus($id, $role) {
        $this->db->select('id, status, created_at, updated_at');
        $this->db->from('tbl_validation_2');
        $this->db->where('user_id', $id);
        $this->db->where('user_role', $role);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
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

    public function getSmartMediaLikes($id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('user_id', $id);
        $this->db->where('post_like', 1);
        $result = $this->db->get();
        return $result->num_rows();
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

    public function getSheetPresidentByDM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, l.name as location');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id'); //bp
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id'); //sp
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
        $this->db->join('tbl_locations as l', 't3.location = l.id');
        $this->db->where('t1.user_id', $id);
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getFamilyHeadByDM($id) {
        $this->db->select('v.id, v.firstname, v.mobile, cm.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id'); //bp
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id'); //sp
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = t3.user_id'); //fh
		$this->db->join('tbl_voters as v', 'v.id = cm.citizen_id');
        $this->db->where('t.user_id', $id);
		$this->db->where('cm.user_role', 46);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
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
        $this->db->select('t2.user_id as dm_id, t3.user_id as smng_id, t4.user_id as gmng_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t3', 't2.parent_id = t3.user_id');
        $this->db->join('tbl_team_mng as t4', 't3.parent_id = t4.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id[] = $result->dm_id;
        $sup_id[] = $result->smng_id;
        $sup_id[] = $result->gmng_id;

        //group inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 62);
        $this->db->where('sm.receiver_id', 66);
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

    /**
     * Date : 03-01-2019
     */
    public function tcExistsByPs($id) {
        $this->db->select('id, user_id, ps_id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('tp.status', 1);
        $tp_result = $this->db->get();
        if($tp_result->num_rows() > 0) {
            $this->db->select('u.id');
            $this->db->from('tbl_team_ps as tp');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->where('u.user_role', 138);
            $this->db->where('u.status', 1);
            $this->db->where('tp.status', 1);
            $this->db->where('tp.ps_id', $id);
            $u_result = $this->db->get();
            if($u_result->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

    public function addTeleCaller($data) {
        $this->db->trans_begin();
        //users table
        $user_d = array(
            'first_name' => $data['firstname'],
            'gender' => $data['gender'],
            'mobile' => $data['mobile'],
            'photo' => $data['photo'],
            'user_role' => 138,
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

        //get village location
        $this->db->select('village_id');
        $this->db->from('tbl_ps');
        $this->db->where('id', $data['psid']);
        $res = $this->db->get()->row();
        $loc = $res->village_id;

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

        //team ps table
        $tm_p = array(
            'user_id' => $uid,
            'ps_id' => $data['psid'],
            'status' => 1,
            'created_by' => $data['user_id']
        );
        $this->db->insert('tbl_team_ps', $tm_p);

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

    public function getTelecaller($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role, p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_ps as tp', 'tm.user_id = tp.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 138);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /**
     * Date : 23-01-19
     * Author : Anees
     */
    public function getBCCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getTCCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 138);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }
}