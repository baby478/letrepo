<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AppCoordinator extends CI_Controller {
    private $_authorized;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('AppCoordinatorModel');
        $this->load->model('AuthModel');
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

    private function __authenticate_user($user_id, $token) {
        $token = trim($token);
        $hash_data = $this->AuthModel->getAuthToken($user_id);
        if($hash_data) {
            $salt = $hash_data->salt;
            $hash = $hash_data->auth_token;
            $verify = password_verify($salt.$token, $hash);
            if($verify) {
                $this->_authorized = true;
                return true;
            }else {
                $this->_authorized = false;
                return false;
            }
        }else {
            $this->_authorized = false;
            return false;
        }
    }

    private function __isactive($data) {
        $active = $this->AuthModel->isActive($data);
        if($active) {
            return true;
        }else {
            return false;
        }
    }

    private function __activityLog($user_id, array $activity) {
        if($this->input->post('lat') && $this->input->post('lng')) {
            $lat = $this->input->post('lat');
            $lng = $this->input->post('lng');
            $geo_coord = $this->AuthModel->geoCoord($lat, $lng);
        }else {
            $geo_coord = null;
        }
        $activity_d = array(
            'user_id' => $user_id,
            'request' => $activity['request'],
            'geo' => $geo_coord,
            'http_status' => $activity['http_status'],
            'status' => $activity['status']
        );
        $this->AuthModel->saveActivity($activity_d);
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
            'AddData' => $data
        );
        return json_encode($resp);
    }

    public function otp() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/otp';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('mobile');
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($error_count > 0) {
                        $this->_httpStatus = http_response_code(400);
                        $data = array(
                            'fail' => 'Bad Request - Mandatory fields are missing.',
                            'status' => 0,
                            'fields' => $error['fields']     
                        );
                        echo $this->__json_output('Failure', $data); 
                    }else {
                        $otp = $this->AppCoordinatorModel->getotp($data['user_id'], $data['mobile']);
                        if($otp) {
                            
                            if(is_array($otp)) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array (
                                    'success' => 'Success.',
                                    'status' => 1,
                                    'data' => $otp     
                                );
                                echo $this->__json_output('success', $data);
                            }else {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'No Content.',
                                    'status' => 2,
                                        
                                );
                                echo $this->__json_output('Success', $data);
                            }
                            
                        }else {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Invalid mobile number.',
                                'status' => 0,
                                    
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                    }
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function divisionpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/divisionpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $dp = $this->AppCoordinatorModel->getDivisionPresident($data['user_id']);
                    if($dp) {
                        if($dp->photo != '') {
                            $dp->photo = base_url($this->config->item('assets_users')).$dp->photo;
                        }else {
                            if($dp->gid == 4) {
                                $dp->photo = base_url($this->config->item('assets_male'));
                            }elseif($dp->gid == 5) {
                                $dp->photo = base_url($this->config->item('assets_female'));
                            }
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $dp     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function divisioncoordinator() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/divisioncoordinator';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $divisioncoordinator = $this->AppCoordinatorModel->getDivisionCoordinator($data['user_id']);
                    if($divisioncoordinator) {
                        foreach($divisioncoordinator as $dc) {
                            
                            if($dc->photo != '') {
                                $dc->photo = base_url($this->config->item('assets_users')).$dc->photo;
                            }else {
                                if($dc->gid == 4) {
                                    $dc->photo = base_url($this->config->item('assets_male'));
                                }elseif($dc->gid == 5) {
                                    $dc->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $divisioncoordinator     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function boothcoordinator() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/boothcoordinator';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $boothcoordinator = $this->AppCoordinatorModel->getBoothCoordinator($data['user_id']);
                    if($boothcoordinator) {
                        foreach($boothcoordinator as $dc) {
                            
                            if($dc->photo != '') {
                                $dc->photo = base_url($this->config->item('assets_users')).$dc->photo;
                            }else {
                                if($dc->gid == 4) {
                                    $dc->photo = base_url($this->config->item('assets_male'));
                                }elseif($dc->gid == 5) {
                                    $dc->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $boothcoordinator     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function boothpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/boothpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $boothpresident = $this->AppCoordinatorModel->getBoothPresident($data['user_id']);
                    if($boothpresident) {
                        foreach($boothpresident as $dc) {
                            
                            if($dc->photo != '') {
                                $dc->photo = base_url($this->config->item('assets_users')).$dc->photo;
                            }else {
                                if($dc->gid == 4) {
                                    $dc->photo = base_url($this->config->item('assets_male'));
                                }elseif($dc->gid == 5) {
                                    $dc->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $boothpresident     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function streetpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/streetpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $streetpresident = $this->AppCoordinatorModel->getStreetPresident($data['user_id']);
                    if($streetpresident) {
                        foreach($streetpresident as $dc) {
                            
                            if($dc->photo != '') {
                                $dc->photo = base_url($this->config->item('assets_users')).$dc->photo;
                            }else {
                                if($dc->gid == 4) {
                                    $dc->photo = base_url($this->config->item('assets_male'));
                                }elseif($dc->gid == 5) {
                                    $dc->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $streetpresident     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function telecaller() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/telecaller';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $telecaller = $this->AppCoordinatorModel->getTelecaller($data['user_id']);
                    if($telecaller) {
                        foreach($telecaller as $dc) {
                            
                            if($dc->photo != '') {
                                $dc->photo = base_url($this->config->item('assets_users')).$dc->photo;
                            }else {
                                if($dc->gid == 4) {
                                    $dc->photo = base_url($this->config->item('assets_male'));
                                }elseif($dc->gid == 5) {
                                    $dc->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $telecaller     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function dashboard() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/dashboard';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('version'); //mandatory fields
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($error_count > 0) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(400);
                        $data = array(
                            'fail' => 'Bad Request - Mandatory fields are missing.',
                            'status' => 0,
                            'fields' => $error['fields']     
                        );
                        echo $this->__json_output('Failure', $data); 
                    }else {
                        $registered = $this->AppCoordinatorModel->getRegisteredCount($data['user_id']);
                        $download = $this->AppCoordinatorModel->getDownloadCount($data['user_id']);
                        $pending = $registered - $download;

                        $update = $this->AppCoordinatorModel->getAppVersion($data['app_id']);
                        if($update) {
                            if($update->version == $data['version']) {
                                $app_status = 1;
                            }else {
                                $app_status = 0;
                            }
                        }else {
                            $app_status = 1;
                        }

                        $dash_data = array(
                            'registered' => $registered,
                            'download' => $download,
                            'pending' => $pending,
                            'app_status' => $app_status
                        );
                        //activity log
                        $activity['http_status'] = 201;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $dash_data    
                        );
                        echo $this->__json_output('Success', $data);
                    }
                    
                }else {
                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data); 
            }
        }
    }

    public function appupdate($user_id, $token, $app_id, $device_id) {
        if($user_id != '' && $token != '' && $app_id != '' && $device_id != '') {
            $verified = $this->__authenticate_user($user_id, $token);
            $active = $this->__isactive(array('user-id' => $user_id, 'app_id' => $app_id, 'device_id' => $device_id));

            if($verified && $active && $this->_authorized) { //user is active and token verified
                $file = 'apps/appcoordinator.apk';
                if(file_exists($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/vnd.android.package-archive');
                    header('Content-Disposition: attachment; filename='.basename($file));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));
                    ob_clean();
                    flush();
                    readfile($file);
                    exit;
                }
            }
        }    
    }

    public function help() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/help';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('app_version', 'title'); //mandatory fields
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($error_count > 0) {
                        $this->_httpStatus = http_response_code(400);
                        $data = array(
                            'fail' => 'Bad Request - Mandatory fields are missing.',
                            'status' => 0,
                            'fields' => $error['fields']     
                        );
                        echo $this->__json_output('Failure', $data); 
                    }else {
                        if(isset($_FILES['attach']) && $_FILES['attach']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = $this->config->item('assets_help');
                            $config['allowed_types'] = 'png|jpg|jpeg';
                            // $config['max_size']  = 2048;
                            $config['file_name'] = time().$data['app_id'];
                            $this->load->library('upload', $config);
    
                            if($this->upload->do_upload('attach')) {
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                $data['attach'] = $uploadedFile;
                                //image resize
                                $options = array(
                                    'source_path' => $this->config->item('assets_help'),
                                    'width' => 680,
                                    'height' => 440
                                );
                                $this->resizeImage($uploadedFile, $options);
                            }else {
                                $this->_httpStatus = http_response_code(409);
                                $data = array(
                                    'fail' => 'Could not save file to server.',
                                    'status' => 0,
                                    'error' => $this->upload->display_errors('<p>', '</p>')    
                                );
                                echo $this->__json_output('Failure', $data);
                            }
                        }else {
                            $data['attach'] = null;
                        }
                        $help = $this->AppCoordinatorModel->saveHelpQuery($data);
                        if($help) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Query send successfully. Our team will respond you shortly',
                                'status' => 1,
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 500;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(500);
                            $data = array(
                                'fail' => 'Internal Server Error - We could not complete your request.',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                    }
                }else {
                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(401);
                $data = array(
                    'fail' => 'Unauthorized - Token failed. Login again.',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);
            }
        }
    }

    public function resizeImage($filename, array $options) {
        
        $config_manip = array(
          'image_library' => 'gd2',
          'source_image' => $options['source_path'] .$filename,
          'maintain_ratio' => TRUE,
          'width' => $options['width'],
          'height' => $options['height']
        );
        $this->load->library('image_lib');
        // Set your config up
        $this->image_lib->initialize($config_manip);
        if (!$this->image_lib->resize()) {
            echo $this->image_lib->display_errors();
        }
        $this->image_lib->clear();
    }

    public function singlesms() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/singlesms';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('sms_type', 'receiver_group', 'mobile', 'user_role', 'language', 'message'); //mandatory fields
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($error_count > 0) {
                        $this->_httpStatus = http_response_code(400);
                        $data = array(
                            'fail' => 'Bad Request - Mandatory fields are missing.',
                            'status' => 0,
                            'fields' => $error['fields']     
                        );
                        echo $this->__json_output('Failure', $data); 
                    }else {
                        if($data['sms_type'] == 63) { //if single sms
                            $sms_send = false;
                            $sms_store = false;
                            $msg_count = 1;

                            $this->load->library('communication');
                            if($data['language'] == 1) {
                                $sms_send = $this->communication->sendsms($data['message'], $data['mobile'], 'return_type', '203');    
                            }else {
                                $sms_send = $this->communication->languagesms($data['message'], $data['mobile'], 'return_type', '203');   
                            }
                            $data['msg_count'] = $msg_count;
                            $sms_store = $this->AppCoordinatorModel->saveSms($data);

                            if($sms_send && $sms_store) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Message sent successfully to '.$msg_count . ' user',
                                    'status' => 1,
                                    'msg_count' => $msg_count 
                                );
                                echo $this->__json_output('Success', $data);
                            }else {
                                //activity log
                                $activity['http_status'] = 500;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(500);
                                $data = array(
                                    'fail' => 'Internal Server Error - We could not complete your request.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data);
                            }

                        }else {
                            //activity log
                            $activity['http_status'] = 403;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(403);
                            $data = array(
                                'fail' => 'Forbidden - Not a single sms',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                    }
                }else {
                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(401);
                $data = array(
                    'fail' => 'Unauthorized - Token failed. Login again.',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);
            }
        }
    }

    public function outbox() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/outbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $sent_sms = $this->AppCoordinatorModel->getSentSms($data['user_id']);
                    if($sent_sms) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $sent_sms     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }
                }else {
                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);
            }
        }
    }

    public function smartmedia() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/smartmedia';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $media = $this->AppCoordinatorModel->getSmartMedia();
                    if($media) {
                        foreach($media as $m) {
                            $m->media_path = base_url($this->config->item('assets_images')).$m->media_path;
                            $m->post_likes = $this->AppCoordinatorModel->getPostLikes($m->id);
                            $m->like = $this->AppCoordinatorModel->getPostLikeByUser($m->id, $data['user_id']);
                        }
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $media     
                        );
                        echo $this->__json_output('success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 2,
                            'data' => 'No data'
                                
                        );
                        echo $this->__json_output('Success', $data);
                    }    
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }

    public function postlike() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'AppCoordinator/postlike';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('post_id', 'like');
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($error_count > 0) {
                        $this->_httpStatus = http_response_code(400);
                        $data = array(
                            'fail' => 'Bad Request - Mandatory fields are missing.',
                            'status' => 0,
                            'fields' => $error['fields']     
                        );
                        echo $this->__json_output('Failure', $data); 
                    }else {
                        $post_like = $this->AppCoordinatorModel->savePostLike($data);
                        
                        if($post_like) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Post like has been saved successfully.',
                                'status' => 1,
                                    
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 500;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(500);
                            $data = array(
                                'fail' => 'We could not save your like.',
                                'status' => 0,
                                    
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                    }
                }else {
                    //activity log
                    $activity['http_status'] = 401;
                    $activity['status'] = 0;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);  
                }
            }else {
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
        }
    }
}