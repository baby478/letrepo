<?php
class SeniorManagerModel extends CI_Model {
	 // Declare variables
        private $_limit;
        private $_pageNumber;
        private $_offset;
        // setter getter function
	
    public function __construct() {
        parent::__construct();
    }
	// for pagination changes
	public function setLimit($limit) {
        $this->_limit = $limit;
    }
 
    public function setPageNumber($pageNumber) {
        $this->_pageNumber = $pageNumber;
    }

    public function setOffset($offset) {
        $this->_offset = $offset;
    }
    // Count all record of table "employee" in database.
    public function getAllEmployeeCount() {
        $this->db->from('employee');
        return $this->db->count_all_results();
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
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, l.value as gender, rl.value as user_role, lc.name as location, u.status as active_status, t.status as user_status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->join('tbl_team_mng as t', 't.user_id = u.id', 'left');
        $this->db->join('tbl_locations as lc', 't.location = lc.id', 'left');
		$this->db->where('u.user_role', 137);
      // $this->db->where('u.created_by', $user_id);
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

    public function emailExists($email) {
        $this->db->select('id');
        $this->db->where('email', $email);
        $result = $this->db->get('tbl_users')->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
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

    public function assignUserRole($id, $role, $location) {
        $this->db->trans_begin();
        //update role
        $this->db->set('user_role', $role);
        $this->db->where('id', $id);
        $this->db->update('tbl_users');

        //insert team
        $parent_id = $this->session->userdata('user')->id;
        $data = array(
            'user_id' => $id,
            'parent_id' => $parent_id,
            'location' => $location,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
        );
        $this->db->insert('tbl_team_mng', $data);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
        
        
    }
	
	public function assignManagerMandal() {
		$user_id = $this->input->post('user');
        $location = $this->input->post('user-mandal');
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
		//echo $this->db->last_query();exit;
        return $insert_id;
    } 

    public function getUserByRole($role) {
        $id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name,u.photo, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', $role);
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
	
	// public function getMobileTeamByMandal($id,$role,$location) {
    //     $this->db->select('u.id, u.first_name, u.last_name,u.photo, t.status, t.location');
    //     $this->db->from('tbl_team_mng t');
    //     $this->db->join('tbl_users u', 't.user_id = u.id');
	// 	$this->db->join('tbl_locations as l', 't.location = l.id');
    //     $this->db->where('t.parent_id', $id);
	// 	$this->db->where('u.user_role', $role);
	// 	$this->db->where('t.location', $location);
    //     $this->db->where('t.status', 1);
    //     $result = $this->db->get()->result();
    //     if($result) {
    //         return $result;
    //     }else {
    //         return false;
    //     }
    // }

    public function getMobileTeamByMandal($location) {
        $this->db->select('u.id, u.first_name, u.last_name,u.photo');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $location);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 55);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
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

    public function teamManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
		$this->db->where('u.user_role', 2);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function userProfile($id) {
        $this->db->select('u.id, u.first_name, u.user_role, u.last_name, u.f_name, l1.name as location, u.photo, u.dob, u.caste, u.mobile, u.gender, u.email, t.date_from');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 't.user_id = u.id');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->where('u.id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $user = $result->row();
            $user_role = $user->user_role;
            if($user_role == 18 || $user_role == 3) {
                $this->db->select('p.ps_no, p.ps_name');
                $this->db->from('tbl_team_ps as tp');
                $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
                $this->db->where('tp.user_id', $user->id);
                $res = $this->db->get();
                if($res->num_rows() > 0) {
                    $ps_res = $res->row();
                    $user->ps_no = $ps_res->ps_no;
                    $user->ps_name = $ps_res->ps_name;
                }
            }
            return $user;
        }else {
            return false;
        }
    }
	// for pagination of team manager
	public function teamLeaderProfile($id,$limit, $start) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo,u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
		$this->db->limit($limit, $start);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	// for pagination of team manager
	 // Count all record of table "team leader" in database.
	public function countTeamLeader($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo,u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
        return $this->db->count_all_results();
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
        $result = $this->db->get()->result();
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

    public function votersByUser($id) {
        $this->db->select('cm.id');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->where('cm.user_id', $id);
        //$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();    
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
        $this->db->select('e.event_name, e.event_description,e.event_date, e.event_img, u.first_name, u.last_name');
        $this->db->from('tbl_events as e');
        $this->db->join('tbl_users as u', 'e.user_id = u.id');
        $this->db->where('e.event_type', $id);
		$this->db->order_by('e.event_date','desc');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
    //myteam for senior manager
	public function teamProfile($id) {
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

	public function votersByManager($id) {
       $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
        $this->db->where('tm.user_id', $id);
        $result = $this->db->get();
        return $result->num_rows();
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
	
	public function getCoordinatorsBySeniorManager($id) {
        $this->db->select('u.id, u.first_name, u.user_role');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
        $this->db->where('t1.parent_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getVolunteerBySeniorManager($id) {
        $this->db->select('v.id, v.firstname, cm.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id');
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = t3.user_id');
		$this->db->join('tbl_voters as v', 'v.id = cm.citizen_id');
        $this->db->where('t.parent_id', $id);
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
	
	public function getCoordinatorsByTeamleader($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name, l.name as location,u.photo, u.dob, u.caste, u.mobile, u.gender, u.email');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        //$this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
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

    public function getVotersByManager($id, $filters = array()) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		 $this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
        $this->db->join('tbl_citizen_mng as ct', 't4.user_id = ct.user_id');
        $this->db->join('tbl_voters as v', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        //$this->db->where('ct.user_role', 17);
        $this->db->where('t1.parent_id', $id);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
    // public function getVoterList($id) {
    //     $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, l.name as mandal, l2.name as village');
    //     $this->db->from('tbl_team_mng as t1');
    //     $this->db->join('tbl_const_location as cl', 't1.location = cl.parent_id');
    //     $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
    //     $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
    //     $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
    //     $this->db->join('tbl_voters as v', 'v.ps_no = p.id');
    //     $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id', 'left');
    //     $this->db->where('t1.user_id', $id);
    //     $this->db->where('cl.level_id', 45);
    //     return $this->db->get();
    // }

    public function getVoterList($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, l.name as mandal, l2.name as village');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_const_location as cl', 't1.location = cl.parent_id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_voters as v', 'v.ps_no = p.id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id', 'left');
        $this->db->where('t1.user_id', $id);
        $this->db->where('cl.level_id', 45);

        $column_order = array(null, 'v.firstname','v.lastname','v.dob', 'v.age', 'v.voter_id', 'v.gender', 'lu.value', 'l.name', 'l2.name'); //set column field database for datatable orderable
        $column_search = array('v.firstname','v.lastname','v.dob', 'v.age', 'v.voter_id', 'v.gender', 'lu.value', 'l.name', 'l2.name'); //set column field database for datatable searchable 
        $order = array('v.id' => 'desc'); // default order
        
        $i = 0;
        foreach ($column_search as $emp)  { // loop column
            if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])){
                $_POST['search']['value'] = $_POST['search']['value'];
            } else
            
            $_POST['search']['value'] = '';
            
            if($_POST['search']['value'])  { // if datatable send POST for search
            
                if($i===0)  { // first loop
                    $this->db->group_start();
                    $this->db->like($emp, $_POST['search']['value']);
                }else {
                    $this->db->or_like($emp, $_POST['search']['value']);
                }
 
                if(count($column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        
        if(isset($_POST['order']))  { // here order processing
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        }  else if(isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }

        if(isset($_POST['length']) && $_POST['length'] < 1) {
            $_POST['length']= '10';
        } else 
        $_POST['length']= $_POST['length'];
        
        if(isset($_POST['start']) && $_POST['start'] > 1) {
            $_POST['start']= $_POST['start'];
        }
        $this->db->limit($_POST['length'], $_POST['start']);
        return $this->db->get()->result();
    }

    public function voters_count_filtered($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, l.name as mandal, l2.name as village');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_const_location as cl', 't1.location = cl.parent_id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_voters as v', 'v.ps_no = p.id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id', 'left');
        $this->db->where('t1.user_id', $id);
        $this->db->where('cl.level_id', 45);

        $column_order = array(null, 'v.firstname','v.lastname','v.dob', 'v.age', 'v.voter_id', 'v.gender', 'lu.value', 'l.name', 'l2.name'); //set column field database for datatable orderable
        $column_search = array('v.firstname','v.lastname','v.dob', 'v.age', 'v.voter_id', 'v.gender', 'lu.value', 'l.name', 'l2.name'); //set column field database for datatable searchable 
        $order = array('v.id' => 'desc'); // default order
        
        $i = 0;
        foreach ($column_search as $emp)  { // loop column
            if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])){
                $_POST['search']['value'] = $_POST['search']['value'];
            } else
            
            $_POST['search']['value'] = '';
            
            if($_POST['search']['value'])  { // if datatable send POST for search
            
                if($i===0)  { // first loop
                    $this->db->group_start();
                    $this->db->like($emp, $_POST['search']['value']);
                }else {
                    $this->db->or_like($emp, $_POST['search']['value']);
                }
 
                if(count($column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        
        if(isset($_POST['order']))  { // here order processing
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        }  else if(isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }

        $query = $this->db->get();
        return $query->num_rows();
    }

    public function voters_count_all($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, l.name as mandal, l2.name as village');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_const_location as cl', 't1.location = cl.parent_id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_voters as v', 'v.ps_no = p.id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id', 'left');
        $this->db->where('t1.user_id', $id);
        $this->db->where('cl.level_id', 45);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getTotalVotersByManager($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t3.user_id');
        $this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->where('t1.user_id', $id);
        $this->db->where('ct.user_role', 17);
            $result = $this->db->get();
        return $result->num_rows();
    }
	 
	public function getTotalVote($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('v.user_id', $id);
		//$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
    }
	
	public function getVolunteerTotalVote($id,$filters = array()) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'ct.citizen_id = v.id');
		//$this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->where('ct.parent_id', $id);
		//$this->db->where('ct.user_role', 17);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
	public function getMembersByVolunteer($id) {
        $this->db->select('v.id, v.firstname, v.lastname ,v.photo ,v.mobile, v.voter_id, v.gender,v.dob , v.age ,l.name,lu.value as voter_status ,lr.value as relationship ,ct.created_at');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id', 'left');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id', 'left');
		$this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->join('tbl_lookup as lr', 'ct.relationship = lr.id');
        $this->db->where('ct.parent_id', $id);
		//$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getMyGroupMembersByVolunteer($id) {
        $this->db->select('v.id, v.firstname, v.lastname ,v.photo ,v.mobile, v.voter_id, v.gender,v.dob , v.age ,l.name,lu.value as voter_status ,lr.value as relationship ,la.value as attend,v.created_at, dg.created_at as attend_time');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id');
		$this->db->join('tbl_digital_booth as dg', 'ct.citizen_id = dg.citizen_id', 'left');
		$this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->join('tbl_lookup as lr', 'ct.relationship = lr.id');
		$this->db->join('tbl_lookup as la', 'dg.attend = la.id', 'left');
        $this->db->where('ct.parent_id', $id);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalAttendant($id) {
        $this->db->select('la.value as attend');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_digital_booth as dg', 'ct.citizen_id = dg.citizen_id');
		$this->db->join('tbl_lookup as la', 'dg.attend = la.id');
        $this->db->where('ct.parent_id', $id);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
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
		//$this->db->where('ct.user_role', 17);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function getPositiveNegVotersByManager($id, $status) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->where('v.voter_status', $status);
		$this->db->where('t1.user_id', $id);
		$this->db->where('ct.user_role', 17);
		 $result = $this->db->get();
        return $result->num_rows();
	}

    public function countVotersByVillage($id) {
        $query = $this->db->query("SELECT  c.id, c.name, sum(c.voters) AS total
        from (
            select count(v.user_id) as voters, l2.name, l2.id
            from tbl_team_mng as t1
            join tbl_locations as l on t1.location = l.id
            join tbl_locations as l2 on l.id = l2.parent_id
            left join tbl_team_mng as t2 on t2.location = l2.id
            left join tbl_team_mng as t3 on t2.user_id = t3.parent_id
            left join tbl_voters as v on v.user_id = t3.user_id
            where t1.user_id = $id
            group by l2.id, l2.name, v.user_id) as c group by c.id, c.name order by total desc limit 10");
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
            where t1.user_id = $id and v.voter_status = $status
            group by l2.id, l2.name, v.user_id) as c group by c.id, c.name order by positive desc limit 10");
        return $query->result();
    }

    public function getPollingStationsByManager($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village');
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

    public function allocateBAgentByManager() {
        $user_id = $this->input->post('userba');
        $ps = $this->input->post('polling-station');
        $parent_id = $this->session->userdata('user')->id;
        $booth_no = $this->input->post('booth-no');
        $data = array(
            'user_id' => $user_id,
            'ps_id' => $ps,
            'booth_no' => $booth_no,
            'date_from' => date('Y-m-d'),
            'status' => 1,
            'created_by' => $parent_id
        );
        $this->db->insert('tbl_ps_member', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
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
        $this->db->select('p.id,  p.ps_no, p.ps_name, p.ps_area, l.id as lc_id, l.name as location');
        $this->db->from('tbl_ps as p');
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

    public function getCoordinatorsByVillageId($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name,u.photo,lc.id , lc.name , tm.user_id ,u.user_role');
		$this->db->from('tbl_users as u');
		$this->db->join('tbl_team_mng as tm', 'tm.user_id = u.id');
		$this->db->join('tbl_locations as lc', 'lc.id = tm.location');
		$this->db->join('tbl_ps as p', 'p.village_id = lc.id');
        $this->db->where('p.id', $id);
		$this->db->limit(5 , 0);
		$this->db->where('u.user_role', 3);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCoordinatorByPS($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name,u.photo ,u.user_role');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getTeamleaderByVillageId($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.dob,u.mobile,u.email,u.f_name,u.photo, lc.id , lc.name , tm.user_id ,u.user_role');
		$this->db->from('tbl_users as u');
		$this->db->join('tbl_team_mng as tm', 'tm.user_id = u.id');
		$this->db->join('tbl_locations as lc', 'lc.id = tm.location');
		$this->db->join('tbl_ps as p', 'p.village_id = lc.id');
        $this->db->where('p.village_id', $id);
		$this->db->where('u.user_role', 18);
		$this->db->group_by('u.id');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getTeamLeaderByPs($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.dob, u.mobile, u.gender, u.email, u.f_name, u.photo, lc.id as location_id, lc.name as location, p.ps_no, p.ps_name, tp.status');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_team_mng as tm', 'tm.user_id = tp.user_id');
		$this->db->join('tbl_locations as lc', 'lc.id = tm.location');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('tp.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
	
    public function getPollingStationMember($id, $role) {
        $this->db->select('u.id as user_id, u.first_name, u.last_name, u.photo, u.gender, u.user_role, pm.booth_no,pm.ps_id');
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

	public function getBoothPSByVillageId($id) {
        $this->db->select('p.id,  p.ps_no, p.ps_name, p.ps_area ,lc.name ,p.village_id');
		$this->db->from('tbl_ps as p');
		$this->db->join('tbl_locations as lc', 'lc.id = p.village_id');
        $this->db->where('lc.id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
    public function getPollingStationCount($id, $filters = array()) {
        $this->db->select('v.id, v.firstname, v.lastname,v.ps_no');
        $this->db->from('tbl_ps as ps');
        $this->db->join('tbl_voters as v', 'v.ps_no = ps.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.citizen_id = v.id');
        $this->db->where('ps.id', $id);
        $this->db->where('cm.user_role', 17);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
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

	public function getTotalCoordinatorsByTL($id) {
        $this->db->select('u.first_name, u.last_name, u.id, u.photo, u.gender');
        $this->db->from('tbl_team_mng as m');
        $this->db->join('tbl_users as u', 'm.user_id = u.id');
		$this->db->where('m.parent_id',$id);
        $this->db->where('u.user_role', 3);
        $this->db->where('m.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }
	
	public function mandalBySeniorManager($id){
		$this->db->select('u.id, l.name ,cl.location_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('t.parent_id', $id);
		$this->db->where('u.user_role', 2);
        $result = $this->db->get()->result();
		
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getMandalsByConstituence($id){
		$this->db->select('l.id,l.name,cl.location_id');
        $this->db->from('tbl_locations as l');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
	public function getPollingstationBySeniorManager($id){
		$this->db->select('p.id, p.ps_no, p.ps_name, p.ps_area');
		$this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_locations as l', 'cl.location_id=l.id');
		$this->db->join('tbl_locations as l2', 'l.id = l2.parent_id');
	    $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->where('tm.user_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
    
    public function new_launch_slide_articles() {
	      $this->db->select('*');
                  $this->db->from('tbl_users');
                  $this->db->order_by('id', 'DESC');
	      return  $query = $this->db->get();
		  echo $this->db->last_query();
	}
	//Query For Dashboard Mandal
	
	public function getWardInfo() {
        $this->db->select('*');
		$this->db->from('dm_ward_info');
		
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
       
    }

	public function getCounselor() {
        $this->db->select('*');
		$this->db->from('dm_counselor');
		
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getPartyName() {
        $this->db->select('p.id,p.party_name,p.party_icon');
		$this->db->from('tbl_party as p');
		$this->db->where('p.id !=', 4);
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
    public function getXpartyData() {
        $this->db->select('p.id,x.name,p.party_name,p.id,x.followers,x.mobile,x.designation,x.age,x.influence');
		$this->db->from('tbl_xparty_info as x');
		$this->db->join('tbl_party as p', 'x.party_id = p.id');
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
	public function getXparty($id) {
        $this->db->select('x.party_id,p.id,x.name,p.party_name,x.followers,x.mobile,x.designation,x.age,x.influence');
		$this->db->from('tbl_xparty_info as x');
		$this->db->join('tbl_party as p', 'x.party_id = p.id');
		$this->db->where('x.party_id', $id);
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getXpartyById($partyid,$locationid) {
		$this->db->select('u.first_name, u.last_name, u.id,x.party_id,x.name,x.designation,x.followers,x.age,p.id,p.party_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'u.id = tp.user_id');
		$this->db->join('tbl_xparty_info as x', 'x.user_id = u.id');
		$this->db->join('tbl_party as p', 'x.party_id = p.id');
        $this->db->where('tp.ps_id', $locationid);
		$this->db->where('x.party_id',$partyid);
        $this->db->where('u.user_role', 3);
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
	public function getMandalEvents() {
        $this->db->select('e.event_name, e.event_description, e.event_date, e.event_img');
        $this->db->from('tbl_events as e');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getOutstationByCoodinator($id) {	
        $this->db->select('ct.citizen_id,ct.user_id,ct.parent_id');
		$this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as ct', 't4.user_id = ct.user_id');
		$this->db->join('tbl_citizen_outstation as os', 'ct.citizen_id = os.citizen_id');
		$this->db->where('t4.parent_id', $id);
		$result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getNeibourhoodByCoodinator($id) {
		$this->db->select('ct.citizen_id,ct.user_id,ct.parent_id');
		$this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as ct', 't4.user_id = ct.user_id');
		$this->db->join('tbl_visit_6 as n', 'ct.citizen_id = n.citizen_id');
		$this->db->where('t4.parent_id', $id);
		$result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getVisitTwoCount($id ,$visit_value) {
		$this->db->select('ct.citizen_id,ct.user_id,ct.parent_id');
		$this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as ct', 't4.user_id = ct.user_id');
		$this->db->join('tbl_visit_2 as v2', 'ct.citizen_id = v2.citizen_id');
		$this->db->join('tbl_visit2_options as vp2', 'v2.id = vp2.visit_id');
		$this->db->where('t4.parent_id', $id);
		$this->db->where('vp2.option_id', $visit_value);
		$result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	//for booth mangement
	public function getTeamLeaderData($id) {
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
	
	public function getContestants($id){
	    $this->db->select('c.party_id,p.id,c.contestants_name,c.age,l.name , c.contestant_photo, p.party_name, p.party_icon,p.party_slug,c.total_voters');
        $this->db->from('tbl_cantestants as c');
		$this->db->join('tbl_party as p', 'c.party_id = p.id');
		$this->db->join('tbl_locations as l', 'c.constitution_id = l.id');
		$this->db->where('c.constitution_id', $id);
		$this->db->where('c.delete_status', 1);
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
        $result = $this->db->get()->result();
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

	public function getMyTasks($id) {
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group ,t.created_at,tm.receiver_id');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->where('tm.receiver_id', $id);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 64);
		//$this->db->where('tm.receiver_id', $id);
		//$this->db->where('tm.receiver_id', 65);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getAllEventTasks($id) {
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, tm.receiver_id');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
		$this->db->where('t.created_by', $id);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $events = $result->result();
            foreach($events as $e) {
                if($e->task_group == 64) {
                    $e->assigned = 'Self';
                }
                if($e->task_group == 62) {
                    if($e->receiver_id == 65) {
                        $e->assigned = 'All Members';
                    }
                    if($e->receiver_id == 66) {
                        $e->assigned = 'All Managers';
                    }
                    if($e->receiver_id == 67) {
                        $e->assigned = 'All Team Leaders';
                    }
                    if($e->receiver_id == 68) {
                        $e->assigned = 'All Coordinators';
                    }
                    if($e->receiver_id == 69) {
                        $e->assigned = 'All Mobile Team';
                    }
                }
                if($e->task_group == 63) {
                    $this->db->select('u.first_name, u.last_name, l.value as role');
                    $this->db->from('tbl_tasks_mng as tm');
                    $this->db->join('tbl_users as u', 'u.id = tm.receiver_id');
                    $this->db->join('tbl_lookup as l', 'l.id = u.user_role');
                    $this->db->where('tm.task_id', $e->id);
                    $res = $this->db->get()->row();
                    $e->assigned = $res->first_name . ' ' . $res->last_name . ' (' . $res->role . ')';
                }

            }
            return $events;
        }else {
            return false;
        }
    }
	
	public function getMobileTeamTasks($id,$mtid) {
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group ,');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->where('receiver_id', $mtid);
		$this->db->where('t.created_by', $id);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 63);
        $result_in = $this->db->get()->result();
		
		$this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group ,');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->where('receiver_id', 69);
		$this->db->where('t.created_by', $id);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $this->db->where('t.task_group', 62);
        $result_all = $this->db->get()->result();
		
		$result = array_merge((array) $result_in, (array) $result_all);
		
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getRelationshipCoordinator($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->where('t1.user_id', $id);
		$this->db->where('ct.user_role', 17);
		 $result = $this->db->get();
        return $result->num_rows();
	}
	/* Coordinator Graph */
	/* Visit 2 */
	public function getVisitTwoCoordinatorCount($id ,$visit_value) {
		$this->db->select('v.id');
		$this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_visit_2 as v2', 'ct.citizen_id = v2.citizen_id');
		$this->db->join('tbl_visit2_options as vp2', 'v2.id = vp2.visit_id');
		$this->db->where('ct.parent_id', $id);
		$this->db->where('vp2.option_id', $visit_value);
		$result = $this->db->get();
		if($result->num_rows() > 0) {
			return $result->num_rows();
		}else {
			return 0;
		}
	}
	/* Visit 5 */
	public function getPersonalInfoCoordinator($id, $service ,$visit_value) {
        $this->db->select('vl.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_visit_5 as v5', 'v.id = v5.citizen_id');
        $this->db->join('tbl_visits_lookup as vl', 'vl.id = v5.'.$service);
        $this->db->where('ct.parent_id', $id);
        $this->db->where('v5.'.$service, $visit_value);
        $result = $this->db->get();
		if($result->num_rows() > 0) {
			return $result->num_rows();
		}else {
			return 0;
		}
    }
	/* Analytic for Visit 21 */
	public function getModiSchemesCount($id, $service) {
        $this->db->select('vl.id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_21 as v4', 'v.id = v4.citizen_id');
        $this->db->join('tbl_visits_lookup as vl', 'vl.id = v4.'.$service);
        $this->db->where('u.user_role', 3);
		$this->db->where('vl.id', 229);
       $result = $this->db->get()->num_rows();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function managerMandalExist($mid,$uid,$role) {
        $this->db->select('t.id, t.user_id');
		$this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->where('t.location', $mid);
		$this->db->where('t.parent_id', $uid);
		$this->db->where('u.user_role', $role);
        $result = $this->db->get()->result();
        if(count($result) > 0) {
            return $result;
        }else {
            return false;
        }

    }

    public function mandalAllocateExists($location) {
        $this->db->select('t.id');
        $this->db->from('tbl_team_mng as t');
        $this->db->where('t.location', $location);
        $this->db->where('t.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	/* public function getCaptcha{
		// First, delete old captchas
		$expiration = time() - 7200; // Two hour limit
		$this->db->where('captcha_time < ', $expiration)
				->delete('captcha');

		// Then see if a captcha exists:
		$sql = 'SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?';
		$binds = array($_POST['captcha'], $this->input->ip_address(), $expiration);
		$query = $this->db->query($sql, $binds);
		$row = $query->row();

		if ($row->count == 0)
		{
				echo 'You must submit the word that appears in the image.';
		}
				
    } */
    
    public function getCoordPerformanceBySM($id) {
        $this->db->select('t4.user_id, count(c.user_id) as registered');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id');
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
        $this->db->join('tbl_citizen_mng as c', 'c.user_id = t4.user_id');
        $this->db->where('t.user_id', $id);
      //  $this->db->where('c.user_role', 17);
        $this->db->group_by('c.user_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
		public function getPollingAgent($id) {
        $this->db->select('pa.first_name,pa.last_name,pa.mobile,pa.photo,ps.ps_name,ps.ps_no');
        $this->db->from('tbl_polling_agent as pa');
		$this->db->join('tbl_ps as ps', 'pa.ps_no = ps.id');
		$this->db->where('pa.ps_no', $id);
		$this->db->where('pa.delete_status', 1);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getChildId($id){
		$this->db->select('l.location_id,lc.level_id');
		$this->db->from('tbl_const_location as l');
		$this->db->join('tbl_locations as lc','l.location_id = lc.id');
		$this->db->where('l.parent_id',$id);
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
    
	
	public function getTelecallerDataByRole($role) {
        $this->db->select('u.id, u.first_name,u.mobile ,u.last_name,u.user_role,qa.answer,q.question');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_q_result as qa', 't.user_id = qa.user_id');
		$this->db->join('tbl_questionnaire as q', 'qa.qid = q.id');
		$this->db->join('tbl_q_lookup as ql', 'qa.answer = ql.id');
        $this->db->where('u.user_role', $role);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getQuestionByRole($role) {
        $this->db->select('q.id, q.user_role,q.report,q.question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.user_role', $role);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getQuestionByReport($role,$rno) {
        $this->db->select('q.id, q.user_role,q.report,q.question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.user_role', $role);
		$this->db->where('q.report', $rno);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    /**
     * Date : 16-01-2019
     */
    public function getDivisionHead($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersCountByDH($id, $filters = array()) {
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

    public function getBoothPresidentByDI($id) {
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

    public function getVotersCountByBP($id, $filters = array()) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
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

    public function getBoothCoordinatorByDI($id) {
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

    public function getBoothCoordinatorPS($id) {
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

    public function getTelecallerByDI($id) {
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

    public function getStreetPresidentByBP($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role, p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_ps as tp', 'tm.user_id = tp.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
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

    public function getFamilyHeadBySP($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,v.photo ,l.name');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id');
        $this->db->where('ct.parent_id', $id);
		//$this->db->where('ct.user_id', $id);
		$this->db->where('ct.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
    
    public function getVotersCountBySP($id, $filters = array()) {
        $this->db->select('c.citizen_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->where('c.user_id', $id);
        //$this->db->where('c.user_role', 17);
        return $this->db->get()->num_rows();
    }

    /**
     * Date : 24-01-19
     * Author : Anees
     */
    public function getTeamValidation($mandal, $role) {
        if($mandal == 'all') {
            $lid = $this->session->userdata('user')->location_id;
            if($role == 46) {
                $this->db->select('v.id, v.firstname as first_name, v.lastname as last_name, v.gender, v.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
                $this->db->from('tbl_const_location as cl');
                $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_citizen_mng as cm', 'cm.parent_id = u.id');
                $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
                $this->db->join('tbl_lookup as lu', 'cm.user_role = lu.id');
                $this->db->where('u.user_role', 3);
                $this->db->where('cm.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->where('cl.parent_id', $lid);
                $this->db->order_by('p.ps_no');
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                }
            }else {
                $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
                $this->db->from('tbl_const_location as cl');
                $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                $this->db->where('u.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->where('cl.parent_id', $lid);
                $this->db->order_by('p.ps_no');
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                }
            }    
        }else {
            if($role == 46) {
                $this->db->select('v.id, v.firstname as first_name, v.lastname as last_name, v.gender, v.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
                $this->db->from('tbl_locations as l1'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_citizen_mng as cm', 'cm.parent_id = u.id');
                $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
                $this->db->join('tbl_lookup as lu', 'cm.user_role = lu.id');
                $this->db->where('l1.id', $mandal);
                $this->db->where('u.user_role', 3);
                $this->db->where('cm.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->order_by('p.ps_no');
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                    // echo '<pre>'; print_r($bp_user); exit;
                }
            }else {
                $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
                $this->db->from('tbl_locations as l1'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                $this->db->where('l1.id', $mandal);
                $this->db->where('u.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->order_by('p.ps_no');
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                    // echo '<pre>'; print_r($bp_user); exit;
                }
            }
            
        }
        if(isset($tm_user) && count($tm_user) > 0) {
            foreach($tm_user as $u) {
                $this->db->select('v.id, v.profession, v.party_participation, v.personal_status, v.family_voters, v.vote_commitment');
                $this->db->from('tbl_validation as v');
                $this->db->where('v.user_id', $u->id);
                $this->db->where('v.user_role', $role);
                $result = $this->db->get();
                if($result->num_rows() > 0) {
                    $val = $result->row();
                    if($val->profession != 0) {
                        $this->db->select('l.value');
                        $this->db->from('tbl_lookup as l');
                        $this->db->where('l.id', $val->profession);
                        $u->profession = $this->db->get()->row()->value;
                    }else {
                        $u->profession = '-';
                    }
                    if($val->party_participation != 0) {
                        $this->db->select('l.value');
                        $this->db->from('tbl_lookup as l');
                        $this->db->where('l.id', $val->party_participation);
                        $u->party_participation = $this->db->get()->row()->value;
                    }else {
                        $u->party_participation = '-';
                    }
                    if($val->personal_status != 0) {
                        $this->db->select('l.value');
                        $this->db->from('tbl_lookup as l');
                        $this->db->where('l.id', $val->personal_status);
                        $u->personal_status = $this->db->get()->row()->value;
                    }else {
                        $u->personal_status = '-';
                    }
                    if($val->family_voters != 0) {
                        $this->db->select('l.value');
                        $this->db->from('tbl_lookup as l');
                        $this->db->where('l.id', $val->family_voters);
                        $u->family_voters = $this->db->get()->row()->value;
                    }else {
                        $u->family_voters = '-';
                    }
                    if($val->vote_commitment != 0) {
                        $this->db->select('l.value');
                        $this->db->from('tbl_lookup as l');
                        $this->db->where('l.id', $val->vote_commitment);
                        $u->vote_commitment = $this->db->get()->row()->value;
                    }else {
                        $u->vote_commitment = '-';
                    }
                }else {
                    $u->profession = '-';
                    $u->party_participation = '-';
                    $u->personal_status = '-';
                    $u->family_voters = '-';
                    $u->vote_commitment = '-';
                }
                
                //govt scheme
                $this->db->select('v1.id');
                $this->db->from('tbl_validation_1 as v1');
                $this->db->where('v1.user_id', $u->id);
                $this->db->where('v1.user_role', $role);
                $result = $this->db->get();
                if($result->num_rows() > 0) {
                    $val = $result->row();
                    $this->db->select('l.value');
                    $this->db->from('tbl_validation1_options as vo');
                    $this->db->join('tbl_lookup as l', 'vo.option_id = l.id');
                    $this->db->where('vo.validation_id', $val->id);
                    $val_option = $this->db->get();
                    if($val_option->num_rows() > 0) {
                        $val_d = $val_option->result();
                        if(count($val_d) > 0) {
                            $govt_schemes = '';
                            $i = 1;
                            foreach($val_d as $v) {
                                if(count($val_d) > $i) {
                                    $govt_schemes .= $v->value . ', ';
                                }else{
                                    $govt_schemes .= $v->value;
                                }
                                $i++;
                            }
                            $u->govt_schemes = $govt_schemes;
                        }else {
                            $u->govt_schemes = $val_d->value;
                        }
                    }else {
                        $u->govt_schemes = '-';
                    }
                }else {
                    $u->govt_schemes = '-';
                }

                //YSR scheme
                $this->db->select('v2.id');
                $this->db->from('tbl_validation_2 as v2');
                $this->db->where('v2.user_id', $u->id);
                $this->db->where('v2.user_role', $role);
                $result = $this->db->get();
                if($result->num_rows() > 0) {
                    $val = $result->row();
                    $this->db->select('l.value');
                    $this->db->from('tbl_validation2_options as vo');
                    $this->db->join('tbl_lookup as l', 'vo.option_id = l.id');
                    $this->db->where('vo.validation_id', $val->id);
                    $val_option = $this->db->get();
                    if($val_option->num_rows() > 0) {
                        $val_d = $val_option->result();
                        if(count($val_d) > 0) {
                            $ysr_schemes = '';
                            $i = 1;
                            foreach($val_d as $v) {
                                if(count($val_d) > $i) {
                                    $ysr_schemes .= $v->value . ', ';
                                }else{
                                    $ysr_schemes .= $v->value;
                                }
                                $i++;
                            }
                            $u->ysr_schemes = $ysr_schemes;
                        }else {
                            $u->ysr_schemes = $val_d->value;
                        }
                    }else {
                        $u->ysr_schemes = '-';
                    }
                }else {
                    $u->ysr_schemes = '-';
                }
            } 
            return $result_d;
        }else {
            return $result_d;
        }
    }

    /**
     * Date : 12-02-2019
     * Author : Anees
     */
    public function getTCValidation($mandal, $role, $report) {
        if($mandal == 'all') {
            $lid = $this->session->userdata('user')->location_id;
            if($role == 46) {
                $this->db->select('v.id, v.firstname as first_name, v.lastname as last_name, v.gender, v.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name, q.id as qid, q.question');
                $this->db->from('tbl_const_location as cl');
                $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_citizen_mng as cm', 'cm.parent_id = u.id');
                $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
                $this->db->join('tbl_lookup as lu', 'cm.user_role = lu.id');
                $this->db->join('tbl_questionnaire as q', 'cm.user_role = q.user_role');
                $this->db->where('u.user_role', 3);
                $this->db->where('cm.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->where('cl.parent_id', $lid);
                $this->db->where('q.user_role', $role);
                $this->db->where('q.report', $report);
                $this->db->order_by('p.ps_no');
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                }
            }else {
                $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name, q.id as qid, q.question');
                $this->db->from('tbl_const_location as cl');
                $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                $this->db->join('tbl_questionnaire as q', 'u.user_role = q.user_role');
                $this->db->where('u.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->where('cl.parent_id', $lid);
                $this->db->order_by('p.ps_no');
                $this->db->where('q.user_role', $role);
                $this->db->where('q.report', $report);
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                }
            }    
        }else {
            if($role == 46) {
                $this->db->select('v.id, v.firstname as first_name, v.lastname as last_name, v.gender, v.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name, q.id as qid, q.question');
                $this->db->from('tbl_locations as l1'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_citizen_mng as cm', 'cm.parent_id = u.id');
                $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
                $this->db->join('tbl_lookup as lu', 'cm.user_role = lu.id');
                $this->db->join('tbl_questionnaire as q', 'cm.user_role = q.user_role');
                $this->db->where('l1.id', $mandal);
                $this->db->where('u.user_role', 3);
                $this->db->where('cm.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->order_by('p.ps_no');
                $this->db->where('q.user_role', $role);
                $this->db->where('q.report', $report);
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                    // echo '<pre>'; print_r($bp_user); exit;
                }
            }else {
                $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name, q.id as qid, q.question');
                $this->db->from('tbl_locations as l1'); //mandal
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
                $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
                $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
                $this->db->join('tbl_users as u', 'tp.user_id = u.id');
                $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                $this->db->join('tbl_questionnaire as q', 'u.user_role = q.user_role');
                $this->db->where('l1.id', $mandal);
                $this->db->where('u.user_role', $role);
                $this->db->where('u.status', 1);
                $this->db->order_by('p.ps_no');
                $this->db->where('q.user_role', $role);
                $this->db->where('q.report', $report);
                $result_d = $this->db->get();
                if($result_d->num_rows() > 0) {
                    $tm_user = $result_d->result();
                    // echo '<pre>'; print_r($bp_user); exit;
                }
            }    
        }
        if(isset($tm_user) && count($tm_user) > 0) {
            foreach($tm_user as $u) {
                $this->db->select('qr.qid, qr.answer');
                $this->db->from('tbl_q_result as qr');
                $this->db->where('qr.user_id', $u->id);
                $this->db->where('qr.user_role', $role);
                $this->db->where('qr.qid', $u->qid);
                $result = $this->db->get();
                if($result->num_rows() > 0) {
                    $val = $result->row();
                    if($val->answer != 0) {
                        $this->db->select('ql.value');
                        $this->db->from('tbl_q_lookup as ql');
                        $this->db->where('ql.id', $val->answer);
                        $u->answer = $this->db->get()->row()->value;
                    }else {
                        $u->answer = '-';
                    }
                }else {
                    $u->answer = '-';
                }
            } 
            return $result_d;
        }else {
            return $result_d;
        }
    }

    public function getLocationById($id) {
        $this->db->select('l.id as lid, l.name as location, l.level_id');
        $this->db->from('tbl_locations as l');
        $this->db->where('l.id', $id);
        return $this->db->get()->row();
    }

    public function getValidationAnalyticData($role,$filters = array()){
		$this->db->select('v.id, v.profession, v.party_participation, v.personal_status, v.family_voters, v.vote_commitment');
        $this->db->from('tbl_validation as v');
        $this->db->where('v.user_role', $role);
		 if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
	}
	
	public function getPresentGovtValidationAnalyticData($role,$filters = array()){
		$this->db->select('v1.id, v1.user_id, v1.user_role,vo.validation_id,vo.option_id');
        $this->db->from('tbl_validation_1 as v1');
		$this->db->join('tbl_validation1_options as vo','vo.validation_id=v1.id');
        $this->db->where('v1.user_role', $role);
		 if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
    
	public function getUpcomingGovtValidationAnalyticData($role,$filters = array()){
		$this->db->select('v2.id, v2.user_id, v2.user_role,vo.validation_id,vo.option_id');
        $this->db->from('tbl_validation_2 as v2');
		$this->db->join('tbl_validation2_options as vo','vo.validation_id=v2.id');
        $this->db->where('v2.user_role', $role);
		 if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
	}
	
	public function getTelecallerAnswerData($role,$filters = array()) {
        $this->db->select('u.id, u.first_name,u.mobile ,u.last_name,u.user_role,qa.answer,q.question');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_q_result as qa', 't.user_id = qa.user_id');
		$this->db->join('tbl_questionnaire as q', 'qa.qid = q.id');
		$this->db->join('tbl_q_lookup as ql', 'qa.answer = ql.id');
        $this->db->where('u.user_role', $role);
		 if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }

    public function getQuestionLabels($qid) {
        $this->db->select('ql.id, ql.value');
        $this->db->from('tbl_qa_map as qm');
        $this->db->join('tbl_q_lookup as ql', 'qm.aid = ql.id');
        $this->db->where('qm.qid', $qid);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getCallingReport($qid, $role, $aid) {
        $location = $this->session->userdata('user')->location_id;
        if($role == 55 || $role == 18) {
            $this->db->select('qr.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_q_result as qr', 'qr.user_id = tm3.user_id');
            $this->db->where('tm.location', $location);
            $this->db->where('qr.user_role', $role);
            $this->db->where('qr.qid', $qid);
            $this->db->where('qr.answer', $aid);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('qr.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_q_result as qr', 'qr.user_id = tm4.user_id');
            $this->db->where('tm.location', $location);
            $this->db->where('qr.user_role', $role);
            $this->db->where('qr.qid', $qid);
            $this->db->where('qr.answer', $aid);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('qr.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_q_result as qr', 'qr.user_id = cm.user_id');
            $this->db->where('tm.location', $location);
            $this->db->where('qr.user_role', $role);
            $this->db->where('qr.qid', $qid);
            $this->db->where('qr.answer', $aid);
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }
    
    public function getQuestionsByRole($role, $report) {
        $this->db->select('q.id, q.question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.user_role', $role);
        $this->db->where('q.report', $report);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

	public function getQuestionById($qid) {
        $this->db->select('q.id, q.question, q.t_question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.id', $qid);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
}