<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    private $_expiry_time;
    private $_secret;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        // $this->load->library('Server', 'server');
        date_default_timezone_set('Asia/Kolkata');
        // $this->load->model('ApiModel');
        $this->load->model('loginModel');
        $this->load->model('AuthModel');
        $this->_expiry_time = date("Y-m-d H:i:s", strtotime('+1 hours'));
        $this->_secret = 'c!T!ze^423*&';
    }

    private function __json_output($response, array $data,  $headers = array()) {
        header('Content-Type: application/json');
        if(count($headers) > 0) {
            foreach($headers as $k => $v) {
                header($k.': '.$v);
            }
        }
        $resp = array(
            'Status' => $this->_httpStatus,
            'Response' => $response,
            'Data' => $data
        );
        return json_encode($resp);
    }

    public function __sanitize_data(array $data) {
        $sanitized_data = array();
        // echo json_encode($data);
        // exit;
        foreach($data as $k => $dt) {
            if(is_array($dt)) {
                // echo json_encode($dt);
                // exit;
                foreach($dt as $v) {
                    $sanitized_data[$k][] = trim($v);
                }
            }else {
                $sanitized_data[trim($k)] = trim($dt);
            }
            
        }
        return $sanitized_data;
    }

    public function index() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $error_count = 0;
            $error = array();
            if(!$this->input->post('username')) {
                $error_count += 1;
                $error[] = 'username';
            }
            if(!$this->input->post('app_id')) {
                $error_count += 1;
                $error[] = 'app_id';
            }
            if($error_count > 0) {
                $this->_httpStatus = http_response_code(403);
                $data = array(  
                    'fail' => 'Bad request - Fields are missing',
                    'status' => 0,
                    'missing' => $error       
                );
                echo $this->__json_output('Failure', $data);    
            }else {
                $user = $this->AuthModel->getUser($data);
                // echo json_encode($user); exit;
                if($user && ($user->status == 1 || $user->status == 3)) {
                    $id = $user->id;
                    $token = bin2hex(random_bytes(64)).$id;
                    $hash_token = sha1($this->_secret.$token);
                    $access_token = array(
                        'token' => $hash_token,
                        'expired_at' => $this->_expiry_time,
                        'user_id'   => $id
                    );
                    $ac_tkn = $this->AuthModel->createAccessToken($access_token);
                    if($ac_tkn) {
                        if($user->status == 3) {
                            $send_otp = $this->AuthModel->generateOtp($id);
                            if($send_otp) {
                                $this->load->library('communication');
                                $message = 'Your One Time Password to verifiy your account is ' . $send_otp . '. Use this to verify your account.';
                                $mobile = $user->mobile;
                                $sms = $this->communication->sendsms($message, $mobile, 'return', '3');
                            }
                            $data = array(
                                'user_id' => $id,
                                'status' => 3,
                                'otp' => 0,
                                'otp_code' => $send_otp    
                            );
                        }else {
                            $setpassword = $this->AuthModel->isPasswordSet($id);
                            if($setpassword) {
                                $data = array(
                                    'user_id' => $id,
                                    'status' => 1,      
                                );
                            }else {
                                $data = array(
                                    'user_id' => $id,
                                    'password' => 0,
                                    'status' => 2
                                );
                            }
                        }
                        
                        $this->_httpStatus = http_response_code(200);
                        
                        $headers = array(
                            'Smart-Authorization' => $token,
                            'Expired-at' => $this->_expiry_time
                        );
                        echo $this->__json_output('Success', $data, $headers);
                    }
                }else {
                    $this->_httpStatus = http_response_code(404);
                    $data = array(  
                        'fail' => 'User does not exists',
                        'status' => 0       
                    );
                    echo $this->__json_output('Failure', $data);
                }
                // if(is_numeric($username)) {
                //     $user = $this->ApiModel->getUserByPhone($username);
                // }else {
                //     $user = $this->ApiModel->getUserByEmail($username);
                // }
                // if($user[0]->status == 1) {
                //     $id = $user[0]->id;
                //     $token = bin2hex(random_bytes(64)).$id;
                //     $hash_token = sha1($this->_secret.$token);
                //     $access_token = array(
                //         'token' => $hash_token,
                //         'expired_at' => $this->_expiry_time,
                //         'user_id'   => $id
                //     );
                //     $ac_tkn = $this->ApiModel->createAccessToken($access_token);
                //     if($ac_tkn) {
                //         $setpassword = $this->ApiModel->isPasswordSet($id);
                //         if($setpassword) {
                //             $data = array(
                //                 'user_id' => $id,
                //                 'status' => 1,      
                //             );
                //         }else {
                //             $data = array(
                //                 'user_id' => $id,
                //                 'password' => 0,
                //                 'status' => 2
                //             );
                //         }
                //         $this->_httpStatus = http_response_code(200);
                        
                //         $headers = array(
                //             'Smart-Authorization' => $token,
                //             'Expired-at' => $this->_expiry_time
                //         );
                //         echo $this->__json_output('Success', $data, $headers);
                //     }else {
                //         $this->_httpStatus = http_response_code(408);
                //         $data = array(
                //             'fail' => 'Timeout - Token expired',
                //             'status' => 0     
                //         );
                //     }
                // }else {
                //     $this->_httpStatus = http_response_code(404);
                //     $data = array(  
                //         'fail' => 'User does not exists',
                //         'status' => 0       
                //     );
                //     echo $this->__json_output('Failure', $data, $headers);
                // }
            }    
        }
         
    }

    public function login() {
        if($this->input->post()) {
            $data = $this->input->post();
            // $headers = $this->input->request_headers();
            $error_count = 0;
            $error = array();
            $fields = array('username', 'auth-key', 'pass', 'user-id', 'app_id', 'device_id');
            foreach($fields as $fd) {
                if(!$this->input->post($fd)) {
                    $error_count += 1;
                    $error['fields'][] = $fd;
                }
            }
            
            if($error_count > 0) {
                $this->_httpStatus = http_response_code(403);
                $data = array(  
                    'fail' => 'Bad request - Fields are missing',
                    'status' => 0,
                    'missing' => $error       
                );
                echo $this->__json_output('Failure', $data);    
            }else {
                $pst_data = $this->input->post();
                $ac_tkn = trim($pst_data['auth-key']);
                $pass = base64_decode(trim($pst_data['pass']));
                $user_id = trim($pst_data['user-id']);
                $email = trim($this->input->post('username'));
                $hash_token = $this->AuthModel->getAccessToken($user_id);
                if($hash_token) {
                    $tkn = $hash_token->access_token;
                    if(sha1($this->_secret.$ac_tkn) == $tkn) {
                        $active = $this->AuthModel->isActive($data);
                        $login = $this->loginModel->userLogin($email, $pass);
                        
                        //geo coord
                        if($this->input->post('lat') && $this->input->post('lng')) {
                            $lat = $this->input->post('lat');
                            $lng = $this->input->post('lng');
                            $geo_coord = $this->AuthModel->geoCoord($lat, $lng);
                        }else {
                            $geo_coord = null;
                        }

                        // echo json_encode($login); exit;
                        if($login && $active) {
                            if($login->user_role == 18 || $login->user_role == 3 || $login->user_role == 138) {
                                $ps_details = $this->AuthModel->getuserps($user_id);
                                if($ps_details) {
                                    $login->ps_id = $ps_details->id;
                                    $login->ps_no = $ps_details->ps_no;
                                    $login->ps_name = $ps_details->ps_name;
                                }
                            }
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
                            
                            $auth_token = $this->AuthModel->createAuthToken($auth_data);
                            $login_log = $this->loginModel->userLog($user_id);
                            if($auth_token) {
                                $this->_httpStatus = http_response_code(200);
                                $response_headers = array(
                                    'SCC-Authorization' => $token,
                                    'Expired-at' => $this->_expiry_time
                                );
                                if($login->photo != '') {
                                    $login->photo = base_url($this->config->item('assets_users')).$login->photo;
                                }
                                
                                //device information update
                                $device_u = array();
                                if($this->input->post('primary_no')) {
                                    $device_u['primary_no'] = $data['primary_no'];
                                }
                                if($this->input->post('secondary_no')) {
                                    $device_u['secondary_no'] = $data['secondary_no'];
                                }
                                if($this->input->post('serial')) {
                                    $device_u['serial'] = $data['serial'];
                                }
                                if($this->input->post('model')) {
                                    $device_u['model'] = $data['model'];
                                }
                                if($this->input->post('manufacture')) {
                                    $device_u['manufacture'] = $data['manufacture'];
                                }
                                if($this->input->post('brand')) {
                                    $device_u['brand'] = $data['brand'];
                                }
                                if($this->input->post('sdk')) {
                                    $device_u['sdk'] = $data['sdk'];
                                }
                                if($this->input->post('version_code')) {
                                    $device_u['version_code'] = $data['version_code'];
                                }
                                if($this->input->post('account_info')) {
                                    $device_u['account_info'] = $data['account_info'];
                                }
                                if(count($device_u) > 0) {
                                    $device_u['user_id'] = $data['user-id'];
                                    $device_u['app_id'] = $data['app_id'];
                                    $device_u['device_id'] = $data['device_id'];
                                    $this->AuthModel->deviceinfo($device_u);
                                }

                                $activity_d = array(
                                    'user_id' => $user_id,
                                    'request' => 'Auth/login',
                                    'geo' => $geo_coord,
                                    'http_status' => 200,
                                    'status' => 1
                                );
                                $this->AuthModel->saveActivity($activity_d);

                                $data = array(
                                    'user_data' => $login
                                    
                                );
                                echo $this->__json_output('Success', $data, $response_headers);
                            }

                            
                        }else {
                            $activity_d = array(
                                'user_id' => $user_id,
                                'request' => 'Auth/login',
                                'geo' => $geo_coord,
                                'http_status' => 401,
                                'status' => 0
                            );
                            $this->AuthModel->saveActivity($activity_d);

                            $this->_httpStatus = http_response_code(401);
                            $data = array(
                                'fail' => 'Password does not match',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }    
                    }else {
                        $this->_httpStatus = http_response_code(403);
                        $data = array(
                            'fail' => 'Forbidden. Token expired',
                            'status' => 0     
                        );
                        echo $this->__json_output('Failure', $data);
                    }
                }else {
                    $this->_httpStatus = http_response_code(408);
                    $data = array(
                        'fail' => 'Password does not match',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);
                }
            }    
        }
    }

    public function setpassword() {
        if($this->input->post()) {
            $data = $this->input->post();
            $error_count = 0;
            $error = array();
            $fields = array('auth-key', 'user-id', 'password', 'app_id', 'device_id');
            foreach($fields as $fd) {
                if(!$this->input->post($fd)) {
                    $error_count += 1;
                    $error['fields'][] = $fd;
                }
            }
            if($error_count > 0) {
                $this->_httpStatus = http_response_code(403);
                $data = array(  
                    'fail' => 'Bad request - Fields are missing',
                    'status' => 0,
                    'missing' => $error       
                );
                echo $this->__json_output('Failure', $data);    
            }else {
                $active = $this->AuthModel->isActive($data);
                $hash_token = $this->AuthModel->getAccessToken($data['user-id']);
                //geo coord
                if($this->input->post('lat') && $this->input->post('lng')) {
                    $lat = $this->input->post('lat');
                    $lng = $this->input->post('lng');
                    $geo_coord = $this->AuthModel->geoCoord($lat, $lng);
                }else {
                    $geo_coord = null;
                }
                $user_id = $data['user-id'];
                if($hash_token && $active) {
                    $ac_tkn = trim($data['auth-key']);
                    $tkn = $hash_token->access_token;
                    if(sha1($this->_secret.$ac_tkn) == $tkn) {
                        $gen_p = $this->AuthModel->generatePassword($data);
                        if($gen_p) {
                            $activity_d = array(
                                'user_id' => $user_id,
                                'request' => 'Auth/setpassword',
                                'geo' => $geo_coord,
                                'http_status' => 200,
                                'status' => 1
                            );
                            $this->AuthModel->saveActivity($activity_d);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Password generated successfully',
                                'status' => 1     
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            $activity_d = array(
                                'user_id' => $user_id,
                                'request' => 'Auth/setpassword',
                                'geo' => $geo_coord,
                                'http_status' => 500,
                                'status' => 0
                            );
                            $this->AuthModel->saveActivity($activity_d);

                            $this->_httpStatus = http_response_code(500);
                            $data = array(
                                'fail' => 'Internal Server Error',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                    }else {
                        $activity_d = array(
                            'user_id' => $user_id,
                            'request' => 'Auth/setpassword',
                            'geo' => $geo_coord,
                            'http_status' => 406,
                            'status' => 0
                        );
                        $this->AuthModel->saveActivity($activity_d);

                        $this->_httpStatus = http_response_code(406);
                        $data = array(
                            'fail' => 'Not Accepted - Token expired',
                            'status' => 0     
                        );
                        echo $this->__json_output('Failure', $data);
                    }
                }else {
                    $activity_d = array(
                        'user_id' => $user_id,
                        'request' => 'Auth/setpassword',
                        'geo' => $geo_coord,
                        'http_status' => 408,
                        'status' => 0
                    );
                    $this->AuthModel->saveActivity($activity_d);

                    $this->_httpStatus = http_response_code(408);
                    $data = array(
                        'fail' => 'Request Timeout',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);
                }
                
            }
        }
    }

    public function verifyotp() {
        if($this->input->post()) {
            $data = $this->input->post();
            $error_count = 0;
            $error = array();
            $fields = array('auth-key', 'user_id', 'otp_code', 'app_id', 'device_id');
            foreach($fields as $fd) {
                if(!$this->input->post($fd)) {
                    $error_count += 1;
                    $error['fields'][] = $fd;
                }
            }
            if($error_count > 0) {
                $this->_httpStatus = http_response_code(403);
                $data = array(  
                    'fail' => 'Bad request - Fields are missing',
                    'status' => 0,
                    'missing' => $error       
                );
                echo $this->__json_output('Failure', $data);    
            }else {
                // echo json_encode('ok'); exit;
                $hash_token = $this->AuthModel->getAccessToken($data['user_id']);
                $user_id = $data['user_id'];
                //geo coord
                if($this->input->post('lat') && $this->input->post('lng')) {
                    $lat = $data['lat'];
                    $lng = $data['lng'];
                    $geo_coord = $this->AuthModel->geoCoord($lat, $lng);
                }else {
                    $geo_coord = null;
                }

                if($hash_token) {
                    $ac_tkn = trim($data['auth-key']);
                    $tkn = $hash_token->access_token;

                    if(sha1($this->_secret.$ac_tkn) == $tkn) {
                        $verify = $this->AuthModel->verifyOtp($data['user_id'], base64_encode($data['otp_code']));
                        
                        if($verify) {
                            $device_d = array();
                            if($this->input->post('device_id')) {
                                $device_d['device_id'] = $data['device_id'];
                            }
                            if($this->input->post('primary_no')) {
                                $device_d['primary_no'] = $data['primary_no'];
                            }
                            if($this->input->post('secondary_no')) {
                                $device_d['secondary_no'] = $data['secondary_no'];
                            }
                            if($this->input->post('serial')) {
                                $device_d['serial'] = $data['serial'];
                            }
                            if($this->input->post('model')) {
                                $device_d['model'] = $data['model'];
                            }
                            if($this->input->post('brand')) {
                                $device_d['brand'] = $data['brand'];
                            }
                            if($this->input->post('type')) {
                                $device_d['type'] = $data['type'];
                            }
                            if($this->input->post('sdk')) {
                                $device_d['sdk'] = $data['sdk'];
                            }
                            if($this->input->post('version_code')) {
                                $device_d['version_code'] = $data['version_code'];
                            }
                            if($this->input->post('account_info')) {
                                $device_d['account_info'] = $data['account_info'];
                            }
                            
                            $device_d['user_id'] = $data['user_id'];
                            $device_d['app_id'] = $data['app_id'];
                            $dev = $this->AuthModel->deviceinfo($device_d);
                            
                            if($dev) {
                                $activity_d = array(
                                    'user_id' => $user_id,
                                    'request' => 'Auth/verifyotp',
                                    'geo' => $geo_coord,
                                    'http_status' => 200,
                                    'status' => 1
                                );
                                $this->AuthModel->saveActivity($activity_d);
                                
                                $pass_set = $this->AuthModel->isPasswordSet($data['user_id']);
                                if($pass_set) {
                                    $this->_httpStatus = http_response_code(200);
                                    $data = array(
                                        'success' => 'Account verified successfully. You can login now.',
                                        'status' => 1
                                    );
                                    echo $this->__json_output('Success', $data);
                                }else {
                                    $this->_httpStatus = http_response_code(200);
                                    $data = array(
                                        'success' => 'Account verified successfully. Set Password now.',
                                        'status' => 2
                                    );
                                    echo $this->__json_output('Success', $data);
                                }
                            }
                            // echo json_encode($device_d); exit;
                        }else {
                            $activity_d = array(
                                'user_id' => $user_id,
                                'request' => 'Auth/verifyotp',
                                'geo' => $geo_coord,
                                'http_status' => 400,
                                'status' => 1
                            );
                            $this->AuthModel->saveActivity($activity_d);

                            $this->_httpStatus = http_response_code(400);
                            $data = array(
                                'fail' => 'Wrong OTP or time out',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                        
                    }else {
                        $this->_httpStatus = http_response_code(406);
                        $data = array(
                            'fail' => 'Not Accepted - Token expired',
                            'status' => 0     
                        );
                        echo $this->__json_output('Failure', $data);
                    }
                }else {
                    $this->_httpStatus = http_response_code(408);
                    $data = array(
                        'fail' => 'Request Timeout',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);
                }
            }
        }
    }
    
} // class end