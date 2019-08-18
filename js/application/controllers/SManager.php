<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SManager extends CI_Controller {
    private $_authorized;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('SManagerModel');
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

    public function mandals() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/mandals';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $mandals = $this->SManagerModel->getMandalsBySM($data['user_id']);
                    if($mandals) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $mandals    
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

    public function divisionincharge() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/divisionincharge';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('dhid'); //mandatory fields
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
                        $DIncharge = $this->SManagerModel->getDIncharge($data['dhid']);
                        if($DIncharge) {
                            foreach($DIncharge as $di) {
                                $ps = $this->SManagerModel->getDivisionInchargePS($di->id);
                                if($di->photo != '') {
                                    $di->photo = base_url($this->config->item('assets_users')).$di->photo;
                                }else {
                                    if($di->gender == 4) {
                                        $di->photo = base_url($this->config->item('assets_male'));
                                    }elseif($di->gender == 5) {
                                        $di->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                                if($ps) {
                                    $di->ps = $ps;
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
                                'data' => $DIncharge    
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

    /**
     * Date : 10-01-2019
     */
    public function divisionhead() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/divisionhead';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    $divisionhead = $this->SManagerModel->getDivisionHead($data['user_id']);
                    if($divisionhead) {
                        foreach($divisionhead as $dh) {
                            if($dh->photo != '') {
                                $dh->photo = base_url($this->config->item('assets_users')).$dh->photo;
                            }else {
                                if($dh->gender == 4) {
                                    $dh->photo = base_url($this->config->item('assets_male'));
                                }elseif($dh->gender == 5) {
                                    $dh->photo = base_url($this->config->item('assets_female'));
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
                            'data' => $divisionhead    
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

    public function boothcoordinator() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/boothcoordinator';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('di_id'); //mandatory fields
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
                        $booth_coordinator = $this->SManagerModel->getBoothObserverByDM($data['di_id']);
                        if($booth_coordinator) {
                            foreach($booth_coordinator as $bc) {
                                $ps = $this->SManagerModel->getBoothObserverPS($bc->id);
                                if($bc->photo != '') {
                                    $bc->photo = base_url($this->config->item('assets_users')).$bc->photo;
                                }else {
                                    if($bc->gid == 4) {
                                        $bc->photo = base_url($this->config->item('assets_male'));
                                    }elseif($bc->gid == 5) {
                                        $bc->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                                if($ps) {
                                    $bc->ps = $ps;
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
                                'data' => $booth_coordinator    
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

    public function boothpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/boothpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('di_id'); //mandatory fields
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
                        $booth_president = $this->SManagerModel->getBoothPresidentByDM($data['di_id']);
                        if($booth_president) {
                            foreach($booth_president as $bp) {
                                $bp->sp_count = $this->SManagerModel->getSPCountByBP($bp->id);
                                if($bp->photo != '') {
                                    $bp->photo = base_url($this->config->item('assets_users')).$bp->photo;
                                }else {
                                    if($bp->gid == 4) {
                                        $bp->photo = base_url($this->config->item('assets_male'));
                                    }elseif($bp->gid == 5) {
                                        $bp->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $booth_president    
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

    public function streetpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/sheetpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('bpid'); //mandatory fields
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
                        $sheet_president = $this->SManagerModel->getSheetPresidentByBP($data['bpid']);
                        if($sheet_president) {
                            foreach($sheet_president as $sp) {
                                $sp->v_count = $this->SManagerModel->getVotersCountBySP($sp->id);
                                if($sp->photo != '') {
                                    $sp->photo = base_url($this->config->item('assets_users')).$sp->photo;
                                }else {
                                    if($sp->gid == 4) {
                                        $sp->photo = base_url($this->config->item('assets_male'));
                                    }elseif($sp->gid == 5) {
                                        $sp->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $sheet_president    
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

    public function familyhead() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/familyhead';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('spid'); //mandatory fields
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
                        $family_head = $this->SManagerModel->getFamilyHeadBySP($data['spid']);
                        if($family_head) {
                            foreach($family_head as $fh) {
                                $fh->v_count = $this->SManagerModel->getVotersCountByFH($fh->id);
                                if($fh->photo != '') {
                                    $fh->photo = base_url($this->config->item('assets_voters')).$fh->photo;
                                }else {
                                    if($fh->gender == 4) {
                                        $fh->photo = base_url($this->config->item('assets_male'));
                                    }elseif($fh->gender == 5) {
                                        $fh->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $family_head    
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

    public function votersbysp() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/votersbysp';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('spid'); //mandatory fields
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
                        $voters = $this->SManagerModel->getVotersBySP($data['spid']);
                        if($voters) {
                            foreach($voters as $v) {
                                if($v->photo != '') {
                                    $v->photo = base_url($this->config->item('assets_voters')).$v->photo;
                                }else {
                                    if($v->gender == 4) {
                                        $v->photo = base_url($this->config->item('assets_male'));
                                    }elseif($v->gender == 5) {
                                        $v->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $voters    
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

    public function votersbyfh() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/votersbyfh';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('fhid');
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
                        $v_members = $this->SManagerModel->getVotersByFH($data['fhid']);
                        if($v_members) {
                            foreach($v_members as $vm) {
                                if($vm->photo != null) {
                                    $vm->photo = base_url($this->config->item('assets_voters')).$vm->photo;
                                }else {
                                    if($vm->gender == 4) {
                                        $vm->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vm->gender == 5) {
                                        $vm->photo = base_url($this->config->item('assets_female'));
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
                                'data' => array(
                                    'members' => $v_members
                                )     
                            );
                            echo $this->__json_output('Success', $data); 
                        }else {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 2;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Success. No members found',
                                'status' => 2,
                                 
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
    }

    /**
     * Date : 11-01-2019
     */
    public function pollingstation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/pollingstation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('mid'); //mandatory fields
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
                        $ps = $this->SManagerModel->getPSByMandal($data['mid']);
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

    public function xparty() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/xparty';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('psid', 'party_id');
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
                        $xparty = $this->SManagerModel->getXpartyByPS($data['psid'], $data['party_id']);
                        if($xparty) {
                            
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);
    
                            $this->_httpStatus = http_response_code(200);
                            $data = array (
                                'success' => 'Success.',
                                'status' => 1,
                                'data' => $xparty     
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

    public function groupsms() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/groupsms';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('sms_type', 'receiver_group', 'user_role', 'language', 'message'); //mandatory fields
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
                        $mobile = array();
                        if($data['sms_type'] == 62) { //if group sms
                            $sms_send = false;
                            $sms_store = false;
                            $msg_count = 0;
                            
                            if($data['receiver_group'] == 140 && $data['user_role'] == 137) { //Division Head
                                $division_incharge = $this->SManagerModel->getDivisionHead($data['user_id']);
                                if($division_incharge) {
                                    foreach($division_incharge as $bc) {
                                        $mobile[] = $bc->mobile;
                                    }
                                    $this->load->library('communication');
                                    if($data['language'] == 1) {
                                        $sms_send = $this->communication->sendsms($data['message'], $mobile, 'return_type', '203');    
                                    }else {
                                        $sms_send = $this->communication->languagesms($data['message'], $mobile, 'return_type', '203');   
                                    }
                                    $msg_count = count($division_incharge);
                                    $data['msg_count'] = $msg_count;
                                    $sms_store = $this->SManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 66 && $data['user_role'] == 2) { //Division Incharge
                                $division_incharge = $this->SManagerModel->getDivisionInchargeBySM($data['user_id']);
                                if($division_incharge) {
                                    foreach($division_incharge as $bc) {
                                        $mobile[] = $bc->mobile;
                                    }
                                    $this->load->library('communication');
                                    if($data['language'] == 1) {
                                        $sms_send = $this->communication->sendsms($data['message'], $mobile, 'return_type', '203');    
                                    }else {
                                        $sms_send = $this->communication->languagesms($data['message'], $mobile, 'return_type', '203');   
                                    }
                                    $msg_count = count($division_incharge);
                                    $data['msg_count'] = $msg_count;
                                    $sms_store = $this->SManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 69 && $data['user_role'] == 55) { //booth coordinator
                                $booth_coordinators = $this->SManagerModel->getBoothCoordinatorBySM($data['user_id']);
                                if($booth_coordinators) {
                                    foreach($booth_coordinators as $bc) {
                                        $mobile[] = $bc->mobile;
                                    }
                                    $this->load->library('communication');
                                    if($data['language'] == 1) {
                                        $sms_send = $this->communication->sendsms($data['message'], $mobile, 'return_type', '203');    
                                    }else {
                                        $sms_send = $this->communication->languagesms($data['message'], $mobile, 'return_type', '203');   
                                    }
                                    $msg_count = count($booth_coordinators);
                                    $data['msg_count'] = $msg_count;
                                    $sms_store = $this->SManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 67 && $data['user_role'] == 18) { //booth president
                                $booth_president = $this->SManagerModel->getBoothPresidentBySM($data['user_id']);
                                if($booth_president) {
                                    foreach($booth_president as $bp) {
                                        $mobile[] = $bp->mobile;
                                    }
                                    $this->load->library('communication');
                                    if($data['language'] == 1) {
                                        $sms_send = $this->communication->sendsms($data['message'], $mobile, 'return_type', '203');    
                                    }else {
                                        $sms_send = $this->communication->languagesms($data['message'], $mobile, 'return_type', '203');   
                                    }
                                    $msg_count = count($booth_president);
                                    $data['msg_count'] = $msg_count;
                                    $sms_store = $this->SManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 68 && $data['user_role'] == 3) { //Sheet president
                                $sheet_president = $this->SManagerModel->getSheetPresidentBySM($data['user_id']);
                                if($sheet_president) {
                                    foreach($sheet_president as $sp) {
                                        $mobile[] = $sp->mobile;
                                    }
                                    $this->load->library('communication');
                                    if($data['language'] == 1) {
                                        $sms_send = $this->communication->sendsms($data['message'], $mobile, 'return_type', '203');    
                                    }else {
                                        $sms_send = $this->communication->languagesms($data['message'], $mobile, 'return_type', '203');   
                                    }
                                    $msg_count = count($sheet_president);
                                    $data['msg_count'] = $msg_count;
                                    $sms_store = $this->SManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 136 && $data['user_role'] == 46) { //family head
                                $family_head = $this->SManagerModel->getFamilyHeadBySM($data['user_id']);
                                if($family_head) {
                                    foreach($family_head as $fh) {
                                        if($fh->mobile != '') {
                                            $mobile[] = $fh->mobile;
                                        }    
                                    }
                                    $this->load->library('communication');
                                    if($data['language'] == 1) {
                                        $sms_send = $this->communication->sendsms($data['message'], $mobile, 'return_type', '203');    
                                    }else {
                                        $sms_send = $this->communication->languagesms($data['message'], $mobile, 'return_type', '203');   
                                    }
                                    $msg_count = count($mobile);
                                    $data['msg_count'] = $msg_count;
                                    $sms_store = $this->SManagerModel->saveSms($data);    
                                }
                            }

                            if($sms_send && $sms_store) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Message sent successfully to '.$msg_count . ' users',
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
                                'fail' => 'Forbidden - Not a group sms',
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

    public function singlesms() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/singlesms';
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
                            $sms_store = $this->SManagerModel->saveSms($data);

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
            $activity['request'] = 'SManager/outbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $sent_sms = $this->SManagerModel->getSentSms($data['user_id']);
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

    public function inbox() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/inbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $inbox = $this->SManagerModel->getSms($data['user_id']);
                    if($inbox) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array (
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $inbox     
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

    public function msgread() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/msgread';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('sms_id', 'read'); //mandatory fields
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
                        $read = $this->SManagerModel->smsread($data['sms_id'], $data['read'], $data['user_id']);
                        if($read) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Message read',
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

    public function help() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/help';
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
                        $help = $this->SManagerModel->saveHelpQuery($data);
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

    public function smartmedia() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/smartmedia';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $media = $this->SManagerModel->getSmartMedia();
                    if($media) {
                        foreach($media as $m) {
                            $m->media_path = base_url($this->config->item('assets_images')).$m->media_path;
                            $m->post_likes = $this->SManagerModel->getPostLikes($m->id);
                            $m->like = $this->SManagerModel->getPostLikeByUser($m->id, $data['user_id']);
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
            $activity['request'] = 'SManager/postlike';
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
                        $post_like = $this->SManagerModel->savePostLike($data);
                        
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

    public function dashboard() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/dashboard';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
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
                        $dh = $this->SManagerModel->getDHCount($data['user_id']);
                        $dincharge = $this->SManagerModel->getDICount($data['user_id']);
                        $voters = $this->SManagerModel->getVotersCount($data['user_id']);
                        

                        $update = $this->SManagerModel->getAppVersion($data['app_id']);
                        if($update) {
                            if($update->version == $data['version']) {
                                $app_status = 1;
                            }else {
                                $app_status = 0;
                            }
                        }else {
                            $app_status = 1;
                        }
                        
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => array(
                                'dhead' => $dh,
                                'dincharge' => $dincharge,
                                'voters' => $voters,
                                'app_status' => $app_status
                            )    
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
                $file = 'apps/seniormanager.apk';
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

    public function overallstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/overallstatus';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    //recruitment
                    $status_d['division_head'] = $this->SManagerModel->getDHCount($data['user_id']);
                    $status_d['division_incharge'] = $this->SManagerModel->getDICount($data['user_id']);
                    $status_d['booth_coordinator'] = $this->SManagerModel->getBCCount($data['user_id']);
                    $status_d['booth_president'] = $this->SManagerModel->getBPCount($data['user_id']);
                    $status_d['telecaller'] = $this->SManagerModel->getTCCount($data['user_id']);
                    $status_d['street_president'] = $this->SManagerModel->getSPCount($data['user_id']);

                    //registration
                    $status_d['family_head'] = $this->SManagerModel->getFHCount($data['user_id']);
                    $status_d['total_voters'] = $this->SManagerModel->getVotersCount($data['user_id']);
                    $status_d['positive'] = $this->SManagerModel->getVotersCount($data['user_id'], array('v.voter_status'=>12));
                    $status_d['negative'] = $this->SManagerModel->getVotersCount($data['user_id'], array('v.voter_status'=>13));
                    $status_d['neutral'] = $this->SManagerModel->getVotersCount($data['user_id'], array('v.voter_status'=>14));

                    //activity log
                    $activity['http_status'] = 200;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'response' => 'Success.',
                        'status' => 1,
                        'data' => $status_d     
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

    /**
     * HTMl Response - Charts
     */
    public function professionteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/professionteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['housewife'] = $this->SManagerModel->getprofessionreport($data['user_role'], 84);
                        $data['student'] = $this->SManagerModel->getprofessionreport($data['user_role'], 85);
                        $data['farmer'] = $this->SManagerModel->getprofessionreport($data['user_role'], 86);
                        $data['caste_profession'] = $this->SManagerModel->getprofessionreport($data['user_role'], 87);
                        $data['employee'] = $this->SManagerModel->getprofessionreport($data['user_role'], 88);
                        $data['agriculture_labour'] = $this->SManagerModel->getprofessionreport($data['user_role'], 89);
                        $data['unemployee'] = $this->SManagerModel->getprofessionreport($data['user_role'], 90);
                        $data['business'] = $this->SManagerModel->getprofessionreport($data['user_role'], 91);
                        $data['other'] = $this->SManagerModel->getprofessionreport($data['user_role'], 92);

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/profession.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/profession-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    public function partyparticipationteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/partyparticipationteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['party_leader'] = $this->SManagerModel->getparticipationreport($data['user_role'], 93);
                        $data['party_member'] = $this->SManagerModel->getparticipationreport($data['user_role'], 94);
                        $data['party_sympathiser'] = $this->SManagerModel->getparticipationreport($data['user_role'], 95);
                        $data['neutral_worker'] = $this->SManagerModel->getparticipationreport($data['user_role'], 96);
                        

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/participation.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/participation-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    public function personalstatusteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/personalstatusteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['joint_family'] = $this->SManagerModel->getPersonalStatusreport($data['user_role'], 97);
                        $data['independent_family'] = $this->SManagerModel->getPersonalStatusreport($data['user_role'], 98);
                        $data['own_vehicle'] = $this->SManagerModel->getPersonalStatusreport($data['user_role'], 99);
                        $data['own_house'] = $this->SManagerModel->getPersonalStatusreport($data['user_role'], 100);
                        $data['rental_house'] = $this->SManagerModel->getPersonalStatusreport($data['user_role'], 101);
                        $data['none'] = $this->SManagerModel->getPersonalStatusreport($data['user_role'], 101);
                        

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/status.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/status-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    public function familyvotersteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/familyvotersteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['ten'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 103);
                        $data['nine'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 106);
                        $data['eight'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 107);
                        $data['seven'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 108);
                        $data['six'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 104);
                        $data['five'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 109);
                        $data['four'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 105);
                        $data['three'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 110);
                        $data['two'] = $this->SManagerModel->getFamilyVotersreport($data['user_role'], 111);
                        

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/family-voters.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/family-voters-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    public function govtschemeteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/govtschemeteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['ntr_housing'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 112);
                        $data['farmer_loan'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 113);
                        $data['chandranna_pellikanuka'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 114);
                        $data['pension'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 115);
                        $data['unemployee_benefit'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 116);
                        $data['chandranna_insurance'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 117);
                        $data['crop_insurance'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 118);
                        $data['free_bicycle'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 119);
                        $data['free_mobile'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 120);
                        $data['aarogya_raksha'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 121);
                        $data['other'] = $this->SManagerModel->getGovtSchemereport($data['user_role'], 122);
                        

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/govt-scheme.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/govt-scheme-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    public function ysrcpschemeteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/ysrcpschemeteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['pension_increment'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 123);
                        $data['aarogyasree'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 124);
                        $data['farmer_welfare'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 125);
                        $data['fee_reimbursement'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 126);
                        $data['housing_scheme'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 127);
                        $data['ysr_asara'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 128);
                        $data['water_irrigation'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 129);
                        $data['loan'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 130);
                        $data['alcohol_prohibition'] = $this->SManagerModel->getYSRCPSchemereport($data['user_role'], 131);
                        
                        

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/ysr-scheme.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/ysr-scheme-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    public function votecommitmentteamreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/votecommitmentteamreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_role');
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
                        $data['p100'] = $this->SManagerModel->getCommitmentreport($data['user_role'], 132);
                        $data['p50'] = $this->SManagerModel->getCommitmentreport($data['user_role'], 133);
                        $data['neutral'] = $this->SManagerModel->getCommitmentreport($data['user_role'], 134);
                        $data['cancel'] = $this->SManagerModel->getCommitmentreport($data['user_role'], 135);
                        

                        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                        $this->load->view('includes/app/header.php');
                        $this->load->view('apps/smanager/commitment.php');
                        $this->load->view('includes/app/plugins.php', $data);
                        $this->load->view('scripts/apps/smanager/commitment-script.php', $data);
                        $this->load->view('includes/footer.php');
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

    /**
     * Date : 23-01-19
     */
    public function allboothcoordinator() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/allboothcoordinator';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $booth_coordinator = $this->SManagerModel->getBoothCoordinatorBySM($data['user_id']);
                    if($booth_coordinator) {
                        foreach($booth_coordinator as $bc) {
                            $ps = $this->SManagerModel->getBoothObserverPS($bc->id);
                            if($bc->photo != '') {
                                $bc->photo = base_url($this->config->item('assets_users')).$bc->photo;
                            }else {
                                if($bc->gid == 4) {
                                    $bc->photo = base_url($this->config->item('assets_male'));
                                }elseif($bc->gid == 5) {
                                    $bc->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            if($ps) {
                                $bc->ps = $ps;
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
                            'data' => $booth_coordinator    
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

    public function allboothpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/allboothpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $booth_president = $this->SManagerModel->getBoothPresidentBySM($data['user_id']);
                        if($booth_president) {
                            foreach($booth_president as $bp) {
                                if($bp->photo != '') {
                                    $bp->photo = base_url($this->config->item('assets_users')).$bp->photo;
                                }else {
                                    if($bp->gid == 4) {
                                        $bp->photo = base_url($this->config->item('assets_male'));
                                    }elseif($bp->gid == 5) {
                                        $bp->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $booth_president    
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

    public function allstreetpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/allstreetpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $sheet_president = $this->SManagerModel->getSheetPresidentBySM($data['user_id']);
                        if($sheet_president) {
                            foreach($sheet_president as $sp) {
                                if($sp->photo != '') {
                                    $sp->photo = base_url($this->config->item('assets_users')).$sp->photo;
                                }else {
                                    if($sp->gid == 4) {
                                        $sp->photo = base_url($this->config->item('assets_male'));
                                    }elseif($sp->gid == 5) {
                                        $sp->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $sheet_president    
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

    public function allfamilyhead() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/allfamilyhead';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $family_head = $this->SManagerModel->getFamilyHeadBySM($data['user_id']);
                        if($family_head) {
                            foreach($family_head as $fh) {
                                if($fh->photo != '') {
                                    $fh->photo = base_url($this->config->item('assets_voters')).$fh->photo;
                                }else {
                                    if($fh->gender == 4) {
                                        $fh->photo = base_url($this->config->item('assets_male'));
                                    }elseif($fh->gender == 5) {
                                        $fh->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $family_head    
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

    public function teamvalidation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/teamvalidation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('vid', 'user_role');
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
                        $validation = $this->SManagerModel->getTeamValidationByUser($data['vid'], $data['user_role']);
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $validation    
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

    public function telecallervalidation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/telecallervalidation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('vid', 'user_role', 'report');
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
                        $validation = $this->SManagerModel->getTeleValidationByUser($data['vid'], $data['user_role'], $data['report']);
                        if($validation) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Success.',
                                'status' => 1,
                                'data' => $validation    
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 500;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Internal Serval Error.',
                                'status' => 0 
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
    }

    /**
     * Date : 13-02-2019
     * Author : Anees
     */
    public function allusersbyps() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'SManager/allusersbyps';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('psid', 'user_role');
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
                        $allUsers = $this->SManagerModel->getAllUsersByPS($data['psid'], $data['user_role']);
                        if($allUsers) {
                            foreach($allUsers as $u) {
                                if($u->photo != '' && ($data['user_role'] == 55 || $data['user_role'] == 3)) {
                                    $u->photo = base_url($this->config->item('assets_users')).$u->photo;
                                }elseif($u->photo != '' && $data['user_role'] == 46) {
                                    $u->photo = base_url($this->config->item('assets_voters')).$u->photo;
                                }else {
                                    if($u->gid == 4) {
                                        $u->photo = base_url($this->config->item('assets_male'));
                                    }elseif($u->gid == 5) {
                                        $u->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $allUsers    
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
}