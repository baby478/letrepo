<?php
class ApiModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function getAllStates() {
        $this->db->select('id, name');
        $this->db->where('level_id', 7);
        $this->db->order_by('name');
        $result = $this->db->get('tbl_locations')->result();
        return $result;
        
    }

    public function getAllConstituencyByState($id) {
        $stateExists = $this->stateExists($id);
        if($stateExists == true) {
            $this->db->select('l1.id, l1.name');
            $this->db->from('tbl_locations l1');
            $this->db->where('l1.parent_id IN (select l2.id from tbl_locations l2 where l2.parent_id = '. $id. ')', NULL, FALSE);
            $this->db->where('l1.level_id', 11);
            $this->db->order_by('l1.name');
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function stateExists($id) {
        $this->db->select('id, name');
        $this->db->where('id', $id);
        $this->db->where('level_id', 7);
        $result = $this->db->get('tbl_locations')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function districtExists($id) {
        $this->db->select('id, name');
        $this->db->where('id', $id);
        $this->db->where('level_id', 8);
        $result = $this->db->get('tbl_locations')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function mandalExists($id) {
        $this->db->select('id, name');
        $this->db->where('id', $id);
        // $this->db->where('level_id', 9);
        $result = $this->db->get('tbl_locations')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function getConstituencyByDetails() {
        $this->db->select("tb1.id, tb1.name as 'Constituency', tb2.name as 'District', tb3.id as 'Sid', tb3.name as 'State'");
        $this->db->from('locations tb1');
        $this->db->join('locations tb2', 'tb1.parent_id = tb2.id');
        $this->db->join('locations tb3', 'tb3.id = tb2.parent_id');
        $this->db->where('tb1.level_id = 5');
        $this->db->order_by('tb1.name');
        return $this->db->get();
    }

    public function constituencyExists($id) {
        $this->db->select('id, name');
        $this->db->where('id', $id);
        $this->db->where('level_id', 5);
        $result = $this->db->get('locations')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function stateConstituencyExists($sid, $id) {
        $st = $this->stateExists($sid);
        $const = $this->constituencyExists($id);

        if($st == true && $const == true) {
            return true;
        }else {
            return false;
        }
    }

    public function getAllDistrictByState($id) {
        $stateExists = $this->stateExists($id);
        if($stateExists == true) {
            $this->db->select('id, name');
            $this->db->from('tbl_locations');
            $this->db->where('parent_id', $id);
            $this->db->where('level_id', 8);
            $this->db->order_by('name');
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function getAllMandalByDistrict($id) {
        $districtExists = $this->districtExists($id);
        if($districtExists == true) {
            $this->db->select('id, name');
            $this->db->from('tbl_locations');
            $this->db->where('parent_id', $id);
            $this->db->where('level_id', 9);
            $this->db->order_by('name');
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function getAllVillageByMandal($id) {
        $mandalExists = $this->mandalExists($id);
        if($mandalExists == true) {
            $this->db->select('id, name, level_id');
            $this->db->from('tbl_locations');
            $this->db->where('parent_id', $id);
            // $this->db->where('level_id', 10);
            $this->db->order_by('name');
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function mandalAllocExists($id) {
        $this->db->select('user_id, location_id');
        $this->db->where('location_id', $id);
        $this->db->where('user_alloc_role', 5);
        $result = $this->db->get('area_allocation')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }
    public function getAllMandalInchargeByMandal($id) {
        $mandalAllocExists = $this->mandalAllocExists($id);
        if($mandalAllocExists == true) {
            $this->db->select('al.user_id, pu.name');
            $this->db->from('area_allocation al');
            $this->db->join('party_users pu', 'pu.id = al.user_id');
            $this->db->where('al.location_id', $id);
            $this->db->where('al.user_alloc_role', 5);
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function villageExists($id) {
        $this->db->select('id, name');
        $this->db->where('id', $id);
        $this->db->where('level_id', 6);
        $result = $this->db->get('locations')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        } 
    }
    
    public function getAllBManagerByVillage($id) {
        $villageExists = $this->villageExists($id);
        if($villageExists == true) {
            $this->db->select('al.user_id, pu.name');
            $this->db->from('area_allocation al');
            $this->db->join('party_users pu', 'pu.id = al.user_id');
            $this->db->where('al.location_id', $id);
            $this->db->where('al.user_alloc_role', 6);
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function getPSByMandal($id) {
        $mandalExists = $this->mandalExists($id);
        if($mandalExists == true) {
            $this->db->select('p.id, p.ps_no, p.ps_name, p.ps_area');
            $this->db->from('tbl_locations as l');
            $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
            $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
            $this->db->where('l.id', $id);
            $this->db->where('l.level_id', 9);
            $result = $this->db->get()->result();
            return $result;
        }else {
            return false;
        }
    }

    public function getUserByEmail($email) {
        $this->db->select('id, status');
        $this->db->from('tbl_users');
        $this->db->where('email', $email);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getUserByPhone($phone) {
        $this->db->select('id, status');
        $this->db->from('tbl_users');
        $this->db->where('mobile', $phone);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function createAccessToken($data) {
        $user_id = $data['user_id'];
        if($this->getAccessToken($user_id)) {
            $update_token = array(
                'access_token' => $data['token'],
                'expired_at' => $data['expired_at'],
                'updated_at' => date('Y-m-d h:i:s')
            );
            $this->db->where('user_id', $user_id);
            $result = $this->db->update('tbl_access_tokens', $update_token);
        }else {
            $access_data = array(
                'user_id' => $data['user_id'],
                'access_token' => $data['token'],
                'expired_at' => $data['expired_at']
            );
            $result = $this->db->insert('tbl_access_tokens', $access_data);
        }
        
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getAccessToken($id) {
        $this->db->select('user_id, access_token');
        $this->db->from('tbl_access_tokens');
        $this->db->where('user_id', $id);
        $this->db->where('expired_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('expired_at', 'desc');
        $this->db->limit(1);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    //get lookup value by gen id
    public function getLookupValueById($id) {
        $this->db->select('id, value');
        $this->db->from('tbl_lookup');
        $this->db->where('gen_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCaste($id) {
        $this->db->select('id, caste_name');
        $this->db->from('tbl_castes');
        $this->db->where('category_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getPollingStationByVillage($id) {
        $this->db->select('id, ps_no, ps_name, ps_area');
        $this->db->from('tbl_ps');
        $this->db->where('village_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getBoothAgentByPollingStation($id) {
        $this->db->select('v.id, v.firstname, v.lastname,v.f_name,v.gender,v.mobile,v.photo');
        $this->db->from('tbl_role_request as r');
		$this->db->join('tbl_voters as v', 'r.volunteer_id = v.id');
        $this->db->where('r.ps_id', $id);
		$this->db->where('r.status', 0);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function isPasswordSet($id) {
        $this->db->select('id');
        $this->db->from('tbl_password');
        $this->db->where('id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function generatePassword($data) {
        $user_id = trim($data['user_id']);
        $salt = uniqid();
        $pass = base64_decode($data['password']);
        $password = password_hash($pass.$salt, PASSWORD_BCRYPT);
        $in_data = array(
            'id' => $user_id,
            'password_salt' => $salt,
            'password' => $password
        );
        $result = $this->db->insert('tbl_password', $in_data);
        
        if($result) {
            return true;
        }else {
            return false;
        }
    }
    
    public function getMemberByGroup($role){
        $id = $this->session->userdata('user')->id;
        $this->db->select('u.id,u.user_role,u.first_name, u.last_name');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('t.parent_id', $id);
        $this->db->where('u.user_role', $role);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCoordinatorByMng($id) {
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
    
    public function getPageDescription($path) {
        $this->db->select('id, url, page_title, page_description');
        $this->db->from('tbl_page_description');
        $this->db->where('url', $path);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function chekUserRole($id, $role) {
        $this->db->select('id');
        $this->db->from('tbl_users');
        $this->db->where('id', $id);
        $this->db->where('user_role', $role);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }
	/* Changes for admin panel*/
	public function getAllParties() {
        $this->db->select('p.id,p.party_name,p.party_icon');
		$this->db->from('tbl_party as p');
		$result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	public function getPollingStations() {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l1', 't.location = l1.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        //$this->db->where('l1.id', $id);
        $result = $this->db->get()->result();
        return $result;
    }
	public function getAllMandalsByConst($locationid){
		$this->db->select('l.id,l.name ,cl.location_id');
        $this->db->from('tbl_locations as l');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        $this->db->where('cl.parent_id', $locationid);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	public function getAllVillages()
	{
		$this->db->select('l.id, l.name');
		$this->db->from('tbl_locations as l');
		$this->db->join('tbl_locations as l1', 'l.parent_id = l1.id');
		$this->db->join('tbl_const_location as cl', 'l1.id = cl.location_id');
		$this->db->where('l.level_id', 10);
		$this->db->where('cl.parent_id', 3545);
		//$this->db->order_by('name');
		 $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	public function getUserByRole($role,$id) {
        $this->db->select('u.id, u.first_name, u.last_name, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        $this->db->where('u.user_role', $role);
		$this->db->where('t.location', $id);
        $result = $this->db->get()->result();
        
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	
}