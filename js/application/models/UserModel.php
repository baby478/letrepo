<?php
class UserModel extends CI_Model {
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

    public function voterExists($id) {
        $this->db->select('id');
        $this->db->where('voter_id', $id);
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
        //$password = password_hash($data['password'].$salt, PASSWORD_BCRYPT);

        $this->db->trans_begin();
        $this->db->insert('tbl_users', $prepared_data);
        $insert_id = $this->db->insert_id();
        
        //password
        // $pass_data = array(
        //     'id' => $insert_id,
        //     'password_salt' => $salt,
        //     'password'    => $password
        // );
        // $this->db->insert('tbl_password', $pass_data);
        
        //Qualification details
        foreach($data['qualification'] as $q) {
            $quali = array(
                'user_id' => $insert_id,
                'qualification' => $q['q'],
                'course_name' => $q['c'],
                'college_name' => $q['clg']
            );
            $this->db->insert('tbl_education', $quali);
        }
        
        //address
        // $address = array(
        //     'id' => $insert_id,
        //     'house_no' => $data['hno'],
        //     'street' => $data['street'],
        //     'landmark' => $data['landmark'],
        //     'location' => $data['village'],
        //     'pincode' => $data['pincode']
        // );
        // $this->db->insert('tbl_address', $address);

        //caste-head
        /* foreach($data['caste_head'] as $ch) {
            $casteHead = array(
                'user_id' => $insert_id,
                'caste_head' => $ch['ch'],
                'mobile' => $ch['chm']
            );
            $this->db->insert('tbl_user_castehead', $casteHead);
        } */
        
        //family details
        /*$family = array(
            'id' => $insert_id,
             'family_members' => $data['familyMember'],
            'father_relations' => $data['fatherRelations'],
            'mother_relations' => $data['motherRelations'],
            'wife_relations' => $data['wifeRelations'],
            'brother_relations' => $data['brotherRelations'],
            'sister_relations' => $data['sisterRelations'],
            'close_relations' => $data['closeRelations'],
            'close_friends' => $data['closeFriends'], 
            'family_voters' => $data['familyvoters'],
            'surname_families' => $data['surnameFamily'],
            'caste_voters' => $data['castevoters'],
            'known_families' => $data['knownfamily'],
        );
        $this->db->insert('tbl_user_family', $family);*/
        
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
            'voter_id' => $data['voterId'],
            'occupation' => $data['occupation'],
            'income' => $data['income'],
            // 'caste' => $data['category'],
            // 'category' => $data['caste'],
            'mobile' => $data['phone'],
            'photo' => $data['photo'],
            'status' => 1,
            'user_role' => 17,
            'created_by' => $user_id
        );
        if($this->input->post('caste')) {
            $user_data['caste'] = $this->input->post('caste');
        }
        if($this->input->post('category')) {
            $user_data['category'] = $this->input->post('category');
        }
        return $user_data;
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

    public function suspendUser($id) {
        $this->db->trans_begin();
        $update_team = array(
            'status' => 0,
            'date_to' => date('Y-m-d')
        );
        $this->db->where('user_id', $id);
        $this->db->update('tbl_team_mng', $update_team);

        $update_user = array(
            'user_role' => 17,
            'status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_users', $update_user);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function getUserData($id) {
        $user_id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob,u.photo, u.mobile, l.value as gender, rl.value as user_role,u.status as active_status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('u.id', $id);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function updateData($data,$id) {
		 $this->db->trans_begin();
		 $update_request = array(
						 'first_name' => $data['firstname'],
						 'last_name' => $data['lastname'] ,
						 
				);
		if($data['photo'] != null) {
			$update_request['photo'] = $data['photo'];	
		}
		$this->db->where('id', $id);
		$this->db->update('tbl_users', $update_request); 
		if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
	}
}

