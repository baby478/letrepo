<?php
class LoginModel extends CI_Model {

    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
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

    public function checkAllocStatus($id) {
        $this->db->select('id');
        $this->db->from('tbl_team_mng');
        $this->db->where('user_id', $id);
        $this->db->where('status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function userLogin($email, $pass) {
        $pass = trim($pass);
        if(is_numeric($email)) {
            $exists = $this->phoneExists($email);
        }else {
            $exists = $this->emailExists($email);
        }
        if($exists) {
            
            $user_id = $exists->id;
            $data = $this->getPassword($user_id);
            $password_salt = $data->password_salt;
            $password_hash = $data->password;
            $password_verify = password_verify($pass.$password_salt, $password_hash);
            
            $allocated = $this->checkAllocStatus($user_id);
            
            if($password_verify && $allocated) {
                $user_data = $this->getUser($user_id);
                return $user_data;
            }
        }else {
            return false;
        }
    }

    public function getUser($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.photo, u.dob, u.email, u.mobile, u.status, u.user_role, l.value as designation, lc.name as location, lc.id as location_id, lc.level_id');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        $this->db->join('tbl_locations as lc', 't.location = lc.id', 'left');
        $this->db->where('u.id', $id);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function getPassword($id) {
        $this->db->select('id, password_salt, password');
        $this->db->where('id', $id);
        $result = $this->db->get('tbl_password')->row();
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

    // User Login Log 
	public function userLog($id) {	 
        $ip_address = $_SERVER['REMOTE_ADDR'];
		 //$ip_address = $this->input->get_client_ip();;
		 $pass_data = array(
            'user_id' => $id,
			'ip_address' => $ip_address
        );
        $this->db->insert('tbl_login_log', $pass_data);
    }
    
    public function getLastLoginId($id) {
        $this->db->select('id');
        $this->db->from('tbl_login_log');
        $this->db->where('user_id', $id);
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);
        $result = $this->db->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    public function logoutLog($id) {
        $exists = $this->getUser($id);
        if($exists) {
            $last_login_id = $this->getLastLoginId($id)->id;
            $update_log = array(
                'logout_time' => date('Y-m-d H:i:s')
            );
            $this->db->where('id', $last_login_id);
            $this->db->update('tbl_login_log', $update_log);
        }
    }
	
	public function verifyUser($mobile) {
        $this->db->select('u.id');
        $this->db->from('tbl_users as u');
        $this->db->where_in('u.user_role', array(143, 144, 44, 57, 137));
        $this->db->where('u.mobile', $mobile);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        $return = array();
        if($result->num_rows() > 0) {
            //password exists
            $user = $result->row();
            $this->db->select('p.id');
            $this->db->from('tbl_password as p');
            $this->db->where('p.id', $user->id);
            $presult = $this->db->get();
            if($presult->num_rows() > 0) {
                $return['id'] = 2;
                $return['msg'] = 'Password Exists. If you forgot your password click on <a href="'.base_url('login/forgetpassword/').'">Forgot Password</a>';
            }else {
                $return['id'] = 1;
                $return['msg'] = 'Verified';
                $return['uid'] = $user->id;
            }	
        }
        else {
            $return['id'] = 0;
            $return['msg'] = 'Invalid mobile number.';
        }
        return $return;
        
    }

    public function generateOtp($id) {
        $otp = rand(100000,999999);
        $otp_d = array(
            'user_id' => $id,
            'otp_code' => base64_encode($otp),
            'expired_at' => date("Y-m-d H:i:s", time() + 300),
        );
        $ins = $this->_sdb->insert('tbl_otp', $otp_d);
        if($ins) {
            return $otp;
        }else {
            return false;
        }
    }

    public function verifyOtp($id, $code) {
        $this->_sdb->select('id, user_id');
        $this->_sdb->from('tbl_otp');
        $this->_sdb->where('user_id', $id);
        $this->_sdb->where('otp_code', $code);
        $this->_sdb->where('expired_at >', date('Y-m-d H:i:s'));
        $this->_sdb->order_by('expired_at', 'desc');
        $this->_sdb->limit(1);
        $result = $this->_sdb->get()->row();
        if($result) {
            $this->_sdb->set('verified', 1);
            $this->_sdb->where('id', $result->id);
            $this->_sdb->update('tbl_otp');
            return $result;
        }else {
            return false;
        }
    }
    
    public function generatePassword($data) {
        $user_id = trim($data['user']);
        $salt = uniqid();
        $pass = $data['password'];
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
}