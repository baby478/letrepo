<?php
class DBoothModel extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getmembersByCoordinator($id) {
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('c1.citizen_id, c1.parent_id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, d.attend, d.created_at as time, p1.ps_no, p1.ps_name');
            $this->db->from('tbl_ps_member as p');
            $this->db->join('tbl_citizen_mng as c', 'p.volunteer_id = c.citizen_id');
            $this->db->join('tbl_citizen_mng as c1', 'c1.user_id = c.user_id');
            $this->db->join('tbl_voters as v', 'c1.citizen_id = v.id');
            $this->db->join('tbl_ps as p1', 'p1.id = v.ps_no');
            $this->db->join('tbl_digital_booth as d', 'd.citizen_id = c1.citizen_id', 'left');
            $this->db->where('p.user_id', $id);
            $this->db->where('c1.parent_id != p.volunteer_id');
            $this->db->where('c1.citizen_id != p.volunteer_id');
            $this->db->order_by('d.created_at', 'asc');
            $result = $this->db->get();
            
        }
        if($user_role == 3) {
            $this->db->select('c.citizen_id, c.parent_id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, d.attend, d.created_at as time, p1.ps_no, p1.ps_name');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
            $this->db->join('tbl_ps as p1', 'p1.id = v.ps_no');
            $this->db->join('tbl_digital_booth as d', 'd.citizen_id = c.citizen_id', 'left');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.parent_id !=', $id);
            $this->db->order_by('d.created_at', 'asc');
            $result = $this->db->get();
        }
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
        
    }

    public function getUser($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('u.id', $id);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getMembers($id) {
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('c1.citizen_id, c1.parent_id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, d.attend, d.created_at as time, p1.ps_no, p1.ps_name');
            $this->db->from('tbl_ps_member as p');
            $this->db->join('tbl_citizen_mng as c', 'p.volunteer_id = c.citizen_id');
            $this->db->join('tbl_citizen_mng as c1', 'c1.parent_id = c.citizen_id');
            $this->db->join('tbl_voters as v', 'c1.citizen_id = v.id');
            $this->db->join('tbl_ps as p1', 'v.ps_no = p1.id');
            $this->db->join('tbl_digital_booth as d', 'c1.citizen_id = d.citizen_id', 'left');
            $this->db->where('p.user_id', $id);
            $this->db->where('c.parent_id != p.volunteer_id');
            $this->db->order_by('d.created_at', 'asc');
            $this->db->order_by('v.firstname');
            $result = $this->db->get();
        }
        if($user_role == 3) {
            $this->db->select('c.citizen_id, c.parent_id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, d.attend, d.created_at as time, p1.ps_no, p1.ps_name');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
            $this->db->join('tbl_ps as p1', 'v.ps_no = p1.id');
            $this->db->join('tbl_digital_booth as d', 'c.citizen_id = d.citizen_id', 'left');
            $this->db->where('c.user_role', 17);
            $this->db->where('c.parent_id', $id);
            $this->db->order_by('d.created_at', 'asc');
            $this->db->order_by('v.firstname');
            $result = $this->db->get();
        }
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getdigitalslips($id) {
        $result_data = array();
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, p1.ps_no, p1.ps_name');
            $this->db->from('tbl_ps_member as p');
            $this->db->join('tbl_voters as v', 'p.ps_id = v.ps_no');
            $this->db->join('tbl_ps as p1', 'v.ps_no = p1.id');
            $this->db->where('p.user_id', $id);
            $result = $this->db->get();
            $result_data['slips'] = $result->result();
        }
        if($user_role == 3) {
            // $this->db->select('p.ps_no, p.ps_name');
            // $this->db->from('tbl_team_mng as t');
            // $this->db->join('tbl_ps as p', 't.location = p.village_id');
            // $this->db->where('t.user_id', $id);
            // $ps_count = $this->db->get();
            // if($ps_count->num_rows() > 1) {
            //     $result_data['ps'] = $ps_count->result();
            // }elseif($ps_count->num_rows() == 1) {
            //     $ps_id = $ps_count->row()->ps_no;
            //     $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, p1.ps_no, p1.ps_name');
            //     $this->db->from('tbl_voters as v');
            //     $this->db->join('tbl_ps as p1', 'v.ps_no = p1.id');
            //     $this->db->where('v.ps_no', $ps_id);
            //     $result = $this->db->get();
            //     $result_data['slips'] = $result->result();
            // }
            $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, p1.ps_no, p1.ps_name');
            $this->db->from('tbl_team_ps as tp');
            $this->db->join('tbl_ps as p1', 'p1.id = tp.ps_id');
            $this->db->join('tbl_voters as v', 'v.ps_no = p1.id');
            $this->db->where('tp.user_id', $id);
            $result = $this->db->get();
            $result_data['slips'] = $result->result();
        }
        if(count($result_data) > 0) {
            return $result_data;
        }else {
            return false;
        }
    }

    public function getPollingStation($id) {
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('p.id, p.ps_no, p.ps_name');
            $this->db->from('tbl_ps_member as pm');
            $this->db->join('tbl_ps as p', 'pm.ps_id = p.id');
            $this->db->where('pm.user_id', $id);
            $result = $this->db->get();
        }
        if($user_role == 3) {
            $this->db->select('p.id, p.ps_no, p.ps_name');
            $this->db->from('tbl_team_mng as t');
            $this->db->join('tbl_ps as p', 't.location = p.village_id');
            $this->db->where('t.user_id', $id);
            $result = $this->db->get();
        }
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getPsMember($ps_id, $b_id, $role) {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.mobile, u.photo, u.user_role');
        $this->db->from('tbl_ps_member as p');
        $this->db->join('tbl_users as u', 'p.user_id = u.id');
        $this->db->where('p.ps_id', $ps_id);
        $this->db->where('p.booth_no', $b_id);
        $this->db->where('u.user_role', $role);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getOutStationMembers($id) {
        $result_data = array();
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, os.mobile as outstation_mobile, os.street, l.name as location');
            $this->db->from('tbl_ps_member as p');
            $this->db->join('tbl_voters as v', 'p.ps_id = v.ps_no');
            $this->db->join('tbl_citizen_outstation as os', 'v.id = os.citizen_id');
            $this->db->join('tbl_locations as l', 'os.location = l.id');
            $this->db->where('p.user_id', $id);
            $result = $this->db->get();
            $result_data['os'] = $result->result();
        }
        if($user_role == 3) {
            // $this->db->select('p.ps_no, p.ps_name');
            // $this->db->from('tbl_team_mng as t');
            // $this->db->join('tbl_ps as p', 't.location = p.village_id');
            // $this->db->where('t.user_id', $id);
            // $ps_count = $this->db->get();
            // if($ps_count->num_rows() > 1) {
            //     $result_data['ps'] = $ps_count->result();
            // }elseif($ps_count->num_rows() == 1) {
            //     $ps_id = $ps_count->row()->ps_no;
            //     $this->db->select('v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, os.mobile as outstation_mobile, os.street, l.name as location');
            //     $this->db->from('tbl_voters as v');
            //     $this->db->join('tbl_citizen_outstation as os', 'v.id = os.citizen_id');
            //     $this->db->join('tbl_locations as l', 'os.location = l.id');
            //     $this->db->where('v.ps_no', $ps_id);
            //     $result = $this->db->get();
            //     $result_data['os'] = $result->result();
            // }
            $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, os.mobile as outstation_mobile, os.street, l.name as location');
            $this->db->from('tbl_team_ps as tp');
            $this->db->join('tbl_voters as v', 'tp.ps_id = v.id');
            $this->db->join('tbl_citizen_outstation as os', 'v.id = os.citizen_id');
            $this->db->join('tbl_locations as l', 'os.location = l.id');
            $this->db->where('tp.user_id', $id);
            $result = $this->db->get();
            $result_data['os'] = $result->result();
        }
        if(count($result_data) > 0) {
            return $result_data;
        }else {
            return false;
        }
    }

    public function getDigitalSlipsByPs($id, $ps_id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, p.ps_no, p.ps_name');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_ps as p', 'v.ps_no = p.id');
        $this->db->where('v.ps_no', $ps_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getOutStationMembersByPs($id, $ps_id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, os.mobile as outstation_mobile, os.street, l.name as location');
        $this->db->from('tbl_voters as v');
        $this->db->join('tbl_citizen_outstation as os', 'v.id = os.citizen_id');
        $this->db->join('tbl_locations as l', 'os.location = l.id');
        $this->db->where('v.ps_no', $ps_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function dBAttend($data) {
        $at_data = array(
            'user_id' => $data['user_id'],
            'citizen_id' => $data['citizen_id'],
            'attend' => $data['attend_id']
        );
        $result = $this->db->insert('tbl_digital_booth', $at_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getGroupAttend($id) {
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('c1.citizen_id');
            $this->db->from('tbl_ps_member as p');
            $this->db->join('tbl_citizen_mng as c', 'p.volunteer_id = c.citizen_id');
            $this->db->join('tbl_citizen_mng as c1', 'c1.user_id = c.user_id');
            $this->db->join('tbl_digital_booth as d', 'd.citizen_id = c1.citizen_id');
            $this->db->where('p.user_id', $id);
            $this->db->where('c1.parent_id != p.volunteer_id');
            $this->db->where('c1.citizen_id != p.volunteer_id');
            $this->db->order_by('d.created_at', 'asc');
            $result = $this->db->get();
            
        }
        if($user_role == 3) {
            $this->db->select('c.citizen_id');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
            $this->db->join('tbl_digital_booth as d', 'd.citizen_id = c.citizen_id');
            $this->db->where('c.user_id', $id);
            $this->db->where('c.parent_id !=', $id);
            $this->db->order_by('d.created_at', 'asc');
            $result = $this->db->get();
        }
        return $result->num_rows();
    }

    public function getMemberAttend($id) {
        $user_role = $this->getUser($id)->role;
        if($user_role == 37 || $user_role || 38) {
            $this->db->select('c1.citizen_id');
            $this->db->from('tbl_ps_member as p');
            $this->db->join('tbl_citizen_mng as c', 'p.volunteer_id = c.citizen_id');
            $this->db->join('tbl_citizen_mng as c1', 'c1.parent_id = c.citizen_id');
            $this->db->join('tbl_digital_booth as d', 'c1.citizen_id = d.citizen_id');
            $this->db->where('p.user_id', $id);
            $this->db->where('c.parent_id != p.volunteer_id');
            $this->db->order_by('d.created_at', 'asc');
            $result = $this->db->get();
        }
        if($user_role == 3) {
            $this->db->select('c.citizen_id');
            $this->db->from('tbl_citizen_mng as c');
            $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
            $this->db->join('tbl_digital_booth as d', 'c.citizen_id = d.citizen_id');
            $this->db->where('c.user_role', 17);
            $this->db->where('c.parent_id', $id);
            $this->db->order_by('d.created_at', 'asc');
            $result = $this->db->get();
        }
        return $result->num_rows();
    }

    public function observerCountExists($user_id) {
        $this->db->select('id');
        $this->db->where('user_id', $user_id);
        $result = $this->db->get('tbl_negative_count')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }
    public function saveNegativeCount($data) {
        $user_id = $data['user_id'];
        $ob_exists = $this->observerCountExists($user_id);
        if($data['party_id'] == 1) {
            $party_column = 'party_one';
        }elseif($data['party_id'] == 2) {
            $party_column = 'party_two';
        }elseif($data['party_id'] == 3) {
            $party_column = 'party_three';
        }elseif($data['party_id'] == 4) {
            $party_column = 'party_four';
        }
        if($ob_exists) {
            // $update_d = array(
            //     $party_column => ($party_column + 1)
            // );
            $query = "UPDATE tbl_negative_count SET " . $party_column . " = " . $party_column . " + 1 WHERE user_id = " .$user_id; 
             
            $result = $this->db->query($query);
        }else {
            //echo json_encode('to be saved'); exit;
            $insert_d = array(
                'user_id' => $user_id,
                $party_column => 1
            );
            $result = $this->db->insert('tbl_negative_count', $insert_d);
        }
        if($result) {
            return true;
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
}