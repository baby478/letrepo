<?php
class MTeamModel extends CI_Model {
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getManagers($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as m');
        $this->db->join('tbl_team_mng as m2', 'm2.parent_id = m.parent_id');
        $this->db->join('tbl_users as u', 'm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('m.user_id', $id);
        $this->db->where('u.user_role', 2);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getTleaders($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as m');
        $this->db->join('tbl_team_mng as m2', 'm2.parent_id = m.parent_id');
        $this->db->join('tbl_team_mng as m3', 'm3.parent_id = m2.user_id');
        $this->db->join('tbl_users as u', 'm3.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('m.user_id', $id);
        $this->db->where('u.user_role', 18);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getCoordinators($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as m');
        $this->db->join('tbl_team_mng as m2', 'm2.parent_id = m.parent_id');
        $this->db->join('tbl_team_mng as m3', 'm3.parent_id = m2.user_id');
        $this->db->join('tbl_team_mng as m4', 'm4.parent_id = m3.user_id');
        $this->db->join('tbl_users as u', 'm4.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('m.user_id', $id);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVolunteers($id) {
        $this->db->select('v.id, v.firstname, v.lastname,  v.age, v.mobile, v.photo, l.value as gender');
        $this->db->from('tbl_team_mng as m');
        $this->db->join('tbl_team_mng as m2', 'm2.parent_id = m.parent_id');
        $this->db->join('tbl_team_mng as m3', 'm3.parent_id = m2.user_id');
        $this->db->join('tbl_team_mng as m4', 'm4.parent_id = m3.user_id');
        $this->db->join('tbl_citizen_mng as c', 'c.parent_id = m4.user_id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_lookup as l', 'v.gender = l.id');
        $this->db->where('m.user_id', $id);
        $this->db->where('c.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getGroupTask($id) {
        
        $this->db->select('t.parent_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id = $result->parent_id;
        
        $this->db->select("t.id, t.task_name, t.task_description, t.date_from, t.date_to, t.priority, concat(u.first_name ,' ' , u.last_name) as task_by, l.value as role");
        $this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 't.id = tm.task_id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tm.receiver_id = 69 or tm.receiver_id = 65', NULL, FALSE);
        $this->db->where('t.task_group', 62);
        $this->db->where('t.created_by', $sup_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            foreach($result->result() as $r) {
                $r->priority = ($r->priority == 1) ? 'High' : 'Normal';
            }
            return $result->result();
        }else {
            return false;
        }
    }

    public function getIndTask($id) {
        $this->db->select("t.id, t.task_name, t.task_description, t.date_from, t.date_to, t.priority, concat(u.first_name ,' ' , u.last_name) as task_by, l.value as role");
        $this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 't.id = tm.task_id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('t.task_group', 63);
        $this->db->where('tm.receiver_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            foreach($result->result() as $r) {
                $r->priority = ($r->priority == 1) ? 'High' : 'Normal';
            }
            return $result->result();
        }else {
            return false;
        }
    }

    public function saveObservationText($data) {
        $this->db->trans_begin();
        $ob_d = array(
            'user_id' => $data['user_id'],
            'msg_type' => $data['msg_type'],
            'ob_type' => $data['ob_type']
        );
        $this->db->insert('tbl_mt_observation', $ob_d);
        $ob_id = $this->db->insert_id();

        $ob_t = array(
            'ob_id' => $ob_id,
            'message' => $data['message']
        );
        $this->db->insert('tbl_mt_observation_text', $ob_t);

        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function saveObservationVoice($data) {
        $this->db->trans_begin();
        $ob_d = array(
            'user_id' => $data['user_id'],
            'msg_type' => $data['msg_type'],
            'ob_type' => $data['ob_type']
        );
        $this->db->insert('tbl_mt_observation', $ob_d);
        $ob_id = $this->db->insert_id();

        $ob_v = array(
            'ob_id' => $ob_id,
            'duration' => $data['duration'],
            'ob_report' => $data['ob_voice']
        );
        $this->db->insert('tbl_mt_observation_voice', $ob_v);

        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function addevent($data) {
        $s_data = array(
            'event_type' => $data['event_type'],
            'event_name' => $data['event_name'],
            'event_date' => date('Y-m-d', strtotime($data['event_date'])),
            'user_id' => $data['user_id'],
            'event_img' => $data['event_img']
        );
        if(isset($data['event_description'])) {
            $s_data['event_description'] = $data['event_description'];
        }
        
        $result = $this->db->insert('tbl_events', $s_data);
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

    public function getMTeamPs($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name, p.ps_area');
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
        $this->db->where('c.user_role', 17);
        return $this->db->get()->num_rows();
    }

    public function getVotersBySP($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.lfname, v.llastname, v.f_name, v.lrname, v.gender, v.photo, v.age, v.voter_id, v.hno, v.mobile');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'v.id = c.citizen_id');
        $this->db->where('c.user_id', $id);
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

    public function getVotersCountByBO($id) {
        $this->db->select('cm.citizen_id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_ps as tp1', 'tp1.ps_id = tp.ps_id');
        $this->db->join('tbl_users as u', 'tp1.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->where('u.user_role', 3);
        $this->db->where('cm.user_role', 17);
        $this->db->where('tp.user_id', $id);
        return $this->db->get()->num_rows();
        
    }

    public function getBPCountByBO($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_ps as tp1', 'tp1.ps_id = tp.ps_id');
        $this->db->join('tbl_users as u', 'tp1.user_id = u.id');
        $this->db->where('u.user_role', 18);
        $this->db->where('tp.user_id', $id);
        return $this->db->get()->num_rows();
    }

    public function getSPCountByBO($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_ps as tp1', 'tp1.ps_id = tp.ps_id');
        $this->db->join('tbl_users as u', 'tp1.user_id = u.id');
        $this->db->where('u.user_role', 3);
        $this->db->where('tp.user_id', $id);
        return $this->db->get()->num_rows();
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
            
            $this->db->insert('tbl_users', $user_d);
            $uid = $this->db->insert_id();
            
            
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

    public function voterExists($voter_id) {
        $this->db->select('id');
        $this->db->from('tbl_voters');
        $this->db->where('voter_id', $voter_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function voterRegisteredByCoordinator($id) {
        $this->db->select('id, firstname, lastname, lfname, llastname, lrname, hno, f_name, gender, age, voter_id');
        $this->db->from('tbl_voters');
        $this->db->where('voter_id', $id);
        $this->db->where('user_id IS NULL', NULL, FALSE);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }    
    }

    public function getSheetPresidentByPSId($id) {
        $this->db->select('u.id, u.first_name, u.last_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getCoordinatorLocation($id) {
        $this->db->select('location');
        $this->db->from('tbl_team_mng');
        $this->db->where('user_id', $id);
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function addCitizen($data) {
        $this->db->trans_begin();
        //Geo coord
        if(isset($data['coord'])) {
            $this->db->insert('tbl_geo_coor', $data['coord']);
            $cord_id = $this->db->insert_id();
        }

        
        if(isset($data['update'])) {
            if(isset($cord_id)) {
                $data['update']['cord_id'] = $cord_id;
            }
            $vt_id = $data['voter_id'];
            $this->db->where('voter_id', $vt_id);
            $this->db->update('tbl_voters', $data['update']);
            $citizen_id = $data['last_id'];
        }else {
            $insert_data = array(
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'gender' => $data['gender'],
                'f_name' => $data['f_name'],
                'voter_id' => $data['voter_id'],
                'ps_no' => $data['polling_station'],
                'voter_status' => $data['voter_status'],
                'user_id' => $data['spid']
            );
            if(isset($data['photo']) && $data['photo'] !== '') {
                $insert_data['photo'] = $data['photo'];
            }
            if(isset($data['age']) && $data['age'] !== '') {
                $insert_data['age'] = $data['age'];
            }
            if(isset($data['mobile']) && $data['mobile'] !== '') {
                $insert_data['mobile'] = $data['mobile'];
            }
            if(isset($data['caste']) && $data['caste'] !== '') {
                $insert_data['caste'] = $data['caste'];
            }
            if(isset($data['religion']) && $data['religion'] !== '') {
                $insert_data['religion'] = $data['religion'];
            }
            if(isset($data['category']) && $data['category'] !== '') {
                $insert_data['category'] = $data['category'];
            }
            if(isset($cord_id)) {
                $insert_data['cord_id'] = $cord_id;
            }
            $this->db->insert('tbl_voters', $insert_data);
            $citizen_id = $this->db->insert_id();
        }

        //address table
        $address = array(
            'citizen_id' => $citizen_id,
            'house_no' => $data['house_no'],
            'street' => $data['street'],
            'local_status' => $data['local_status']
        );
        if(isset($data['landmark']) && $data['landmark'] !== '') {
            $address['landmark'] = $data['landmark'];
        }
        $location = $this->getCoordinatorLocation($data['spid'])->location;
        $address['location'] = $location;
        $this->db->insert('tbl_citizen_address', $address);

        //outstation table
        if(isset($data['outstation'])) {
            $outstation = array();
            foreach($data['outstation'] as $k => $v) {
                $outstation[$k] = $v;
            }
            $outstation['citizen_id'] = $citizen_id;
            $this->db->insert('tbl_citizen_outstation', $outstation);
        }

        //citizen mng table
        $add_detail = array(
            'citizen_id' => $citizen_id,
            'user_id' => $data['spid'],
            'user_role' => $data['user_role'],
            'group_id' => $data['group_id'],
            'relationship' => $data['relationship'],
            'local_status' => $data['local_status'],
            'status' => 1    
        );
        if(isset($data['parent_id']) && $data['parent_id'] !== '') {
            $add_detail['parent_id'] = $data['parent_id'];
        }
        $this->db->insert('tbl_citizen_mng', $add_detail);

        
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return $citizen_id;
        }

    }

    public function getVolunteerBySP($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.age, v.mobile, v.photo');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->where('cm.user_id', $id);
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function userExists($id, $role) {
        if($role == 46) {
            $this->db->select('cm.citizen_id');
            $this->db->from('tbl_citizen_mng as cm');
            $this->db->where('cm.citizen_id', $id);
            $this->db->where('cm.user_role', 46);
            $this->db->where('cm.status', 1);
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }elseif($role == 3) {
            $this->db->select();
            $this->db->from('tbl_users as u');
            $this->db->where('u.id', $id);
            $this->db->where('u.user_role', 3);
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

    /**
     * Date : 03-01-2019
     */
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

    public function getBoothPresidentByBC($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_ps as tp1', 'tp.ps_id = tp1.ps_id');
        $this->db->join('tbl_users as u', 'tp1.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSheetPresidentByBC($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_ps as tp1', 'tp.ps_id = tp1.ps_id');
        $this->db->join('tbl_users as u', 'tp1.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getFamilyHeadByBC($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.user_role as role');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_ps as tp1', 'tp.ps_id = tp1.ps_id');
        $this->db->join('tbl_users as u', 'tp1.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('cm.user_role', 46);
        $this->db->where('u.status', 1);
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
        $this->db->select('t2.user_id as di_id, t3.user_id as dm_id, t4.user_id as smng_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t3', 't2.parent_id = t3.user_id');
        $this->db->join('tbl_team_mng as t4', 't3.parent_id = t4.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id[] = $result->di_id;
        $sup_id[] = $result->dm_id;
        $sup_id[] = $result->smng_id;

        //group inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 62);
        $this->db->where('sm.receiver_id', 69);
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
}