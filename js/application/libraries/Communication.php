<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* @package     Communication
* @author      Anees
* 
* 
* 
**/

class Communication {
    private $ci;
    private $_domain;
    private $_username;
    private $_password;
    private $_senderid;
    private $_smsapi;
    private $_languageapi;
    private $_scheduleapi;
    
    public function __construct() {
        $this->ci =& get_instance();
        $this->ci->load->model('CommunicationModel');
        $config = $this->ci->CommunicationModel->getConfig();
        if($config) {
            foreach($config as $conf) {
                if($conf->name == 'sms_domain') {
                   $this->_domain = $conf->value; 
                }
                if($conf->name == 'sms_username') {
                    $this->_username = $conf->value;
                }
                if($conf->name == 'sms_password') {
                    $this->_password = $conf->value;
                }
                if($conf->name == 'sms_senderid') {
                    $this->_senderid = $conf->value;
                }
                if($conf->name == 'sms_sendapi') {
                    $this->_smsapi = $conf->value;
                }
                if($conf->name == 'sms_schedule') {
                    $this->_scheduleapi = $conf->value;
                }
                if($conf->name == 'sms_language') {
                    $this->_languageapi = $conf->value;
                }
            }
        }
    }
    
    public function sendsms($message, $mobile, $response, $type) {
        $domain = $this->_domain;
        $username = $this->_username;
        $password = $this->_password;
        $sender = $this->_senderid;
        $smsapi = $this->_smsapi;
        if(is_array($mobile)) {
            $mobile = implode(',', $mobile);
        }
        $url = $domain."/".$smsapi."?user=".urlencode($username)."&password=".urlencode($password)."&mobile=".urlencode($mobile)."&sender=".urlencode($sender)."&message=".urlencode($message)."&type=".urlencode($type);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_scraped_page = curl_exec($ch);
        if(curl_error($ch)) {
            $error = true;
        }else {
            $error = false;
        }
        curl_close($ch);

        if($response == 'json') {
            if($error) {
                echo json_encode(array('status' => 0, 'status_message' => 'failed'));
            }else {
                echo json_encode(array('status' => 1, 'status_message' => 'success'));
            }
        }else {
            if($error) {
                return false;
            }else {
                return true;
            }
        }
    }
 
    public function languagesms($message, $mobile, $response, $type) {
        $domain = $this->_domain;
        $username = $this->_username;
        $password = $this->_password;
        $sender = $this->_senderid;
        $languageapi = $this->_languageapi;
        if(is_array($mobile)) {
            $mobile = implode(',', $mobile);
        }
        $url = $domain."/".$languageapi."?username=".urlencode($username)."&password=".urlencode($password)."&mobilenumber=".urlencode($mobile)."&senderid=".urlencode($sender)."&message=".urlencode($message)."&type=".urlencode($type);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_scraped_page = curl_exec($ch);
        if(curl_error($ch)) {
            $error = true;
        }else {
            $error = false;
        }
        curl_close($ch);

        if($response == 'json') {
            if($error) {
                echo json_encode(array('status' => 0, 'status_message' => 'failed'));
            }else {
                echo json_encode(array('status' => 1, 'status_message' => 'success'));
            }
        }else {
            if($error) {
                return false;
            }else {
                return true;
            }
        }
    }
    
}