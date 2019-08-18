<?php
class EmailModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }


    public function emailsList($userole){
                   $this->db->select('*');
                   $this->db->from('tbl_users');
                   $this->db->where('user_role',$userole);
                   $this->db->order_by('id', 'DESC');
                   return $this->db->get()->result();

    }

    public function enterEmailContent($data){

             

                $this->db->insert('tbl_mail_content',$data);
                return $this->db->insert_id();
    }


    public function filesUpload($data){
         $this->db->insert('tbl_files_data',$data);
                //return $this->db->insert_id();
        
    }

    public function IndividualEmailEnter($senderid,$emailId,$cc,$bcc,$insertval){
                 $emaildata=array(
                    'sender_id'=>$senderid,
                    'receiver_id'=>$emailId,
                    'cc'=>$cc,
                    'bcc'=>$bcc,
                    'group_id'=>0,
                    'individual_id'=>1,
                    'flag'=>1,
                    'trash'=>0,
                    'junk'=>0,
                    'mail_id'=>$insertval
                );
             

                $this->db->insert('tbl_mail_conversation_members',$emaildata);
                
    }

    public function inboxmails($id){
              //$sql="SELECT * FROM `tbl_mail_conversation_members` join tbl_users on tbl_mail_conversation_members.sender_id=tbl_users.id JOIN tbl_mail_content on tbl_mail_conversation_members.mail_id=tbl_mail_content.mail_id where tbl_mail_conversation_members.receiver_id=".$id;

              $sql="SELECT * FROM `tbl_mail_conversation_members` join tbl_users on tbl_mail_conversation_members.sender_id=tbl_users.id JOIN tbl_mail_content on tbl_mail_conversation_members.mail_id=tbl_mail_content.mail_id where tbl_mail_conversation_members.receiver_id=$id or tbl_mail_conversation_members.cc  LIKE '%$id%' OR tbl_mail_conversation_members.bcc  LIKE '%$id%' order by conversation_id desc";
             return $this->db->query($sql)->result();


    }

    public function trashmails($id){
              $sql="SELECT * FROM `tbl_mail_conversation_members` join tbl_users on tbl_mail_conversation_members.sender_id=tbl_users.id JOIN tbl_mail_content on tbl_mail_conversation_members.mail_id=tbl_mail_content.mail_id where tbl_mail_conversation_members.receiver_id=$id and where tbl_mail_conversation_members.trash=1";
              return $this->db->query($sql)->result();


    }

    public function sentFolderData($id){
              $sql="SELECT * FROM `tbl_mail_conversation_members` join tbl_users on tbl_mail_conversation_members.sender_id=tbl_users.id JOIN tbl_mail_content on tbl_mail_conversation_members.mail_id=tbl_mail_content.mail_id where tbl_mail_conversation_members.sender_id=".$id;
             return $this->db->query($sql)->result();


    }

    public function flag($value){
              $this->db->set('flag',0); //value that used to update column  
              $this->db->where('conversation_id', $value); //which row want to upgrade  
              $this->db->update('tbl_mail_conversation_members');
    }

    public function important($datavalue){
              $this->db->set('starred',1); //value that used to update column  
              $this->db->where('conversation_id', $datavalue); //which row want to upgrade  
              $this->db->update('tbl_mail_conversation_members');
    }





   public  function particularContentdata($value){
              

              

            $sql="SELECT * FROM `tbl_mail_conversation_members` join tbl_users on tbl_mail_conversation_members.sender_id=tbl_users.id JOIN tbl_mail_content on tbl_mail_conversation_members.mail_id=tbl_mail_content.mail_id where tbl_mail_conversation_members.conversation_id=".$value;
             return $this->db->query($sql)->result();
          
          

       }

    public function trashData($datavalue){

           $this->db->set('trash', 1); //value that used to update column  
           $this->db->where('conversation_id', $datavalue); //which row want to upgrade  
           $this->db->update('tbl_mail_conversation_members');
                
                       
        
    }

     public function folderdata($datavalue){

           $this->db->set('folder', 1); //value that used to update column  
           $this->db->where('conversation_id', $datavalue); //which row want to upgrade  
           $this->db->update('tbl_mail_conversation_members');
                
                       
        
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
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('u.created_by', $user_id);
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

    
    public function assignUserRole($id, $role) {
        $this->db->set('user_role', $role);
        $this->db->where('id', $id);
        $this->db->update('tbl_users');
        $result = $this->db->affected_rows();
        if($result > 0) {
            return true;
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
        $this->db->where('u.status', 1);
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
        $this->db->where('tm.parent_id', $id);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function votersByStatusTL($id, $status) {
        $this->db->select('v.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_voters as v', 'tm.user_id = v.user_id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('v.voter_status', $status);
        $result = $this->db->get();
        return $result->num_rows(); 
    }

    public function votersByUser($id) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
        $this->db->where('v.user_id', $id);
        $result = $this->db->get();
        return $result->num_rows();
        
    }

    public function votersByStatusCr($id, $status) {
        $this->db->select('v.id');
        $this->db->from('tbl_voters as v');
        $this->db->where('v.user_id', $id);
        $this->db->where('v.voter_status', $status);
        $result = $this->db->get();
        return $result->num_rows();
        
    }

    public function getEvents($id) {
        $this->db->select('e.event_name, e.event_description, e.event_date, e.event_img, u.first_name, u.last_name');
        $this->db->from('tbl_events as e');
        $this->db->join('tbl_users as u', 'e.user_id = u.id');
        $this->db->where('e.event_type', $id);
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

    public function getVotersByManager($id, $filters = array()) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status, lu2.value as local_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_team_mng as t2', 't1.user_id = t2.parent_id');
        $this->db->join('tbl_team_mng as t3', 't2.user_id = t3.parent_id');
        $this->db->join('tbl_voters as v', 'v.user_id = t3.user_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->join('tbl_lookup as lu2', 'v.local_status = lu2.id');
        $this->db->where('t1.user_id', $id);
		
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
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
            group by l2.name, v.user_id) as c group by c.name order by total desc limit 10");
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
            group by l2.name, v.user_id) as c group by c.name order by positive desc limit 10");
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

	public function new_launch_slide_articles() {
	      $this->db->select('*');
                  $this->db->from('tbl_users');
                  $this->db->order_by('id', 'DESC');
	      return  $query = $this->db->get();
		  echo $this->db->last_query();exit;
     
	}
}