 <?php
class AdminModel extends CI_Model {
    private $_sdb;
    
    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function addPsDetails($data) {
        $insert_data = array(
            'ps_id' => $data['polling-station'],
            'ps_type' => $data['ps-type'],
            'sl_no_start' => $data['sl-start'],
            'sl_no_end' => $data['sl-end'],
            'male' => $data['male-vt'],
            'female' => $data['female-vt'],
            'third_gender' => $data['other-vt']
        );

        $id = $this->db->insert('tbl_ps_dmg', $insert_data);
        if($id) {
            return $id;
        }else {
            return false;
        }
    }

    public function addPsImages($id, $images) {
        $this->db->trans_begin();
        
        foreach($images as $img) {
            $data = array(
                'ps_id' => $id,
                'img_path' => $img
            );
            $this->db->insert('tbl_ps_img', $data);
        }

        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
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

	public function getUsersData() {
        $user_id = $this->session->userdata('user')->id;
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

	public function getUsersDataByRole($role) {
        
        //$user_id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name,u.photo, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', $role);
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

    public function getMandalsByConstituence($location) {
        $this->db->select('l.id, l.name, l.level_id');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('cl.parent_id', $location);
        $this->db->where('cl.level_id', 45);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }	
    }

    public function getPollingStationByMandal($id) {
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
	// data of user in admin
	
	public function registerUser($data) {
        $prepared_data = $this->prepareAddUser($data);
        $salt = uniqid();
        // $password = password_hash($data['password'].$salt, PASSWORD_BCRYPT);

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

	public function getUserData() {
        $user_id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, l.value as gender, rl.value as user_role,u.status as active_status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function getUserDataById($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.gender, u.photo, u.dob, u.mobile, u.user_role,u.status as active_status');
        $this->db->from('tbl_users as u');
        $this->db->where('u.id', $id);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function updateData($data,$id) {
        
        $e_data = $this->getUserDataById($id);
        
        $this->db->trans_begin();
        $update_request = array(
            'first_name' => $data['firstname'],           
            );
        if($data['photo'] != null) {
            $update_request['photo'] = $data['photo'];	
        }
        if($this->input->post('lastname')) {
            $update_request['last_name'] = $data['lastname'];
        }
        if($e_data->mobile != $this->input->post('phone')) {
            $update_request['mobile'] = $data['phone'];
        }
        if($e_data->email != $this->input->post('email')) {
            $update_request['email'] = $data['email'];
        }
        $this->db->where('id', $id);
        $this->db->update('tbl_users', $update_request);
        if($e_data->user_role == 18) {
            $ps_details = $this->getPsAssignedByUser($id);
            if($ps_details[0]->pid != $this->input->post('pollingstation')) {
                //update team ps
                $t_ps = array(
                    'ps_id' => $data['pollingstation']
                );
                $this->db->where('user_id', $id);
                $this->db->update('tbl_team_ps', $t_ps);

                //get village
                $this->db->select('village_id');
                $this->db->from('tbl_ps');
                $this->db->where('id', $data['pollingstation']);
                $p_res = $this->db->get()->row();

                //update team mng
                $t_mng = array(
                    'location' => $p_res->village_id
                );
                $this->db->where('user_id', $id);
                $this->db->update('tbl_team_mng', $t_mng);

                //get children
                $this->db->select('u.id as uid');
                $this->db->from('tbl_team_mng as tm');
                $this->db->join('tbl_users as u', 'tm.user_id = u.id');
                $this->db->where('tm.parent_id', $id);
                $this->db->where('u.user_role', 3);
                $this->db->where('u.status', 1);
                $c_res = $this->db->get();
                if($c_res->num_rows() > 0) {
                    $c_user = $c_res->result();
                    foreach($c_users as $cu) {
                        //update child team ps
                        $c_tps = array(
                            'ps_id' => $data['pollingstation']
                        );
                        $this->db->where('user_id', $cu->uid);
                        $this->db->update('tbl_team_ps', $c_tps);

                        //update child team mng
                        $c_tmng = array(
                            'location' => $p_res->village_id
                        );
                        $this->db->where('user_id', $cu->uid);
                        $this->db->update('tbl_team_mng', $c_tmng);
                    }
                }
            }
        } 
        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	
	public function getSMByAdmin($id) {
        $this->db->select('t.user_id, t.location');
        $this->db->from('tbl_team_mng as t');
        $this->db->where('t.parent_id', $id);
        $this->db->where('t.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getTeamLeaderByManagerLocation($loc) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_team_mng as t', 't.location = l.id');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->where('l.parent_id', $loc);
        $this->db->where('t.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getManByLocation($loc) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->where('t.location', $loc);
        $this->db->where('u.user_role', 2);
        $this->db->where('t.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getVillageByPs($id) {
        $this->db->select('p.village_id');
        $this->db->from('tbl_ps as p');
        $this->db->where('p.id', $id);
        return $this->db->get()->row();
    }

	public function assignUserRole($data) {
        $admin_id = $this->session->userdata('user')->id;
        
        $this->db->trans_begin();
        //update role
        $this->db->set('user_role', $data['user-role']);
        $this->db->where('id', $data['user']);
        $this->db->update('tbl_users');

        if($data['user-role'] == 2) {
            $sm = $this->getSMByAdmin($admin_id);
            if($sm) {
                $smid = $sm->user_id;
                $smloc = $this->getTeamLeaderByManagerLocation($data['mandal']);
                if($smloc) {
                    foreach($smloc as $u) {
                        $this->db->set('parent_id', $data['user']);
                        $this->db->where('user_id', $u->id);
                        $this->db->update('tbl_team_mng');
                    }
                }
            }else {
                $smid = null;
            }
            
            $m_data = array(
                'user_id' => $data['user'],
                'parent_id' => $smid,
                'location' => $data['mandal'],
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id

            );
            $this->db->insert('tbl_team_mng', $m_data);
        }elseif($data['user-role'] == 18) {
            $v_loc = $this->getVillageByPs($data['pollingstation'])->village_id;
            $man = $this->getManByLocation($data['mandal']);
            if($man) {
                $mid = $man->id;
            }else {
                $mid = null;
            }
            
            $b_data = array(
                'user_id' => $data['user'],
                'parent_id' => $mid,
                'location' => $v_loc,
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_mng', $b_data);

            $p_data = array(
                'user_id' => $data['user'],
                'ps_id' => $data['pollingstation'],
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_ps', $p_data);
        }elseif($data['user-role'] == 3) {
            $v_loc = $this->getVillageByPs($data['pollingstation'])->village_id;
            $bp = $this->input->post('bpuser');

            $b_data = array(
                'user_id' => $data['user'],
                'parent_id' => $bp,
                'location' => $v_loc,
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_mng', $b_data);

            $p_data = array(
                'user_id' => $data['user'],
                'ps_id' => $data['pollingstation'],
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_ps', $p_data);
        }

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
        
        
    }
	 
	//contestant
	public function getContestant() {
       $this->db->select('c.party_id,c.id,c.contestants_name,c.age,l.name , c.contestant_photo, p.party_name, p.party_icon,p.party_slug');
        $this->db->from('tbl_cantestants as c');
		$this->db->join('tbl_party as p', 'c.party_id = p.id');
		$this->db->join('tbl_locations as l', 'c.constitution_id = l.id');
		$this->db->where('c.delete_status', 1);
         $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function addContestant($data) {
		$addconst = array(
						'contestants_name' => $data['contestant'],
						'constitution_id' => '3545',
						'age'=> $data['age'],
						'party_id' => $data['party'],
						'contestant_photo' => $data['photo']
						);
		$contestant=$this->db->insert('tbl_cantestants', $addconst);
		if($contestant) {
            return $contestant;
        }else {
            return false;
        }
	}
	
	public function getSmartMedias() {
        $this->db->select('id,media_path,publish_date,status');
        $this->db->from('tbl_smart_media');
		$this->db->where('delete_status', 1);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function addSmartMedia($data) {
		$addconst = array(
						'media_path' => 'smart-media/'.$data['photo'],
						'publish_date' => $data['publishdate'],
						'status'=> $data['status']
						);
		$smtmedia=$this->db->insert('tbl_smart_media', $addconst);
		if($smtmedia) {
            return $smtmedia;
        }else {
            return false;
        }
    }
    
	public function getPollingAgent() {
        $this->db->select('pa.id,pa.first_name,pa.last_name,pa.mobile,pa.photo,ps.ps_name,ps.ps_no');
        $this->db->from('tbl_polling_agent as pa');
		$this->db->join('tbl_ps as ps', 'pa.ps_no = ps.id');
		$this->db->where('delete_status', 1);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function addPollingAgent($data) {
		$addpa = array(
						'first_name' => $data['firstname'],
						'last_name' => $data['lastname'],
						'ps_no'=> $data['pollingno'],
						'mobile'=> $data['mobile'],
						'photo'=> $data['photo']
						);
		$psa=$this->db->insert('tbl_polling_agent', $addpa);	
		if($psa) {
            return $psa;
        }else {
            return false;
        }
    }
    
	public function getBulkSms() {
        $this->db->select('bs.id,bs.language,bs.role,bs.message,lp.value');
        $this->db->from('tbl_bulk_sms as bs');
		$this->db->join('tbl_lookup as lp', 'lp.id = bs.role');
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function insertBulkSms($data) {
		$addsms = array(
						'language' => $data['language'],
						'role' => $data['userrole'],
						'message'=> $data['message']
						);
		$bulksms=$this->db->insert('tbl_bulk_sms', $addsms);	
		if($bulksms) {
            return $bulksms;
        }else {
            return false;
        }
    }
    
	public function getSmsLimit() {
        $this->db->select('s.id,s.role,s.smslimit,lp.value');
        $this->db->from('tbl_sms_limit as s');
		$this->db->join('tbl_lookup as lp', 'lp.id = s.role');
		$this->db->where('s.delete_status', 1);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function insertSmsLimit($data) {
		$addsms = array(
						'role' => $data['userrole'],
						'smslimit'=> $data['limit']
						);
		$smslimit=$this->db->insert('tbl_sms_limit', $addsms);
		if($smslimit) {
            return $smslimit;
        }else {
            return false;
        }
    }
    
	public function getSeniormanagerByConst($location) {
		$this->db->select('user_id');
		$this->db->from('tbl_team_mng');
		$this->db->where('t.location', $location);
		$result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
	}
	
	public function getAllEvents() {
        $this->db->select('e.id,e.event_type,e.event_name,e.event_date,e.event_description,e.event_img,lp.value');
        $this->db->from('tbl_events as e');
		$this->db->join('tbl_lookup as lp', 'lp.id = e.event_type');
		$this->db->where('e.delete_status', 1);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function insertEvents($data) {
		$addsms = array(
						'event_type' => $data['eventtype'],
						'event_name'=> $data['eventname'],
						'event_date'=> $data['eventdate'],
						'event_description'=> $data['description'],
						'user_id'=> 1,
						'event_img'=> $data['photo']
						);
		$events=$this->db->insert('tbl_events', $addsms);
		if($events) {
            return $events;
        }else {
            return false;
        }
	}
    
    public function insertBranding($data) {
		$addsms = array(
						'location'=>'430',
						'user_id'=> 1,
						'brand_img'=> $data['photo']
						);
		$events=$this->db->insert('tbl_branding', $addsms);
		if($events) {
            return $events;
        }else {
            return false;
        }
    }
    
	public function allocateGroupTaskBySeniorManager($data,$group) {
		$this->db->trans_begin();
		$id = $this->session->userdata('user')->id;
		$taskname = $data['taskname'];
		$gdatefrom = date('Y-m-d', strtotime($data['datefrom']));
		$gdateto = date('Y-m-d', strtotime($data['dateto']));
		$priority = $data['priority'];
		$receiver = $data['receiver'];
		$taskdescription = $data['editor_contents'];
		
		$allocatetask = array(
						'task_name' => $taskname,
						'task_description'=> $taskdescription,
						'date_from' => $gdatefrom,
						'date_to' => $gdateto,
						'priority' => $priority,
						'task_group' => $group,
						'created_by' =>1
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

	public function getAllEventTasks() {
        $this->db->select('t.id, t.task_name ,t.task_description ,t.priority,lp.value, t.date_from ,t.date_to , t.task_group ,');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
		$this->db->join('tbl_lookup as lp', 'lp.id = tm.receiver_id');
		$this->db->where('t.delete_status', 1);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	/** Delete and Deactivate **/
	/*Sms Limit*/
	public function deleteSmsLimit($id) {
        $this->db->trans_begin();
        $update_status = array(
            'delete_status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_sms_limit', $update_status);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	
	/*Events*/
	public function delEvents($id) {
        $this->db->trans_begin();
        $update_status = array(
            'delete_status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_events', $update_status);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	
	/*Tasks*/
	public function delTasks($id) {
        $this->db->trans_begin();
        $update_status = array(
            'delete_status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_tasks', $update_status);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	
	/*Smart Media*/
	public function delSmartMediaa($id) {
        $this->db->trans_begin();
        $update_status = array(
            'delete_status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_smart_media', $update_status);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	
	/*Contestant*/
	public function delContestants($id) {
        $this->db->trans_begin();
        $update_status = array(
            'delete_status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_cantestants', $update_status);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	
	/*Polling Agent*/
	public function delPollingAgent($id) {
        $this->db->trans_begin();
        $update_status = array(
            'delete_status' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_polling_agent', $update_status);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }
	//REGISTRATION
	public function getTotalRegistration() {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_citizen_mng as cm', 't3.user_id = cm.user_id');
		$this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        //$this->db->where('t1.parent_id', $id);
		$this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalMandalBySM(){
		$this->db->select('u.id, u.first_name,u.last_name ,l.name , u.gender, u.mobile, u.user_role');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 't.user_id = u.id');
        $this->db->join('tbl_locations as l', 't.location = l.id');
		$this->db->join('tbl_const_location as cl', 'l.id = cl.location_id');
        //$this->db->where('t.parent_id', $id);
		$this->db->where('u.user_role', 2);
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

	public function getTotalTeamLeaderBySM() {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, u.user_role, l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_users as u', 't2.user_id = u.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
       // $this->db->where('t1.parent_id', $id);
		$this->db->where('u.user_role', 18);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalCoordinatorBySM() {
        $this->db->select('u.id, u.first_name,u.last_name, u.gender, u.mobile, u.user_role, l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_users as u', 't3.user_id = u.id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        //$this->db->where('t1.parent_id', $id);
		$this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
	
	public function getTotalVolunteerBySM() {
        $this->db->select('v.id,v.user_id, v.firstname, v.lastname ,l1.name,v.gender, v.mobile , l1.name as village, l2.name as Mandal');
        $this->db->from('tbl_team_mng as t1');
		$this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id');
		$this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id');
		$this->db->join('tbl_voters as v', 't3.user_id = v.user_id');
		$this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
		$this->db->join('tbl_locations as l1', 't2.location = l1.id');
		$this->db->join('tbl_locations as l2', 't1.location = l2.id');
        //$this->db->where('t1.parent_id', $id);
		$this->db->where('ct.user_role', 46);
        $result = $this->db->get();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

	public function getAllEventss() {
        $this->db->select('t.id, t.task_name ,t.task_description , t.date_from ,t.date_to , t.task_group, tm.receiver_id');
		$this->db->from('tbl_tasks as t');
		$this->db->join('tbl_tasks_mng as tm', 'tm.task_id = t.id');
		$this->db->where('t.task_group', 62);
		$this->db->where('t.date_from >= CURRENT_DATE', NULL, FALSE);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $events = $result->result();
			foreach($events as $e) {
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
			}
            return $events;
        }else {
            return false;
        }
    }

    /* Get all users who are active and not downloaded an app */
    public function getTLByApp($id) {
        $this->db->select('u.id as uid, u.first_name, u.last_name, u.mobile');
        $this->db->from('tbl_team_mng as t1'); //SM
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id'); //Manager
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id'); //TL
        $this->db->join('tbl_team_ps as tp', 't3.user_id = tp.user_id'); // Ps assigned
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
        $this->db->where('u.status', 1);
        $this->db->where('t3.status', 1);
        $this->db->where('tp.status', 1);
        $this->db->where('u.user_role', 18);
        $res = $this->db->get();
        if($res->num_rows() > 0) {
            $users = $res->result();
            foreach($users as $k=> $v) {
                $this->_sdb->select('d.id as did, d.status');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $v->uid);
                $res_d = $this->_sdb->get();

                if($res_d->num_rows() > 0) {
                    $user_d = $res_d->row();
                    if($user_d->status == 1) {
                        unset($users[$k]);
                    }
                }
            }
        
            return $users;    
        }else {
            return false;
        }
    }

    public function getCoordByApp($id) {
        $this->db->select('u.id as uid, u.first_name, u.last_name, u.mobile');
        $this->db->from('tbl_team_mng as t1'); //SM
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id'); //Manager
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id'); //TL
        $this->db->join('tbl_team_mng as t4', 't4.parent_id = t3.user_id'); //Coordinator
        $this->db->join('tbl_team_ps as tp', 't4.user_id = tp.user_id'); // Ps assigned
        $this->db->join('tbl_users as u', 't4.user_id = u.id');
        $this->db->where('u.status', 1);
        $this->db->where('t4.status', 1);
        $this->db->where('tp.status', 1);
        $this->db->where('u.user_role', 3);
        $res = $this->db->get();
        if($res->num_rows() > 0) {
            $users = $res->result();
            foreach($users as $k=> $v) {
                $this->_sdb->select('d.id as did, d.status');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $v->uid);
                $res_d = $this->_sdb->get();

                if($res_d->num_rows() > 0) {
                    $user_d = $res_d->row();
                    if($user_d->status == 1) {
                        unset($users[$k]);
                    }
                }
            }
            return $users;    
        }else {
            return false;
        }
    }

    public function getBOByApp($id) {
        $this->db->select('u.id as uid, u.first_name, u.last_name, u.mobile');
        $this->db->from('tbl_team_mng as t1'); //SM
        $this->db->join('tbl_team_mng as t2', 't2.parent_id = t1.user_id'); //Manager
        $this->db->join('tbl_team_mng as t3', 't3.parent_id = t2.user_id'); //TL
        $this->db->join('tbl_team_ps as tp', 't3.user_id = tp.user_id'); // Ps assigned
        $this->db->join('tbl_users as u', 't3.user_id = u.id');
        $this->db->where('u.status', 1);
        $this->db->where('t3.status', 1);
        $this->db->where('tp.status', 1);
        $this->db->where('u.user_role', 55);
        $this->db->group_by('tp.user_id');
        $res = $this->db->get();
        if($res->num_rows() > 0) {
            $users = $res->result();
            foreach($users as $k=> $v) {
                $this->_sdb->select('d.id as did, d.status');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $v->uid);
                $res_d = $this->_sdb->get();

                if($res_d->num_rows() > 0) {
                    $user_d = $res_d->row();
                    if($user_d->status == 1) {
                        unset($users[$k]);
                    }
                }
            }
        
            return $users;    
        }else {
            return false;
        }
    }

    public function saveAppDownload($data) {
        $id = $this->session->userdata('user')->id;
        $this->_sdb->trans_begin();
        if($data['user-group'] == 'all') {
            if($data['app'] == 78) {
                $users = $this->getCoordByApp($id);    
            }elseif($data['app'] == 79) {
                $users = $this->getTLByApp($id); 
            }elseif($data['app'] == 81) {
                $users = $this->getBOByApp($id);
            }

            if($users) {
                foreach($users as $user) {
                    $this->_sdb->select('id');
                    $this->_sdb->from('tbl_download');
                    $this->_sdb->where('user_id', $user->uid);
                    $isSent = $this->_sdb->get();
                    if($isSent->num_rows() > 0) {
                        $user_u = array(
                            'status' => 0
                        );
                        $this->_sdb->where('user_id', $user->uid);
                        $this->_sdb->update('tbl_download', $user_u);
                    }else {
                        $user_d = array(
                            'user_id' => $user->uid,
                            'app_id' => $data['app']
                        );
                        $this->_sdb->insert('tbl_download', $user_d);
                    }
                    
                }
            }
        }else {
            $this->_sdb->select('id');
            $this->_sdb->from('tbl_download');
            $this->_sdb->where('user_id', $user->uid);
            $isSent = $this->_sdb->get();
            if($isSent->num_rows() > 0) {
                $user_u = array(
                    'status' => 0
                );
                $this->_sdb->where('user_id', $user->uid);
                $this->_sdb->update('tbl_download', $user_u);
            }else {
                $user_d = array(
                    'user_id' => $data['user'],
                    'app_id' => $data['app']
                );
                $this->_sdb->insert('tbl_download', $user_d);
            }
            
        }
        if($this->_sdb->trans_status() === FALSE)  {
            $this->_sdb->trans_rollback();
            return false;
        }else {
            $this->_sdb->trans_commit();
            return true;
        }
    }

    public function getUser($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile');
        $this->db->from('tbl_users as u');
        $this->db->where('u.id', $id);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
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

    public function getBPBypsid($id) {
        $this->db->select('u.id, u.first_name, u.last_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('tp.status', 1);
        $this->db->where('u.status', 1);
        $result = $this->db->get()->result();
        return $result;
    }

    public function getAllUsersByPS($id, $filters = array()) {
        //ps members
        $this->db->select('u.first_name, u.last_name, u.email, u.mobile, lu.value as user_role, p.ps_no, p.ps_name, l.name as location');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'p.id = tp.ps_id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->join('tbl_team_mng as tm', 'tm.user_id = u.id');
        $this->db->join('tbl_locations as l', 'l.id = tm.location');
        $this->db->where('tp.ps_id', $id);
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->order_by('u.first_name');
        return $this->db->get();    
    }

    public function getLocationById($id) {
        $this->db->select('l.id as lid, l.name as location, l.level_id');
        $this->db->from('tbl_locations as l');
        $this->db->where('l.id', $id);
        return $this->db->get()->row();
    }

    public function getPSById($id) {
        $this->db->select('p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_ps as p');
        $this->db->where('p.id', $id);
        return $this->db->get()->row();
    }

    public function getManagerByMandal($id) {
        //manager
        $this->db->select('u.first_name, u.last_name, u.email, u.mobile, lu.value as user_role, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->join('tbl_locations as l', 'l.id = tm.location');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 137);
        return $this->db->get();
        
    }

    public function getAllVotersByPS($id, $filters = array()) {
        $this->db->select('v.firstname, v.lastname, v.voter_id, v.mobile as vmobile, u.first_name, u.last_name, u.mobile as umobile, c.created_at as r_date');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_users as u', 'v.user_id = u.id', 'left');
        $this->db->join('tbl_citizen_mng as c', 'c.citizen_id = v.id', 'left');
        $this->db->where('v.ps_no', $id);
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->order_by('c.created_at', 'desc');
        
        return $this->db->get();
    }

    public function getUserByRole($role) {
        //$user_id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id');
        $this->db->where('u.status', 1);
        $this->db->where('t.status', 1);
        $this->db->where('u.user_role', $role);
        $result = $this->db->get()->result();
        if($result) {
            return $result;    
        }else {
            return false;
        }
    }

    public function groupsms(array $users, $message) {
        $id = $this->session->userdata('user')->id;
        $this->db->trans_begin();
        
        foreach($users as $u) {
            $data = array(
                'user_id' => $id,
                'receiver_id' => $u->id,
                'mobile' => $u->mobile,
                'text_message' => $message
            );
            $this->db->insert('tbl_sms_details', $data);
        }

        if($this->db->trans_status() === FALSE)  {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function getAllUsersByMandal($mandal, $role = false, $filters = array()) {
        
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.user_role as urole, u.mobile, lu.value as user_role, p.ps_no, p.ps_name, l2.name as location');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id ');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id', 'left');
        
        if($role) {
            $this->db->join('tbl_users as u', 'tp.user_id = u.id  and u.status = 1 and u.user_role = '.$role, 'left');
            
        }else {
            $this->db->join('tbl_users as u', 'tp.user_id = u.id and u.status = 1', 'left');
        }

        $this->db->join('tbl_lookup as lu', 'lu.id = u.user_role', 'left');
        $this->db->where('l1.id', $mandal);
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->group_by('p.ps_no, u.id');
        $this->db->order_by('p.ps_no');
        $this->db->order_by('u.first_name', 'desc');
        
        return $this->db->get();
        // echo '<pre>'; print_r($this->db->last_query()); exit;
    }

    public function getRoleById($id) {
        $this->db->select('id, value');
        $this->db->from('tbl_lookup');
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }

    public function getPSMemberByConstituencyRole($id, $role = false) {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, u.user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        if($role) {
            $this->db->where('u.user_role', $role);
        }
        $this->db->where('u.status', 1);
        $this->db->where('cl.parent_id', $id);
        $result = $this->db->get();
        return $result;
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

    public function getPSCountByMandal($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_locations as l1'); //mandal
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
        $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
        $this->db->where('l1.id', $id);
        return $this->db->get()->num_rows();
    }

    public function getAppDownloadCount($mandal, $role) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.location', $mandal);
        $this->db->where('u.user_role', $role);
        $res = $this->db->get();
        $downloads = 0;
        if($res->num_rows() > 0) {
            $u_data = $res->result();
            foreach($u_data as $u) {
                $this->_sdb->select();
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $u->id);
                $this->_sdb->where('d.status', 1);
                $downloads += $this->_sdb->get()->num_rows();
            }
        }
        return $downloads;
    }

    public function getPsAssignedByUser($id) {
        $this->db->select('p.id as pid, p.ps_no, p.ps_name, l1.id as vid, l1.name as village, l2.id as mid, l2.name as mandal');
        $this->db->from('tbl_team_ps as ts');
        $this->db->join('tbl_ps as p', 'ts.ps_id = p.id');
        $this->db->join('tbl_locations as l1', 'p.village_id = l1.id'); //village
        $this->db->join('tbl_locations as l2', 'l2.id = l1.parent_id'); //mandal
        $this->db->where('ts.user_id', $id);
        $this->db->where('ts.status', 1);
        $res = $this->db->get();
        if($res->num_rows() > 0) {
            return $res->result();
        }else {
            return false;
        }
    }

    public function getAllOtp() {
        $this->_sdb->select('o.user_id, from_base64(o.otp_code) as otp, o.created_at');
        $this->_sdb->from('tbl_otp as o');   
        $this->_sdb->where('o.created_at >= DATE_ADD(CURDATE(), INTERVAL - 3 DAY)', NULL, FALSE);
        $this->_sdb->order_by('o.created_at', 'desc');
        $res = $this->_sdb->get();
        if($res->num_rows() > 0) {
            $o_data = $res->result();
            foreach($o_data as $u) {
                $this->db->select('u.first_name, u.last_name, u.mobile, lu.value as user_role');
                $this->db->from('tbl_users as u');
                $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                $this->db->where('u.id', $u->user_id);
                $res_u = $this->db->get()->row();
                $u->name = $res_u->first_name . ' ' .$res_u->last_name;
                $u->mobile = $res_u->mobile;
                $u->role = $res_u->user_role;
            }
            return $o_data;
        }else {
            return false;
        }  
         
    }
          
    // public function getAllDownload($appid) {
    //      $this->_sdb->select('dw.app_id ,dw.download_at');
    //      $this->_sdb->from('tbl_download as dw');
    //      $this->_sdb->where('dw.status', 1);
    //      $this->_sdb->where('dw.app_id', $appid);
    //      $result = $this->_sdb->get();
    //      return $result;
    // }

    public function getAppDownloadStatus($id, $role) {
        $this->_sdb->select('d.id, d.user_id, d.status, d.download_at, d.created_at');
        $this->_sdb->from('tbl_download as d');
        $this->_sdb->where('d.user_id', $id);
        if($role == 3) {
            $this->_sdb->where('d.app_id', 78);
        }elseif($role == 18) {
            $this->_sdb->where('d.app_id', 79);
        }elseif($role == 55) {
            $this->_sdb->where('d.app_id', 81);
        }
        $result = $this->_sdb->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getAllDownload($appid) {
        $this->_sdb->select('dw.app_id ,dw.download_at');
        $this->_sdb->from('tbl_download as dw');
        $this->_sdb->where('dw.status', 1);
        $this->_sdb->where('dw.app_id',$appid);
       $result = $this->_sdb->get();
       return $result;
    }
   
    public function getDownloadDetails($appid) {
        $this->_sdb->select('dw.user_id,dw.status,dw.app_id ,dw.download_at,dw.status');
        $this->_sdb->from('tbl_download as dw');
        $this->_sdb->where('dw.app_id',$appid);
        $this->_sdb->where('dw.status',1);
        $res = $this->_sdb->get();
       if($res->num_rows() > 0) {
           $d_data = $res->result();
           foreach($d_data as $u) {
               $this->db->select('u.first_name, u.last_name, u.mobile, lu.value as user_role');
               $this->db->from('tbl_users as u');
               $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
               $this->db->where('u.id', $u->user_id);
               $res_u = $this->db->get()->row();
               $u->name = $res_u->first_name . ' ' .$res_u->last_name;
               $u->mobile = $res_u->mobile;
               $u->role = $res_u->user_role;
           }
           
           return $d_data;
       }else {
           return false;
       }  
        
    }
   
    public function votersCountByBP($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
        $this->db->join('tbl_citizen_mng as ct', 'v.id = ct.citizen_id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('ct.user_role', 17);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function spCountByBP($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_users as u', 'u.id = t.user_id');
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('t.parent_id', $id);
        $result = $this->db->get();
        return $result->num_rows(); 
    }

    public function votersCountBySP($id, $filters = array()) {
        $this->db->select('v.id');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->where('cm.user_id', $id);
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getBPPerformanceByMandal($id) {
        
        if($id == 'all') {
            $lid = $this->session->userdata('user')->location_id;
            $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
            $this->db->from('tbl_const_location as cl');
            $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
            $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
            $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
            $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
            $this->db->where('u.user_role', 18);
            $this->db->where('u.status', 1);
            $this->db->where('cl.parent_id', $lid);
            $this->db->order_by('p.ps_no');
            $result = $this->db->get();
            
            if($result->num_rows() > 0) {
                $bp_user = $result->result();
            }
        }else {
            $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
            $this->db->from('tbl_locations as l1'); //mandal
            $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
            $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
            $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
            $this->db->where('l1.id', $id);
            $this->db->where('u.user_role', 18);
            $this->db->where('u.status', 1);
            $this->db->order_by('p.ps_no');
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                $bp_user = $result->result();
                // echo '<pre>'; print_r($bp_user); exit;
            }
        }

        if($bp_user && count($bp_user) > 0) {
            foreach($bp_user as $u) {
                //download status
                $app_d = $this->getAppDownloadStatus($u->id, 18);
                if($app_d) {
                    if($app_d->status == 1) {
                        $u->downloadstatus = 'Yes';
                    }else {
                        $u->downloadstatus = 'No';
                    }
                }else {
                    $u->downloadstatus = 'No';
                }
                
                //sp count
                $u->spcount = $this->spCountByBP($u->id);
                
                //voters count
                $u->voterscount = $this->votersCountByBP($u->id);
            }
            return $result;
        }else {
            return false;
        }
        
    }

    public function getSPPerformanceByMandal($id) {
        if($id == 'all') {
            $lid = $this->session->userdata('user')->location_id;
            $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
            $this->db->from('tbl_const_location as cl');
            $this->db->join('tbl_locations as l1', 'l1.id = cl.location_id'); //mandal
            $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
            $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
            $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
            $this->db->where('u.user_role', 3);
            $this->db->where('u.status', 1);
            $this->db->where('cl.parent_id', $lid);
            $this->db->order_by('p.ps_no');
            $result = $this->db->get();
            
            if($result->num_rows() > 0) {
                $sp_user = $result->result();
            }
        }else {
            $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, lu.value as user_role, l2.name as village, l1.name as Mandal, p.ps_no, p.ps_name');
            $this->db->from('tbl_locations as l1'); //mandal
            $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id'); //village
            $this->db->join('tbl_ps as p', 'p.village_id = l2.id');
            $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
            $this->db->join('tbl_users as u', 'tp.user_id = u.id');
            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
            $this->db->where('l1.id', $id);
            $this->db->where('u.user_role', 3);
            $this->db->where('u.status', 1);
            $this->db->order_by('p.ps_no');
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                $sp_user = $result->result();
                // echo '<pre>'; print_r($bp_user); exit;
            }
        }

        if($sp_user && count($sp_user) > 0) {
            foreach($sp_user as $u) {
                //download status
                $app_d = $this->getAppDownloadStatus($u->id, 3);
                if($app_d) {
                    if($app_d->status == 1) {
                        $u->downloadstatus = 'Yes';
                    }else {
                        $u->downloadstatus = 'No';
                    }
                }else {
                    $u->downloadstatus = 'No';
                }
                
                //voters count
                $u->voterscount = $this->votersCountBySP($u->id);

                //positive
                $u->positivecount = $this->votersCountBySP($u->id, array('v.voter_status' => 12));

                //negative
                $u->negativecount = $this->votersCountBySP($u->id, array('v.voter_status' => 13));

                //neutral
                $u->neutralcount = $this->votersCountBySP($u->id, array('v.voter_status' => 14));
            }
            return $result;
        }else {
            return false;
        }
    }

    public function getQuestion($role) {
        $this->db->select('q.id, q.user_role,q.report,q.question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.user_role', $role);
        $result = $this->db->get()->result();
        return $result;
    }
}