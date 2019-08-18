<?php
class DownloadModel extends CI_Model {
    
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function verifyUser($mobile, $app) {
        $this->db->select('u.id');
        $this->db->from('tbl_users as u');
        $this->db->where('u.mobile', $mobile);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        $return = array();
        if($result->num_rows() > 0) {
            $uid = $result->row()->id;
            $this->_sdb->select('d.id, d.status, d.download_at');
            $this->_sdb->from('tbl_download as d');
            $this->_sdb->where('d.user_id', $uid);
            $this->_sdb->where('d.app_id', $app);
            $res = $this->_sdb->get();
            if($res->num_rows() > 0) {
                $d_status = $res->row()->status;
                if($d_status == 0) {
                    $return['id'] = 1;
                    $return['msg'] = 'You can download app';
                    $return['uid'] = $uid;
                }elseif($d_status == 1) {
                    $return['id'] = 3;
                    $return['msg'] = 'You have already downloaded the app.';
                    $return['uid'] = $uid;
                }
            }else {
                $return['id'] = 2;
                $return['msg'] = 'No download request sent to you.';
                $return['uid'] = $uid;
            }
        }else {
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

    public function downloadUpdate($id, $app, $token) {
        $this->_sdb->trans_begin();
        //create download hash
        $expires = date("Y-m-d H:i:s", strtotime('+1 hours'));
        $hash_d = array(
            'user_id' => $id,
            'app_id' => $app,
            'token' => $token,
            'expires_at' => $expires
        );
        $this->_sdb->insert('tbl_download_auth', $hash_d);

        //update download table
        $this->_sdb->set('status', 1);
        $this->_sdb->set('download_at', date('Y-m-d H:i:s'));
        $this->_sdb->where('user_id', $id);
        $this->_sdb->where('app_id', $app);
        $this->_sdb->update('tbl_download');

        if($this->_sdb->trans_status() === FALSE) {
            $this->_sdb->trans_rollback();
            return false;
        }else {
            $this->_sdb->trans_commit();
            return true;
        }
    }

    public function getDownloadHash($id, $app) {
        $this->_sdb->select('user_id, token');
        $this->_sdb->from('tbl_download_auth');
        $this->_sdb->where('user_id', $id);
        $this->_sdb->where('app_id', $app);
        $this->_sdb->where('expires_at >', date('Y-m-d H:i:s'));
        $this->_sdb->order_by('expires_at', 'desc');
        $this->_sdb->limit(1);
        $result = $this->_sdb->get()->row();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }
}