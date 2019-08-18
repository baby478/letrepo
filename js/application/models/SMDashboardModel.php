<?php
class SMDashboardModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function getServiceByVillage($id, $service) {
        $this->db->select('l1.id, l1.name, count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_1 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit1_options as vo', 'v1.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.service_id', $service);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getServiceDetails($id, $service) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, vo.lead_status as status, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_1 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit1_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    // echo '<pre>'; print_r($res_v); exit;
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            // echo '<pre>'; print_r($ser);exit;
            return $ser;
        }else {
            return $result;
        }
    }
	
	public function registerPartyLeader($data) {
        $prepared_data = $this->preparePartyLeader($data);
        $this->db->trans_begin();
        $this->db->insert('tbl_party_leaderinfo', $prepared_data);
        $insert_id = $this->db->insert_id();
        //address
		
        $address = array(
            'leader_id' => $insert_id,
            'house_no' => $data['hno'],
            'street' => $data['street'],
            'landmark' => $data['landmark'],
            'location' => $data['village'],
            'pincode' => $data['pincode']
        );
		
        $this->db->insert('tbl_partyleader_add', $address);

        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

	public function sanitizeInput(array $data) {
        foreach($data as $k => $dt) {
            $this->db->escape($dt);
            if(empty($data[$k])) {
                $data[$k] = null;
            }
        }
        return $data;    
    }

	public function preparePartyLeader($data) {
        $data = $this->sanitizeInput($data);
		$id=$this->session->userdata('user')->id;
        $leader_data = array(
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'age' => $data['age'],
            'gender' => $data['gender'],
			'mobile' => $data['mobile'],
            'email' => $data['email'],
            'voterid' => $data['voterId'],
            'designation' => $data['designation'],
            'community' => $data['community'],
            'membership_no' => $data['membership'],
			'created_by' => $id
        );
        return $leader_data;
    }

	public function getPartyLeader($id) {
        $this->db->select('x.first_name,x.last_name,x.mobile,x.email,x.designation,x.age,x.community,x.voterid,x.membership_no,x.community');
		$this->db->from('tbl_party_leaderinfo as x');
		$this->db->where('x.created_by', $id);
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getMessageTemplates() {
        $result = $this->db->get('tbl_msg_template');
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getServicename($id) {
        $this->db->where('id', $id);
        $result = $this->db->get('tbl_visits_lookup');
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getStatusByVillage($id, $status) {
        $this->db->select('l1.id, l1.name, count(v2.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_2 as v2', 'v.id = v2.citizen_id');
        // $this->db->join('tbl_visit2_options as vo', 'v2.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        // $this->db->where('vo.status_id', $status);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getStatusDetails($id, $status) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, c.group_id, v2.id as visit_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_2 as v2', 'v.id = v2.citizen_id');
        // $this->db->join('tbl_visit2_options as vo', 'v2.id = vo.visit_id');
        // $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        // $this->db->where('vo.status_id', $status);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id !== 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
                $this->db->select('vl.name, vl.icon');
                $this->db->from('tbl_visit2_options as vo');
                $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
                $this->db->where('vo.visit_id', $v->visit_id);
                $this->db->where('vo.status_id', $status);
                $v->status = $this->db->get()->result();
            }
            return $ser;
        }else {
            return $result;
        }
        
    }

    public function getPublicWelfareByVillage($id) {
        $this->db->select('l1.id, l1.name, count(v3.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_3 as v3', 'v.id = v3.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getPublicWelfare($id, $service) {
        $this->db->select('vl.id, vl.name, v3.id as visit_id, count(v3.id) as services');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_3 as v3', 'v.id = v3.citizen_id');
        $this->db->join('tbl_visits_lookup as vl', 'vl.id = v3.'.$service);
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('vl.id');
        $this->db->group_by('v3.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();    
        }else {
            return false;
        }
    }

    public function getLocationByID($id) {
        $this->db->where('id', $id);
        $result = $this->db->get('tbl_locations');
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getGvtProjectByVillage($id) {
        $this->db->select('l1.id, l1.name, count(v4.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_4 as v4', 'v.id = v4.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getGovtProject($id, $service) {
        $this->db->select('vl.id, vl.name, v4.id as visit_id, count(v4.id) as services');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_4 as v4', 'v.id = v4.citizen_id');
        $this->db->join('tbl_visits_lookup as vl', 'vl.id = v4.'.$service);
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('vl.id');
        $this->db->group_by('v4.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();    
        }else {
            return false;
        }
    }
	
	 public function getModiSchemes($id, $service) {
        $this->db->select('vl.id, vl.name, v4.id as visit_id, count(v4.id) as services');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_21 as v4', 'v.id = v4.citizen_id');
        $this->db->join('tbl_visits_lookup as vl', 'vl.id = v4.'.$service);
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('vl.id');
        $this->db->group_by('v4.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();    
        }else {
            return false;
        }
    }

    public function getPersonalInfoByVillage($id) {
        $this->db->select('l1.id, l1.name, count(v5.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_5 as v5', 'v.id = v5.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getPersonalInfo($id, $service) {
        $this->db->select('vl.id, vl.name, v5.id as visit_id, count(v5.id) as services');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_5 as v5', 'v.id = v5.citizen_id');
        $this->db->join('tbl_visits_lookup as vl', 'vl.id = v5.'.$service);
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('vl.id');
        $this->db->group_by('v5.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();    
        }else {
            return false;
        }
    }

    public function getNeighbourReferenceVillages($id) {
        $this->db->select('l1.id, l1.name, count(vo.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_6 as v6', 'v.id = v6.citizen_id');
        $this->db->join('tbl_visit6_options as vo', 'v6.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getNeighgourReference($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vo.name as ref_name, vo.relationship, vo.mobile, vo.voters, l.name as location, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_6 as v6', 'v.id = v6.citizen_id');
        $this->db->join('tbl_visit6_options as vo', 'v6.id = vo.visit_id');
        $this->db->join('tbl_locations as l', 'vo.location = l.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
	
	//RECRUITMENT
	public function getTotalDivheadBySM($id){
		$this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('t.parent_id', $id);
		$this->db->where('u.user_role', 137);
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
		$this->db->join('tbl_team_mng as t1', 't.user_id = t1.parent_id');
        $this->db->join('tbl_users as u', 't1.user_id = u.id');
		//$this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('t.parent_id', $id);
		$this->db->where('u.user_role', 2);
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }	
    }
    
    public function getMandalsRegist($id){
		/* $this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
		$this->db->where('u.user_role', 137);
		$this->db->group_by('l.id');
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }	 */
		$this->db->select('l.id,l.name ,cl.location_id');
        $this->db->from('tbl_locations as l');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
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
		$this->db->join('tbl_team_ps as ts', 'ts.user_id = u.id');
		$this->db->join('tbl_ps as ps', 'ps.id = ts.ps_id');
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
    
	/*public function getTotalMandalBySM($id){
		$this->db->select('l.id,l.name ,cl.location_id');
        $this->db->from('tbl_locations as l');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $id);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}*/

	public function getTotalTeamLeaderBySM($id) {
        $this->db->select('u.id, u.first_name, u.last_name,ps.ps_no,ps.ps_name,u.gender, u.mobile, u.user_role, l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_locations as l1', 't3.location = l1.id');
		$this->db->join('tbl_locations as l2', 't2.location = l2.id');
		$this->db->join('tbl_team_ps as ts', 'ts.user_id = u.id');
		$this->db->join('tbl_ps as ps', 'ps.id = ts.ps_id');
        $this->db->where('t1.parent_id', $id);
		$this->db->where('u.user_role', 18);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getStateBySM($id) {
        $this->db->select('l3.name as state ,l3.id as stateid');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_users as u', 't1.user_id = u.id');
		$this->db->join('tbl_locations as l1', 't1.location = l1.id');
		$this->db->join('tbl_locations as l2', 'l1.parent_id = l2.id');
		$this->db->join('tbl_locations as l3', 'l2.parent_id = l3.id');
        $this->db->where('t1.user_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	
	public function getTotalCoordinatorBySM($id) {
        $this->db->select('u.id, u.first_name,u.last_name, u.gender,ps.ps_no,ps.ps_name , u.mobile, u.user_role, l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_users as u', 't4.user_id = u.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
		$this->db->join('tbl_team_ps as ts', 'ts.user_id = u.id');
		$this->db->join('tbl_ps as ps', 'ps.id = ts.ps_id');
        $this->db->where('t1.parent_id', $id);
		$this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalTextmsgMandalBySM($id){
		$this->db->select('t.user_id,l.id');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_sms_details as sm', 't.user_id = sm.user_id');
		$this->db->where('l.parent_id',$id);
        $result = $this->db->get();
        return $result->num_rows();
	}
	
	
	public function getTotalTextmsgByRole($id,$role){
		$this->db->select('t.user_id,u.user_role');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_sms_details as sm', 't.user_id = sm.user_id');
		$this->db->where('l.parent_id',$id);
		$this->db->where('u.user_role',$role);
        $result = $this->db->get();
        return $result->num_rows();
	}
	
	public function getTotalCallrecordMandalBySM($id){
		$this->db->select('t.user_id');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_call_details as cal', 't.user_id = cal.user_id');
		$this->db->where('l.parent_id',$id);
        $result = $this->db->get();
        return $result->num_rows();
	}
	
	
	public function getTotalCallrecordByRole($id,$role){
		$this->db->select('t.user_id,u.user_role');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_call_details as cal', 't.user_id = cal.user_id');
		$this->db->where('l.parent_id',$id);
		$this->db->where('u.user_role',$role);
        $result = $this->db->get();
        return $result->num_rows();
	}

	public function getMessageDetails($id) {
        //Senior Manager
		$this->db->select('u.id, u.first_name, u.last_name, sd.mobile, sd.text_message, sd.created_at, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_sms_details as sd', 'sd.user_id = t.user_id');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->order_by('sd.created_at','DESC');
        $result_sm = $this->db->get()->result();
		
		
        // Manager
		$this->db->select('u.id, u.first_name, u.last_name, sd.mobile, sd.text_message, sd.created_at, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
		$this->db->join('tbl_sms_details as sd', 'sd.user_id = t1.user_id');
		$this->db->join('tbl_users as u', 't1.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->where('u.user_role',2);
		$this->db->order_by('sd.created_at','DESC');
        $result_m = $this->db->get()->result();

        // Mobile Team
		$this->db->select('u.id, u.first_name, u.last_name, sd.mobile, sd.text_message, sd.created_at, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
		$this->db->join('tbl_sms_details as sd', 'sd.user_id = t1.user_id');
		$this->db->join('tbl_users as u', 't1.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->where('u.user_role',55);
		$this->db->order_by('sd.created_at','DESC');
        $result_mt = $this->db->get()->result();

        //Coordinator
		$this->db->select('u.id, u.first_name, u.last_name, sd.mobile, sd.text_message, sd.created_at, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
        $this->db->join('tbl_team_mng as t2','t2.parent_id=t1.user_id');
        $this->db->join('tbl_team_mng as t3','t3.parent_id=t2.user_id');
        $this->db->join('tbl_sms_details as sd', 'sd.user_id = t3.user_id');
		$this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->where('u.user_role',3);
		$this->db->order_by('sd.created_at','DESC');
        $result_c = $this->db->get()->result();

        //Observer or Agent
		$this->db->select('u.id, u.first_name, u.last_name, sd.mobile, sd.text_message, sd.created_at, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
        $this->db->join('tbl_ps_member as p', 'p.created_by = t1.user_id');
        $this->db->join('tbl_sms_details as sd', 'sd.user_id = p.user_id');
        $this->db->join('tbl_users as u', 'p.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
        $this->db->where('u.user_role',37);
        $this->db->or_where('u.user_role', 38);
		$this->db->order_by('sd.created_at','DESC');
        $result_pm = $this->db->get()->result();
        

		$result = array_merge((array) $result_sm,(array) $result_m, (array) $result_c, (array) $result_mt, (array) $result_pm);
		
		//var_dump($result);exit;
		if($result) {
            return $result;
        }else {
            return false;
        }
	}

	public function getCallrecordingDetails($id){
		$this->db->select('u.id, u.first_name, u.last_name, cal.mobile,cal.call_duration,cal.created_at,cal.recording_path, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_call_details as cal', 't.user_id = cal.user_id');
		$this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->order_by('cal.created_at','DESC');
        $result_sm = $this->db->get()->result();
		
		//var_dump($result_sm);exit;
        // Manager
		$this->db->select('u.id, u.first_name, u.last_name, cal.mobile,cal.call_duration,cal.created_at,cal.recording_path, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
		$this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
		$this->db->join('tbl_call_details as cal', 'cal.user_id = t1.user_id');
		$this->db->join('tbl_users as u', 't1.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->where('u.user_role',2);
		$this->db->order_by('cal.created_at','DESC');
        $result_m = $this->db->get()->result();

        //Coordinator
		$this->db->select('u.id, u.first_name, u.last_name, cal.mobile,cal.call_duration,cal.created_at,cal.recording_path, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
        $this->db->join('tbl_team_mng as t2','t2.parent_id=t1.user_id');
        $this->db->join('tbl_team_mng as t3','t3.parent_id=t2.user_id');
        $this->db->join('tbl_call_details as cal', 'cal.user_id = t3.user_id');
		$this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->where('u.user_role',3);
		$this->db->order_by('cal.created_at','DESC');
        $result_c = $this->db->get()->result();

		$result = array_merge((array) $result_sm,(array) $result_m, (array) $result_c);
		
		//var_dump($result);exit;
		if($result) {
            return $result;
        }else {
            return false;
        }
		
	}
	
	public function getVoiceMessageDetails($id){
		// $this->db->select('u.id, u.first_name, u.last_name, cal.duration,cal.created_at,cal.voice_message, lu.value as user_role');
		// $this->db->from('tbl_team_mng as t');
		// $this->db->join('tbl_voice_message as cal', 't.user_id = cal.user_id');
		// $this->db->join('tbl_users as u', 't.user_id = u.id');
		// $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		// $this->db->where('t.user_id',$id);
		// $this->db->order_by('cal.created_at','DESC');
		
        // $result_sm = $this->db->get()->result();
		
		// //var_dump($result_sm);exit;
        // // Manager
		// $this->db->select('u.id, u.first_name, u.last_name,cal.duration,cal.created_at,cal.voice_message, lu.value as user_role');
		// $this->db->from('tbl_team_mng as t');
		// $this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
		// $this->db->join('tbl_voice_message as cal', 'cal.user_id = t1.user_id');
		// $this->db->join('tbl_users as u', 't1.user_id = u.id');
		// $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		// $this->db->where('t.user_id',$id);
		// $this->db->where('u.user_role',2);
		// $this->db->order_by('cal.created_at','DESC');
        // $result_m = $this->db->get()->result();

        // //Coordinator
		$this->db->select('u.id, u.first_name, u.last_name,v.firstname,v.lastname,cal.duration,cal.created_at,cal.voice_message, lu.value as user_role');
		$this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t1','t1.parent_id=t.user_id');
        $this->db->join('tbl_team_mng as t2','t2.parent_id=t1.user_id');
        $this->db->join('tbl_team_mng as t3','t3.parent_id=t2.user_id');
		$this->db->join('tbl_voice_message as cal', 'cal.user_id = t3.user_id');
		$this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_voters as v', 'cal.receiver_id = v.id');
		// $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
		$this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
		$this->db->where('t.user_id',$id);
		$this->db->where('u.user_role',3);
		$this->db->order_by('cal.created_at','DESC');
        $result = $this->db->get()->result();
        
        
		// $result = array_merge((array) $result_sm, (array) $result_m, (array) $result_c);
		
		//var_dump($result);exit;
		if($result) {
            return $result;
        }else {
            return false;
        }
		
	}
	
	
	
	public function getTotalVolunteerBySM($id) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_voters as v', 't4.user_id = v.user_id');
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

    public function getTotalTelecallerBySM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role, p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_ps as tp', 'tm.user_id = tp.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
      //  $this->db->where('tm.parent_id', $id);
		$this->db->where('u.user_role', 138);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function getOutstationDistrictBySM($locationid) {
        $this->db->select('l4.id, l4.name, count(l4.id) as nos');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = t.user_id');
	    $this->db->join('tbl_citizen_outstation as co', 'c.citizen_id = co.citizen_id');
		$this->db->join('tbl_locations as l2', 'co.location = l2.id');
		$this->db->join('tbl_locations as l3', 'l3.id = l2.parent_id');
		$this->db->join('tbl_locations as l4', 'l4.id = l3.parent_id');
        $this->db->where('cl.parent_id', $locationid);
		$this->db->group_by('l4.name');
		$this->db->group_by('l4.id');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    } 
	
	public function getMandalByDistrict($locationid,$id) {
        $this->db->select('l5.id, l5.name, count(l5.id) as nos,l4.id as disd');
		$this->db->from('tbl_const_location as cl');
		$this->db->join('tbl_locations as l5', 'l5.id = cl.location_id');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = t.user_id');
	    $this->db->join('tbl_citizen_outstation as co', 'c.citizen_id = co.citizen_id');
		$this->db->join('tbl_locations as l2', 'co.location = l2.id');
		$this->db->join('tbl_locations as l3', 'l3.id = l2.parent_id');
		$this->db->join('tbl_locations as l4', 'l4.id = l3.parent_id');
        $this->db->where('cl.parent_id', $locationid);
		$this->db->where('l4.id', $id);
		$this->db->group_by('l5.name');
		$this->db->group_by('l5.id');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getOutstationVillages($locationid,$id,$mid) {
        $this->db->select(' l.id, l.name, count(l.id) as nos,cl.location_id as mlocation,l4.id as disid');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = t.user_id');
	    $this->db->join('tbl_citizen_outstation as co', 'c.citizen_id = co.citizen_id');
		$this->db->join('tbl_locations as l2', 'co.location = l2.id');
		$this->db->join('tbl_locations as l3', 'l3.id = l2.parent_id');
		$this->db->join('tbl_locations as l4', 'l4.id = l3.parent_id');
        $this->db->where('cl.parent_id', $locationid);
		$this->db->where('l4.id', $id);
		$this->db->where('cl.location_id', $mid);
		$this->db->group_by('l.name');
		$this->db->group_by('l.id');

        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getOutstationVillageTotal($locationid,$vid,$mid,$disid) {
        $this->db->select('v.firstname, v.lastname, u.first_name ,u.last_name,co.mobile , l2.name as os_village');
		$this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'l.parent_id = cl.location_id');
		$this->db->join('tbl_team_mng as t', 'l.id = t.location');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = t.user_id');
	    $this->db->join('tbl_citizen_outstation as co', 'c.citizen_id = co.citizen_id');
		$this->db->join('tbl_users as u', 'c.user_id = u.id');
		$this->db->join('tbl_voters as v', 'v.id = c.citizen_id');
		$this->db->join('tbl_locations as l2', 'co.location = l2.id');
		$this->db->join('tbl_locations as l3', 'l3.id = l2.parent_id');
		$this->db->join('tbl_locations as l4', 'l4.id = l3.parent_id');
        $this->db->where('cl.parent_id', $locationid);
		$this->db->where('l4.id', $disid);
		$this->db->where('cl.location_id', $mid);
		$this->db->where('l.id', $vid);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	
	public function getVillageByMandal($locationid,$id) {
        $this->db->select('l.name as village ,l.id as villageid');
		$this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_users as u', 't1.user_id = u.id');
        $this->db->join('tbl_locations as l', 't1.location = l.id');
        $this->db->where('l.parent_id', $locationid);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	//MOBILE TEAM
	Public function getMobileTeamMessage($id,$obtype){
		$this->db->select('ob.msg_type,ob.ob_type,ob.user_id,obt.ob_id,obt.message,ob.created_at');
		$this->db->from('tbl_mt_observation as ob');
		$this->db->join('tbl_mt_observation_text as obt', 'obt.ob_id  = ob.id');
        $this->db->where('ob.user_id', $id);
        $this->db->where('ob.ob_type', $obtype);
        $this->db->order_by('ob.created_at','DESC');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	Public function getMobileTeamVoiceCall($id,$obtype){
		$this->db->select('ob.msg_type,ob.ob_type,ob.user_id,obv.ob_id,obv.duration,obv.ob_report,ob.created_at');
		$this->db->from('tbl_mt_observation as ob');
		$this->db->join('tbl_mt_observation_voice as obv', 'obv.ob_id  = ob.id');
        $this->db->where('ob.user_id', $id);
        $this->db->where('ob.ob_type', $obtype);
        $this->db->order_by('ob.created_at','DESC');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
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
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't4.user_id = cm.user_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        $this->db->where('t1.parent_id', $id);
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
		$this->db->where('t1.location', $id);
        //$this->db->where('t1.user_id', $id);
		//$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getLiveTotalRegistration($id) {
        $this->db->select('cm.*');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't4.user_id = cm.user_id');
	    $this->db->where('t1.location', $id);
		$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getLiveTotalRegistrationMandal($id) {
        $this->db->select('cm.*');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't4.user_id = cm.user_id');
	    $this->db->where('t2.location', $id);
		$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalAttendant($id,$filters = array()) {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't3.user_id = cm.user_id');
		$this->db->join('tbl_digital_booth as dg', 'cm.citizen_id = dg.citizen_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        $this->db->where('t1.parent_id', $id);
	    //$this->db->where('t1.location', $id);
		$this->db->where('cm.user_role', 17);
		$this->db->where('dg.attend', 53);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
	public function getLiveTotalAttendant($id,$filters = array()) {
        $this->db->select('cm.*');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't4.user_id = cm.user_id');
		$this->db->join('tbl_digital_booth as dg', 'cm.citizen_id = dg.citizen_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
	    $this->db->where('t1.location', $id);
		$this->db->where('cm.user_role', 17);
		$this->db->where('dg.attend', 53);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
	public function getByMandalLiveTotalAttendant($id,$filters = array()) {
        $this->db->select('cm.*');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_users as u ', 't1.user_id = u.id');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't3.user_id = cm.user_id');
		$this->db->join('tbl_digital_booth as dg', 'cm.citizen_id = dg.citizen_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
	    $this->db->where('t1.location', $id);
		$this->db->where('cm.user_role', 17);
		$this->db->where('dg.attend', 53);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
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

    public function getPollingStaionsByDivision($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village, l2.level_id, p.village_id');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->where('l1.id', $id);
        $this->db->group_by('p.id');
        $result = $this->db->get()->result();
        return $result;
    }
	
	public function getLiveTotalRegistrationVillage($id) {
        $this->db->select('cm.*');
        $this->db->from( 'tbl_voters as v');
		$this->db->join('tbl_citizen_mng as cm', 'v.id = cm.citizen_id');
		//$this->db->join('tbl_digital_booth as dg', 'cm.citizen_id = dg.citizen_id');
	    $this->db->where('v.ps_no', $id);
		$this->db->where('cm.user_role', 17);
         $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	public function getLiveTotalAttendantVillage($id,$filters = array()) {
         $this->db->select('cm.*');
        $this->db->from( 'tbl_voters as v');
		$this->db->join('tbl_citizen_mng as cm', 'v.id = cm.citizen_id');
		$this->db->join('tbl_digital_booth as dg', 'cm.citizen_id = dg.citizen_id');
	    $this->db->where('v.ps_no', $id);
		$this->db->where('cm.user_role', 17);
        $this->db->where('dg.attend', 53);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }
	
	public function getLikes($id){
		$this->db->select('sl.id as likes');
        $this->db->from('tbl_sm_likes as sm');
        $this->db->join('tbl_smart_media as sl', 'sm.post_id = sl.id');
		$this->db->where('sl.id', $id);
		 $result = $this->db->get();
        return $result->num_rows();
       
	}
	/* Changes For Amberpet */
	public function getColoniesByDivision($locationid) {
        $this->db->select('l.name as colonies ,l.id as coloniesid');
		$this->db->from('tbl_locations as l');
        $this->db->where('l.parent_id', $locationid);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	/*Visit 21 */
	public function getModiSchemesLeadByDivision($id) {
        $this->db->select('l1.id, l1.name');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_21 as v21', 'v.id = v21.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	/* End Visit 21 */
	
	/* Visit 22 */
    public function getHealthLeadsByDivision($id) {
        $this->db->select('l1.id, l1.name, count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_22 as v22', 'v.id = v22.citizen_id');
        $this->db->join('tbl_visit22_options as vo', 'v22.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        $this->db->group_by('l1.id');
        // $this->db->group_by('vo.option_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function gethealthserviceDetails($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, vo.lead_status as status, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_22 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit22_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
	/*End Visit 22 */
	
	/** Visit 23 */
	public function getJobNeedsLeadsByDivision($id) {
        $this->db->select('l1.id, l1.name, count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_23 as v23', 'v.id = v23.citizen_id');
        $this->db->join('tbl_visit23_options as vo', 'v23.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        $this->db->group_by('l1.id');
        // $this->db->group_by('vo.option_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getJobneedsserviceDetails($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, vo.lead_status as status, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_23 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit23_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
	/** End Visit 23 */
	
	/** Visit 24 */
	public function getCertificateLeadsByDivision($id) {
        $this->db->select('l1.id, l1.name, count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_24 as v24', 'v.id = v24.citizen_id');
        $this->db->join('tbl_visit24_options as vo', 'v24.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        $this->db->group_by('l1.id');
        // $this->db->group_by('vo.option_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getCertificateserviceDetails($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, vo.lead_status as status, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_24 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit24_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
	/** End Visit 24 */
	
	/* Visit 25 */
    public function getIdCardLeadsByDivision($id) {
        $this->db->select('l1.id, l1.name, count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_25 as v25', 'v.id = v25.citizen_id');
        $this->db->join('tbl_visit25_options as vo', 'v25.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        $this->db->group_by('l1.id');
        // $this->db->group_by('vo.option_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getidcardserviceDetails($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, vo.lead_status as status, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_25 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit25_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('vo.option_id != ', 24, FALSE);
        $this->db->where('vo.option_id != ', 25, FALSE);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
    /*End Visit 25 */
	
	/* Other Request */
	public function getOtherRequestLeadsByDivision($id) {
        $this->db->select('l1.id, l1.name');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_service_request as sv', 'v.id = sv.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getotherrequestserviceDetails($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, sv.service_name as name, sv.visit_id as status, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_service_request as sv', 'v.id = sv.citizen_id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id !== 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
    /* End Other Request */
    
    public function getBrandingImg() {
        $this->db->select('id, brand_img');
		$this->db->from('tbl_branding');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	/* New Visits */
	/* Visit 27 */
	public function getGovSchemesByVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id,count(v27.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_27 as v27', 'v.id = v27.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getGovSchemesByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_27 as v27', 'v.id = v27.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

	/*Visit 28 */
	public function getGovtProjByVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, count(v28.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_28 as v28', 'v.id = v28.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getGovtProjByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_28 as v28', 'v.id = v28.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

	/*Visit 29 */
	public function getBangaruTelanganaVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, count(v29.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_29 as v29', 'v.id = v29.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getBangaruTelanganaByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_29 as v29', 'v.id = v29.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

	/*Visit 30 */
	public function getGovtAchievVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, count(v30.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_30 as v30', 'v.id = v30.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getGovtAchievByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_30 as v30', 'v.id = v30.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

	/*Visit 31 */
	public function getSchemeBanifVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id,count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_31 as v31', 'v.id = v31.citizen_id');
		$this->db->join('tbl_visit31_options as vo', 'v31.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	/*Visit 32 */
	public function getPensionBanifVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id,count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_32 as v32', 'v.id = v32.citizen_id');
		$this->db->join('tbl_visit32_options as vo', 'v32.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getVisitsDetails($id){
		$this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
		$result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
		
	}
	
	public function getGovtSchemesDetails($id){
        $this->db->select('v.id, v.firstname, v.lastname,t.location, v.gender, v.photo, c.group_id, v27.status,v27.created_at');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_27 as v27', 'v27.citizen_id = v.id', 'left');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
		// echo '<pre>'; print_r($result->result()); exit;
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
	}
	
	public function getGovtProjectDetails($id){
		$this->db->select('v.id, v.firstname, v.lastname,t.location, v.gender, v.photo, c.group_id, v28.status,v28.created_at');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_28 as v28', 'v28.citizen_id = v.id', 'left');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
		
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
	}
	
	public function getBangaruTelanganaDetails($id){
		$this->db->select('v.id, v.firstname, v.lastname,t.location, v.gender, v.photo, c.group_id, v29.status,v29.created_at');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_29 as v29', 'v29.citizen_id = v.id', 'left');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
		
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
	}
	
	public function getGovtAchievDetails($id){
		$this->db->select('v.id, v.firstname, v.lastname,t.location, v.gender, v.photo, c.group_id, v30.status,v30.created_at');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_30 as v30', 'v30.citizen_id = v.id', 'left');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
		
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
	}
	
	public function getGovtSchemesBenificiaryDetails($id) {
		
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, v1.status, v1.created_at, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_31 as v1', 'v1.citizen_id = c.citizen_id');
        $this->db->join('tbl_visit31_options as vo', 'vo.visit_id = v1.id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('c.user_role', 17);
        $this->db->order_by('v1.created_at', 'desc');
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
	}
	
	public function getGovtPensionBenificiaryDetails($id) {
		$this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, v1.status,v1.created_at, c.group_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_32 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit32_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('t.location', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
    
    /*Visit 33 */
	public function getGovtFailureVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, count(v33.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_33 as v33', 'v.id = v33.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getGovtFailureByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_33 as v33', 'v.id = v33.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getFailureDetails($id){
		$this->db->select('v.id, v.firstname, v.lastname,t.location, v.gender, v.photo, c.group_id, v33.status,v33.created_at');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_33 as v33', 'v33.citizen_id = v.id', 'left');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
		
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
    
    /*Visit 34 */
	public function getGovtManifestoVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, count(v34.id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_34 as v34', 'v.id = v34.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getGovtManifestoByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_34 as v34', 'v.id = v34.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
	
	public function getManifestoDetails($id){
		$this->db->select('v.id, v.firstname, v.lastname,t.location, v.gender, v.photo, c.group_id, v34.status,v34.created_at');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
		$this->db->join('tbl_citizen_mng as c', 'c.user_id = u.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_visit_34 as v34', 'v34.citizen_id = v.id', 'left');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
		
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }
    
    /* Visit 35 */
    public function getServiceVisitVillage($id) {
        $this->db->select('l1.id, l1.name, l1.level_id,count(vo.option_id) as leads');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_35 as v35', 'v.id = v35.citizen_id');
		$this->db->join('tbl_visit35_options as vo', 'v35.id = vo.visit_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /* Modified village list by ps */
    public function getServiceVisitByPS($id) {
        $this->db->select('l1.id, l1.name, l1.level_id, p.id as pid, p.ps_no, p.ps_name, count(p.id) as psno');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_team_mng as t', 't.location = l1.id');
        $this->db->join('tbl_team_ps as tp', 'tp.user_id = t.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_35 as v35', 'v.id = v35.citizen_id');
        $this->db->where('l1.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->group_by('l1.id');
        $this->db->group_by('p.id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
    public function getPersonalServiceDetails($id) {
		$this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, vl.name, v1.status, vo.status_id as service_status, v1.created_at, c.group_id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as t', 'tp.user_id = t.user_id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_voters as v', 't.user_id = v.user_id');
        $this->db->join('tbl_visit_35 as v1', 'v.id = v1.citizen_id');
        $this->db->join('tbl_visit35_options as vo', 'v1.id = vo.visit_id');
        $this->db->join('tbl_visits_lookup as vl', 'vo.option_id = vl.id');
        $this->db->join('tbl_citizen_mng as c', 'v.id = c.citizen_id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 3);
		$this->db->where('c.user_role', 17);
        //$this->db->where('vo.service_id', $service);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $ser = $result;
            foreach($ser->result() as $v) {
                $this->db->select("u.id as coord_id, concat(u.first_name, ' ', u.last_name) as coord_name, u.photo as coord_photo, u.gender as coord_gender");
                $this->db->from('tbl_citizen_mng as c');
                $this->db->join('tbl_users as u', 'c.user_id = u.id');
                $this->db->where('c.citizen_id', $v->id);
                $res = $this->db->get()->row();
                $v->coord_id = $res->coord_id;
                $v->coord_name = $res->coord_name;
                $v->coord_photo = $res->coord_photo;
                $v->coord_gender = $res->coord_gender;
                if($v->group_id != 39) {
                    $this->db->select("c2.id as vlt_id, concat(c2.firstname, ' ', c2.lastname) as vlt_name, c2.photo as vlt_photo, c2.gender as vlt_gender");
                    $this->db->from('tbl_citizen_mng as c');
                    $this->db->join('tbl_voters as c2', 'c.parent_id = c2.id');
                    $this->db->where('c.citizen_id', $v->id);
                    $res_v = $this->db->get()->row();
                    $v->vlt_id = $res_v->vlt_id;
                    $v->vlt_name = $res_v->vlt_name;
                    $v->vlt_photo = $res_v->vlt_photo;
                    $v->vlt_gender = $res_v->vlt_gender;
                }
            }
            return $ser;
        }else {
            return $result;
        }
    }

    public function getUserByMandal($lid,$role){
		$this->db->select('u.id, u.first_name, u.last_name, u.photo , u.gender, u.mobile, u.user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
		$this->db->where('u.user_role', $role);
        $this->db->where('u.status', 1);
        $this->db->where('l1.id', $lid);
		$result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
		
	}
}