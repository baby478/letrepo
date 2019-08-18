<?php
class GeneralManagerModel extends CI_Model {
	 // Declare variables
        private $_limit;
        private $_pageNumber;
        private $_offset;
        // setter getter function
	
    public function __construct() {
        parent::__construct();
    }
	public function teamManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo');
        $this->db->from('tbl_const_location as cl');
		$this->db->join('tbl_team_mng as t', 't.location = cl.location_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('cl.location_id', $id);
        $this->db->where('u.user_role', 44);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	public function DivisionHeadManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.user_role, l.name, u.photo');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->where('t.parent_id', $id);
		$this->db->where('u.user_role', 137);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	public function seniorManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.user_role, l.name, u.photo');
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

	public function userProfile($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.f_name, l1.name as location, u.photo, u.dob, u.caste, u.mobile, u.gender, u.email, t.date_from');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 't.user_id = u.id');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->where('u.id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
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
	
	public function votersBySeniorManager($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
        $this->db->where('tm.parent_id', $id);
        $result = $this->db->get();
        return $result->num_rows();
    }
	
	public function getTotalVotersBySManager($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		$this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t4.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->where('t1.parent_id', $id);
		//$this->db->where('ct.user_role', 17);
		 $result = $this->db->get();
        return $result->num_rows();
	} 
	
	public function votersByManager($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
        $this->db->where('tm.parent_id', $id);
        $result = $this->db->get();
        return $result->num_rows();
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
	
	public function getTotalVotersByManager($id) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		$this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t4.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->where('t1.user_id', $id);
		//$this->db->where('ct.user_role', 17);
		 $result = $this->db->get();
        return $result->num_rows();
	}
	 
	 
	public function getPositiveNegVotersByManager($id, $status) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		$this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t4.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->where('v.voter_status', $status);
		$this->db->where('t1.user_id', $id);
		//$this->db->where('ct.user_role', 17);
		 $result = $this->db->get();
        return $result->num_rows();
	}
	 
	public function getPositiveNegVotersBySManager($id, $status) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		$this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t4.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->where('v.voter_status', $status);
		$this->db->where('t1.parent_id', $id);
		//$this->db->where('ct.user_role', 17);
		 $result = $this->db->get();
        return $result->num_rows();
	}
	 
	public function getTlByManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.photo,  t.status, t.location');
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
	
	public function getLikes($id){
		$this->db->select('sl.id as likes');
        $this->db->from('tbl_sm_likes as sm');
        $this->db->join('tbl_smart_media as sl', 'sm.post_id = sl.id');
		$this->db->where('sl.id', $id);
		 $result = $this->db->get();
        return $result->num_rows();
       
	}
	
	public function getMessageDetails(){
		$this->db->select('t.user_id,u.first_name,u.last_name,v.firstname,v.lastname,sm.mobile,sm.text_message,sm.created_at');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_sms_details as sm', 't.user_id = sm.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'sm.receiver_id = ct.citizen_id');
		$this->db->join('tbl_voters as v', 'v.id = ct.citizen_id');
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getCallrecordingDetails(){
		$this->db->select('t.user_id,u.first_name,u.last_name,v.firstname,v.lastname,cal.mobile,cal.call_duration,cal.created_at,cal.recording_path');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_call_details as cal', 't.user_id = cal.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'cal.receiver_id = ct.citizen_id');
		$this->db->join('tbl_voters as v', 'v.id = ct.citizen_id');
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	 
	 public function teamProfile($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.name, u.photo');
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
	
	 public function getTotalVote($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('v.user_id', $id);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
    }
	
	public function votersByStatusCr($id, $status) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('v.user_id', $id);
        $this->db->where('v.voter_status', $status);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
        
    }
	
