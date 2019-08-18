<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OAuth extends CI_Controller {
    
    private $_expiry_time;
    private $_secret;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        // $this->load->library('Server', 'server');
        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('ApiModel');
        $this->load->model('loginModel');
        $this->_expiry_time = date("Y-m-d H:i:s", strtotime('+1 hours'));
        $this->_secret = 'c!T!ze^423*&';
    }

    public function index() {
        if($this->input->post()) {
            $data = $this->input->post();
            $username = $data['username'];

            if(is_numeric($username)) {
                $user = $this->ApiModel->getUserByPhone($username);
            }else {
                $user = $this->ApiModel->getUserByEmail($username);
            }
            if($user[0]->status == 1) {
                $id = $user[0]->id;
                $token = bin2hex(random_bytes(64)).$id;
                $hash_token = sha1($this->_secret.$token);
                $access_token = array(
                    'token' => $hash_token,
                    'expired_at' => $this->_expiry_time,
                    'user_id'   => $id
                );
                $ac_tkn = $this->ApiModel->createAccessToken($access_token);
                if($ac_tkn) {
                    $this->_httpStatus = http_response_code();
                    $response_array = array(
                        'Status' => $this->_httpStatus,
                        'Response' => 'Success',
                        'Data' => array(
                            'user_id' => $id,
                            'status' => 1,    
                        )    
                    );
                    header('Content-Type: application/json');
                    header('Smart-Authorization: TOKEN '. $token);
                    header('Expired-at: '.$this->_expiry_time);
                    echo json_encode($response_array);
                }
            }else {
                header('Content-Type: application/json');
                $response = array(
                    'Status' => $this->_httpStatus,
                    'Response' => 'Failure',
                    'error' => array(
                        'fail' => 'User does not exists',
                        'status' => 0
                    )     
                );
                echo json_encode($response);
            }
        }
         
    }

    public function login() {
        if($this->input->post()) {
            $headers = $this->input->request_headers();
            $ac_tkn = $headers['auth-key'];
            $pass = base64_decode($headers['pass']);
            $user_id = $headers['user-id'];
            $email = $this->input->post('email');
            $hash_token = $this->ApiModel->getAccessToken($user_id);
            $this->_httpStatus = http_response_code();
            
            if($hash_token) {
                $tkn = $hash_token->access_token;
                if(sha1($this->_secret.$ac_tkn) == $tkn) {
                    $login = $this->loginModel->userLogin($email, $pass);
                    if($login) {
                        $token = bin2hex(random_bytes(64)).$user_id;
                        $salt = uniqid();
                        $token_hash = password_hash($salt.$token, PASSWORD_BCRYPT);
                        $this->_expiry_time = date('Y-m-d H:i:s', strtotime('+7 days'));
                        // $hash_token = sha1($this->_secret.$token);
                        $auth_data = array(
                            'user_id' => $user_id,
                            'salt'  => $salt,
                            'auth_token' => $token_hash,
                            'expires' => $this->_expiry_time
                        );
                        $auth_token = true;
                        //$auth_token = $this->ApiModel->createAuthToken($auth_data);
                        if($auth_token) {
                            header('Content-Type: application/json');
                            header('SCC-Authorization: TOKEN '. $token);
                            header('Expired-at: '.$this->_expiry_time);
                            $response = array(
                                'Status' => $this->_httpStatus,
                                'Response' => 'Success',
                                'Data' => $login
                            );
                            echo json_encode($response);
                        }
                        
                    }else {
                        header('Content-Type: application/json');
                        $response = array(
                            'Status' => $this->_httpStatus,
                            'Response' => 'failure',
                            'error' => array(
                                'fail' => 'Password does not match',
                                'status' => 0   
                            )     
                        );
                        echo json_encode($response);
                    }
                    
                }
            }else {
                header('Content-Type: application/json');
                $response = array(
                    'Status' => $this->_httpStatus,
                    'Response' => 'failure',
                    'error' => array(
                        'fail' => 'Token expired.',
                        'status' => 0     
                    )     
                );
                echo json_encode($response);
            }
            
        }
    }
}