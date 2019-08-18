<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MobileTeam extends CI_Controller {
    private $_authorized;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('MTeamModel');
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

    public function managers() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/managers';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $managers = $this->MTeamModel->getManagers($data['user_id']);
                    if($managers) {
                        foreach($managers as $vd) {
                            if($vd->photo !== '') {
                                $vd->photo = base_url($this->config->item('assets_users')).$vd->photo;
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
                            'data' => $managers    
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

    public function teamleaders() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/teamleaders';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $teamleader = $this->MTeamModel->getBoothPresidentByBC($data['user_id']);
                    if($teamleader) {
                        foreach($teamleader as $vd) {
                            if($vd->photo !== '') {
                                $vd->photo = base_url($this->config->item('assets_users')).$vd->photo;
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
                            'data' => $teamleader    
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

    public function coordinators() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/coordinators';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $coordinators = $this->MTeamModel->getCoordinators($data['user_id']);
                    if($coordinators) {
                        foreach($coordinators as $vd) {
                            if($vd->photo !== '') {
                                $vd->photo = base_url($this->config->item('assets_users')).$vd->photo;
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
                            'data' => $coordinators    
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

    public function volunteers() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/volunteers';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $volunteers = $this->MTeamModel->getVolunteers($data['user_id']);
                    if($volunteers) {
                        foreach($volunteers as $vd) {
                            if($vd->photo !== '') {
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
                            'data' => $volunteers    
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

    //task
    public function task() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/task';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $user_id = $data['user_id'];
                    $group_task = $this->MTeamModel->getGroupTask($user_id);
                    $ind_task = $this->MTeamModel->getIndTask($user_id);
                    if($group_task && $ind_task) {
                        $tasks = array_merge($group_task, $ind_task); 
                    }elseif($group_task && $ind_task == false) {
                        $tasks = $group_task;
                    }elseif($ind_task && $group_task == false) {
                        $tasks = $ind_task;
                    }elseif($group_task == false && $ind_task == false) {
                        $tasks = 'No Tasks';
                    }
                    //activity log
                    $activity['http_status'] = 200;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'success' => 'Success.',
                        'status' => 1,
                        'data' => $tasks     
                    );
                    echo $this->__json_output('success', $data);
                    
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
    
    public function observation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/observation';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_id', 'msg_type', 'ob_type'); //mandatory fields
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
                        if($data['msg_type'] == 1) { //Text message
                            if(!$this->input->post('message')) {
                                $error_count += 1;
                                $error['fields'][] = 'message';
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => $error['fields']     
                                );
                                echo $this->__json_output('Failure', $data);    
                            }else {
                                $ob_text = $this->MTeamModel->saveObservationText($data);
                                if($ob_text) {
                                    //activity log
                                    $activity['http_status'] = 200;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(200);
                                    $data = array(
                                        'success' => 'Message sent successfully.',
                                        'status' => 1,
                                            
                                    );
                                    echo $this->__json_output('success', $data);
                                }else {
                                    //activity log
                                    $activity['http_status'] = 500;
                                    $activity['status'] = 0;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(500);
                                    $data = array(
                                        'fail' => 'Message could not sent.',
                                        'status' => 0,
                                            
                                    );
                                    echo $this->__json_output('success', $data); 
                                }
                            }

                        }elseif($data['msg_type'] == 2) { //Voice message
                            if(!$this->input->post('duration')) {
                                $error_count += 1;
                                $error['fields'][] = 'duration';      
                            }
                            // if(!$this->input->post('ob_voice')) {
                            //     $error_count += 1;
                            //     $error['fields'][] = 'ob_voice';      
                            // }
                            if($_FILES['ob_voice']['name'] == '') {
                                $error_count += 1;
                                $error['fields'][] = 'ob_voice';
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
                                // if($this->input->post('ob_voice') !== '') {
                                //     $ob_name = time().$data['user_id'].'.3gp';
                                //     $ob_path = 'uploads/mobile-team/'.$ob_name;

                                //     $ob_file = fopen($ob_path, 'wb');
                                //     if(fwrite($ob_file, base64_decode($data['ob_voice'])) === FALSE) {
                                //         echo json_encode(array('error' => 'Voice message not sent', 'status' => 0));
                                //         exit;
                                //     }
                                //     fclose($ob_file);
                                //     $data['ob_voice'] = $ob_name;
                                // }
                                if($_FILES['ob_voice']['name'] !== '') {
                                    //upload photo
                                    $config['upload_path']   = 'uploads/mobile-team/';
                                    $config['allowed_types'] = '*';
                                    $config['max_size']  = 100048;
                                    $config['file_name'] = time().$data['user_id'];
                                    $this->load->library('upload', $config);

                                    if($this->upload->do_upload('ob_voice')) {
                                        $uploadData = $this->upload->data();
                                        $uploadedFile = $uploadData['file_name'];
                                        $data['ob_voice'] = $uploadedFile;
                                    }else {
                                        //activity log
                                        $activity['http_status'] = 409;
                                        $activity['status'] = 0;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(409);
                                        $data = array(
                                            'fail' => 'Could not save file to server.',
                                            'status' => 0,
                                            'error' => $this->upload->display_errors('<p>', '</p>')    
                                        );
                                        echo $this->__json_output('Failure', $data);
                                    }
                                }        
                                $ob_v = $this->MTeamModel->saveObservationVoice($data);
                                if($ob_v) {
                                    //activity log
                                    $activity['http_status'] = 200;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(200);
                                    $data = array(
                                        'success' => 'Voice message sent successfully.',
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

    public function addEvent() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/addEvent';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $error_count = 0;
                    $error = array();
                    $fields = array('event_type',  'event_name', 'user_id', 'event_date'); //mandatory fields
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($_FILES['event_img']['name'] == '') {
                        $error_count += 1;
                        $error['fields'][] = 'event_img';
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
                        if($_FILES['event_img']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = $this->config->item('assets_events');
                            $config['allowed_types'] = 'png|jpg|jpeg';
                            // $config['max_size']  = 5120;
                            $config['file_name'] = time().$data['event_name'];
                            $this->load->library('upload', $config);

                            if($this->upload->do_upload('event_img')) {
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                
                                $data['event_img'] = $uploadedFile;
                                //image resize
                                $options = array(
                                    'source_path' => $this->config->item('assets_events'),
                                    'width' => 800,
                                    'height' => 600
                                );
                                
                                $this->resizeImage($uploadedFile, $options);
                                $this->imageThumb($uploadedFile);
                                $upload_data = $this->MTeamModel->addEvent($data);
                                if($upload_data) {
                                    //activity log
                                    $activity['http_status'] = 200;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(200);
                                    $data = array(
                                        'success' => 'Event saved successfully.',
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
                                // echo json_encode('failed to upload'); exit;
                                // echo json_encode($this->upload->display_errors('<p>', '</p>')); exit;
                                //activity log
                                $activity['http_status'] = 409;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(409);
                                $data = array(
                                    'fail' => 'Could not save file to server.',
                                    'status' => 0,
                                    'error' => $this->upload->display_errors('<p>', '</p>')    
                                );
                                echo $this->__json_output('Failure', $data);
                            }
                        }
                        
                        /*
                        $evnt_name = time().$data['event_name'].'.png';
                        $image_path = $this->config->item('assets_events').$evnt_name;
                        
                        // if(is_writable($image_path)) {
                            $evnt = fopen($image_path, 'wb');
                            if(fwrite($evnt, base64_decode($data['event_img'])) === FALSE) {
                                echo json_encode(array('error' => 'Photo could not be saved', 'status' => 0));
                                exit;
                            }
                            fclose($evnt);
                            $data['event_img'] = $evnt_name;
                        // }else {
                        //     echo json_encode(array('error' => 'Some error occurred could not write', 'status' => 0));
                        //     exit;
                        // }
                        $upload_data = $this->MTeamModel->addEvent($data);
                        if($upload_data) {
                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Event saved successfully.',
                                'status' => 1,
                                    
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            $this->_httpStatus = http_response_code(500);
                            $data = array(
                                'fail' => 'Internal Server Error - We could not complete your request.',
                                'status' => 0     
                            );
                            echo $this->__json_output('Failure', $data);
                        } */
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

    //sms
    public function smsdetails() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'TeamLeader/smsdetails';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
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
                        $smsstore = $this->MTeamModel->saveMessage($data);
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

    public function getps() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getps';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $getps = $this->MTeamModel->getMTeamPs($data['user_id']);
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

    public function getBoothPresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getBoothPresident';
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
                        $booth_president = $this->MTeamModel->getBoothPresidentByPs($data['psid']);
                        if($booth_president) {
                            foreach($booth_president as $bp) {
                                $bp->sp_count = $this->MTeamModel->getSPCountByBP($bp->id);
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

    public function getSheetPresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getSheetPresident';
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
                        $sheet_president = $this->MTeamModel->getSheetPresidentByBP($data['bpid']);
                        if($sheet_president) {
                            foreach($sheet_president as $sp) {
                                $sp->v_count = $this->MTeamModel->getVotersCountBySP($sp->id);
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

    public function getVotersBySP() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getVotersBySP';
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
                        $voters = $this->MTeamModel->getVotersBySP($data['spid']);
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
            $activity['request'] = 'MobileTeam/votersbyPS';
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
                        $voters = $this->MTeamModel->getVotersByPS($data['psid']);
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

    public function dashboard() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/dashboard';
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
                        $voters = $this->MTeamModel->getVotersCountByBO($data['user_id']);
                        $booth_p = $this->MTeamModel->getBPCountByBO($data['user_id']);
                        $sheet_p = $this->MTeamModel->getSPCountByBO($data['user_id']);

                        $update = $this->MTeamModel->getAppVersion($data['app_id']);
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
                                'booth_p' => $booth_p,
                                'sheet_p' => $sheet_p,
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

    public function resizeImage($filename, array $options) {
        //   $source_path = $this->config->item('assets_events') . $filename;
        //   $target_path =  $this->config->item('assets_eventsth');
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

    public function imageThumb($filename) {
        $source_path = $this->config->item('assets_events') . $filename;
        $target_path =  $this->config->item('assets_eventsth');
        $config_manip = array(
            'image_library' => 'gd2',
            'source_image' => $source_path,
            'new_image' => $target_path,
            'maintain_ratio' => TRUE,
            'create_thumb' => TRUE,
            'thumb_marker' => '_thumb',
            'width' => 200,
            'height' => 200
        );
        $this->load->library('image_lib');
        // Set your config up
        $this->image_lib->initialize($config_manip);
        if (!$this->image_lib->resize()) {
          echo $this->image_lib->display_errors();
        }
  
  
        $this->image_lib->clear();
    }

    public function addsheetpresident() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/addsheetpresident';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
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
                        $this->_httpStatus = http_response_code(400);
                        $data = array(
                            'fail' => 'Bad Request - Mandatory fields are missing.',
                            'status' => 0,
                            'fields' => $error['fields']     
                        );
                        echo $this->__json_output('Failure', $data); 
                    }else {
                        $mobile = $this->MTeamModel->mobileVerify($data['mobile']);
                        if($this->input->post('email')) {
                            $email = $this->MTeamModel->emailVerify($data['email']);
                        }else {
                            $email = false;
                        }
                        if(!$mobile && !$email) {
                            // echo 'ok'; exit;
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
                            $add_u = $this->MTeamModel->addUser($data);
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
                    $this->_httpStatus = http_response_code(401);
                    $data = array(
                        'fail' => 'Unauthorized - Token failed. Login again.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);
                }
            }
        }
    }

    //get citizen data if user is available and not registered by coordinator
    public function getUserByVoterId() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getUserByVoterId';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('voter_id');
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
                        $voter_exists = $this->MTeamModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $voter_available = $this->MTeamModel->voterRegisteredByCoordinator($data['voter_id']);
                            if($voter_available) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'status' => 1,
                                    'user_data' => $voter_available
                                );
                                echo $this->__json_output('Success', $data); 
                            }else {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Voter Id already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data); 
                            }    
                        }else {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 2;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'status' => 2,
                                'success' => 'No Records found. Data can be inserted'
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

    public function getSheetPresidentByPS() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getSheetPresident';
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
                        $sheet_president = $this->MTeamModel->getSheetPresidentByPSId($data['psid']);
                        if($sheet_president) {
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

    public function addVolunteer() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/addVolunteer';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'lastname', 'gender', 'age', 'voter_id', 'polling_station', 'mobile', 'local_status', 'group_id', 'relationship', 'voter_status', 'spid');
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
                        $voter_exists = $this->MTeamModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $is_registered = $this->MTeamModel->voterRegisteredByCoordinator($data['voter_id']);
                            if($is_registered) {
                                $vt_id = $is_registered->id;
                                $data['last_id'] = $vt_id;
                                
                                if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                                    //upload photo
                                    $config['upload_path']   = $this->config->item('assets_voters');
                                    $config['allowed_types'] = 'png|jpg|jpeg';
                                    //$config['max_size']  = 2048;
                                    $config['file_name'] = time().$data['voter_id'];
                                    $this->load->library('upload', $config);

                                    if($this->upload->do_upload('photo')) {
                                        $uploadData = $this->upload->data();
                                        $uploadedFile = $uploadData['file_name'];
                                        $data['photo'] = $uploadedFile;
                                        //image resize
                                        $options = array(
                                            'source_path' => $this->config->item('assets_voters'),
                                            'width' => 250,
                                            'height' => 250
                                        );
                                        $this->resizeImage($uploadedFile, $options);
                                    }else {
                                        //activity log
                                        $activity['http_status'] = 409;
                                        $activity['status'] = 0;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(409);
                                        $data = array(
                                            'fail' => 'Could not save file to server.',
                                            'status' => 0,
                                            'error' => $this->upload->display_errors('<p>', '</p>')    
                                        );
                                        echo $this->__json_output('Failure', $data);
                                    }
                                }
                                //$update_voter = $this->CoordinatorModel->updateVoter($data);
                                $update_data = array(
                                    'firstname' => $data['firstname'],
                                    'lastname' => $data['lastname'],
                                    'f_name' => $data['f_name'],
                                    'gender' => $data['gender'],
                                    'ps_no' => $data['polling_station'],
                                    'voter_status' => $data['voter_status'],
                                    'user_id' => $data['spid']
                                );
                                if(isset($data['photo'])) {
                                    $update_data['photo'] = $data['photo'];
                                }
                                if($this->input->post('age')) {
                                    $update_data['age'] = $data['age'];
                                }
                                if($this->input->post('caste')) {
                                    $update_data['caste'] = $data['caste'];
                                }
                                if($this->input->post('mobile')) {
                                    $update_data['mobile'] = $data['mobile'];
                                }
                                if($this->input->post('religion')) {
                                    $update_data['religion'] = $data['religion'];
                                }
                                if($this->input->post('category')) {
                                    $update_data['category'] = $data['category'];
                                }
                                $data['update'] = $update_data;
                                
                            }else {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Voter Id already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data); exit;
                            }
                             
                        }
                        //if geo coordinates are set
                        $coord = array();
                        if($this->input->post('lat') && $this->input->post('long')) {
                            $coord['lat'] = $this->input->post('lat');
                            $coord['lng'] = $this->input->post('long');
                            $data['coord'] = $coord;
                        }
                        
                        if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = $this->config->item('assets_voters');
                            $config['allowed_types'] = 'png|jpg|jpeg';
                            // $config['max_size']  = 2048;
                            $config['file_name'] = time().$data['voter_id'];
                            $this->load->library('upload', $config);

                            if($this->upload->do_upload('photo')) {
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                $data['photo'] = $uploadedFile;
                                //image resize
                                $options = array(
                                    'source_path' => $this->config->item('assets_voters'),
                                    'width' => 250,
                                    'height' => 250
                                );
                                $this->resizeImage($uploadedFile, $options);
                            }else {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(409);
                                $data = array(
                                    'fail' => 'Could not save file to server.',
                                    'status' => 0,
                                    'error' => $this->upload->display_errors('<p>', '</p>')    
                                );
                                echo $this->__json_output('Failure', $data);
                            }
                        }
                        if($data['local_status'] == 16) {
                            $os_fields = array('os_mobile', 'os_street', 'os_location');
                            foreach($os_fields as $fd) {
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
                                $os_data = array(
                                    'mobile' => $data['os_mobile'],
                                    'street' => $data['os_street'],
                                    'location' => $data['os_location']
                                );
                                if($this->input->post('os_landmark')) {
                                    $os_data['landmark'] = $data['os_landmark'];    
                                }
                                if($this->input->post('os_pincode')) {
                                    $os_data['pincode'] = $data['os_pincode'];    
                                }
                                
                                $data['outstation'] = $os_data;
                            }
                        }
                        if($data['group_id'] == 40) {
                            $data['parent_id'] = $data['spid'];
                            $data['user_role'] = 46;
                            
                        }
                        
                        $usr_id = $this->MTeamModel->addCitizen($data);
                        if($usr_id) {
                            //activity log
                            $activity['http_status'] = 201;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(201);
                            $data = array(
                                'success' => 'Created - User information is successfully saved.',
                                'status' => 1,
                                'data' => array(
                                    'user_id' => $usr_id
                                )     
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 500;
                            $activity['status'] = 2;
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
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
            
        }
    }

    public function getVolunteerBySP() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/getVolunteerBySP';
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
                        $volunteer = $this->MTeamModel->getVolunteerBySP($data['spid']);
                        if($volunteer) {
                            foreach($volunteer as $vlt) {
                                if($vlt->photo != '') {
                                    $vlt->photo = base_url($this->config->item('assets_voters')).$vlt->photo;
                                }else {
                                    if($vlt->gender == 4) {
                                        $vlt->photo = base_url($this->config->item('assets_male'));
                                    }elseif($vlt->gender == 5) {
                                        $vlt->photo = base_url($this->config->item('assets_female'));
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
                                'data' => $volunteer    
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

    public function addvoter() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/addvoter';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'lastname', 'gender', 'age', 'voter_id', 'polling_station',  'local_status', 'group_id', 'relationship', 'voter_status', 'spid');
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($this->input->post('group_id') == 40) {
                        if(!$this->input->post('vid')) {
                            $error_count += 1;
                            $error['fields'][] = 'vid';
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
                        $voter_exists = $this->MTeamModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $is_registered = $this->MTeamModel->voterRegisteredByCoordinator($data['voter_id']);
                            if($is_registered) {
                                $vt_id = $is_registered->id;
                                $data['last_id'] = $vt_id;
                                
                                if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                                    //upload photo
                                    $config['upload_path']   = $this->config->item('assets_voters');
                                    $config['allowed_types'] = 'png|jpg|jpeg';
                                    //$config['max_size']  = 2048;
                                    $config['file_name'] = time().$data['voter_id'];
                                    $this->load->library('upload', $config);

                                    if($this->upload->do_upload('photo')) {
                                        $uploadData = $this->upload->data();
                                        $uploadedFile = $uploadData['file_name'];
                                        $data['photo'] = $uploadedFile;
                                        //image resize
                                        $options = array(
                                            'source_path' => $this->config->item('assets_voters'),
                                            'width' => 250,
                                            'height' => 250
                                        );
                                        $this->resizeImage($uploadedFile, $options);
                                    }else {
                                        //activity log
                                        $activity['http_status'] = 409;
                                        $activity['status'] = 0;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(409);
                                        $data = array(
                                            'fail' => 'Could not save file to server.',
                                            'status' => 0,
                                            'error' => $this->upload->display_errors('<p>', '</p>')    
                                        );
                                        echo $this->__json_output('Failure', $data);
                                    }
                                }
                                //$update_voter = $this->CoordinatorModel->updateVoter($data);
                                $update_data = array(
                                    'firstname' => $data['firstname'],
                                    'lastname' => $data['lastname'],
                                    'f_name' => $data['f_name'],
                                    'gender' => $data['gender'],
                                    'ps_no' => $data['polling_station'],
                                    'voter_status' => $data['voter_status'],
                                    'user_id' => $data['spid']
                                );
                                if(isset($data['photo'])) {
                                    $update_data['photo'] = $data['photo'];
                                }
                                if($this->input->post('age')) {
                                    $update_data['age'] = $data['age'];
                                }
                                if($this->input->post('caste')) {
                                    $update_data['caste'] = $data['caste'];
                                }
                                if($this->input->post('mobile')) {
                                    $update_data['mobile'] = $data['mobile'];
                                }
                                if($this->input->post('religion')) {
                                    $update_data['religion'] = $data['religion'];
                                }
                                if($this->input->post('category')) {
                                    $update_data['category'] = $data['category'];
                                }
                                $data['update'] = $update_data;
                                
                            }else {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Voter Id already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data); exit;
                            }
                             
                        }
                        //if geo coordinates are set
                        $coord = array();
                        if($this->input->post('lat') && $this->input->post('long')) {
                            $coord['lat'] = $this->input->post('lat');
                            $coord['lng'] = $this->input->post('long');
                            $data['coord'] = $coord;
                        }
                        
                        if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = $this->config->item('assets_voters');
                            $config['allowed_types'] = 'png|jpg|jpeg';
                            // $config['max_size']  = 2048;
                            $config['file_name'] = time().$data['voter_id'];
                            $this->load->library('upload', $config);

                            if($this->upload->do_upload('photo')) {
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                $data['photo'] = $uploadedFile;
                                //image resize
                                $options = array(
                                    'source_path' => $this->config->item('assets_voters'),
                                    'width' => 250,
                                    'height' => 250
                                );
                                $this->resizeImage($uploadedFile, $options);
                            }else {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(409);
                                $data = array(
                                    'fail' => 'Could not save file to server.',
                                    'status' => 0,
                                    'error' => $this->upload->display_errors('<p>', '</p>')    
                                );
                                echo $this->__json_output('Failure', $data);
                            }
                        }
                        if($data['local_status'] == 16) {
                            $os_fields = array('os_mobile', 'os_street', 'os_location');
                            foreach($os_fields as $fd) {
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
                                $os_data = array(
                                    'mobile' => $data['os_mobile'],
                                    'street' => $data['os_street'],
                                    'location' => $data['os_location']
                                );
                                if($this->input->post('os_landmark')) {
                                    $os_data['landmark'] = $data['os_landmark'];    
                                }
                                if($this->input->post('os_pincode')) {
                                    $os_data['pincode'] = $data['os_pincode'];    
                                }
                                
                                $data['outstation'] = $os_data;
                            }
                        }
                        if($data['group_id'] == 40) {
                            $data['parent_id'] = $data['vid'];
                            $data['user_role'] = 17;
                            
                        }elseif($data['group_id'] == 39) {
                            $data['parent_id'] = $data['spid'];
                            $data['user_role'] = 17;
                        }
                        
                        $usr_id = $this->MTeamModel->addCitizen($data);
                        if($usr_id) {
                            //activity log
                            $activity['http_status'] = 201;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(201);
                            $data = array(
                                'success' => 'Created - User information is successfully saved.',
                                'status' => 1,
                                'data' => array(
                                    'user_id' => $usr_id
                                )     
                            );
                            echo $this->__json_output('Success', $data);
                        }else {
                            //activity log
                            $activity['http_status'] = 500;
                            $activity['status'] = 2;
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
                $this->_httpStatus = http_response_code(403);
                $data = array(
                    'fail' => 'Forbidden - token is missing',
                    'status' => 0     
                );
                echo $this->__json_output('Failure', $data);    
            }
            
        }
    }

    public function validationreport() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/validationreport';
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
                        $user_exists = $this->MTeamModel->userExists($data['val_id'], $data['user_role']);
                        if($user_exists) {
                            if($this->input->post('profession')) {
                                $validate = $this->MTeamModel->saveValidation($data, 'profession');    
                            }
                            if($this->input->post('party_participation')) {
                                $validate = $this->MTeamModel->saveValidation($data, 'party_participation');    
                            }
                            if($this->input->post('personal_status')) {
                                $validate = $this->MTeamModel->saveValidation($data, 'personal_status');    
                            }
                            if($this->input->post('family_voters')) {
                                $validate = $this->MTeamModel->saveValidation($data, 'family_voters');    
                            }
                            if($this->input->post('vote_commitment')) {
                                $validate = $this->MTeamModel->saveValidation($data, 'vote_commitment');    
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

    public function validationreportstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/validationreport';
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
                        $validate = $this->MTeamModel->validationExists($data['val_id'], $data['user_role']);
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
                        $val_1 = $this->MTeamModel->validationOneStatus($data['val_id'], $data['user_role']);
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
                        $val_2 = $this->MTeamModel->validationTwoStatus($data['val_id'], $data['user_role']);
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

    public function smartmedia() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/smartmedia';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) {
                    
                    $media = $this->MTeamModel->getSmartMedia();
                    if($media) {
                        foreach($media as $m) {
                            $m->media_path = base_url($this->config->item('assets_images')).$m->media_path;
                            $m->post_likes = $this->MTeamModel->getPostLikes($m->id);
                            $m->like = $this->MTeamModel->getPostLikeByUser($m->id, $data['user_id']);
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
            $activity['request'] = 'MobileTeam/postlike';
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
                        $post_like = $this->MTeamModel->savePostLike($data);
                        
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

    public function appupdate($user_id, $token, $app_id, $device_id) {
        $activity = array();
        $activity['request'] = 'MobileTeam/appupdate';
        if($user_id != '' && $token != '' && $app_id != '' && $device_id != '') {
            $verified = $this->__authenticate_user($user_id, $token);
            $active = $this->__isactive(array('user-id' => $user_id, 'app_id' => $app_id, 'device_id' => $device_id));

            if($verified && $active && $this->_authorized) { //user is active and token verified
                $file = 'apps/boothobserver.apk';
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

    //validation 1
    public function govtschemevalidation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/govtschemevalidation';
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
                        $user_exists = $this->MTeamModel->userExists($data['val_id'], $data['user_role']);
                        if($user_exists) {
                            if($this->input->post('govt_scheme')) {
                                foreach($data['govt_scheme'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['govt_scheme'][$k]);
                                    }
                                }
                                $count_v = count($data['govt_scheme']);
                                if($count_v > 0) {
                                    $save_v = $this->MTeamModel->saveGovtSchemeValidation($data);
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
            $activity['request'] = 'MobileTeam/govtschemevalidation';
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
                        $user_exists = $this->MTeamModel->userExists($data['val_id'], $data['user_role']);
                        if($user_exists) {
                            if($this->input->post('ysr_scheme')) {
                                foreach($data['ysr_scheme'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['ysr_scheme'][$k]);
                                    }
                                }
                                $count_v = count($data['ysr_scheme']);
                                if($count_v > 0) {
                                    $save_v = $this->MTeamModel->saveYSRSchemeValidation($data);
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

    /**
     * Date : 03-01-2019
     */
    public function groupsms() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'MobileTeam/groupsms';
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
                            
                            
                            if($data['receiver_group'] == 67 && $data['user_role'] == 18) { //booth president
                                $booth_president = $this->MTeamModel->getBoothPresidentByBC($data['user_id']);
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
                                    $sms_store = $this->MTeamModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 68 && $data['user_role'] == 3) { //Sheet president
                                $sheet_president = $this->MTeamModel->getSheetPresidentByBC($data['user_id']);
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
                                    $sms_store = $this->MTeamModel->saveSms($data);    
                                }
                            }

                            if($data['receiver_group'] == 136 && $data['user_role'] == 46) { //family head
                                $family_head = $this->MTeamModel->getFamilyHeadByBC($data['user_id']);
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
                                    $sms_store = $this->MTeamModel->saveSms($data);    
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
            $activity['request'] = 'MobileTeam/singlesms';
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
                            $sms_store = $this->MTeamModel->saveSms($data);

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
            $activity['request'] = 'MobileTeam/outbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $sent_sms = $this->MTeamModel->getSentSms($data['user_id']);
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
            $activity['request'] = 'MobileTeam/inbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $inbox = $this->MTeamModel->getSms($data['user_id']);
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
            $activity['request'] = 'MobileTeam/msgread';
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
                        $read = $this->MTeamModel->smsread($data['sms_id'], $data['read'], $data['user_id']);
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
            $activity['request'] = 'MobileTeam/help';
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
                        $help = $this->MTeamModel->saveHelpQuery($data);
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
}