	public function getContestants(){
	    $this->db->select('c.party_id,p.id,c.contestants_name, c.contestant_photo, p.party_name, p.party_icon,p.party_slug,c.total_voters');
        $this->db->from('tbl_cantestants as c');
		$this->db->join('tbl_party as p', 'c.party_id = p.id');
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
        $this->db->where('cm.user_role', 17);
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
	
	public function getMembersByVolunteer($id) {
        $this->db->select('v.id, v.firstname, v.lastname ,v.photo ,v.mobile, v.voter_id, v.gender,v.dob , v.age ,l.name,lu.value as voter_status ,lr.value as relationship ,v.created_at');
        $this->db->from('tbl_voters as v');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_citizen_address as cadd', 'ct.citizen_id = cadd.citizen_id');
        $this->db->join('tbl_locations as l', 'cadd.location = l.id');
		$this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
		$this->db->join('tbl_lookup as lr', 'ct.relationship = lr.id');
        $this->db->where('ct.parent_id', $id);
		$this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function mandalBySeniorManager($id){
		$this->db->select('u.id, l.name ,cl.location_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('t.parent_id', $id);
        $result = $this->db->get()->result();
		//echo $this->db->last_query();exit;
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function mandalByGeneralManager($id){
		$this->db->select('u.id, l.name ');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        //$this->db->where('t.parent_id', $id);
		$result = $this->db->get()->result();
		//echo $this->db->last_query();exit;
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	
	public function getCoordinatorsByPS($id) {
        $this->db->select('u.first_name, u.last_name, u.id, u.photo, u.gender');
        $this->db->from('tbl_team_mng as m');
        $this->db->join('tbl_users as u', 'm.user_id = u.id');
        $this->db->where('m.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('m.status', 1);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getMyGroupMembersByVolunteer($id) {
        $this->db->select('v.id, v.firstname, v.lastname ,v.photo ,v.mobile, v.voter_id, v.gender,v.dob , v.age ,l.name,lu.value as voter_status ,lr.value as relationship ,la.value as attend,v.created_at');
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
	
	public function getPollingStationCount($id, $filters = array()) {
        $this->db->select('v.id, v.firstname, v.lastname,v.ps_no');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_ps as ps', 'ps.id = v.ps_no');
		 $this->db->where('ps.id', $id);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
	public function getCoordinatorsByVillageId($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.f_name,u.photo,lc.id , lc.name , tm.user_id ,u.user_role');
		$this->db->from('tbl_users as u');
		$this->db->join('tbl_team_mng as tm', 'tm.user_id = u.id');
		$this->db->join('tbl_locations as lc', 'lc.id = tm.location');
		$this->db->join('tbl_ps as p', 'p.village_id = lc.id');
        $this->db->where('p.id', $id);
		$this->db->where('u.user_role', 3);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTeamleaderByVillageId($id) {
        $this->db->select('u.id, u.first_name, u.last_name,u.dob,u.mobile,u.email,u.f_name,u.photo,lc.id , lc.name , tm.user_id ,u.user_role');
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
	
	//RECRUITMENT
	
	public function getTotalDivheadBySM($id){
		$this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
		$this->db->where('u.user_role', 137);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalSManagerByGM($id){
		$this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('u.user_role', 44);
        $this->db->where('cl.parent_id', $id);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getTotalMandalBySM($id){
		$this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
		$this->db->where('u.user_role', 2);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
    public function getBoothObserverCount($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, u.user_role,  l1.name as Mandal,');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
        // $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
        $this->db->join('tbl_team_mng as tm', 'tm.location = l1.id');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('u.user_role', 55);
        $this->db->where('cl.parent_id', $id);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result;
    }
    
    public function getTotalBoothObserversBySM($id){
		$this->db->select('u.id, u.first_name, u.last_name,ps.ps_no,ps.ps_name, u.gender, u.mobile, u.user_role,  l1.name as Mandal,');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
        $this->db->join('tbl_team_mng as tm', 'tm.location = l1.id');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
		$this->db->join('tbl_team_ps as ts', 'ts.user_id = u.id');
		$this->db->join('tbl_ps as ps', 'ps.id = ts.ps_id');
        $this->db->where('u.user_role', 55);
        $this->db->where('cl.parent_id', $id);
        $this->db->where('u.status', 1);
		$this->db->order_by('ps.ps_no');
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	} 
    
	
	public function getTotalTeamLeaderBySM($id) {
       
		
		$this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, u.user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $this->db->where('cl.parent_id', $id);
        $result = $this->db->get();
        return $result;
    }
	
	public function getTotalVolunteerBySM($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_voters as v', 't3.user_id = v.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        $this->db->where('t1.parent_id', $id);
		$this->db->where('ct.user_role', 46);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function getTotalCoordinatorBySM($id) {
      
		$this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, u.user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('cl.parent_id', $id);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalMandalBySMa($id){
		$this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
		$this->db->where('u.user_role', 2);
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getMandalsByConstituences($id){
		$this->db->select('l.id,l.name ,cl.location_id');
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
	
	 public function getPollingStationByMandals($id) {
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
	
	public function getBoothPresidentByPss($id) {
        $this->db->select('u.first_name,u.last_name,u.id');
        $this->db->from('tbl_users as u'); 
        $this->db->join('tbl_team_ps as tp', 'tp.user_id=u.id');
        $this->db->where('u.user_role', 18);
		$this->db->where('tp.ps_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }	
    }
	
	public function getSheetPresidentByBPs($id) {
        $this->db->select('u.first_name,u.last_name,u.id');
        $this->db->from('tbl_users as u'); 
		$this->db->join('tbl_team_mng as t', 't.user_id=u.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id=u.id');
		$this->db->where('u.user_role', 3);
        $this->db->where('t.parent_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }	
    }
	
	//REGISTRATION
	
	public function getTotalRegistration($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't3.user_id = cm.user_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
		$this->db->join('tbl_const_location as cl', 'l2.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
		//$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	
	public function getTotalRegistrationMandal($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't3.user_id = cm.user_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        $this->db->where('t1.user_id', $id);
		$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	//Analytics
	
	public function getVotersByManager($id, $filters = array()) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, l1.name as mandal, l2.name as village');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
		$this->db->join('tbl_team_mng as t4', 't3.user_id = t4.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t4.user_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id');
        $this->db->join('tbl_locations as l2', 't2.location = l2.id');
        $this->db->join('tbl_const_location as cl', 'l2.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
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
	
	public function getOutstationByCoodinator($id)
	{
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
	
	public function getNeibourhoodByCoodinator($id)
	{
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
	
	public function getVisitTwoCount($id ,$visit_value)
	{
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
	
	public function getCoordinatorsBySeniorManager($id) {
        $this->db->select('u.id, u.first_name, u.user_role');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_locations as l', 't1.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
       // $this->db->where('t1.parent_id', $id);
	    $this->db->where('cl.parent_id', $id);
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
		$this->db->where('cm.user_role', 17);
		$this->db->where('cm.relationship', $relation);
		$result = $this->db->get();
		if($result->num_rows() > 0) {
			return $result->num_rows();
		}else {
			return 0;
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
	 public function getCoordPerformanceBySM($id) {
        $this->db->select('t4.user_id, count(c.user_id) as registered');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t.user_id');
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
        $this->db->join('tbl_citizen_mng as c', 'c.user_id = t4.user_id');
		 $this->db->join('tbl_locations as l', 't2.location = l.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('c.user_role', 17);
        $this->db->group_by('c.user_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function allocateGroupTaskByGeneralManager($group) {
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
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group ,');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
        $this->db->where('receiver_id', $id);
        $this->db->where('task_group', 64);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	
	 public function getAllConstituence($id){
		$this->db->select ('l.name,l.id,cl.location_id, cl.parent_id');
		$this->db->from('tbl_const_location as cl');
		$this->db->join('tbl_locations as l', 'cl.location_id=l.id');
		$this->db->where('cl.parent_id', $id);
		$result = $this->db->get()->result();
			if($result) {
				return $result;
			}else {
				return false;
			}
	} 
	
	public function mandalByConstituency($id){
		$this->db->select ('l.name,l.id,cl.location_id, cl.parent_id');
		$this->db->from('tbl_const_location as cl');
		$this->db->join('tbl_locations as l', 'cl.location_id=l.id');
		$this->db->where('cl.parent_id', $id);
		$result = $this->db->get()->result();
			if($result) {
				return $result;
			}else {
				return false;
			}
	}
	
	public function getPollingStaionsByDivision($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village, l2.level_id, p.village_id');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->group_by('p.id');
        $result = $this->db->get()->result();
        return $result;
    }
	
	public function getConstituence($id){
		$this->db->select('l.id,l.name ,cl.location_id');
        $this->db->from('tbl_locations as l');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
		 $this->db->where('cl.level_id', 58);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getAssignRole() {
        $this->db->select('l.id, l.value');
        $this->db->from('tbl_ac_role as r');
        $this->db->join('tbl_acl as ac', 'r.acl_id = ac.id');
        $this->db->join('tbl_lookup as l', 'ac.value = l.id');
        $this->db->where('ac.gen_id', 1);
        $this->db->where('r.user_role', 1);
        $result = $this->db->get()->result();
		
        return $result;
    }
}