<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DigitalBooth extends CI_Controller {
    private $_authorized;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('DBoothModel');
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
    
    public function getallmembers() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/getallmembers';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $members = $this->DBoothModel->getmembersByCoordinator($data['user_id']);
                    if($members) {
                        foreach($members as $vd) {
                            if($vd->photo != null) {
                                $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                            }else {
                                if($vd->gender == 4) {
                                    $vd->photo = base_url($this->config->item('assets_male'));
                                }elseif($vd->gender == 5) {
                                    $vd->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            if($vd->attend == null) {
                                $vd->status = 0;
                               
                            }else {
                                $vd->status = 1;
                            }
                        }
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $members    
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'fail' => 'No Content.',
                            'status' => 0,
                            'data' => array('no-content')     
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

    public function getmymembers() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/getmymembers';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $members = $this->DBoothModel->getMembers($data['user_id']);
                    if($members) {
                        foreach($members as $vd) {
                            if($vd->photo != null) {
                                $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                            }else {
                                if($vd->gender == 4) {
                                    $vd->photo = base_url($this->config->item('assets_male'));
                                }elseif($vd->gender == 5) {
                                    $vd->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            if($vd->attend == null) {
                                $vd->status = 0;
                               
                            }elseif($vd->attend == 53) {
                                $vd->status = 1;
                            }elseif($vd->attend == 54) {
                                $vd->status = 2;
                            }
                        }
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $members    
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'fail' => 'No Content.',
                            'status' => 2,
                            'data' => array('no-content')    
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

    public function digitalslips() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/digitalslips';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $dslips = $this->DBoothModel->getdigitalslips($data['user_id']);
                    if($dslips) {
                        if(isset($dslips['slips'])) {
                            foreach($dslips['slips'] as $vd) {
                                if($vd->photo != null) {
                                    $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                                }else {
                                    if($vd->gender == 4) {
                                        $vd->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vd->gender == 5) {
                                        $vd->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                            }
                        }
                        if(count($dslips['slips']) == 0) {
                            unset($dslips['slips']);
                        }
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $dslips    
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'fail' => 'No Content.',
                            'status' => 2,
                            'data' => array('no-content')     
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

    public function digitalstation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/digitalstation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('ps_id', 'booth_id', 'role_id');
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
                        $bth_agent = $this->DBoothModel->getPsMember($data['ps_id'], $data['booth_id'], $data['role_id']);
                        if($bth_agent) {
                            foreach($bth_agent as $vd) {
                                if($vd->photo != null) {
                                    $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                                }else {
                                    if($vd->gender == 4) {
                                        $vd->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vd->gender == 5) {
                                        $vd->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                            }
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Success.',
                                'status' => 1,
                                'data' => $bth_agent    
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 2;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'fail' => 'No Content.',
                                'status' => 2,
                                'data' => array('no-content')    
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
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);
            }
        }
    }

    public function pstation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/pstation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $ps = $this->DBoothModel->getPollingStation($data['user_id']);
                    if($ps) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $ps    
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'fail' => 'No Content.',
                            'status' => 2,
                            'data' => array('no-content')     
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

    public function outstation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/outstation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $os_members = $this->DBoothModel->getOutStationMembers($data['user_id']);
                    if($os_members) {
                        if(isset($os_members['os'])) {
                            foreach($os_members['os'] as $vd) {
                                if($vd->photo != null) {
                                    $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                                }else {
                                    if($vd->gender == 4) {
                                        $vd->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vd->gender == 5) {
                                        $vd->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                            }
                        }
                        if(count($os_members['os']) == 0) {
                            unset($os_members['os']);
                        }
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $os_members    
                        );
                        echo $this->__json_output('Success', $data);
                        
                       
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'fail' => 'No Content.',
                            'status' => 2,
                            'data' => array('no-content')     
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

    public function psdigitalslips() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/psdigitalslips';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('ps_id');
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
                        $dslips = $this->DBoothModel->getDigitalSlipsByPs($data['user_id'], $data['ps_id']);
                        if($dslips) {
                            foreach($dslips as $vd) {
                                if($vd->photo != null) {
                                    $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                                }else {
                                    if($vd->gender == 4) {
                                        $vd->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vd->gender == 5) {
                                        $vd->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                            }
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Success.',
                                'status' => 1,
                                'data' => $dslips    
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 2;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'fail' => 'No Content.',
                                'status' => 2,
                                'data' => array('no-content')     
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
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);
            }
        }
    }

    public function psoutstation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/psoutstation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('ps_id');
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
                        $os_members = $this->DBoothModel->getOutStationMembersByPs($data['user_id'], $data['ps_id']);
                        if($os_members) {
                            foreach($os_members['os'] as $vd) {
                                if($vd->photo != null) {
                                    $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                                }else {
                                    if($vd->gender == 4) {
                                        $vd->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vd->gender == 5) {
                                        $vd->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                            }
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Success.',
                                'status' => 1,
                                'data' => $os_members    
                            );
                            echo $this->__json_output('Success', $data);   
                        }else {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 2;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'fail' => 'No Content.',
                                'status' => 2,
                                'data' => array('no-content')     
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
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);
            }
        }
    }

    public function dbattendance() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DigitalBooth/dbattendance';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id', 'attend_id');
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
                        $attend = $this->DBoothModel->dBAttend($data);
                        if($attend) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Success.',
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
                                'failure' => 'Internal Server Error.',
                                'status' => 0,
                                    
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
            $activity['request'] = 'DigitalBooth/dashboard';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $group = $this->DBoothModel->getGroupAttend($data['user_id']);
                    $member = $this->DBoothModel->getMemberAttend($data['user_id']);
                    $attend = $group + $member;
                    
                    $dash_data = array(
                        'attend' => $attend,
                        'my_members' => $member,
                        'group_members' => $group
                    );
                    //activity log
                    $activity['http_status'] = 200;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'success' => 'Success.',
                        'status' => 1,
                        'data' => $dash_data    
                    );
                    echo $this->__json_output('Success', $data);
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

    public function negativeCount() {
        $data = $this->__sanitize_data($this->input->post());
        $activity = array();
        $activity['request'] = 'DigitalBooth/negativeCount';
        if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
            $verified = $this->__authenticate_user($data['user_id'], $data['token']);
            $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

            if($verified && $active && $this->_authorized) { //user is active and token verified
                $error_count = 0;
                $error = array();
                $fields = array('party_id');
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
                    $n_count = $this->DBoothModel->saveNegativeCount($data);
                    if($n_count) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Count Saved.',
                            'status' => 1,  
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 406;
                        $activity['status'] = 0;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(406);
                        $data = array(
                            'success' => 'Count Not Saved.',
                            'status' => 0,  
                        );
                        echo $this->__json_output('Success', $data);
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
            $this->_httpStatus = http_response_code(403);
            $data = array(
                'fail' => 'Forbidden - token is missing',
                'status' => 0     
            );
            echo $this->__json_output('Failure', $data); 
        }
    }

    //sms
    public function smsdetails() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'TeamLeader/smsdetails';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_id', 'receiver_id', 'mobile', 'message', 'msg_type'); //mandatory fields
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
                        $this->load->library('communication');
                        if($data['msg_type'] == 2) {
                            $sms = $this->communication->languagesms($data['message'], $data['mobile'], 'return_type');
                        }else {
                            $sms = $this->communication->sendsms($data['message'], $data['mobile'], 'return_type', '203');    
                        }
                        $smsstore = $this->DBoothModel->saveMessage($data);
                        if($sms && $smsstore) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Message successfully sent.',
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
                                'fail' => 'Internal server error.',
                                'status' => 0,
                                        
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