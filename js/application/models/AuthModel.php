<?php
class AuthModel extends CI_Model {
    
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
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

    public function getUser($data) {
        if(is_numeric($data['username'])) {
            $user_field = 'mobile';
        }else {
            $user_field = 'email';
        }

        $where = "u.".$user_field."='".$data['username']."'";
        
        $this->db->select('u.id, u.user_role, u.mobile, u.status as active_status, tm.status as team_status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as tm', 'u.id = tm.user_id');
        // $this->db->where('u.'.$user_field, $data['username']);

        if($data['app_id'] == 78) { //Coordinator App
            $where .= " AND u.user_role = 3";
        }elseif($data['app_id'] == 79) { // Team Leader App
            $where .= " AND u.user_role = 18";
        }elseif($data['app_id'] == 80) { // Digital Booth App
            $where .= " AND (u.user_role = 3 OR u.user_role = 37 OR u.user_role = 38)";
        }elseif($data['app_id'] == 81) { // Mobile Team App
            $where .= " AND u.user_role = 55";
        }elseif($data['app_id'] == 82) { // Kiosk App
            $where .= " AND (u.user_role = 76 OR u.user_role = 44)";
        }elseif($data['app_id'] == 83) { // SM App
            $where .= " AND u.user_role = 44";
        }elseif($data['app_id'] == 84) { // DI App
            $where .= " AND u.user_role = 2";
        }elseif($data['app_id'] == 85) { // Telecaller App
            $where .= " AND u.user_role = 138";
        }elseif($data['app_id'] == 86) { // Division Head App
            $where .= " AND u.user_role = 137";
        }elseif($data['app_id'] == 87) { // GM App
            $where .= " AND u.user_role = 57";
        }elseif($data['app_id'] == 88) { // Party President App
            $where .= " AND u.user_role = 59";
        }elseif($data['app_id'] == 89) { // App Coordinator App
            $where .= " AND u.user_role = 145";
        }
        $this->db->where($where);
        
        $result = $this->db->get();
        // $query = $this->db->last_query();
        // echo json_encode($query); exit;
        // echo json_encode($result->result()); exit;
        if($result->num_rows() > 0) {
            
            $usr = $result->row();
            // echo json_encode($usr); exit;
            if($usr->active_status == 1 && $usr->team_status == 1) {
                $this->_sdb->select('id, user_id, app_id, status');
                $this->_sdb->from('tbl_device');
                $this->_sdb->where('user_id', $usr->id);
                $this->_sdb->where('app_id', $data['app_id']);
                $this->_sdb->where('status', 1);
                $dev = $this->_sdb->get();
                
                if($dev->num_rows() > 0) {
                    
                    $status = 1;    
                }else {
                    $status = 3;
                }
            }else {
                $status = 0;
            }
            $usr->status = $status;
            return $usr;
        }else {
            return false;
        }
        
    }

    public function getuserps($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('tp.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
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

    // public function getOtp($id) {

    // }
    
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

    public function deviceinfo(array $data) {
        $id = $data['user_id'];
        $app_id = $data['app_id'];

        $this->_sdb->select('id, user_id, app_id');
        $this->_sdb->from('tbl_device');
        $this->_sdb->where('user_id', $id);
        $this->_sdb->where('app_id', $app_id);
        $this->_sdb->where('status', 1);
        $ex = $this->_sdb->get();

        if($ex->num_rows() > 0) {
            $dev_id = $ex->row()->id;
            $this->_sdb->where('id', $dev_id);
            $dev = $this->_sdb->update('tbl_device', $data);
        }else {
            $dev = $this->_sdb->insert('tbl_device', $data);
        }

        if($dev) {
            return true;
        }else {
            return false;
        }

    }

    public function isActive($data) {
        $id = $data['user-id'];
        $app_id = $data['app_id'];
        $device_id = $data['device_id'];

        $this->db->select('u.id, u.status as active_status, tm.status as team_status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as tm', 'u.id = tm.user_id');
        $this->db->where('u.id', $id);
        $this->db->where('u.status', 1);
        $this->db->where('tm.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $this->_sdb->select('id, user_id');
            $this->_sdb->from('tbl_device');
            $this->_sdb->where('user_id', $id);
            $this->_sdb->where('app_id', $app_id);
            $this->_sdb->where('device_id', $device_id);
            $this->_sdb->where('status', 1);
            $res = $this->_sdb->get();
            // echo json_encode($this->_sdb->last_query()); exit;
            if($res->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

    public function geoCoord($lat, $lng) {
        $this->_sdb->select('id');
        $this->_sdb->from('tbl_geo_coor');
        $this->_sdb->where('lat', $lat);
        $this->_sdb->where('lng', $lng);
        $result = $this->_sdb->get();
        if($result->num_rows() > 0) {
            $id = $result->row()->id;
        }else {
            $data = array(
                'lat' => $lat,
                'lng' => $lng
            );
            $this->_sdb->insert('tbl_geo_coor', $data);
            $id = $this->_sdb->insert_id();
        }
        return $id;
    }

    public function saveActivity($data) {
        $data = array(
            'user_id' => $data['user_id'],
            'request' => $data['request'],
            'geo_coord' => $data['geo'],
            'status' => $data['status'],
            'http_status' => $data['http_status']
        );
        $result = $this->_sdb->insert('tbl_activity', $data);
        if($result) {
            return true;
        }else {
            return false;
        }
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

    public function generatePassword($data) {
        $user_id = trim($data['user-id']);
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
} //class end