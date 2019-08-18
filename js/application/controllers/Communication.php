<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Communication extends CI_Controller {
    private $_domain;
    private $_username;
    private $_password;
    private $_senderid;
    private $_smsapi;
    private $_languageapi;
    private $_scheduleapi;

    public function __construct() {
        parent::__construct();
        $this->load->model('SMDashboardModel');
        $this->load->model('CommunicationModel');
        $config = $this->CommunicationModel->getConfig();
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

    
    // public function sendsms() {
    //     if($this->input->post()) {
    //         $data = $this->input->post();
    //         // $sentsms = true;
    //         $sentsms = $this->CommunicationModel->storeSMS($data);

    //         $username= "Praveen96";
    //         $password = "praveen123@";
    //         $number= $data['mobile'];
    //         $sender = "TESTID";
    //         $message = $data['message'];

    //         $url="login.bulksmsgateway.in/unicodesmsapi.php?username=".urlencode($username)."&password=".urlencode($password)."&mobilenumber=".urlencode($number)."&senderid=".urlencode($sender)."&message=".urlencode($message)."&type=".urlencode('3'); 
    //         $ch = curl_init($url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         $curl_scraped_page = curl_exec($ch);
    //         if (curl_error($ch)) {
    //            echo $error_msg = curl_error($ch);
    //         }
    //         curl_close($ch);

    //         // if($sentsms && !curl_error($ch)) {
    //         if($sentsms) {
    //             echo json_encode(array('status' => 1, 'status_message' => 'success'));
    //         }else {
    //             echo json_encode(array('status' => 0, 'status_message' => 'failed'));
    //         }
    //     }
    // }
    
    public function sendsms($message, $mobile, $response) {
        $domain = $this->_domain;
        $username = $this->_username;
        $password = $this->_password;
        $sender = $this->_senderid;
        $smsapi = $this->_smsapi;
        if(is_array($mobile)) {
            $mobile = implode(',', $mobile);
        }
        $url = $domain."/".$smsapi."?user=".urlencode($username)."&password=".urlencode($password)."&mobile=".urlencode($mobile)."&sender=".urlencode($sender)."&message=".urlencode($message)."&type=".urlencode('3');
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
    
    public function getmessage($id) {
        $result = $this->CommunicationModel->getTemplateMessageByID($id);
        if($result) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }

    public function __jsonResult(Array $data) {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    
}