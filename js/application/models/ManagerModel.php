<?php
class ManagerModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }
	
    public function checkAllocStatus($id) {
        $this->db->select('id');
        $this->db->from('tbl_team_mng');
        $this->db->where('user_id', $id);
        $this->db->where('status', 1);
        return $this->db->get()->num_rows();
    }

    public function phoneExists($phone) {
        $this->db->select('id');
        $this->db->where('mobile', $phone);
        $result = $this->db->get('tbl_users')->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function registerUser($data) {
        $prepared_data = $this->prepareAddUser($data);
        $salt = uniqid();
		
        $password = password_hash($salt.$data['password'], PASSWORD_BCRYPT);
		
		
        $this->db->trans_begin();
        $this->db->insert('tbl_users', $prepared_data);
        $insert_id = $this->db->insert_id();
        
        $pass_data = array(
            'id' => $insert_id,
            'password_salt' => $salt,
            'password'    => $password
        );
        $this->db->insert('tbl_password', $pass_data);
       
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function sanitizeInput(array $data) {
        foreach($data as $dt) {
            $this->db->escape($dt);
        }
        return $data;
    }

    public function prepareAddUser($data) {
        $data = $this->sanitizeInput($data);
        $user_id = $this->session->userdata('user')->id;
        $user_data = array(
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'f_name' => $data['fathername'],
            'dob' => $data['dob'],
            'gender' => $data['gender'],
            'email' => $data['email'],
            'mobile' => $data['phone'],
            'address' => $data['address'],
            'location' => $data['mandal'],
            'pincode' => $data['pincode'],
            'photo' => $data['photo'],
            'status' => 1,
            'user_role' => 17,
            'created_by' => $user_id
        );
        return $user_data;
    }

    public function getUsers() {
        $user_id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.photo, u.dob, u.mobile, l.value as gender, rl.value as user_role, lc.name as location, p.ps_no, p.ps_name');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->join('tbl_team_mng as m', 'm.user_id = u.id', 'left');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = u.id', 'left');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id', 'left');
        $this->db->join('tbl_locations as lc', 'lc.id = m.location', 'left');
        $this->db->where('u.created_by', $user_id);
        // $this->db->where('u.user_role', 18);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getAssignRole() {
        $user_role = $this->session->userdata('user')->user_role;
        $this->db->select('l.id, l.value');
        $this->db->from('tbl_ac_role as r');
        $this->db->join('tbl_acl as ac', 'r.acl_id = ac.id');
        $this->db->join('tbl_lookup as l', 'ac.value = l.id');
        $this->db->where('ac.gen_id', 1);
        $this->db->where('r.user_role', $user_role);
        $result = $this->db->get()->result();
        return $result;
    }

    public function getAllocatedLocation($id) {
        $this->db->select('l.name as location, tm.location as lc_id, tm.date_from, tm.status');
        $this->db->from('tbl_team_mng tm');
        $this->db->join('tbl_locations l', 'tm.location = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('tm.status', 1);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getVillageAnalytics($id) {
        $this->db->where('location_id', $id);
        $result = $this->db->get('village_analytics')->result();
        if(count($result) > 0) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCasteByVillage($id) {
        $this->db->where('location_id', $id);
        $result = $this->db->get('caste_village')->result();
        if(count($result) > 0) {
            return $result;
        }else {
            return false;
        }
    }
    
    public function getVoterByGender($id, $gender) {
        $this->db->where('location', $id);
        $this->db->where('gender', $gender);
        $result = $this->db->get('tbl_voters');
        return $result;
    }

    //constituency demographics
    public function constituencyExists($id) {
        $this->db->select('id, name');
        $this->db->where('id', $id);
        $this->db->where('level_id', 11);
        $result = $this->db->get('tbl_locations')->result();
        if(count($result) > 0) {
            return $result;
        }else {
            return false;
        }

    }

    public function getDemographicByConst($id) {
        $const_exists = $this->constituencyExists($id);
        if($const_exists) {
            $data = array();
            $this->db->select('area_density, population, economy, villages, farmers, gram_panchayat, sex_ratio, literacy, voters_count');
            $this->db->where('location_id', $id);
            $result = $this->db->get('demographic')->result();
            if(count($result) > 0) {
                $data['name'] = $const_exists[0]->name;
                $data['demographic'] = $result;
                $data['options'] = $this->getDemographicOptions($id);
                $data['last_result'] = $this->getLastElectionResult($id);
                return $data;
            }else {
                return false;
            }
            
        }else {
            return false;
        }
    }

    public function getDemographicOptions($id) {
        $options = array();
        $this->db->select('*');
        $this->db->where('location_id', $id);
        $result = $this->db->get('demographic_options')->result();
        foreach($result as $v) {
            $options[$v->option_label][$v->option_name] = $v->option_value;
        }
        return $options;
    }

    public function getLastElectionResult($id) {
        $this->db->select('election_date, party_affiliate, member, duration_from, duration_to');
        $this->db->where('location_id', $id);
        $this->db->order_by('election_date');
        $this->db->limit(1);
        return $this->db->get('election_result')->result();
    }

    
    // public function assignUserRole($id, $role) {
    //     $this->db->set('user_role', $role);
    //     $this->db->where('id', $id);
    //     $this->db->update('tbl_users');
    //     $result = $this->db->affected_rows();
    //     if($result > 0) {
    //         return true;
    //     }else {
    //         return false;
    //     }
        
    // }

    public function assignUserRole($id, $role, $location) {
        
        $this->db->trans_begin();
        //update role
        $this->db->set('user_role', $role);
        $this->db->where('id', $id);
        $this->db->update('tbl_users');

        if($role == 18) {
            //get village location
            $this->db->select('village_id');
            $this->db->from('tbl_ps');
            $this->db->where('id', $location);
            $res = $this->db->get()->row();
            $loc = $res->village_id;
        }elseif($role == 55) {
            $loc = $this->session->userdata('user')->location_id;
        }elseif($role == 2) {
            $loc = $this->session->userdata('user')->location_id;
        }
        
        //insert team
        $parent_id = $this->session->userdata('user')->id;
        $data = array(
            'user_id' => $id,
            'parent_id' => $parent_id,
            'location' => $loc,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
        );
        $this->db->insert('tbl_team_mng', $data);

        if($role == 18) {
            //insert team ps
            $data_p = array(
                'user_id' =>$id,
                'ps_id' => $location,
                'status' => 1,
                'created_by' => $parent_id
            );
            $this->db->insert('tbl_team_ps', $data_p);
        }elseif($role == 55 && is_array($location)) {
            foreach($location as $l => $v) {
                $data_p = array(
                    'user_id' =>$id,
                    'ps_id' => $v,
                    'status' => 1,
                    'created_by' => $parent_id
                );
                $this->db->insert('tbl_team_ps', $data_p);
            }
            
        }elseif($role == 2 && is_array($location)) {
            foreach($location as $l => $v) {
                $data_p = array(
                    'user_id' =>$id,
                    'ps_id' => $v,
                    'status' => 1,
                    'created_by' => $parent_id
                );
                $this->db->insert('tbl_team_ps', $data_p);
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

    public function mandalAllocateExists($location) {
        $this->db->select('t.id');
        $this->db->from('tbl_team_ps as t');
        $this->db->where('t.ps_id', $location);
        $this->db->where('t.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
    
    public function getUserByRole($role) {
        $id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        //$this->db->join('tbl_team_mng as t1', 't1.id = t.id AND t1.status = 0', 'left');
        // $this->db->where('u.status', 1);
        $this->db->where('u.user_role', $role);
        //$this->db->where('t.status', 'IS NULL');
        $this->db->where('u.created_by', $id);
        $result = $this->db->get()->result();
        
        if($result) {
            $data = array();
            foreach($result as $i => $r) {
                if($r->status == '' || $r->status == 0) {
                    $data[] = $result[$i];
                }
            }
            return $data;
        }else {
            return false;
        }
    }

    public function allocateVillageByManager() {
        $user_id = $this->input->post('usertl');
        $location = $this->input->post('village');
        $parent_id = $this->session->userdata('user')->id;
        $data = array(
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'location' => $location,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
        );
        $this->db->insert('tbl_team_mng', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function getTlByManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, t.status, t.location');
        $this->db->from('tbl_team_mng t');
        $this->db->join('tbl_users u', 't.user_id = u.id');
        $this->db->where('t.parent_id', $id);
        $this->db->where('t.status', 1);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function allocateTlByManager() {
        $id = $this->session->userdata('user')->id;
        $user_id = $this->input->post('usercr');
        $parent_id = $this->input->post('allctl');
        $location = $this->getAllocatedLocation($parent_id)[0]->lc_id;
        
        $data = array(
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'location' => $location,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $id
        );
        $this->db->insert('tbl_team_mng', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function getboothPresidentByManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function teamProfile($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function userProfile($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.f_name, l1.name as location, u.photo, u.dob, u.caste, u.mobile, u.gender, u.email, t.date_from, p.ps_no, p.ps_name');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 't.user_id = u.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = u.id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->where('u.id', $id);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getVoterData() {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, lu2.value as local_status');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->join('tbl_lookup as lu2', 'v.local_status = lu2.id');
        return $this->db->get();
    }

    public function userQualification($id) {
        $this->db->select('l.value as qualification, e.course_name, e.college_name');
        $this->db->from('tbl_education as e');
        $this->db->join('tbl_lookup as l', 'e.qualification = l.id');
        $this->db->where('user_id', $id);
        $this->db->order_by('e.qualification', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function votersByTeamLeader($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('tm.parent_id', $id);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function votersByStatusTL($id, $status) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('v.voter_status', $status);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows(); 
    }

    public function votersByUser($id) {
        $this->db->select('cm.id');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->where('cm.user_id', $id);
        //$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();    
        
    }
	
	public function getTotalVote($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('v.user_id', $id);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getVolunteerTotalVote($id,$filters = array()) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'ct.citizen_id = v.id');
		//$this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->where('ct.parent_id', $id);
		$this->db->where('ct.user_role', 17);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
    public function votersByStatusCr($id, $status) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('v.user_id', $id);
        $this->db->where('v.voter_status', $status);
		//$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
        
    }

    public function getEvents($id) {
        $this->db->select('e.event_name, e.event_description, e.event_date, e.event_img, u.first_name, u.last_name');
        $this->db->from('tbl_events as e');
        $this->db->join('tbl_users as u', 'e.user_id = u.id');
        $this->db->where('e.event_type', $id);
		$this->db->where('e.delete_status', 1);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCoordinatorsByManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name as location');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
        $this->db->join('tbl_locations as l', 't3.location = l.id');
        $this->db->where('t1.user_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getVolunteerByManager($id) {
        $this->db->select('v.id, v.firstname, cm.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id');
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = t3.user_id');
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
    
	public function getCitizenByrelation($id,$relation) {
		$this->db->select('cm.id');
        $this->db->from('tbl_citizen_mng as cm');
		$this->db->where('cm.parent_id', $id);
		//$this->db->where('cm.user_role', 17);
		$this->db->where('cm.relationship', $relation);
		$result = $this->db->get();
		if($result->num_rows() > 0) {
			return $result->num_rows();
		}else {
			return 0;
		}
	}

    public function getVotersByManager($id, $filters = array()) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender,v.category, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		$this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
		$this->db->join('tbl_citizen_mng as ct', 't4.user_id = ct.user_id');
        $this->db->join('tbl_voters as v', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
       // $this->db->where('ct.user_role', 17);
        $this->db->where('t1.user_id', $id);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }

    public function getAllVoters($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender,v.category, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_voters as v', 'v.ps_no = p.id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id', 'left');
        $this->db->where('t1.user_id', $id);
        return $this->db->get();
    }

    public function countVotersByVillage($id) {
        $query = $this->db->query("SELECT  c.id, c.name, c.level_id, sum(c.voters) AS total
        from (
            select count(v.user_id) as voters, l2.name, l2.id, l2.level_id
            from tbl_team_mng as t1
            join tbl_locations as l on t1.location = l.id
            join tbl_locations as l2 on l.id = l2.parent_id
            left join tbl_team_mng as t2 on t2.location = l2.id
            left join tbl_team_mng as t3 on t2.user_id = t3.parent_id
            left join tbl_voters as v on v.user_id = t3.user_id
            left join tbl_citizen_mng as cm on cm.citizen_id = v.id
            where t1.user_id = $id and cm.user_role = 17
            group by l2.id, l2.name, v.user_id) as c group by c.id, c.name, c.level_id order by total desc limit 10");
        return $query->result();
    }

    public function countVotersByStatusVillage($id, $status) {
        $query = $this->db->query("SELECT  c.id, c.name, sum(c.voters) AS positive
        from (
            select count(v.user_id) as voters, l2.name, l2.id
            from tbl_team_mng as t1
            join tbl_locations as l on t1.location = l.id
            join tbl_locations as l2 on l.id = l2.parent_id
            left join tbl_team_mng as t2 on t2.location = l2.id
            left join tbl_team_mng as t3 on t2.user_id = t3.parent_id
            left join tbl_voters as v on v.user_id = t3.user_id
            left join tbl_citizen_mng as cm on v.id = cm.citizen_id
            where t1.user_id = $id and v.voter_status = $status and cm.user_role = 17
            group by l2.id, l2.name, v.user_id) as c group by c.id, c.name order by positive desc limit 10");
        return $query->result();
    }

    public function getPollingStationsByManager($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village, l2.id as location, l2.level_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->where('t.user_id', $id);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get()->result();
        return $result;
    }

    public function getPSMemberByManager($id, $role) {
        $this->db->select('u.id, u.first_name, u.last_name, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_ps_member as t', 'u.id = t.user_id', 'left');
        //$this->db->join('tbl_team_mng as t1', 't1.id = t.id AND t1.status = 0', 'left');
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', $role);
        $this->db->or_where('u.user_role', 38, NULL, FALSE);
        //$this->db->where('t.status', 'IS NULL');
        $this->db->where('u.created_by', $id);
        $result = $this->db->get()->result();
        
        if($result) {
            $data = array();
            foreach($result as $i => $r) {
                if($r->status == '' || $r->status == 0) {
                    $data[] = $result[$i];
                }
            }
            return $data;
        }else {
            return false;
        }
    }

	public function getAllVillageByVoterStatus($id) {
        $this->db->select('p.id as pid, p.ps_name, p.ps_no, p.ps_area, l1.id, l1.name,l1.level_id,count(l1.id)');
        //$this->db->from('tbl_locations');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->join('tbl_ps as p', 'p.village_id = l1.id');
        $this->db->where('l1.parent_id', $id);
        $this->db->order_by('name');
        $this->db->group_by('p.id');
        $this->db->group_by('l1.id');
        $result = $this->db->get()->result();
        return $result;
    }

    public function getPollingStationsByMandals($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village, l2.level_id, p.village_id, count(p.id)');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->where('l1.id', $id);
        $this->db->group_by('p.id');
        $result = $this->db->get()->result();
        return $result;
    }

    public function allocateBAgentByManager() {
		$this->db->trans_begin();
		
		//$user_id = $this->input->post('userba');
		//ps_member table
		$user_id = $this->input->post('boothagent');
		$ps = $this->input->post('polling-station');
		$parent_id = $this->session->userdata('user')->id;
		$booth_no = $this->input->post('boothno');
		$data['user_data']= $this->getVotersById($user_id);
		
		//update role request
		$update_request = array(
		    'status' => 1 
		);
		$this->db->where('volunteer_id', $user_id);
		$this->db->update('tbl_role_request', $update_request); 
        
        //insert in user table
		foreach($data['user_data'] as $k => $udt) {
            $user_insertdata = array(
                'first_name' => $udt->firstname,
                'last_name' => $udt->lastname,
                'f_name' => $udt->f_name,
                'gender' => $udt->gender,
                'mobile' => $udt->mobile,
                'photo' => $udt->photo,
                'user_role' => 37,
                'status' => 1,
                'created_by' => $parent_id
            );
            $this->db->insert('tbl_users', $user_insertdata);
		}
        $insert_id = $this->db->insert_id();
        
        //team mng table
        $this->db->select('t.user_id, t.location');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_team_mng as t', 'c.user_id = t.user_id');
        $this->db->where('c.citizen_id', $user_id);
        $team_d = $this->db->get()->row();

        $tm_d = array(
            'user_id' => $insert_id,
            'parent_id' => $team_d->user_id,
            'location' => $team_d->location,
            'date_from' => date('Y-m-d'),
            'created_by' => $parent_id,
            'status' => 1
        );
        $this->db->insert('tbl_team_mng', $tm_d);
		
		// insert in ps_member
		$data_member = array(
            'user_id' => $insert_id,
            'volunteer_id'=> $user_id,
            'ps_id' => $ps,
            'booth_no' => $booth_no,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
		);
		$this->db->insert('tbl_ps_member', $data_member);

		if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
		}else {
            $this->db->trans_commit();
            return true;
		}
    }
	
	public function allocateObserverByManager() {
		$this->db->trans_begin();
		
		//$user_id = $this->input->post('userba');
		//ps_member table
		$user_id = $this->input->post('observer');
		$ps = $this->input->post('polling-station1');
		$parent_id = $this->session->userdata('user')->id;
		$booth_no = $this->input->post('boothno1');
		$data['user_data']= $this->managerModel->getVotersById($user_id);
		
		//update role request
		$update_request = array(
		    'status' => 1 
		);
		$this->db->where('volunteer_id', $user_id);
		$this->db->update('tbl_role_request', $update_request); 
		//insert in user table
		foreach($data['user_data'] as $k => $udt) {
            $user_insertdata = array(
                'first_name' => $udt->firstname,
                'last_name' => $udt->lastname,
                'f_name' => $udt->f_name,
                'gender' => $udt->gender,
                'mobile' => $udt->mobile,
                'photo' => $udt->photo,
                'user_role' => 38,
                'status' => 1,
                'created_by' => $parent_id
            );
            $this->db->insert('tbl_users', $user_insertdata);
		}
        $insert_id = $this->db->insert_id();
        
        //team mng table
        $this->db->select('t.user_id, t.location');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_team_mng as t', 'c.user_id = t.user_id');
        $this->db->where('c.citizen_id', $user_id);
        $team_d = $this->db->get()->row();

        $tm_d = array(
            'user_id' => $insert_id,
            'parent_id' => $team_d->user_id,
            'location' => $team_d->location,
            'date_from' => date('Y-m-d'),
            'created_by' => $parent_id,
            'status' => 1
        );
        $this->db->insert('tbl_team_mng', $tm_d);
		
		// insert in ps_member
		$data_member = array(
            'user_id' => $insert_id,
            'volunteer_id'=> $user_id,
            'ps_id' => $ps,
            'booth_no' => $booth_no,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
		);
		$this->db->insert('tbl_ps_member', $data_member);

		if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
		}else {
            $this->db->trans_commit();
            return true;
		}
    }

    public function allocatePSMember($data) {
        $this->db->trans_begin();

        $user_id = $data['vid'];
		$ps = $data['pid'];
		$role = $data['role'];
        $booth_no = $data['booth'];
        $parent_id = $this->session->userdata('user')->id;
		$data['user_data']= $this->getVotersById($user_id);

		//update role request
		$update_request = array(
		    'status' => 1 
		);
		$this->db->where('volunteer_id', $user_id);
        $this->db->update('tbl_role_request', $update_request);
        
        //insert in user table
		foreach($data['user_data'] as $k => $udt) {
            $user_insertdata = array(
                'first_name' => $udt->firstname,
                'last_name' => $udt->lastname,
                'f_name' => $udt->f_name,
                'gender' => $udt->gender,
                'mobile' => $udt->mobile,
                'photo' => $udt->photo,
                'user_role' => $role,
                'status' => 1,
                'created_by' => $parent_id
            );
            $this->db->insert('tbl_users', $user_insertdata);
		}
        $insert_id = $this->db->insert_id();

        //team mng table
        $this->db->select('t.user_id, t.location');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_team_mng as t', 'c.user_id = t.user_id');
        $this->db->where('c.citizen_id', $user_id);
        $team_d = $this->db->get()->row();

        $tm_d = array(
            'user_id' => $insert_id,
            'parent_id' => $team_d->user_id,
            'location' => $team_d->location,
            'date_from' => date('Y-m-d'),
            'created_by' => $parent_id,
            'status' => 1
        );
        $this->db->insert('tbl_team_mng', $tm_d);

        // insert in ps_member
		$data_member = array(
            'user_id' => $insert_id,
            'volunteer_id'=> $user_id,
            'ps_id' => $ps,
            'booth_no' => $booth_no,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
		);
		$this->db->insert('tbl_ps_member', $data_member);

		if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
		}else {
            $this->db->trans_commit();
            return true;
		}
    }

    public function getPSMember($id, $role) {
        $this->db->select('u.id, u.first_name, u.last_name, u.f_name, u.dob, u.voter_id, u.photo, lu.value as gender, p.ps_no, p.ps_name, p.ps_area, pm.booth_no');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->join('tbl_locations as l2', 'l.id = l2.parent_id');
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_ps_member as pm', 'pm.ps_id = p.id');
        $this->db->join('tbl_users as u', 'u.id = pm.user_id');
        $this->db->join('tbl_lookup as lu', 'u.gender = lu.id');
        $this->db->where('t.id', $id);
        $this->db->where('u.user_role', $role);
        $result = $this->db->get()->result();
        if($result) {
           return $result;
            
        }else {
            return false;
        }
        
    }

    public function getPollingStation($id) {
        $this->db->select('p.id,  p.ps_no, p.ps_name, p.ps_area, pd.ps_type, pd.sl_no_start, pd.sl_no_end, pd.male, pd.female, pd.third_gender, l.id as lc_id, l.name as location');
        $this->db->from('tbl_ps as p');
        $this->db->join('tbl_ps_dmg as pd', 'p.id = pd.ps_id');
        $this->db->join('tbl_locations as l', 'p.village_id = l.id');
        $this->db->where('p.id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getPollingStationImage($id) {
        $this->db->select('p.id,  p.ps_no, pi.img_path');
        $this->db->from('tbl_ps as p');
        $this->db->join('tbl_ps_img as pi', 'p.id = pi.ps_id');
        $this->db->where('p.id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getPollingStationMember($id, $role) {
        $this->db->select('u.id as user_id, u.first_name, u.last_name, u.photo, u.gender, u.user_role, pm.booth_no');
        $this->db->from('tbl_ps_member as pm');
        $this->db->join('tbl_users as u', 'pm.user_id = u.id');
        $this->db->where('pm.ps_id', $id);
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', $role);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCoordinatorsByPS($id) {
        $this->db->select('u.first_name, u.last_name, u.id, u.photo, u.gender, u.status');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'u.id = tp.user_id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	//Inserting User Estimated Total Amount
	public function userEstimatedCost($data,$id) {
		$userTotalAmount = array(
            'user_id' => $id,
            'total_amount' => $data['amount_total']
        );
        $this->db->trans_begin();
		$this->db->insert('tbl_estimation', $userTotalAmount);
		$insert_id = $this->db->insert_id();
		
		foreach($data['estimated_cost'] as $estco) {
            $estimatedCost = array(
                'estimated_id' => $insert_id,
                'item' => $estco['itm'],
                'description' => $estco['itd'],
                'quantity' => $estco['qty'],
                'unit' => $estco['ut'],
                'rate' => $estco['rt'],
                'per_unit' => $estco['put'],
                'amount' => $estco['amt']
            );
            $this->db->insert('tbl_estimation_details', $estimatedCost);
        }
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
	}
	//for booth mangement
	public function getTeamLeaderData($id) {
		$id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name,u.mobile ,u.last_name, l.name,l.id as vid, u.photo,u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	public function getCoordinatorsByTeamleader($id) {
		$id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name, l.name as location,u.photo, u.dob, u.caste, u.mobile, u.gender, u.email');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->join('tbl_locations as l', 't2.location = l.id');
        $this->db->where('t1.user_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getVolunteerByCoordinator($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,v.photo ,l.name');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id');
        $this->db->where('ct.parent_id', $id);
		$this->db->where('ct.user_id', $id);
		$this->db->where('ct.user_role', 46);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function getVolunteerProfile($id) {
        $this->db->select('v.id, v.firstname, v.lastname ,v.photo ,v.mobile, v.voter_id, v.gender,v.dob , v.age ,l.name');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id');
		
        $this->db->where('ct.citizen_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getMembersByVolunteer($id) {
        $this->db->select('v.id, v.firstname, v.lastname ,v.photo ,v.mobile, v.voter_id, v.gender,v.dob , v.age ,l.name, lu.value as voter_status ,lr.value as relationship ,v.created_at, ct.created_at as date_registered');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id');
		$this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->join('tbl_lookup as lr', 'ct.relationship = lr.id');
        $this->db->where('ct.parent_id', $id);
        //$this->db->where('ct.user_role', 17);
        $this->db->order_by('ct.created_at', 'desc');
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	//coordinator by village 
	public function getCoordinatorsByVillageId($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name,u.photo,lc.id , lc.name , tm.user_id ,u.user_role');
		$this->db->from('tbl_users as u');
		$this->db->join('tbl_team_mng as tm', 'tm.user_id = u.id');
		$this->db->join('tbl_locations as lc', 'lc.id = tm.location');
        $this->db->where('lc.id', $id);
		$this->db->where('u.user_role', 3);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getBoothAgentByVillageId($id ,$userrole) {
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name,u.photo,lc.id , lc.name , psm.user_id ,u.user_role');
		$this->db->from('tbl_users as u');
		$this->db->join('tbl_ps_member as psm', 'psm.user_id = u.id');
		$this->db->join('tbl_ps as ps', 'ps.ps_no = psm.ps_id');
		$this->db->join('tbl_locations as lc', 'lc.id = ps.village_id');
        $this->db->where('lc.id', $id);
		$this->db->where('u.user_role', $userrole);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getParentId($id)
	{
		$this->db->select('u.parent_id as pid');
		$this->db->from('tbl_team_mng as u');
		$this->db->where('u.user_id', $id);
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getVotersById($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.f_name, v.gender, v.mobile, v.photo');
        $this->db->from('tbl_voters as v');
        $this->db->where('v.id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	/* public function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    } */
	
	public function getCoordinatorsByManagerCitizen($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name as location');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_citizen_mng as ct', 'ct.user_id = u.id');
        $this->db->join('tbl_locations as l', 't3.location = l.id');
		
        $this->db->where('t1.user_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getOutstationByCoodinator($id)
	{
		$this->db->select('ct.citizen_id,ct.user_id,ct.parent_id');
		$this->db->from('tbl_citizen_mng as ct');
		$this->db->join('tbl_citizen_outstation as os', 'ct.citizen_id = os.citizen_id');
		$this->db->where('user_id', $id);
		$result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getNeibourhoodByCoodinator($id)
	{
		$this->db->select('ct.citizen_id,ct.user_id,ct.parent_id');
		$this->db->from('tbl_citizen_mng as ct');
		$this->db->join('tbl_visit_6 as n', 'ct.citizen_id = n.citizen_id');
		$this->db->where('user_id', $id);
		$result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getVisitTwoCount($id ,$visit_value)
	{
		$this->db->select('ct.citizen_id,ct.user_id,ct.parent_id');
		$this->db->from('tbl_citizen_mng as ct');
		$this->db->join('tbl_visit_2 as v2', 'ct.citizen_id = v2.citizen_id');
		$this->db->join('tbl_visit2_options as vp2', 'v2.id = vp2.visit_id');
		$this->db->where('user_id', $id);
		$this->db->where('vp2.option_id', $visit_value);
		$result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	public function getContestants($id){
	    $this->db->select('c.party_id,p.id,c.contestants_name,c.age,l.name , c.contestant_photo, p.party_name, p.party_icon,p.party_slug,c.total_voters');
        $this->db->from('tbl_cantestants as c');
		$this->db->join('tbl_party as p', 'c.party_id = p.id');
		$this->db->join('tbl_locations as l', 'c.constitution_id = l.id');
		$this->db->where('c.constitution_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	//TASK
	public function getGroups() {
        $this->db->select('id,value');
		$this->db->from('tbl_lookup');
		$this->db->where('gen_id', 14);
		$this->db->where('id !=' ,66);
		$this->db->where('id !=' ,69);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getMyTasks($id) {
        //Self assigned task
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, t.created_by, t.created_at, tm.receiver_id, u.first_name, u.last_name, l.value as role');
		$this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tm.receiver_id', $id);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 64);
        $result_m = $this->db->get()->result(); 
        
        //Group Task for all members assigned by superior
        $sup_id = array();
        $this->db->select('t4.user_id as smng_id');
        $this->db->from('tbl_team_mng as t');
        // $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        // $this->db->join('tbl_team_mng as t3', 't.parent_id = t3.user_id');
        $this->db->join('tbl_team_mng as t4', 't.parent_id = t4.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        // $sup_id[] = $result->tl_id;
        // $sup_id[] = $result->mng_id;
        $sup_id = $result->smng_id;
        
        $this->db->select("t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, t.created_by, t.created_at, tm.receiver_id, u.first_name, u.last_name, l.value as role");
        $this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 't.id = tm.task_id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tm.receiver_id', 65);
        $this->db->where('t.task_group', 62);
        $this->db->where('t.created_by', $sup_id);
        $result_ag = $this->db->get()->result();

        //Individual Task assigned by superior 
		$this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, t.created_by, t.created_at, tm.receiver_id, u.first_name, u.last_name, l.value as role');
		$this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
		$this->db->where('tm.receiver_id', $id);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 63);
        $result_a = $this->db->get()->result();
        
        //Group task allocated to managers
		$this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, t.created_by, t.created_at, tm.receiver_id, u.first_name, u.last_name, l.value as role');
		$this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 62);
        $this->db->where('tm.receiver_id', 66);
        $result_my = $this->db->get()->result();

        //Group Task assigned to Team leader or Coordinator
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group,t.created_by, t.created_at, tm.receiver_id');
        $this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 62);
        $this->db->where('t.created_by', $id);
        $this->db->where('(tm.receiver_id = 67 OR tm.receiver_id = 68)', NULL, FALSE);
        //$this->db->or_where('tm.receiver_id', 68);
        $result_ga = $this->db->get()->result();

        //Individual Task assigned to Team leader or Coordinator
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, t.created_by, t.created_at, tm.receiver_id, u.first_name, u.last_name, u.user_role, l.value as role');
        $this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->join('tbl_team_mng as m', 'tm.receiver_id = m.user_id');
        $this->db->join('tbl_users as u', 'u.id = m.user_id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 63);
        $this->db->where('t.created_by', $id);
        $this->db->where('u.user_role', 3);
        $this->db->or_where('u.user_role', 18);
        $result_ia = $this->db->get()->result();
		
		$result = array_merge((array) $result_m, (array) $result_a, (array) $result_my, (array) $result_ga, (array) $result_ia, (array) $result_ag);
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function allocateGroupTaskBySeniorManager($group) {
		$this->db->trans_begin();
		
		$id = $this->session->userdata('user')->id;
		$taskname = $this->input->post('taskname');
		$gdatefrom = date('Y-m-d', strtotime($this->input->post('datefrom')));
		$gdateto = date('Y-m-d', strtotime($this->input->post('dateto')));
		$priority = $this->input->post('priority');
		$receiver = $this->input->post('receiver');
		$taskdescription = $this->input->post('editor_contents');
		
		$allocatetask = array(
						'task_name' => $taskname,
						'task_description'=> $taskdescription,
						'date_from' => $gdatefrom,
						'date_to' => $gdateto,
						'priority' => $priority,
						'task_group' => $group,
						'created_by' =>$id
						);
		$this->db->insert('tbl_tasks', $allocatetask);	
		$insert_id = $this->db->insert_id();
		$recivertask = array(
						'task_id' => $insert_id,
						'receiver_id'=> $receiver 
						);
		$this->db->insert('tbl_tasks_mng', $recivertask);
		if($this->db->trans_status() === FALSE) {
		$this->db->trans_rollback();
		return false;
		}else {
		$this->db->trans_commit();
		return true;
		}
    }
	

	public function new_launch_slide_articles() {
	      $this->db->select('*');
                  $this->db->from('tbl_users');
                  $this->db->order_by('id', 'DESC');
	      return  $query = $this->db->get();
		  echo $this->db->last_query();exit;
     
    }
    
    public function getCoordPerformanceBySM($id) {
        $this->db->select('t3.user_id, count(c.user_id) as registered');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id');
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
        $this->db->join('tbl_citizen_mng as c', 'c.user_id = t3.user_id');
        $this->db->where('t.user_id', $id);
        $this->db->where('c.user_role', 17);
        $this->db->group_by('c.user_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDBRoleRequest($id) {
        $this->db->select('u.id as uid, u.first_name, u.last_name, v.id as vid, v.firstname, v.lastname, p.id as pid, p.ps_no, p.ps_name, r.id as rid, r.role_id,  lu.value as role, r.status, pm.booth_no');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->join('tbl_role_request as r', 'r.user_id = u.id');
        $this->db->join('tbl_ps_member as pm', 'r.volunteer_id = pm.volunteer_id', 'left');
        $this->db->join('tbl_lookup as lu', 'r.role_id = lu.id');
        $this->db->join('tbl_voters as v', 'r.volunteer_id = v.id');
        $this->db->join('tbl_ps as p', 'r.ps_id = p.id');
        $this->db->where('t.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->order_by('r.status');
        $this->db->order_by('r.created_at', 'desc');
        $result = $this->db->get();
        return $result;
    }

    public function declinePSMemberRequest($rid) {
        $this->db->set('status', 2);
        $this->db->where('id', $rid);
        $this->db->update('tbl_role_request');
        $result = $this->db->affected_rows();
        if($result > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function getDivisionIncharge($id) {
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
	
	public function getVotersCountByDI($id, $filters = array()) {
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
        //$this->db->where('cm.user_role', 17);
        $this->db->where('tm.parent_id', $id);
        return $this->db->get()->num_rows();
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
}