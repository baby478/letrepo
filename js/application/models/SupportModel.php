<?php
class SupportModel extends CI_Model {
    private $_sdb;
    
    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }
	
	public function getUsersData() {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob,u.mobile, l.value as gender, rl.value as user_role,u.status as active_status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('u.user_role !=', 1);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	public function getUserByPhone($mobile) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob,u.mobile, u.user_role');
        $this->db->from('tbl_users as u');
        $this->db->where('u.mobile =', $mobile);
		return $this->db->get()->row();
        //$result = $this->db->get()->result();
       // return $result;
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
	
	public function getPSByMandal($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village, l2.id as location, l2.level_id');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->where('l1.id', $id);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get()->result();
        return $result;
    }
	
	public function getAllVotersByPS($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,v.mobile,v.photo ,u.first_name,u.last_name , v.age,v.voter_id,v.voter_status');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_users as u', 'v.user_id = u.id','left');
        $this->db->where('v.ps_no', $id);
        $result = $this->db->get()->result();
        return $result;
    }
	
	public function insertFeedbackDetails($data) {
        $insert_data = array(
            'agent_id' => $data['agent_id'],
            'user_id' => $data['user_id'],
            'title' => $data['title'],

            'duration' => $data['callduration'],
            'feeddate' => $data['feedbackdate'],
            'description' => $data['description']
        );

        $id = $this->db->insert('tbl_feedback', $insert_data);
        if($id) {
            return $id;
        }else {
            return false;
        }
    }
	
	public function getAllFeedback($id) {
        $this->db->select('f.agent_id, f.user_id,u.first_name,u.last_name, f.title, f.reason, f.duration, f.feeddate, f.description');
        $this->db->from('tbl_feedback as f');
		$this->db->join('tbl_users as u', 'f.user_id = u.id');
        $this->db->where('agent_id', $id);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	// Add Tickets
	
	public function generateTickets($data) {
        $insert_data = array(
			'userid' => $data['uid'],
            'title' => $data['title'],
            'issue_type' => $data['issuetype'],
            'priority' => $data['priority'],
            'description' => $data['description']
        );

        $id = $this->db->insert('tbl_ticket_generate', $insert_data);
        if($id) {
            return $id;
        }else {
            return false;
        }
    }
	
	public function getAllTickets() {
        $this->db->select('tg.title, tg.issue_type, tg.priority,u.mobile, tg.description,tg.userid ,tg.created_at, tg.status');
        $this->db->from('tbl_ticket_generate as tg');
		$this->db->join('tbl_users as u', 'tg.userid = u.id');
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
}