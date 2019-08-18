<?php
class CoordinatorModel extends CI_Model {

    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getVotersCountByCoordinator($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_citizen_mng as ct', 'ct.citizen_id = v.id');
        // $this->db->where('ct.user_role', 17);
        $this->db->where('ct.user_id', $id);
        $count = $this->db->get()->num_rows();
        return $count;
    }

    public function createAuthToken($data) {
        $user_id = $data['user_id'];
        if($this->getAuthToken($user_id)) {
            $up_data = array(
                'salt' => $data['salt'],
                'auth_token' => $data['auth_token'],
                'updated_at' => date('Y-m-d h:i:s')
            );
            $this->db->where('user_id', $user_id);
            $result = $this->db->update('tbl_auth', $data);
        }else {
            $result =  $this->db->insert('tbl_auth', $data);
        }
        if($result) {
         return true;
         }else {
             return false;
         }
    }

    public function getAuthToken($id) {
        $this->db->select('user_id, salt, auth_token, expires');
        $this->db->from('tbl_auth');
        $this->db->where('user_id', $id);
        $this->db->where('expires >', date('Y-m-d H:i:s'));
        $this->db->order_by('expires', 'desc');
        $this->db->limit(1);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
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

    public function userMobileExists($mobile) {
        $this->db->select('id');
        $this->db->where('mobile', $mobile);
        $result = $this->db->get('tbl_users')->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
    
    public function addVoter($data) {
        unset($data['token']);
        $result = $this->db->insert('tbl_voters', $data);
        if($result) {
            $id = $this->db->insert_id();
            return $id;
        }else {
            return false;
        }
    }

    public function updateVoter($data) {
        unset($data['token']);
        $voter_id = $data['voter_id'];
        unset($data['voter_id']);
        $this->db->where('voter_id', $voter_id);
        $result = $this->db->update('tbl_voters', $data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }
    
    public function addVoterAddress() {
        unset($data['user_id']);
        unset($data['token']);
        $result = $this->db->insert('tbl_voters', $data);
        if($result) {
            $id = $this->db->insert_id();
            return $id;
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

    public function citizenExistsByCoordinator($citizen_id, $user_id) {
        $this->db->select('id');
        $this->db->from('tbl_voters');
        $this->db->where('id', $citizen_id);
        $this->db->where('user_id', $user_id);
        $count = $this->db->get()->num_rows();
        if($count > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function volunteerExists($user_id, $group_id) {
        $this->db->select('citizen_id');
        $this->db->from('tbl_citizen_mng');
        $this->db->where('user_id', $user_id);
        $this->db->where('group_id', $group_id);
        $this->db->where('user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function addCitizenDetail($data) {
        $address = array(
            'citizen_id' => $data['citizen_id'],
            'house_no' => $data['house_no'],
            'street' => $data['street'],
            'local_status' => $data['local_status']
        );
        if(isset($data['landmark']) && $data['landmark'] !== '') {
            $address['landmark'] = $data['landmark'];
        }
        $location = $this->getCoordinatorLocation($data['user_id'])->location;
        $address['location'] = $location;
        
        $this->db->trans_begin();
        $this->db->insert('tbl_citizen_address', $address);
        if(isset($data['outstation'])) {
            $outstation = array();
            foreach($data['outstation'] as $k => $v) {
                $outstation[$k] = $v;
            }
            $outstation['citizen_id'] = $data['citizen_id'];
            $this->db->insert('tbl_citizen_outstation', $outstation);
        }

        $add_detail = array(
            'citizen_id' => $data['citizen_id'],
            'user_id' => $data['user_id'],
            'user_role' => $data['user_role'],
            'group_id' => $data['group_id'],
            'relationship' => $data['relationship'],
            'religion' => $data['religion'],
            'category' => $data['category'],
            'polling_station' => $data['polling_station'],
            'status' => $data['voter_status']
        );
        if(isset($data['caste']) && $data['caste'] !== '') {
            $add_detail['caste'] = $data['caste'];
        }
        if(isset($data['parent_id']) && $data['parent_id'] !== '') {
            $add_detail['parent_id'] = $data['parent_id'];
        }
        $this->db->insert('tbl_citizen_mng', $add_detail);
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
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
                'user_id' => $data['user_id']
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
        $location = $this->getCoordinatorLocation($data['user_id'])->location;
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
            'user_id' => $data['user_id'],
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

        //update member's parent if not set earlier
        if(isset($data['volunteer']) && $data['volunteer'] !== '') {
            $update_mng = array(
                'parent_id' => $citizen_id
            );
            $this->db->where('parent_id is null', null, FALSE);
            $this->db->where('user_id', $data['user_id']);
            $this->db->where('group_id', $data['group_id']);
            $this->db->update('tbl_citizen_mng', $update_mng);
        }
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return $citizen_id;
        }

    }

    public function getCoordinatorById($id) {
        $this->db->select('u.id, u.first_name, u.last_name, l.value as designation, lc.name as location');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        $this->db->join('tbl_locations as lc', 't.location = lc.id', 'left');
        $this->db->where('u.id', $id);
        return $this->db->get()->row();
    }

    public function getVolunteerByCoordinator($id) {
        $this->db->select('v.firstname, v.lastname, v.gender, v.mobile, v.photo, l.value as designation, c.citizen_id, lc.name as village_name');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_team_mng as t', 't.user_id = c.user_id');
        $this->db->join('tbl_locations as lc', 't.location = lc.id');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_lookup as l', 'c.user_role = l.id');
        $this->db->where('c.parent_id', $id);
        $this->db->where('c.user_role', 46);
        $this->db->order_by('c.group_id');
        //$this->db->group_by('c2.parent_id');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getCoordinatorMember($id) {
        $this->db->select('v.firstname, v.lastname, v.photo, v.mobile, v.voter_id');
        $this->db->from('tbl_citizen_mng c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->where('c.parent_id', $id);
        $this->db->where('c.user_role', 17);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->num_rows();
        }else {
            return 0;
        }
    }

    public function getVolunteerMember($user_id, $vnt_id) {
        $this->db->select('v.firstname, v.lastname, v.mobile, v.gender, v.photo, v.voter_id, c.citizen_id, p.ps_no, p.ps_name');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_ps as p', 'v.ps_no = p.id');
        $this->db->where('c.parent_id', $vnt_id);
        $this->db->where('c.user_id', $user_id);
        $this->db->where('c.user_role', 17);
        $this->db->where('c.local_status', 15);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getVisit() {
        $this->db->select('id, name');
        $this->db->from('tbl_visits_lookup');
        $this->db->where('level_id', 1);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getVisitOpt($id) {
        $this->db->select('id, name');
        $this->db->from('tbl_visits_lookup');
        $this->db->where('parent_id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    /* Visit One */
    public function saveVisitOne($id, array $data) {
        $this->db->trans_begin();

        $v_values = $data['visit_values'];
        $ids = array_column($v_values, 'id');
        $values = array_column($v_values, 'values');
        $count_values = count($v_values);
        //insert
        if(isset($data['visit'])) {
            if($count_values == 8) {
                $data['visit']['status'] = 1;
            }
            $data['visit']['citizen_id'] = $id;
            $this->db->insert('tbl_visit_1', $data['visit']);
            $visit_id = $this->db->insert_id();
            
            for($i = 0; $i < $count_values; $i++) {
                $service_id = $ids[$i];
                $value = $values[$i];
                foreach($value as $v) {
                    $op_data = array(
                        'visit_id' => $visit_id,
                        'service_id' => $service_id,
                        'option_id' => $v
                    );
                    $this->db->insert('tbl_visit1_options', $op_data);
                }
            }    
        }
        //update
        if(isset($data['update_visit'])) {
            $update_data = $data['update_visit'];
            $update_data['modified_at'] = date('Y-m-d H:i:s');
            $visit_id = $update_data['id'];
            unset($update_data['id']);
            
            //update visit
            $this->db->where('id', $visit_id);
            $this->db->update('tbl_visit_1', $update_data);
            $sp = "CALL updatevisit_status(?)";
            $this->db->query($sp, array('visitId' => $visit_id));
            for($i = 0; $i < $count_values; $i++) {
                $service_id = $ids[$i];
                $value = $values[$i];
                foreach($value as $v) {
                    $option_exists = $this->visitOptionExists($visit_id, $service_id, $v);
                    if($option_exists == false) {
                        $op_data = array(
                            'visit_id' => $visit_id,
                            'service_id' => $service_id,
                            'option_id' => $v
                        );
                        $this->db->insert('tbl_visit1_options', $op_data);
                    }
                }
            }
            
        }
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return $visit_id;
        }
    }

    public function isVisited($id) {
        $this->db->select('id, citizen_id');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get('tbl_visit_1');
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }


    public function visitOptionExists($visit_id, $service_id, $option) {
        $this->db->select('id');
        $this->db->where('visit_id', $visit_id);
        $this->db->where('service_id', $service_id);
        $this->db->where('option_id', $option);
        $result = $this->db->get('tbl_visit1_options');
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function visitOneStatus($id) {
        $this->db->select('id, govt_scheme, health, job, certificate, id_cards, status, created_at, modified_at');
        $this->db->from('tbl_visit_1');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
    /* Visit One Ends */
    
    /* Visit TWo */
    public function isVisitedV2($id) {
        $this->db->select('id, citizen_id');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get('tbl_visit_2');
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveVisitTwo($id, array $data) {
        $this->db->trans_begin();

        $v_values = $data['visit_values'];
        $ids = array_column($v_values, 'id');
        $values = array_column($v_values, 'values');
        $count_values = count($v_values);

        //insert
        if(isset($data['visit'])) {
            if($count_values == 3) {
                $data['visit']['status'] = 1;
            }
            $data['visit']['citizen_id'] = $id;
            $this->db->insert('tbl_visit_2', $data['visit']);
            $visit_id = $this->db->insert_id();
            
            for($i = 0; $i < $count_values; $i++) {
                $status_id = $ids[$i];
                $value = $values[$i];
                foreach($value as $v) {
                    $op_data = array(
                        'visit_id' => $visit_id,
                        'status_id' => $status_id,
                        'option_id' => $v
                    );
                    $this->db->insert('tbl_visit2_options', $op_data);
                }
            }    
        }

        //update
        if(isset($data['update_visit'])) {
            $update_data = $data['update_visit'];
            $update_data['modified_at'] = date('Y-m-d H:i:s');
            $visit_id = $update_data['id'];
            unset($update_data['id']);

            //update visit
            $this->db->where('id', $visit_id);
            $this->db->update('tbl_visit_2', $update_data);
            $sp = "CALL updatevisit2_status(?)";
            $this->db->query($sp, array('visitId' => $visit_id));

            for($i = 0; $i < $count_values; $i++) {
                $status_id = $ids[$i];
                $value = $values[$i];
                foreach($value as $v) {
                    $option_exists = $this->visitTwoOptionExists($visit_id, $status_id, $v);
                    if($option_exists == false) {
                        $op_data = array(
                            'visit_id' => $visit_id,
                            'status_id' => $status_id,
                            'option_id' => $v
                        );
                        $this->db->insert('tbl_visit2_options', $op_data);
                    }
                }
            }
        }
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return $visit_id;
        }
    }

    public function visitTwoOptionExists($visit_id, $status_id, $option) {
        $this->db->select('id');
        $this->db->where('visit_id', $visit_id);
        $this->db->where('status_id', $status_id);
        $this->db->where('option_id', $option);
        $result = $this->db->get('tbl_visit2_options');
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function visitTwoStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_2');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
    /* Visit Two Ends */

    /* Visit Three */
	public function addVisitThree($data) {
		$insert_data = array(
            'citizen_id' => $data['citizen_id'],
            'current_supply' => $data['current_supply'],
            'water_supply' => $data['water_supply'],
            'pention' => $data['pention'],
            'subsides' => $data['subsides'],
            'ration_supply' => $data['ration_supply'],
            'runamafi' => $data['runamafi'],
            'rythu_bandhu' => $data['rythu_bandhu'],
            'rythu_beema' => $data['rythu_beema'],
            'govt_schemes' => $data['govt_schemes'],
			'status' => 1
            );
	    $result =  $this->db->insert('tbl_visit_3', $insert_data);
		if($result) {
			return $result;
		}else {
				return false;
		}
    }
    
    public function citizenVisitThreeExists($citizen_id) {
        $this->db->select('id');
        $this->db->where('citizen_id', $citizen_id);
        $result = $this->db->get('tbl_visit_3')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function visitThreeStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_3');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
    /* Visit Three Ends */
    
    /* Visit Four */
	public function addVisitFour($data) {
		$insert_data = array(
            'citizen_id' => $data['citizen_id'],
            'mission_bhagirath' => $data['mission_bhagirath'],
            'mission_kakatiya' => $data['mission_kakatiya'],
			'kaleshwaram_project' => $data['kaleshwaram_project'],
            'rangareddy_chevella' => $data['rangareddy_chevella'],
            'tsi_pass' => $data['tsi_pass'],
			't_hub' => $data['t_hub'],
            'metro_rail' => $data['metro_rail'],
            'softnet' => $data['softnet'],
            'she_teams' => $data['she_teams'],
            'she_cabs' => $data['she_cabs'],
			'status' => 1
            );
	    $result =  $this->db->insert('tbl_visit_4', $insert_data);
		if($result) {
			return $result;
		}else {
				return false;
		}
    }

    public function citizenVisitFourExists($citizen_id) {
        $this->db->select('id');
        $this->db->where('citizen_id', $citizen_id);
        $result = $this->db->get('tbl_visit_4')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function visitFourStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_4');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
    /* Visit Four Ends */

    /* Visit Five */
	public function addVisitFive($data) {
		$insert_data = array(
            'citizen_id' => $data['citizen_id'],
            'education' => $data['education'],
            'profession' => $data['profession'],
			'monthly_income' => $data['monthly_income'],
            'caste_activity' => $data['caste_activity'],
            'political_sympathser' => $data['political_sympathser'],
			'last_time_vote' => $data['last_time_vote'],
            'digital_village_activity' => $data['digital_village_activity'],
            'associations' => $data['associations'],
            'hobbies' => $data['hobbies'],
            'mobile_data' => $data['mobile_data'],
			'status' => 1
            );
	    $result =  $this->db->insert('tbl_visit_5', $insert_data);
		if($result) {
			return $result;
		}else {
				return false;
		}
    }

    public function citizenVisitFiveExists($citizen_id) {
        $this->db->select('id');
        $this->db->where('citizen_id', $citizen_id);
        $result = $this->db->get('tbl_visit_5')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function visitFiveStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_5');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
    /* Visit Five Ends */
    
    /* Visit Six */
    public function citizenExistsVisitSix($id) {
        $this->db->select('id');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get('tbl_visit_6')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function saveVisitSix($id, $data) {
        $this->db->trans_begin();
        
        if(isset($data['no-reference'])) {
            $in_data = array(
                'citizen_id' => $id,
                'neighbourhood' => $data['no-reference'],
                'status' => 1
            );
            $this->db->insert('tbl_visit_6', $in_data);
            $visit_id = $this->db->insert_id();
        }
        if(isset($data['reference'])) {
            $vs_data = array(
                'citizen_id' => $id,
                'neighbourhood' => 1,
                'status' => 1
            );
            $this->db->insert('tbl_visit_6', $vs_data);
            $visit_id = $this->db->insert_id();
            $ref_data = $data['reference'];
            foreach($ref_data as $k => $v) {
                $ref_data[$k]['visit_id'] = $visit_id;
            }
            foreach($ref_data as $v) {
                $this->db->insert('tbl_visit6_options', $v);
            }    
        }
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return $visit_id;
        }
    }

    public function visitSixStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_6');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
    /* Visit Six Ends */

    //Count Coordinator Activity
    public function getCoordinatorActivity($id) {
        $count = 0;
        
        //count visit 1 activity
        $this->db->select('v1.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_27 as v1', 'c.citizen_id = v1.citizen_id');
        $this->db->where('c.user_id', $id);
        $v1 = $this->db->get();
        if($v1->num_rows() > 0) {
            foreach($v1->result() as $vc) {
                $count += 1;
            }    
        }

        //count visit 2 activity
        $this->db->select('v2.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_28 as v2', 'c.citizen_id = v2.citizen_id');
        $this->db->where('c.user_id', $id);
        $v2 = $this->db->get();
        if($v2->num_rows() > 0) {
            foreach($v2->result() as $vc) {
                $count += 1;
            }  
        }

        //count visit 3 activity
        $this->db->select('v3.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_29 as v3', 'c.citizen_id = v3.citizen_id');
        $this->db->where('c.user_id', $id);
        $v3 = $this->db->get();
        if($v3->num_rows() > 0) {
            foreach($v3->result() as $vc) {
                $count += 1;
            }  
        }

        //count visit 4 activity
        $this->db->select('v4.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_30 as v4', 'c.citizen_id = v4.citizen_id');
        $this->db->where('c.user_id', $id);
        $v4 = $this->db->get();
        if($v4->num_rows() > 0) {
            foreach($v4->result() as $vc) {
                $count += 1;
            }  
        }

        //count visit 5 activity
        $this->db->select('v5.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_33 as v5', 'c.citizen_id = v5.citizen_id');
        $this->db->where('c.user_id', $id);
        $v5 = $this->db->get();
        if($v5->num_rows() > 0) {
            foreach($v5->result() as $vc) {
                $count += 1;
            }  
        }

        //count visit 6 activity
        $this->db->select('v6.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_34 as v6', 'c.citizen_id = v6.citizen_id');
        $this->db->where('c.user_id', $id);
        $v6 = $this->db->get();
        if($v6->num_rows() > 0) {
            foreach($v6->result() as $vc) {
                $count += 1;
            }  
        }

        //count visit 6 activity
        $this->db->select('v7.id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_35 as v7', 'c.citizen_id = v7.citizen_id');
        $this->db->where('c.user_id', $id);
        $v7 = $this->db->get();
        if($v7->num_rows() > 0) {
            foreach($v7->result() as $vc) {
                $count += 1;
            }  
        }
        
        return $count;
        
    }

    /* Coordinator SMS Count */
    public function getCoordinatorMsgCount($id) {
        $this->db->select('id');
        $this->db->from('tbl_sms_details');
        $this->db->where('user_id', $id);
        return $this->db->get()->num_rows();
    }

    //get superiors
    public function getCoordinatorSuperior($id) {
        $superior = array();

        //Get Booth Coordinator
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.user_role, u.gender, u.photo, l.value as designation');
        $this->db->from('tbl_team_ps as tp1');
        $this->db->join('tbl_team_ps as tp2', 'tp1.ps_id = tp2.ps_id');
        $this->db->join('tbl_users as u', 'tp2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tp1.user_id', $id);
        $this->db->where('u.user_role', 55);
        $bc = $this->db->get()->row();
        $superior[] = $bc;

        //Get Team Leader
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.user_role, u.gender, u.photo, l.value as designation');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.parent_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('t.user_id', $id);
        $tl = $this->db->get()->row();
        $superior[] = $tl;
        
        //Get Assistant Manager
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.user_role, u.gender, u.photo, l.value as designation');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_users as u', 't2.parent_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('t.user_id', $id);
        $mng = $this->db->get()->row();
        $superior[] = $mng;
        
        if(count($superior) > 0) {
            return $superior;
        }else {
            return false;
        }
    }

    //get service count
    public function getServiceCountByServiceId($id, $service) {
        $this->db->select('v1.id, v1.home_assist, v1.citizen_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_1 as v1', 'v1.citizen_id = c.citizen_id');
        $this->db->where('c.user_id', $id);
        $this->db->where('v1.'.$service, 1);
        return $this->db->get()->num_rows();    
    }

    public function getMemberByService($id, $service) {
        $service = trim($service);
        $this->db->select('v1.id, v1.citizen_id, v.firstname, v.lastname, v.photo, v.gender');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_1 as v1', 'v1.citizen_id = c.citizen_id');
        $this->db->join('tbl_voters as v', 'v.id = v1.citizen_id');
        $this->db->where('v1.'.$service, 1);
        $this->db->where('c.user_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $members = $result->result();
            foreach($members as $k => $v) {
                $this->db->select('vl.name');
                $this->db->from('tbl_visit1_options as opt');
                $this->db->join('tbl_visits_lookup as vl', 'opt.option_id = vl.id');
                $this->db->where('opt.visit_id', $v->id);
                $options = $this->db->get();
                $services = '';
                if($options->num_rows() > 0) {
                    foreach($options->result() as $r) {
                        $services .= $r->name . ' , ';
                    }
                    $v->services = $services;
                }
            }
            return $members;
        }else {
            return false;
        }
        
    }

    public function getneighbouringvillages($id) {
        $this->db->select('l3.id, l3.name');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->join('tbl_locations as l2', 'l2.id = l.parent_id');
        $this->db->join('tbl_locations as l3', 'l3.parent_id = l2.id');
        $this->db->where('t.user_id', $id);
        $this->db->order_by('l3.name');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    //save xparty member
    public function xpartymember($data) {
        $x_data = array(
            'user_id'   => $data['user_id'],
            'party_id' => $data['party_id'],
            'name'  => $data['name'],
            'designation' => $data['designation']
        );
        if(isset($data['age']) && !empty($data['age'])) {
            $x_data['age'] = $data['age'];
        }
        if(isset($data['mobile']) && !empty($data['mobile'])) {
            $x_data['mobile'] = $data['mobile'];
        }
        if(isset($data['caste']) && !empty($data['caste'])) {
            $x_data['caste'] = $data['caste'];
        }
        if(isset($data['followers']) && !empty($data['followers'])) {
            $x_data['followers'] = $data['followers'];
        }
        
        $result = $this->db->insert('tbl_xparty_info', $x_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getVotersByPS($id) {
        $this->db->select('v.id as citizen_id, v.firstname, v.lastname, v.lfname, v.llastname, v.f_name, v.lrname, v.hno, v.voter_id, v.gender, v.age, v.photo, p.ps_no, p.ps_name, p.ps_area');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_ps as tp', 't.user_id = tp.user_id');
        // $this->db->join('tbl_locations as l', 't.location = l.id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_voters as v', 'p.id = v.ps_no');
        $this->db->where('t.user_id', $id);
        $this->db->where('v.user_id IS NULL', NULL, FALSE);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function referPsMember($data) {
        $r_data = array(
            'user_id' => $data['user_id'],
            'volunteer_id' => $data['volunteer_id'],
            'role_id' => $data['role_id'],
            'ps_id' => $data['ps_id']
        );

        $result = $this->db->insert('tbl_role_request', $r_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getVolunteerToRefer($id) {
        $this->db->select('r.volunteer_id');
        $this->db->from('tbl_role_request as r');
        $this->db->where('r.user_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $vnt = array();
            foreach($result->result() as $k => $v) {
                $vnt[] = $v->volunteer_id;
            }    
        }
        
        $this->db->select('c.citizen_id, v.firstname, v.lastname');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->where('c.parent_id', $id);
        $this->db->where('c.user_role', 46);
        if(isset($vnt)) {
            $this->db->where_not_in('c.citizen_id', $vnt);
        }
        
        $vnt_result = $this->db->get();
        if($vnt_result->num_rows() > 0) {
            return $vnt_result->result();
        }else {
            return false;
        }
    }

    public function getRefMembers($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.photo, r.status');
        $this->db->from('tbl_role_request as r');
        $this->db->join('tbl_voters as v', 'r.volunteer_id = v.id');
        $this->db->where('r.user_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
        
    }

    public function getAssignPs($id) {
        $this->db->select('p.ps_no, p.ps_name, l.value as user_role, m.booth_no');
        $this->db->from('tbl_ps_member as m');
        $this->db->join('tbl_ps as p', 'm.ps_id = p.id');
        $this->db->join('tbl_users as u', 'm.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('m.volunteer_id', $id);
        $this->db->where('m.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
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
	//testing get caste flow
	public function getCasteData() {
        $this->db->select('id,caste,values');
        $this->db->from('cast_flow');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getGroupTask($id) {
        $sup_id = array();
        $this->db->select('t2.user_id as tl_id, t3.user_id as mng_id, t4.user_id as smng_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t3', 't2.parent_id = t3.user_id');
        $this->db->join('tbl_team_mng as t4', 't3.parent_id = t4.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id[] = $result->tl_id;
        $sup_id[] = $result->mng_id;
        $sup_id[] = $result->smng_id;
        
        $this->db->select("t.id, t.task_name, t.task_description, t.date_from, t.date_to, t.priority, concat(u.first_name ,' ' , u.last_name) as task_by, l.value as role");
        $this->db->from('tbl_tasks as t');
        $this->db->join('tbl_tasks_mng as tm', 't.id = tm.task_id');
        $this->db->join('tbl_users as u', 't.created_by = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tm.receiver_id = 68 or tm.receiver_id = 65', NULL, FALSE);
        $this->db->where('t.task_group', 62);
        $this->db->where_in('t.created_by', $sup_id);
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

    public function saveCallRecord($data) {
        $inst_data = array(
            'user_id' => $data['user_id'],
            'receiver_id' => $data['receiver_id'],
            'mobile' => $data['mobile'],
            'call_duration' => $data['call_duration'],
            'recording_path' => $data['call_record']
        );
        $result = $this->db->insert('tbl_call_details', $inst_data);
        if($result) {
            $id = $this->db->insert_id();
            return $id;
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

    public function saveVoiceMessage($data) {
        $s_data = array(
            'user_id' => $data['user_id'],
            'receiver_id' => $data['receiver_id'],
            'duration' => $data['message_duration'],
            'voice_message' => $data['voice_message']
        );
        $result = $this->db->insert('tbl_voice_message', $s_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function isVisited_21($id) {
        $this->db->select('id, citizen_id');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get('tbl_visit_21');
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveVisitTwentyOne($id, array $data) {
        // echo json_encode($data); exit;
        
        //Insert
        if(isset($data['visit'])) {
            $v_values = $data['visit'];
            $count_values = count($v_values);
            if($count_values == 10) {
                $data['visit']['status'] = 1;
            }
            $data['visit']['citizen_id'] = $id;
            $result = $this->db->insert('tbl_visit_21', $data['visit']);
            $last_id = $this->db->insert_id();
        }
        //update
        if(isset($data['update_visit'])) {
            $visit_id = $data['update_visit']['id'];
            $update_d = $data['update_visit'];
            unset($update_d['id']);
            //echo json_encode($update_d); exit;
            $this->db->where('id', $visit_id);
            $result = $this->db->update('tbl_visit_21', $update_d);
            $sp = "CALL updatevisit21_status(?)";
            $this->db->query($sp, array('visitId' => $visit_id));
            $last_id = $visit_id;
        }
        
        if($result)  {
            return $last_id;
        }else {
            
            return false;
        }
    }
    // Added by Prawyn
    public function saveVisitTwentyTwo(array $data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['health'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_22', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit22_options', $o_data);
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

    public function saveVisitTwentythree($data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['job_values'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_23', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit23_options', $o_data);
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

    public function saveVisitTwentyfour($data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['certificate'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_24', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit24_options', $o_data);
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

    public function saveVisitTwentyfive($data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['card'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_25', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit25_options', $o_data);
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

    public function visitTwentyOneStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_21');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function visitTwentyTwoStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_22');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function visitTwentyThreeStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_23');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function visitTwentyFourStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_24');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function visitTwentyFiveStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_25');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveServiceRequest($data) {
        $s_data = array(
            'citizen_id' => $data['citizen_id'],
            'visit_id' => $data['visit_id'],
            'service_name' => $data['service']
        );
        $result = $this->db->insert('tbl_service_request', $s_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getVisit21ServiceCount($id) {
        $this->db->select('v1.id, v1.citizen_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_21 as v1', 'v1.citizen_id = c.citizen_id');
        $this->db->where('c.user_id', $id);
        return $this->db->get()->num_rows();
    }

    public function getServiceCountByCityService($id, $service, $service_option) {
        $this->db->select('vo.id, vo.visit_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join(''.$service . ' as v1', 'v1.citizen_id = c.citizen_id');
        $this->db->join(''.$service_option.' as vo', 'v1.id = vo.visit_id');
        $this->db->where('c.user_id', $id);
        $this->db->where('vo.option_id !=', 24, FALSE);
        $this->db->where('vo.option_id !=', 25, FALSE);
        return $this->db->get()->num_rows();
    }

    public function getMemberByCityService($id, $service) {
        $service = trim($service);
        $this->db->select('v1.id, v1.citizen_id, v.firstname, v.lastname, v.photo, v.gender');
        $this->db->from('tbl_citizen_mng as c');
        if($service == 'modi_scheme') {
            $this->db->join('tbl_visit_21 as v1', 'v1.citizen_id = c.citizen_id');
        }
        if($service == 'health') {
            $this->db->join('tbl_visit_22 as v1', 'v1.citizen_id = c.citizen_id');
        }
        if($service == 'job') {
            $this->db->join('tbl_visit_23 as v1', 'v1.citizen_id = c.citizen_id');
        }
        if($service == 'certificate') {
            $this->db->join('tbl_visit_24 as v1', 'v1.citizen_id = c.citizen_id');
        }
        if($service == 'id_cards') {
            $this->db->join('tbl_visit_25 as v1', 'v1.citizen_id = c.citizen_id');
        }
        $this->db->join('tbl_voters as v', 'v.id = v1.citizen_id');
        $this->db->where('c.user_id', $id);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $members = $result->result();
            if($service == 'health' || $service == 'job' || $service == 'certificate' || $service == 'id_cards') {
                foreach($members as $k => $v) {
                    $this->db->select('opt.id');
                    if($service == 'health') {
                        $this->db->from('tbl_visit22_options as opt');
                    }
                    if($service == 'job') {
                        $this->db->from('tbl_visit23_options as opt');
                    }
                    if($service == 'certificate') {
                        $this->db->from('tbl_visit24_options as opt');
                    }
                    if($service == 'id_cards') {
                        $this->db->from('tbl_visit25_options as opt');
                    }
                    $this->db->join('tbl_visits_lookup as vl', 'opt.option_id = vl.id');
                    $this->db->where('opt.visit_id', $v->id);
                    $v->service_count = $this->db->get()->num_rows();
                }
            }
            return $members;
        }else {
            return false;
        }
    }

    public function getOutStationMemberByCoord($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.voter_id, l.name as location, os.mobile, os.street, c.group_id, c.parent_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_citizen_outstation as os', 'os.citizen_id = c.citizen_id');
        $this->db->join('tbl_locations as l', 'os.location = l.id');
        $this->db->join('tbl_voters as v', 'v.id = c.citizen_id');
        $this->db->where('c.user_id', $id);
        $this->db->where('c.user_role', 17);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $data = $result->result();
            foreach($data as $r) {
                if($r->group_id != 39) {
                    $this->db->select('v.firstname, v.lastname');
                    $this->db->from('tbl_voters as v');
                    $this->db->where('v.id', $r->parent_id);
                    $volunteer = $this->db->get()->row();
                    $r->member_of = $volunteer->firstname . ' ' . $volunteer->lastname;
                    $r->designation = 'Volunteer';
                }else {
                    $this->db->select('u.first_name, u.last_name');
                    $this->db->from('tbl_users as u');
                    $this->db->where('u.id', $r->parent_id);
                    $coord = $this->db->get()->row();
                    $r->member_of = $coord->first_name . ' ' . $coord->last_name;
                    $r->designation = 'Coordinator';
                }
                unset($r->group_id);
                unset($r->parent_id);
            }
            return $data;
        }else {
            return false;
        }
    }

    public function getNeighbouringCitizen($id) {
        $this->db->select("vo.name, vo.relationship, vo.mobile, vo.voters, l.name as location, concat(vt.firstname, ' ', vt.lastname) as referred_by");
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_visit_6 as v', 'c.citizen_id = v.citizen_id');
        $this->db->join('tbl_visit6_options as vo', 'v.id = vo.visit_id');
        $this->db->join('tbl_locations as l', 'vo.location = l.id');
        $this->db->join('tbl_voters as vt', 'c.citizen_id = vt.id');
        $this->db->where('c.user_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function saveSchemeFlashData($data){
		$d_save = array(
            'citizen_id' => $data['citizen_id'],
            'status' => 1
        );
		$visit_no = $data['visit_no'];
		if($visit_no == '27') {
			$insertsplash = $this->db->insert('tbl_visit_27',$d_save);
		}
		if($visit_no == '28') {
			$insertsplash = $this->db->insert('tbl_visit_28',$d_save);
		}
		if($visit_no == '29') {
			$insertsplash = $this->db->insert('tbl_visit_29',$d_save);
		}
		if($visit_no == '30') {
			$insertsplash = $this->db->insert('tbl_visit_30',$d_save);
        }
        if($visit_no == '33') {
			$insertsplash = $this->db->insert('tbl_visit_33',$d_save);
        }
        if($visit_no == '34') {
			$insertsplash = $this->db->insert('tbl_visit_34',$d_save);
		}
		if($insertsplash) {
			return true;
		}else{
			return false;
		}
    }
    
    public function saveVisitThirtyOne($data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['schemes'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_31', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit31_options', $o_data);
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

    public function saveVisitThirtyTwo($data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['pension'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_32', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit32_options', $o_data);
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

    public function getFlashVisitStatus($id, $table) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from($table);
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getVisitsCount($id) {
        //voters registerd
        $this->db->select('c.citizen_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->where('c.user_id', $id);
        $this->db->where('c.user_role', 17);
        $this->db->where('c.local_status', 15);
        $data = $this->db->get();
        if($data->num_rows() > 0) {
            
            //visit 1
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_27 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v1 = $res->result();
            }

            //visit 2
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_28 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v2 = $res->result();
            }

            //visit 3
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_29 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v3 = $res->result();
            }

            //visit 4
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_30 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v4 = $res->result();
            }

            //visit 5
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_33 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v5 = $res->result();
            }

            //visit 6
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_34 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
            $res = $this->db->get();
            if($res->num_rows() > 0) {
                $res_v6 = $res->result();
            }

            //visit 7
            $this->db->select('c.citizen_id, v.status');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_visit_35 as v', 'c.citizen_id = v.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.user_role', 17);
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

    public function getVoiceMessageCount($id) {
        $this->db->select('id');
        $this->db->from('tbl_voice_message');
        $this->db->where('user_id', $id);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getSmartMediaLikes($id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('user_id', $id);
        $this->db->where('post_like', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getVotersByCoordinator($id) {
        $this->db->select('v.id, v.voter_status');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->where('c.user_id', $id);
        $this->db->where('c.user_role', 17);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function saveVisitThirtyFive($data) {
        $user_id = $data['user_id'];
        $citizen_id = $data['citizen_id'];
        $o_values = $data['service'];
        if(is_array($o_values)) {
            $this->db->trans_begin();
            $v_data = array(
                'citizen_id' => $citizen_id,
                'status' => 1
            );
            $this->db->insert('tbl_visit_35', $v_data);
            $visit_id = $this->db->insert_id();

            foreach($o_values as $v) {
                $o_data = array(
                    'visit_id' => $visit_id,
                    'option_id' => $v
                );
                $this->db->insert('tbl_visit35_options', $o_data);
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

    public function visitThirtyFiveStatus($id) {
        $this->db->select('id, status, created_at, modified_at');
        $this->db->from('tbl_visit_35');
        $this->db->where('citizen_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
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

    /**
     * Date : 02-01-2019
     */

    public function getSms($id) {
        $inbox = array();

        $sup_id = array();
        $this->db->select('t2.user_id as tl_id, t3.user_id as mng_id, t4.user_id as smng_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t3', 't2.parent_id = t3.user_id');
        $this->db->join('tbl_team_mng as t4', 't3.parent_id = t4.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id[] = $result->tl_id;
        $sup_id[] = $result->mng_id;
        $sup_id[] = $result->smng_id;

        //group inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 62);
        $this->db->where('sm.receiver_id', 68);
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
    
    public function getSentSms($id) {
        $outbox = array();
        
        //group sms
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
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
        $this->db->select('s.id, s.sms_type, s.receiver_user_role, s.text_message, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
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
}