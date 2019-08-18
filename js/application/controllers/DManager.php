<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DManager extends CI_Controller {
    private $_authorized;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('DManagerModel');
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

    public function dashboard() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/dashboard';
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
                        $bpresident = $this->DManagerModel->getBPresidentCount($data['user_id']);
                        $spresident = $this->DManagerModel->getSPresidentCount($data['user_id']);
                        $voters = $this->DManagerModel->getVotersCount($data['user_id']);

                        $update = $this->DManagerModel->getAppVersion($data['app_id']);
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
                                'voters' => $voters,
                                'booth_p' => $bpresident,
                                'sheet_p' => $spresident,
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

    public function getps() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/getps';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $getps = $this->DManagerModel->getMandalPs($data['user_id']);
                    if($getps) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $getps    
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
    
    public function boothpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/boothpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $booth_president = $this->DManagerModel->getBoothPresidentByDM($data['user_id']);
                    if($booth_president) {
                        foreach($booth_president as $bp) {
                            $bp->sp_count = $this->DManagerModel->getSPCountByBP($bp->id);
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

    public function sheetpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/sheetpresident';
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
                        $sheet_president = $this->DManagerModel->getSheetPresidentByBP($data['bpid']);
                        if($sheet_president) {
                            foreach($sheet_president as $sp) {
                                $sp->v_count = $this->DManagerModel->getVotersCountBySP($sp->id);
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

    public function votersbysp() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/votersbysp';
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
                        $voters = $this->DManagerModel->getVotersBySP($data['spid']);
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

    public function votersbyPS() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/votersbyPS';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('psid'); //mandatory fields
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
                        $voters = $this->DManagerModel->getVotersByPS($data['psid']);
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

    public function smartmedia() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/smartmedia';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $media = $this->DManagerModel->getSmartMedia();
                    if($media) {
                        foreach($media as $m) {
                            $m->media_path = base_url($this->config->item('assets_images')).$m->media_path;
                            $m->post_likes = $this->DManagerModel->getPostLikes($m->id);
                            $m->like = $this->DManagerModel->getPostLikeByUser($m->id, $data['user_id']);
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
            $activity['request'] = 'DManager/postlike';
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
                        $post_like = $this->DManagerModel->savePostLike($data);
                        
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
                        $smsstore = $this->DManagerModel->saveMessage($data);
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

    /* Status */
    public function overallstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/overallstatus';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    //recruitment
                    $status_d['booth_coordinator'] = $this->DManagerModel->getBCCount($data['user_id']);
                    $status_d['booth_president'] = $this->DManagerModel->getBPresidentCount($data['user_id']);
                    $status_d['street_president'] = $this->DManagerModel->getSPresidentCount($data['user_id']);
                    $status_d['telecaller'] = $this->DManagerModel->getTCCount($data['user_id']);

                    //registration
                    $status_d['registration'] = $this->DManagerModel->getVotersCount($data['user_id']);
                    $status_d['positive'] = $this->DManagerModel->getVotersCount($data['user_id'], array('v.voter_status'=>12));
                    $status_d['negative'] = $this->DManagerModel->getVotersCount($data['user_id'], array('v.voter_status'=>13));
                    $status_d['neutral'] = $this->DManagerModel->getVotersCount($data['user_id'], array('v.voter_status'=>14));

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

    public function addboothpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/addboothpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'gender', 'mobile', 'psid'); //mandatory fields
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
                        $bp_exists = $this->DManagerModel->bpExistsByPs($data['psid']);
                        if($bp_exists) {
                            //activity log
                            $activity['http_status'] = 406;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(406);
                            $data = array(
                                'fail' => 'Not Accepted - Booth President already allocated to selected polling station.',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }else {
                            $mobile = $this->DManagerModel->mobileVerify($data['mobile']);
                            if($this->input->post('email')) {
                                $email = $this->DManagerModel->emailVerify($data['email']);
                            }else {
                                $email = false;
                            }
                            if(!$mobile && !$email) {
                                if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                                    //upload photo
                                    $config['upload_path']   = $this->config->item('assets_users');
                                    $config['allowed_types'] = 'png|jpg|jpeg';
                                    // $config['max_size']  = 2048;
                                    $config['file_name'] = time().$data['mobile'];
                                    $this->load->library('upload', $config);
            
                                    if($this->upload->do_upload('photo')) {
                                        $uploadData = $this->upload->data();
                                        $uploadedFile = $uploadData['file_name'];
                                        $data['photo'] = $uploadedFile;
                                        //image resize
                                        $options = array(
                                            'source_path' => $this->config->item('assets_users'),
                                            'width' => 250,
                                            'height' => 250
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
                                    $data['photo'] = null;
                                }
                                $add_u = $this->DManagerModel->addBoothPresident($data);
                                if($add_u) {
                                    $this->_httpStatus = http_response_code(201);
                                    $data = array(
                                        'success' => 'Created - User information is successfully saved.',
                                        'status' => 1,
                                        'data' => array(
                                            'user_id' => $add_u
                                        )     
                                    );
                                    echo $this->__json_output('Success', $data);
                                }else {
                                    $this->_httpStatus = http_response_code(500);
                                    $data = array(
                                        'fail' => 'Internal Server Error - We could not complete your request.',
                                        'status' => 0     
                                    );
                                    echo $this->__json_output('Failure', $data);
                                }
                            }else {
                                $this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Mobile already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data);
                            }
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

    public function resizeImage($filename, array $options) {
        //   $source_path = $this->config->item('assets_events') . $filename;
        //   $target_path =  $this->config->item('assets_eventsthm');
        $config_manip = array(
          'image_library' => 'gd2',
          'source_image' => $options['source_path'] .$filename,
        //   'new_image' => $target_path,
          'maintain_ratio' => TRUE,
        //   'create_thumb' => TRUE,
        //   'thumb_marker' => '_thumb',
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

    public function addboothobserver() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/addboothobserver';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'gender', 'mobile'); //mandatory fields
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
                        if($this->input->post('psid')) {
                            $count_ps = count($data['psid']);
                            if($count_ps > 0) {
                                $ps_exists = array();
                                foreach($data['psid'] as $ps) {
                                    $bo_exists = $this->DManagerModel->boExistsByPs($ps);
                                    if($bo_exists) {
                                        $ps_exists[] = $bo_exists->ps_no;
                                    }
                                }
                                if(count($ps_exists) > 0) {
                                    //activity log
                                    $activity['http_status'] = 406;
                                    $activity['status'] = 0;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(406);
                                    $data = array(
                                        'fail' => 'Not Accepted - Booth Observer already allocated to selected polling station.',
                                        'status' => 0,
                                        'ps' => $ps_exists     
                                    );
                                    echo $this->__json_output('Failure', $data);
                                }else {
                                    $mobile = $this->DManagerModel->mobileVerify($data['mobile']);
                                    if($this->input->post('email')) {
                                        $email = $this->DManagerModel->emailVerify($data['email']);
                                    }else {
                                        $email = false;
                                    }
                                    if(!$mobile && !$email) {
                                        if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                                            //upload photo
                                            $config['upload_path']   = $this->config->item('assets_users');
                                            $config['allowed_types'] = 'png|jpg|jpeg';
                                            // $config['max_size']  = 2048;
                                            $config['file_name'] = time().$data['mobile'];
                                            $this->load->library('upload', $config);
                    
                                            if($this->upload->do_upload('photo')) {
                                                $uploadData = $this->upload->data();
                                                $uploadedFile = $uploadData['file_name'];
                                                $data['photo'] = $uploadedFile;
                                                //image resize
                                                $options = array(
                                                    'source_path' => $this->config->item('assets_users'),
                                                    'width' => 250,
                                                    'height' => 250
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
                                            $data['photo'] = null;
                                        }
                                        $add_u = $this->DManagerModel->addBoothObserver($data);
                                        if($add_u) {
                                            $this->_httpStatus = http_response_code(201);
                                            $data = array(
                                                'success' => 'Created - User information is successfully saved.',
                                                'status' => 1,
                                                'data' => array(
                                                    'user_id' => $add_u
                                                )     
                                            );
                                            echo $this->__json_output('Success', $data);
                                        }else {
                                            $this->_httpStatus = http_response_code(500);
                                            $data = array(
                                                'fail' => 'Internal Server Error - We could not complete your request.',
                                                'status' => 0     
                                            );
                                            echo $this->__json_output('Failure', $data);
                                        }
                                    }else {
                                        $this->_httpStatus = http_response_code(406);
                                        $data = array(
                                            'fail' => 'Not Accepted - Mobile already exists.',
                                            'status' => 0     
                                        );
                                        echo $this->__json_output('Failure', $data);
                                    }
                                }
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Send atleast one polling station.',
                                    'status' => 2
                                         
                                );
                                echo $this->__json_output('Failure', $data); 
                            }
                        }else {
                            $this->_httpStatus = http_response_code(400);
                            $data = array(
                                'fail' => 'Bad Request - Mandatory fields are missing.',
                                'status' => 0,
                                'fields' => 'psid'    
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

    public function boothobserver() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/boothobserver';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $booth_observer = $this->DManagerModel->getBoothObserverByDM($data['user_id']);
                    if($booth_observer) {
                        foreach($booth_observer as $bp) {
                            $ps = $this->DManagerModel->getBoothObserverPS($bp->id);
                            if($bp->photo != '') {
                                $bp->photo = base_url($this->config->item('assets_users')).$bp->photo;
                            }else {
                                if($bp->gid == 4) {
                                    $bp->photo = base_url($this->config->item('assets_male'));
                                }elseif($bp->gid == 5) {
                                    $bp->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            if($ps) {
                                $bp->ps = $ps;
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
                            'data' => $booth_observer    
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

    public function familyhead() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/sheetpresident';
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
                        $family_head = $this->DManagerModel->getFamilyHeadBySP($data['spid']);
                        if($family_head) {
                            foreach($family_head as $fh) {
                                $fh->v_count = $this->DManagerModel->getVotersCountByFH($fh->id);
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
                }
            }
        }
    }

    public function votersbyfh() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/votersbyfh';
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
                        $v_members = $this->DManagerModel->getVotersByFH($data['fhid']);
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

    //validation
    public function validationreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/validationreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('val_id', 'user_role'); //mandatory fields
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
                        $user_exists = $this->DManagerModel->userExists($data['val_id'], $data['user_role']);
                        if($user_exists) {
                            if($this->input->post('profession')) {
                                $validate = $this->DManagerModel->saveValidation($data, 'profession');    
                            }
                            if($this->input->post('party_participation')) {
                                $validate = $this->DManagerModel->saveValidation($data, 'party_participation');    
                            }
                            if($this->input->post('personal_status')) {
                                $validate = $this->DManagerModel->saveValidation($data, 'personal_status');    
                            }
                            if($this->input->post('family_voters')) {
                                $validate = $this->DManagerModel->saveValidation($data, 'family_voters');    
                            }
                            if($this->input->post('vote_commitment')) {
                                $validate = $this->DManagerModel->saveValidation($data, 'vote_commitment');    
                            }
                            

                            if($validate) {
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
                                'fail' => 'Forbidden - User does not exists',
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

    //validation 1
    public function govtschemevalidation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/govtschemevalidation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('val_id', 'user_role'); //mandatory fields
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
                        $user_exists = $this->DManagerModel->userExists($data['val_id'], $data['user_role']);
                        if($user_exists) {
                            if($this->input->post('govt_scheme')) {
                                foreach($data['govt_scheme'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['govt_scheme'][$k]);
                                    }
                                }
                                $count_v = count($data['govt_scheme']);
                                if($count_v > 0) {
                                    $save_v = $this->DManagerModel->saveGovtSchemeValidation($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Validation saved successfully',
                                            'status' => 1 
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
                                    $this->_httpStatus = http_response_code(400);
                                    $data = array(
                                        'fail' => 'Send atleast one option.',
                                        'status' => 2
                                             
                                    );
                                    echo $this->__json_output('Failure', $data); 
                                }
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => 'govt_scheme'    
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
                                'fail' => 'Forbidden - User does not exists',
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

    //validation 2
    public function ysrschemevalidation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/govtschemevalidation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('val_id', 'user_role'); //mandatory fields
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
                        $user_exists = $this->DManagerModel->userExists($data['val_id'], $data['user_role']);
                        if($user_exists) {
                            if($this->input->post('ysr_scheme')) {
                                foreach($data['ysr_scheme'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['ysr_scheme'][$k]);
                                    }
                                }
                                $count_v = count($data['ysr_scheme']);
                                if($count_v > 0) {
                                    $save_v = $this->DManagerModel->saveYSRSchemeValidation($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Validation saved successfully',
                                            'status' => 1 
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
                                    $this->_httpStatus = http_response_code(400);
                                    $data = array(
                                        'fail' => 'Send atleast one option.',
                                        'status' => 2
                                             
                                    );
                                    echo $this->__json_output('Failure', $data); 
                                }
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => 'ysr_scheme'    
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
                                'fail' => 'Forbidden - User does not exists',
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

    public function validationreportstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/validationreport';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('val_id', 'user_role'); //mandatory fields
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
                        $validation = array();
                        $validate = $this->DManagerModel->validationExists($data['val_id'], $data['user_role']);
                        if($validate) {
                            if($validate->profession != 0) {
                                $validation['profession'] = array(
                                    'status_code' => 1,
                                    'status' => 'Completed'    
                                );
                            }elseif($validate->profession == 0) {
                                $validation['profession'] = array(
                                    'status_code' => 2,
                                    'status' => 'Pending'
                                );
                            }
                            if($validate->party_participation != 0) {
                                $validation['party_participation'] = array(
                                    'status_code' => 1,
                                    'status' => 'Completed'
                                );
                            }elseif($validate->party_participation == 0) {
                                $validation['party_participation'] = array(
                                    'status_code' => 2,
                                    'status' => 'Pending'
                                );
                            }
                            if($validate->personal_status != 0) {
                                $validation['personal_status'] = array(
                                    'status_code' => 1,
                                    'status' => 'Completed'
                                );
                            }elseif($validate->personal_status == 0) {
                                $validation['personal_status'] = array(
                                    'status_code' => 2,
                                    'status' => 'Pending'
                                );
                            }
                            if($validate->family_voters != 0) {
                                $validation['family_voters'] = array(
                                    'status_code' => 1,
                                    'status' => 'Completed'
                                );
                            }elseif($validate->family_voters == 0) {
                                $validation['family_voters'] = array(
                                    'status_code' => 2,
                                    'status' => 'Pending'
                                );
                            }
                            if($validate->vote_commitment != 0) {
                                $validation['vote_commitment'] = array(
                                    'status_code' => 1,
                                    'status' => 'Completed'
                                );
                            }elseif($validate->vote_commitment == 0) {
                                $validation['vote_commitment'] = array(
                                    'status_code' => 2,
                                    'status' => 'Pending'
                                );
                            }
                        }else {
                            $validation['profession'] = array(
                                'status_code' => 2,
                                'status' => 'Pending'
                            );
                            $validation['party_participation'] = array(
                                'status_code' => 2,
                                'status' => 'Pending'
                            );
                            $validation['personal_status'] = array(
                                'status_code' => 2,
                                'status' => 'Pending'
                            );
                            $validation['family_voters'] = array(
                                'status_code' => 2,
                                'status' => 'Pending'
                            );
                            $validation['vote_commitment'] = array(
                                'status_code' => 2,
                                'status' => 'Pending'
                            );
                        }

                        //validation 1
                        $val_1 = $this->DManagerModel->validationOneStatus($data['val_id'], $data['user_role']);
                        if($val_1) {
                            $status = $val_1->status;
                            if($val_1->updated_at == null) {
                                $visit_time = $val_1->created_at;
                            }else {
                                $visit_time = $val_1->updated_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $validation['govt_scheme'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_validate' => $visit_time
                            );
                        }else {
                            $validation['govt_scheme'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //validation 1
                        $val_2 = $this->DManagerModel->validationTwoStatus($data['val_id'], $data['user_role']);
                        if($val_2) {
                            $status = $val_2->status;
                            if($val_2->updated_at == null) {
                                $visit_time = $val_2->created_at;
                            }else {
                                $visit_time = $val_2->updated_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $validation['ysr_scheme'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_validate' => $visit_time
                            );
                        }else {
                            $validation['ysr_scheme'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Validation Status.',
                            'status' => 1,
                            'data' => array(
                                'validation' => $validation
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
                $this->_httpStatus = http_response_code(401);
                $data = array(
                    'fail' => 'Unauthorized - Token failed. Login again.',
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
                $file = 'apps/mandalpresident.apk';
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

    public function groupsms() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/groupsms';
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
                            
                            if($data['receiver_group'] == 69 && $data['user_role'] == 55) { //booth coordinator
                                $booth_coordinators = $this->DManagerModel->getBoothObserverByDM($data['user_id']);
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
                                    $sms_store = $this->DManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 67 && $data['user_role'] == 18) { //booth president
                                $booth_president = $this->DManagerModel->getBoothPresidentByDM($data['user_id']);
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
                                    $sms_store = $this->DManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 68 && $data['user_role'] == 3) { //Sheet president
                                $sheet_president = $this->DManagerModel->getSheetPresidentByDM($data['user_id']);
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
                                    $sms_store = $this->DManagerModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 136 && $data['user_role'] == 46) { //family head
                                $family_head = $this->DManagerModel->getFamilyHeadByDM($data['user_id']);
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
                                    $sms_store = $this->DManagerModel->saveSms($data);    
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
            $activity['request'] = 'DManager/singlesms';
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
                            $sms_store = $this->DManagerModel->saveSms($data);

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

    //outbox sms
    public function outbox() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/outbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $sent_sms = $this->DManagerModel->getSentSms($data['user_id']);
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

    //inbox sms
    public function inbox() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/inbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $inbox = $this->DManagerModel->getSms($data['user_id']);
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
            $activity['request'] = 'DManager/msgread';
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
                        $read = $this->DManagerModel->smsread($data['sms_id'], $data['read'], $data['user_id']);
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

    public function help() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/help';
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
                        $help = $this->DManagerModel->saveHelpQuery($data);
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

    /**
     * Date : 03-01-2019
     */
    public function addtelecaller() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/addtelecaller';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'gender', 'mobile', 'psid'); //mandatory fields
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
                        $tc_exists = $this->DManagerModel->tcExistsByPs($data['psid']);
                        if($tc_exists) {
                            //activity log
                            $activity['http_status'] = 406;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(406);
                            $data = array(
                                'fail' => 'Not Accepted - Tele Caller already allocated to selected polling station.',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        }else {
                            $mobile = $this->DManagerModel->mobileVerify($data['mobile']);
                            if($this->input->post('email')) {
                                $email = $this->DManagerModel->emailVerify($data['email']);
                            }else {
                                $email = false;
                            }
                            if(!$mobile && !$email) {
                                if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                                    //upload photo
                                    $config['upload_path']   = $this->config->item('assets_users');
                                    $config['allowed_types'] = 'png|jpg|jpeg';
                                    // $config['max_size']  = 2048;
                                    $config['file_name'] = time().$data['mobile'];
                                    $this->load->library('upload', $config);
            
                                    if($this->upload->do_upload('photo')) {
                                        $uploadData = $this->upload->data();
                                        $uploadedFile = $uploadData['file_name'];
                                        $data['photo'] = $uploadedFile;
                                        //image resize
                                        $options = array(
                                            'source_path' => $this->config->item('assets_users'),
                                            'width' => 250,
                                            'height' => 250
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
                                    $data['photo'] = null;
                                }
                                $add_u = $this->DManagerModel->addTeleCaller($data);
                                if($add_u) {
                                    $this->_httpStatus = http_response_code(201);
                                    $data = array(
                                        'success' => 'Created - User information is successfully saved.',
                                        'status' => 1,
                                        'data' => array(
                                            'user_id' => $add_u
                                        )     
                                    );
                                    echo $this->__json_output('Success', $data);
                                }else {
                                    $this->_httpStatus = http_response_code(500);
                                    $data = array(
                                        'fail' => 'Internal Server Error - We could not complete your request.',
                                        'status' => 0     
                                    );
                                    echo $this->__json_output('Failure', $data);
                                }
                            }else {
                                $this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Mobile already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data);
                            }
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

    public function telecaller() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'DManager/telecaller';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $telecaller = $this->DManagerModel->getTelecaller($data['user_id']);
                    if($telecaller) {
                        foreach($telecaller as $tc) {
                            if($tc->photo != '') {
                                $tc->photo = base_url($this->config->item('assets_users')).$tc->photo;
                            }else {
                                if($tc->gid == 4) {
                                    $tc->photo = base_url($this->config->item('assets_male'));
                                }elseif($tc->gid == 5) {
                                    $tc->photo = base_url($this->config->item('assets_female'));
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
                            'data' => $telecaller    
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
}