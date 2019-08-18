<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coordinator extends CI_Controller {
    private $_authorized;
    private $_user_id;
    private $_httpStatus;
    
    public function __construct() {
        parent::__construct();
        $this->load->model('CoordinatorModel');
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

    public function addUser() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/adduser';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'lastname', 'gender', 'age', 'voter_id', 'polling_station',  'local_status', 'group_id', 'relationship', 'voter_status');
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($this->input->post('volunteer') && $data['volunteer'] == true) {
                        if(!$this->input->post('mobile')) {
                            $error_count += 1;
                            $error['fields'][] = 'mobile';
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
                        if($this->input->post('volunteer') == true) {
                            $mobile_exists = $this->CoordinatorModel->userMobileExists($data['mobile']);
                            if($mobile_exists) {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 3;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Mobile already exists.',
                                    'status' => 3     
                                );
                                echo $this->__json_output('Failure', $data); exit;
                            }
                        }
                        $voter_exists = $this->CoordinatorModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $is_registered = $this->CoordinatorModel->voterRegisteredByCoordinator($data['voter_id']);
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
                                        $this->resizeImage($uploadedFile);
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
                                    'user_id' => $data['user_id']
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
                                echo $this->__json_output('Failure', $data);
                            }
                             
                        }
                        //if geo coordinates are set
                        $coord = array();
                        if($this->input->post('lat') && $this->input->post('long')) {
                            $coord['lat'] = $data['lat'];
                            $coord['lng'] = $data['long'];
                            $data['coord'] = $coord;
                        }
                        
                        if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = $this->config->item('assets_voters');
                            $config['allowed_types'] = 'png|jpg|jpeg';
                            $config['max_size']  = 2048;
                            $config['file_name'] = time().$data['voter_id'];
                            $this->load->library('upload', $config);

                            if($this->upload->do_upload('photo')) {
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                $data['photo'] = $uploadedFile;
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
                        if($data['group_id'] == 39) {
                            $data['parent_id'] = $data['user_id'];
                            $data['user_role'] = 17;
                            
                        }else {
                            $vnt_exists = $this->CoordinatorModel->volunteerExists($data['user_id'], $data['group_id']);
                            if($vnt_exists) {
                                $vnt_id = $vnt_exists->citizen_id;
                                $data['parent_id'] = $vnt_id;
                                $data['user_role'] = 17;
                            }else {
                                if($this->input->post('volunteer')) {
                                    $data['parent_id'] = $data['user_id'];
                                    $data['user_role'] = 46;
                                }else {
                                    $data['user_role'] = 17;    
                                }
                            }
                            
                        }
                        $usr_id = $this->CoordinatorModel->addCitizen($data);
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

    //get citizen data if user is available and not registered by coordinator
    public function getUserByVoterId() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getUserByVoterId';
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
                        $voter_exists = $this->CoordinatorModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $voter_available = $this->CoordinatorModel->voterRegisteredByCoordinator($data['voter_id']);
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

    public function isvolunteerexists($user_id, $group_id) {
        $id = $this->CoordinatorModel->volunteerExists($user_id, $group_id);
        if($id) {
            $data = array(
                'status' => 1,
                'vnt_id' => $id->citizen_id
            );
        }else {
            $data = array(
                'status' => 0
            );
        }
        $this->_httpStatus = http_response_code(200);
        echo $this->__json_output('Success', $data); 
    }

    public function ismobileexists($mobile) {
        
        $mobile_exists = $this->CoordinatorModel->userMobileExists($mobile);
        if($mobile_exists) {
            $data = array(
                'status' => 2,
                'data' => 'Mobile number already exists'
            );
        }else {
            $data = array(
                'status' => 1,
                'data' => ''
            );
        }
        
        
        $this->_httpStatus = http_response_code(200);
        echo $this->__json_output('Success', $data); 
    }
 
    public function getgroup() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getgroup';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $group_data = array();
                    $coord_dt = $this->CoordinatorModel->getCoordinatorMember($data['user_id']);
                    $vlt_dt = $this->CoordinatorModel->getVolunteerByCoordinator($data['user_id']);
                    if($vlt_dt) {
                        foreach($vlt_dt as $vd) {
                            if($vd->photo != null) {
                                $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                            }else {
                                if($vd->gender == 4) {
                                    $vd->photo = base_url($this->config->item('assets_male'));
                                }elseif($vd->gender == 5) {
                                    $vd->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            $vd->members = $this->CoordinatorModel->getCoordinatorMember($vd->citizen_id);
                        }
                    }
                    
                    if($coord_dt) {
                        $group_data['coord_members'] = $coord_dt;
                    }
                    if($vlt_dt) {
                        $group_data['volunteer_data'] = $vlt_dt;
                    }

                    if(count($group_data) > 0) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => array(
                                'coord_member' => $coord_dt,
                                'volunteer' => $vlt_dt
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
                            'success' => 'Success. No Content',
                            'status' => 2
                                
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

    public function getVolunteerMembers() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getVolunteerMembers';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $v_members = $this->CoordinatorModel->getVolunteerMember($data['user_id'], $data['citizen_id']);
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

    //get visits
    public function getVisits() {
        $visits = $this->CoordinatorModel->getVisit();
        if($visits) {
            $this->_httpStatus = http_response_code(200);
            $data = array(
                'success' => 'Success.',
                'status' => 1,
                'data' => array(
                    'visits' => $visits
                )     
            );
            echo $this->__json_output('Success', $data); 
        }else {
            $this->_httpStatus = http_response_code(200);
            $data = array(
                'success' => 'Success. No Content',
                'status' => 2,
                    
            );
            echo $this->__json_output('Success', $data);  
        }
    }

    public function getVisitOption($id) {
        $vs_opt = $this->CoordinatorModel->getVisitOpt($id);
        if($vs_opt) {
            $this->_httpStatus = http_response_code(200);
            $data = array(
                'success' => 'Success.',
                'status' => 1,
                'data' => array(
                    'visits-options' => $vs_opt
                )     
            );
            echo $this->__json_output('Success', $data); 
        }else {
            $this->_httpStatus = http_response_code(200);
            $data = array(
                'success' => 'Success. No Content',
                'status' => 2,
                   
            );
            echo $this->__json_output('Success', $data);  
        }
    }
    
    //visit 1
    public function serviceVisit() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/serviceVisit';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            $visit_values = array();
                            $service = array();
                            $save_data = array();
                            if($this->input->post('home_assist')) {
                                $home_assist = array();
                                $home_assist['id'] = $data['home_assist'];
                                if($this->input->post('ha_value')) {
                                    if(count($data['ha_value']) > 0) {
                                        $home_assist['values'] = $data['ha_value'];
                                        foreach($home_assist['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($home_assist['values'][$k]);
                                            }
                                        }
                                        $service['home_assist'] = 1;
                                    }    
                                }
                                $visit_values[] = $home_assist; 
                            }
                            if($this->input->post('education')) {
                                $education = array();
                                $education['id'] = $data['education'];
                                if($this->input->post('ed_value')) {
                                    if(count($data['ed_value']) > 0) {
                                        $education['values'] = $data['ed_value'];
                                        foreach($education['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($education['values'][$k]);
                                            }
                                        }
                                        $service['education'] = 1;
                                    }    
                                }
                                $visit_values[] = $education;
                            }
                            if($this->input->post('health')) {
                                $health = array();
                                $health['id'] = $data['health'];
                                if($this->input->post('health_value')) {
                                    if(count($data['health_value']) > 0) {
                                        $health['values'] = $data['health_value'];
                                        foreach($health['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($health['values'][$k]);
                                            }
                                        }
                                        $service['health'] = 1;
                                    }    
                                }
                                $visit_values[] = $health;
                            }
                            if($this->input->post('job')) {
                                $job = array();
                                $job['id'] = $data['job'];
                                if($this->input->post('job_value')) {
                                    if(count($data['job_value']) > 0) {
                                        $job['values'] = $data['job_value'];
                                        foreach($job['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($job['values'][$k]);
                                            }
                                        }
                                        $service['job'] = 1;
                                    }    
                                }
                                $visit_values[] = $job;
                            }
                            if($this->input->post('training')) {
                                $training = array();
                                $training['id'] = $data['training'];
                                if($this->input->post('training_value')) {
                                    if(count($data['training_value']) > 0) {
                                        $training['values'] = $data['training_value'];
                                        foreach($training['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($training['values'][$k]);
                                            }
                                        }
                                        $service['training'] = 1;
                                    }    
                                }
                                $visit_values[] = $training;
                            }
                            if($this->input->post('id_cards')) {
                                $id_cards = array();
                                $id_cards['id'] = $data['id_cards'];
                                if($this->input->post('id_cards_value')) {
                                    if(count($data['id_cards_value']) > 0) {
                                        $id_cards['values'] = $data['id_cards_value'];
                                        foreach($id_cards['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($id_cards['values'][$k]);
                                            }
                                        }
                                        $service['id_cards'] = 1;
                                    }    
                                }
                                $visit_values[] = $id_cards;
                            }
                            if($this->input->post('certificate')) {
                                $certificate = array();
                                $certificate['id'] = $data['certificate'];
                                if($this->input->post('certificate_value')) {
                                    if(count($data['certificate_value']) > 0) {
                                        $certificate['values'] = $data['certificate_value'];
                                        foreach($certificate['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($certificate['values'][$k]);
                                            }
                                        }
                                        $service['certificate'] = 1;
                                    }    
                                }
                                $visit_values[] = $certificate;
                            }
                            if($this->input->post('govt_scheme')) {
                                $govt_scheme = array();
                                $govt_scheme['id'] = $data['govt_scheme'];
                                if($this->input->post('govt_scheme_value')) {
                                    if(count($data['govt_scheme_value']) > 0) {
                                        $govt_scheme['values'] = $data['govt_scheme_value'];
                                        foreach($govt_scheme['values'] as $k => $v) {
                                            if($v == 0) {
                                                unset($govt_scheme['values'][$k]);
                                            }
                                        }
                                        $service['govt_scheme'] = 1;
                                    }    
                                }
                                $visit_values[] = $govt_scheme;
                            }
                            $visit_exists = $this->CoordinatorModel->isVisited($data['citizen_id']);
                            if($visit_exists) {
                                $save_data['update_visit'] = $service;
                                $save_data['update_visit']['id'] = $visit_exists->id;
                            }else {
                                $save_data['visit'] = $service;
                            }
                            $save_data['visit_values'] = $visit_values;
                            
                            
                            $save_visit = $this->CoordinatorModel->saveVisitOne($data['citizen_id'], $save_data);
                            if($save_visit) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Success. Visit saved successfully',
                                    'status' => 1,
                                    'data' => array(
                                        'visit_id' => $save_visit
                                    )     
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
                                'fail' => 'Forbidden - Citizen Not exists',
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
    
    //Visit 2
    public function statusvisit() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/statusvisit';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
               $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            $visit_values = array();
                            $c_status = array();
                            $save_data = array();
                            if($this->input->post('family_status')) {
                                $family_status = array();
                                $family_status['id'] = $data['family_status'];
                                if($this->input->post('fs_value')) {
                                    if(count($data['fs_value']) > 0) {
                                        $family_status['values'] = $data['fs_value'];
                                        $c_status['family_status'] = 1;
                                    }    
                                }
                                $visit_values[] = $family_status; 
                            }
                            if($this->input->post('family_health')) {
                                $family_health = array();
                                $family_health['id'] = $data['family_health'];
                                if($this->input->post('fh_value')) {
                                    if(count($data['fh_value']) > 0) {
                                        $family_health['values'] = $data['fh_value'];
                                        $c_status['family_health'] = 1;
                                    }    
                                }
                                $visit_values[] = $family_health;
                            }
                            if($this->input->post('family_usage')) {
                                $family_usage = array();
                                $family_usage['id'] = $data['family_usage'];
                                if($this->input->post('fu_value')) {
                                    if(count($data['fu_value']) > 0) {
                                        $family_usage['values'] = $data['fu_value'];
                                        $c_status['family_usage'] = 1;
                                    }    
                                }
                                $visit_values[] = $family_usage;
                            }
                            $visit_exists = $this->CoordinatorModel->isVisitedV2($data['citizen_id']);
                            if($visit_exists) {
                                $save_data['update_visit'] = $c_status;
                                $save_data['update_visit']['id'] = $visit_exists->id;
                            }else {
                                $save_data['visit'] = $c_status;
                            }
                            $save_data['visit_values'] = $visit_values;
                            $save_visit = $this->CoordinatorModel->saveVisitTwo($data['citizen_id'], $save_data);
                            if($save_visit) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Success. Visit saved successfully',
                                    'status' => 1,
                                    'data' => array(
                                        'visit_id' => $save_visit
                                    )     
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
                                'fail' => 'Forbidden - Citizen Not exists',
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
	//Save Data For Visit3
    public function saveVisitThree() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/saveVisitThree';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
					
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id','current_supply','water_supply','pention','subsides','ration_supply','runamafi','rythu_bandhu','rythu_beema','govt_schemes');
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
						$citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'],$data['user_id']);
                        if($citizen_exists)  {
                            $isvisit_exists = $this->CoordinatorModel->citizenVisitThreeExists($data['citizen_id']);
                            if($isvisit_exists) {
                                $this->_httpStatus = http_response_code(403);
                                $data = array(
                                    'fail' => 'Forbidden - Visit already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data); 
                                
                            }else {
                                $usr_id = $this->CoordinatorModel->addVisitThree($data);
                                if($usr_id) {
                                    //activity log
                                    $activity['http_status'] = 201;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(201);
                                    $data = array(
                                        'success' => 'Created - Visit information is successfully saved.',
                                        'status' => 1,
                                        'data' => array(
                                            'user_id' => $usr_id
                                        )     
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
                            //activity log
                            $activity['http_status'] = 406;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(406);
                            $data = array(
                                'fail' => 'Not Accepted - Citizen Id already exists.',
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
	//Save Data For Visit4
    public function saveVisitFour() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/saveVisitFour';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id','mission_bhagirath','mission_kakatiya','kaleshwaram_project','rangareddy_chevella','tsi_pass','t_hub','metro_rail','softnet','she_teams','she_cabs');
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
						$citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'],$data['user_id']);
                        if($citizen_exists) {
								$isvisit_exists = $this->CoordinatorModel->citizenVisitFourExists($data['citizen_id']);
								if($isvisit_exists) {
                                    //activity log
                                    $activity['http_status'] = 403;
                                    $activity['status'] = 0;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(403);
                                    $data = array(
                                        'fail' => 'Forbidden - Visit already exists.',
                                        'status' => 0     
                                    );
                                    echo $this->__json_output('Failure', $data); 
										
								}else {
                                    $usr_id = $this->CoordinatorModel->addVisitFour($data);
                                    if($usr_id) {
                                        //activity log
                                        $activity['http_status'] = 201;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(201);
                                        $data = array(
                                            'success' => 'Created - Visit information is successfully saved.',
                                            'status' => 1,
                                            'data' => array(
                                                'user_id' => $usr_id
                                            )     
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
							else {
                                //activity log
                                $activity['http_status'] = 406;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

								$this->_httpStatus = http_response_code(406);
                                $data = array(
                                    'fail' => 'Not Accepted - Citizen Id already exists.',
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
	//Save Data For Visit5
    public function saveVisitFive() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/saveVisitFive';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id','education','profession','monthly_income','caste_activity','political_sympathser','last_time_vote','digital_village_activity','associations','hobbies','mobile_data');
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
						$citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'],$data['user_id']);
                        if($citizen_exists) {
                            $isvisit_exists = $this->CoordinatorModel->citizenVisitFiveExists($data['citizen_id']);
                            if($isvisit_exists) {
                                //activity log
                                $activity['http_status'] = 403;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(403);
                                $data = array(
                                    'fail' => 'Forbidden - Visit already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data); 
                                
                            }else {
                                $usr_id = $this->CoordinatorModel->addVisitFive($data);
                                if($usr_id) {
                                    //activity log
                                    $activity['http_status'] = 201;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(201);
                                    $data = array(
                                        'success' => 'Created - Visit information is successfully saved.',
                                        'status' => 1,
                                        'data' => array(
                                            'user_id' => $usr_id
                                        )     
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
                            //activity log
                            $activity['http_status'] = 406;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(406);
                            $data = array(
                                'fail' => 'Not Accepted - Citizen does not exists.',
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
	
	//Visit 6
    public function neighbourhoodvisit() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/neighbourhoodvisit';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'],$data['user_id']);
                        if($citizen_exists) {
                            $isVisited = $this->CoordinatorModel->citizenExistsVisitSix($data['citizen_id']);
                            if($isVisited) {
                                //activity log
                                $activity['http_status'] = 403;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(403);
                                $data = array(
                                    'fail' => 'Forbidden - Visit already exists.',
                                    'status' => 0     
                                );
                                echo $this->__json_output('Failure', $data);
                            }else {
                                $ngh_data = array();
                                if($this->input->post('no-reference')) {
                                    $ngh_data['no-reference'] = 220;
                                
                                }else {
                                    $error_count = 0;
                                    $error = array();
                                    $fields = array('name', 'relationship', 'mobile', 'voters', 'location');
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
                                        $reference = array();
                                        if(is_array($data['name'])) {
                                            foreach($data['name'] as $k => $nm) {
                                                $reference[$k]['name'] = $nm;
                                            }
                                        }
                                        if($this->input->post('age')) {
                                            if(is_array($data['age'])) {
                                                foreach($data['age'] as $k => $nm) {
                                                    $reference[$k]['age'] = $nm;
                                                }
                                            }
                                        }
                                        
                                        if(is_array($data['relationship'])) {
                                            foreach($data['relationship'] as $k => $nm) {
                                                $reference[$k]['relationship'] = $nm;
                                            }
                                        }
                                        if(is_array($data['mobile'])) {
                                            foreach($data['mobile'] as $k => $nm) {
                                                $reference[$k]['mobile'] = $nm;
                                            }
                                        }
                                        if(is_array($data['voters'])) {
                                            foreach($data['voters'] as $k => $nm) {
                                                $reference[$k]['voters'] = $nm;
                                            }
                                        }
                                        if($this->input->post('landmark')) {
                                            if(is_array($data['landmark'])) {
                                                foreach($data['landmark'] as $k => $nm) {
                                                    $reference[$k]['landmark'] = $nm;
                                                }
                                            }
                                        }
                                        if($this->input->post('location')) {
                                            if(is_array($data['location'])) {
                                                foreach($data['location'] as $k => $nm) {
                                                    $reference[$k]['location'] = $nm;
                                                }
                                            }
                                        }
                                        $ngh_data['reference'] = $reference;
                                    }
                                }
                                $save_data = $this->CoordinatorModel->saveVisitSix($data['citizen_id'], $ngh_data);
                                if($save_data) {
                                    //activity log
                                    $activity['http_status'] = 201;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(201);
                                    $data = array(
                                        'success' => 'Created - Visit information is successfully saved.',
                                        'status' => 1,
                                        'data' => array(
                                            'user_id' => $save_data
                                        )     
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
                            //activity log
                            $activity['http_status'] = 406;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(406);
                            $data = array(
                                'fail' => 'Not Accepted - Citizen does not exists.',
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

    //Visit Status
    public function getvisitstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getvisitstatus';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_id = $data['citizen_id'];
                        $visits = array();

                        //visit 1
                        $visit_1 = $this->CoordinatorModel->visitOneStatus($citizen_id);
                        if($visit_1) {
                            $status = $visit_1->status;
                            if($visit_1->modified_at == null) {
                                $visit_time = $visit_1->created_at;
                            }else {
                                $visit_time = $visit_1->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_1'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_1'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 2
                        $visit_2 = $this->CoordinatorModel->visitTwoStatus($citizen_id);
                        if($visit_2) {
                            $status = $visit_2->status;
                            if($visit_2->modified_at == null) {
                                $visit_time = $visit_2->created_at;
                            }else {
                                $visit_time = $visit_2->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_2'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_2'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 3
                        $visit_3 = $this->CoordinatorModel->visitThreeStatus($citizen_id);
                        if($visit_3) {
                            $status = $visit_3->status;
                            if($visit_3->modified_at == null) {
                                $visit_time = $visit_3->created_at;
                            }else {
                                $visit_time = $visit_3->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_3'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_3'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 4
                        $visit_4 = $this->CoordinatorModel->visitFourStatus($citizen_id);
                        if($visit_4) {
                            $status = $visit_4->status;
                            if($visit_4->modified_at == null) {
                                $visit_time = $visit_4->created_at;
                            }else {
                                $visit_time = $visit_4->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_4'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_4'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 5
                        $visit_5 = $this->CoordinatorModel->visitFiveStatus($citizen_id);
                        if($visit_5) {
                            $status = $visit_5->status;
                            if($visit_5->modified_at == null) {
                                $visit_time = $visit_5->created_at;
                            }else {
                                $visit_time = $visit_5->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_5'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_5'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 6
                        $visit_6 = $this->CoordinatorModel->visitSixStatus($citizen_id);
                        if($visit_6) {
                            $status = $visit_6->status;
                            if($visit_6->modified_at == null) {
                                $visit_time = $visit_6->created_at;
                            }else {
                                $visit_time = $visit_6->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_6'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_6'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }
                        //activity log
                        $activity['http_status'] = 201;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(201);
                        $data = array(
                            'success' => 'Visits Status.',
                            'status' => 1,
                            'data' => array(
                                'visits' => $visits
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

    //service visit status
    public function getservicevisitstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getservicevisitstatus';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_id = $data['citizen_id'];
                        $visits = array();

                        //visit 1
                        $visit_1 = $this->CoordinatorModel->visitOneStatus($citizen_id);
                        if($visit_1) {
                            $status = $visit_1->govt_scheme;
                            if($visit_1->modified_at == null) {
                                $visit_time = $visit_1->created_at;
                            }else {
                                $visit_time = $visit_1->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_1'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_1'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 2
                        $visit_2 = $this->CoordinatorModel->visitOneStatus($citizen_id);
                        if($visit_2) {
                            $status = $visit_2->health;
                            if($visit_2->modified_at == null) {
                                $visit_time = $visit_2->created_at;
                            }else {
                                $visit_time = $visit_2->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_2'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_2'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 3
                        $visit_3 = $this->CoordinatorModel->visitOneStatus($citizen_id);
                        if($visit_3) {
                            $status = $visit_3->job;
                            if($visit_3->modified_at == null) {
                                $visit_time = $visit_3->created_at;
                            }else {
                                $visit_time = $visit_3->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_3'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_3'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 4
                        $visit_4 = $this->CoordinatorModel->visitOneStatus($citizen_id);
                        if($visit_4) {
                            $status = $visit_4->certificate;
                            if($visit_4->modified_at == null) {
                                $visit_time = $visit_4->created_at;
                            }else {
                                $visit_time = $visit_4->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_4'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_4'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 5
                        $visit_5 = $this->CoordinatorModel->visitOneStatus($citizen_id);
                        if($visit_5) {
                            $status = $visit_5->id_cards;
                            if($visit_5->modified_at == null) {
                                $visit_time = $visit_5->created_at;
                            }else {
                                $visit_time = $visit_5->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_5'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_5'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 6
                        $visit_6 = $this->CoordinatorModel->visitSixStatus($citizen_id);
                        if($visit_6) {
                            $status = $visit_6->status;
                            if($visit_6->modified_at == null) {
                                $visit_time = $visit_6->created_at;
                            }else {
                                $visit_time = $visit_6->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_6'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_6'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }
                        //activity log
                        $activity['http_status'] = 201;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(201);
                        $data = array(
                            'success' => 'Visits Status.',
                            'status' => 1,
                            'data' => array(
                                'visits' => $visits
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

    //smart connect
    public function smartconnect() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/smartconnect';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $getSuperior = $this->CoordinatorModel->getCoordinatorSuperior($data['user_id']);
                    if($getSuperior) {
                        foreach($getSuperior as $k => $v) {
                            if($v->photo != '') {
                                $v->photo = base_url($this->config->item('assets_users')).$v->photo;
                            }else {
                                if($v->gender == 4) {
                                    $v->photo = base_url($this->config->item('assets_male'));
                                }else {
                                    $v->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                        }
                        //activity log
                        $activity['http_status'] = 201;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success',
                            'status' => 1,
                            'data' => $getSuperior
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 500;
                        $activity['status'] = 0;
                        $this->__activityLog($data['user_id'], $activity);
                        $this->_httpStatus = http_response_code(500);
                        $data = array(
                            'fail' => 'Internal Server Error.',
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

    //get dashboard
    public function dashboard() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/dashboard';
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
                        $registered = $this->CoordinatorModel->getVotersCountByCoordinator($data['user_id']);
                        $activities = $this->CoordinatorModel->getCoordinatorActivity($data['user_id']);
                        $followup = $this->CoordinatorModel->getCoordinatorMsgCount($data['user_id']);

                        $update = $this->CoordinatorModel->getAppVersion($data['app_id']);
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
                            'voters_count' => $registered,
                            'follow_up' => $followup,
                            'activities' => $activities,
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

    //get services
    public function getservices() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getservices';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $services = array();
                    $ha_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'home_assist');
                    $services['home_assist']['id'] = 7;
                    $services['home_assist']['count'] = $ha_service;

                    $edu_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'education');
                    $services['education']['id'] = 8;
                    $services['education']['count'] = $edu_service;

                    $hlth_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'health');
                    $services['health']['id'] = 9;
                    $services['health']['count'] = $hlth_service;

                    $job_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'job');
                    $services['job']['id'] = 10;
                    $services['job']['count'] = $job_service;

                    $trn_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'training');
                    $services['training']['id'] = 11;
                    $services['training']['count'] = $trn_service;

                    $id_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'id_cards');
                    $services['id_cards']['id'] = 11;
                    $services['id_cards']['count'] = $id_service;

                    $crf_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'certificate');
                    $services['certificate']['id'] = 11;
                    $services['certificate']['count'] = $crf_service;

                    $gvt_service = $this->CoordinatorModel->getServiceCountByServiceId($data['user_id'], 'govt_scheme');
                    $services['govt_scheme']['id'] = 11;
                    $services['govt_scheme']['count'] = $gvt_service;

                    //activity log
                    $activity['http_status'] = 201;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'success' => 'Success',
                        'status' => 1,
                        'data' => $services     
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

    public function getcitizenbyservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getcitizenbyservice';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('service');
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
                        $members = $this->CoordinatorModel->getMemberByService($data['user_id'], $data['service']);
                        if($members) {
                            foreach($members as $k => $v) {
                                if($v->photo != '') {
                                    $v->photo = base_url($this->config->item('assets_voters')).$v->photo;
                                }else {
                                    if($v->gender == 4) {
                                        $v->photo = base_url($this->config->item('assets_male'));
                                    }else {
                                        $v->photo = base_url($this->config->item('assets_female'));
                                    }
                                }
                            }
                            //activity log
                            $activity['http_status'] = 201;
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
                            $activity['http_status'] = 201;
                            $activity['status'] = 2;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'No Content - Token failed. Login again.',
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

    //neighbouring villages
    public function getneighbourvillage() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getneighbourvillage';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $ngh_villages = $this->CoordinatorModel->getneighbouringvillages($data['user_id']);
                    if($ngh_villages) {
                        //activity log
                        $activity['http_status'] = 201;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success',
                            'status' => 1,
                            'data' => $ngh_villages     
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 500;
                        $activity['status'] = 0;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(500);
                        $data = array(
                            'fail' => 'Internal Server Error.',
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

    //X party info
    public function xpartyinfo() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/xpartyinfo';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('party_id', 'name', 'designation');
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
                        $save_party = $this->CoordinatorModel->xpartymember($data);
                        if($save_party) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Record save successfully.',
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
                                'fail' => 'Internal Server Error - Could not save record.',
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

    public function voters() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/voters';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $vtr = $this->CoordinatorModel->getVotersByPS($data['user_id']);
                    if($vtr) {
                        foreach($vtr as $v) {
                            if($v->photo != '') {
                                $v->photo = base_url($this->config->item('assets_voters')).$v->photo;
                            }else{
                                if($v->gender == 4) {
                                    $v->photo = base_url($this->config->item('assets_male'));
                                }else {
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
                            'data' => $vtr    
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'No Content.',
                            'status' => 2,
                               
                        );
                        echo $this->__json_output('success', $data);
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
    
    //refer volunteer to agents & observer
    public function boothreference() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/boothreference';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('volunteer_id', 'ps_id', 'role_id');
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
                        $refer = $this->CoordinatorModel->referPsMember($data);
                        if($refer) {
                            //activity log
                            $activity['http_status'] = 200;
                            $activity['status'] = 1;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(200);
                            $data = array(
                                'success' => 'Request send successfully.',
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
                                'fail' => 'Internal Server Error - Could not save record.',
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

    public function volunteertorefer() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/volunteertorefer';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $vnt = $this->CoordinatorModel->getVolunteerToRefer($data['user_id']);
                    if($vnt) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Records found.',
                            'status' => 1,
                            'data' => $vnt     
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 406;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(406);
                        $data = array(
                            'fail' => 'No content.',
                            'status' => 2     
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

    public function referedmember() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/referedmember';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $refmem = $this->CoordinatorModel->getRefMembers($data['user_id']);
                    if($refmem) {
                        foreach($refmem as $vd) {
                            if($vd->photo != null) {
                                $vd->photo = base_url($this->config->item('assets_voters')).$vd->photo;
                            }else {
                                if($vd->gender == 4) {
                                    $vd->photo = base_url($this->config->item('assets_male'));
                                }elseif($vd->gender == 5) {
                                    $vd->photo = base_url($this->config->item('assets_female'));
                                }
                            }
                            if($vd->status == 0) {
                                $vd->ref_status = 0;
                                $vd->status_name = 'Pending';
                                $vd->ps_no = 0;
                                $vd->ps_name = 0;
                                $vd->user_role = 0;
                                $vd->booth_no = 0;
                            }elseif($vd->status == 1) {
                                $vd->ref_status = 1;
                                $assign_ps = $this->CoordinatorModel->getAssignPs($vd->id);
                                $vd->ps_no = $assign_ps[0]->ps_no;
                                $vd->ps_name = $assign_ps[0]->ps_name;
                                $vd->user_role = $assign_ps[0]->user_role;
                                $vd->booth_no = $assign_ps[0]->booth_no;
                            }else {
                                $vd->ref_status = 2;
                                $vd->status_name = 'Rejected';
                                $vd->ps_no = 0;
                                $vd->ps_name = 0;
                                $vd->user_role = 0;
                                $vd->booth_no = 0;
                            }
                        }
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);
                        
                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Records found.',
                            'status' => 1,
                            'data' => $refmem     
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 406;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(406);
                        $data = array(
                            'fail' => 'No content.',
                            'status' => 2     
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
	
	// test api for Cast Flow
	public function getCastFlow() {
        $vs_opt = $this->CoordinatorModel->getCasteData();
        if($vs_opt) {
            $this->_httpStatus = http_response_code(200);
            $data = array(
                'success' => 'Success.',
                'status' => 1,
                'data' => array(
                    'caste-value' => $vs_opt
                )     
            );
            echo $this->__json_output('Success', $data); 
        }else {
            $this->_httpStatus = http_response_code(200);
            $data = array(
                'success' => 'Success. No Content',
                'status' => 2,
                    
            );
            echo $this->__json_output('Success', $data);  
        }
    }

    //sms
    public function smsdetails() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/smsdetails';
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
                        $smsstore = $this->CoordinatorModel->saveMessage($data);
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

    //task
    public function mytask() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/mytask';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $user_id = $data['user_id'];
                    $group_task = $this->CoordinatorModel->getGroupTask($user_id);
                    $ind_task = $this->CoordinatorModel->getIndTask($user_id);
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

    public function smartmedia() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/smartmedia';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $media = $this->CoordinatorModel->getSmartMedia();
                    if($media) {
                        foreach($media as $m) {
                            $m->media_path = base_url($this->config->item('assets_images')).$m->media_path;
                            $m->post_likes = $this->CoordinatorModel->getPostLikes($m->id);
                            $m->like = $this->CoordinatorModel->getPostLikeByUser($m->id, $data['user_id']);
                        }

                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
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
                            'data' => 'No Post'    
                        );
                        echo $this->__json_output('success', $data);
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

    public function calldetails() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/calldetails';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_id', 'receiver_id', 'mobile', 'call_duration'); //mandatory fields
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($_FILES['call_record']['name'] == '') {
                        $error_count += 1;
                        $error['fields'][] = 'call_record';
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
                        if($_FILES['call_record']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = 'uploads/call-recordings/';
                            $config['allowed_types'] = 'mp3|wav|amr|mp4|3gp';
                            $config['max_size']  = 10240;
                            $config['file_name'] = time().$data['mobile'];
                            $this->load->library('upload', $config);
                            if($this->upload->do_upload('call_record')){
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                $data['call_record'] = $uploadedFile;
                                $call_id = $this->CoordinatorModel->saveCallRecord($data);
                                if($call_id) {
                                    //activity log
                                    $activity['http_status'] = 200;
                                    $activity['status'] = 1;
                                    $this->__activityLog($data['user_id'], $activity);

                                    $this->_httpStatus = http_response_code(200);
                                    $data = array(
                                        'success' => 'Recording is saved successfully.',
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
                                $activity['http_status'] = 204;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                // echo json_encode($this->upload->display_errors('<p>', '</p>')); exit;
                                $this->_httpStatus = http_response_code(204);
                                $data = array(
                                    'fail' => 'Could not save file to server.',
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

    public function postlike() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/postlike';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('post_id', 'like'); //mandatory fields
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
                        $post_like = $this->CoordinatorModel->savePostLike($data);
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

    public function voicemessage() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/voicemessage';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('user_id', 'receiver_id', 'message_duration'); //mandatory fields
                    foreach($fields as $fd) {
                        if(!$this->input->post($fd)) {
                            $error_count += 1;
                            $error['fields'][] = $fd;
                        }
                    }
                    if($_FILES['voice_message']['name'] == '') {
                        $error_count += 1;
                        $error['fields'][] = 'voice_message';
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
                        if($_FILES['voice_message']['name'] !== '') {
                            //upload photo
                            $config['upload_path']   = 'uploads/voice-messages/';
                            $config['allowed_types'] = '*';
                            $config['max_size']  = 2048;
                            $config['file_name'] = time().$data['user_id'];
                            $this->load->library('upload', $config);

                            if($this->upload->do_upload('voice_message')) {
                                $uploadData = $this->upload->data();
                                $uploadedFile = $uploadData['file_name'];
                                $data['voice_message'] = $uploadedFile;
                                $vc_m = $this->CoordinatorModel->saveVoiceMessage($data);
                                if($vc_m) {
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
                                
                            }else {
                                //activity log
                                $activity['http_status'] = 409;
                                $activity['status'] = 0;
                                $this->__activityLog($data['user_id'], $activity);

                                // echo json_encode('failed to upload'); exit;
                                // echo json_encode($this->upload->display_errors('<p>', '</p>')); exit;
                                $this->_httpStatus = http_response_code(409);
                                $data = array(
                                    'fail' => 'Could not save file to server.',
                                    'status' => 0,
                                    'error' => $this->upload->display_errors('<p>', '</p>')    
                                );
                                echo $this->__json_output('Failure', $data);
                            }
                        }
                        // $vc_msg = time().$data['user_id'].'.mp3';
                        // $vc_path = 'uploads/voice-messages/'.$vc_msg;

                        // $vc_file = fopen($vc_path, 'wb');
                        
                        // if(fwrite($vc_file, base64_decode($data['voice_message'])) === FALSE) {
                        //     echo json_encode(array('error' => 'Voice message not sent', 'status' => 0));
                        //     exit;
                        // }
                        // fclose($vc_file);
                        // $data['voice_message'] = $vc_msg;

                        // $vc_m = $this->CoordinatorModel->saveVoiceMessage($data);
                        // if($vc_m) {
                        //     $this->_httpStatus = http_response_code(200);
                        //     $data = array(
                        //         'success' => 'Voice message sent successfully.',
                        //         'status' => 1,
                                    
                        //     );
                        //     echo $this->__json_output('Success', $data);
                        // }else {
                        //     $this->_httpStatus = http_response_code(500);
                        //     $data = array(
                        //         'fail' => 'Internal Server Error - We could not complete your request.',
                        //         'status' => 0     
                        //     );
                        //     echo $this->__json_output('Failure', $data);
                        // }
                        
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
     * City Visits
     */
    /* Visit 21 */
    public function schemesvisit() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/schemesvisit';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            $visit_values = array();
                            $save_data = array();
                            if($this->input->post('jan_dhan')) {
                                $visit_values['jan_dhan'] = $data['jan_dhan'];
                            }
                        
                            if($this->input->post('beti_bachao')) {
                                $visit_values['beti_bachao'] = $data['beti_bachao'];
                            }

                            if($this->input->post('make_india')) {
                                $visit_values['make_india'] = $data['make_india'];
                            }
                        
                            if($this->input->post('swatch_bhart')) {
                                $visit_values['swatch_bhart'] = $data['swatch_bhart'];
                            }

                            if($this->input->post('digital_india')) {
                                $visit_values['digital_india'] = $data['digital_india'];
                            }
                        
                            if($this->input->post('build_toilets')) {
                                $visit_values['build_toilets'] = $data['build_toilets'];
                            }

                            if($this->input->post('one_pension')) {
                                $visit_values['one_pension'] = $data['one_pension'];
                            }
                            if($this->input->post('seventh_pay')) {
                                $visit_values['seventh_pay'] = $data['seventh_pay'];
                            }
                        
                            if($this->input->post('suraksha_beema')) {
                                $visit_values['suraksha_beema'] = $data['suraksha_beema'];
                            }

                            if($this->input->post('jeevan_jyoti_beema')) {
                                $visit_values['jeevan_jyoti_beema'] = $data['jeevan_jyoti_beema'];
                            }                            
                            //echo json_encode($visit_values); exit;
                            $visit_exists = $this->CoordinatorModel->isVisited_21($data['citizen_id']);
                            
                            if($visit_exists) {
                                $save_data['update_visit'] = $visit_values;
                                $save_data['update_visit']['id'] = $visit_exists->id;
                            }else {
                                $save_data['visit'] = $visit_values;
                            }
                            //$save_data['visit_values'] = $visit_values;
                            $save_visit = $this->CoordinatorModel->saveVisitTwentyOne($data['citizen_id'], $save_data);
                            if($save_visit) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Success. Visit saved successfully',
                                    'status' => 1,
                                    'data' => array(
                                        'visit_id' => $save_visit
                                    )     
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Visit 22 */
    public function healthvisit() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/healthvisit';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            //echo json_encode('exists'); exit;
                            if($this->input->post('health')) {
                                foreach($data['health'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['health'][$k]);
                                    }
                                }
                                $count_v = count($data['health']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitTwentyTwo($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                //echo json_encode($count_v); exit;
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => 'health'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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
    } /* Visit 22 */

    /* Visit 23 */
    public function jobservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/jobservice';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            //echo json_encode('exists'); exit;
                            if($this->input->post('job_values')) {
                                foreach($data['job_values'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['job_values'][$k]);
                                    }
                                }
                                $count_v = count($data['job_values']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitTwentythree($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                //echo json_encode($count_v); exit;
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => 'job_values'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Visit 24 */
    public function certificateservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/certificateservice';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            //echo json_encode('exists'); exit;
                            if($this->input->post('certificate')) {
                                foreach($data['certificate'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['certificate'][$k]);
                                    }
                                }
                                $count_v = count($data['certificate']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitTwentyfour($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                //echo json_encode($count_v); exit;
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => 'certificate'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Visit 25 */
    public function cardservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/cardservice';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            //echo json_encode('exists'); exit;
                            if($this->input->post('card')) {
                                foreach($data['card'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['card'][$k]);
                                    }
                                }
                                $count_v = count($data['card']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitTwentyfive($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                //echo json_encode($count_v); exit;
                            }else {
                                $this->_httpStatus = http_response_code(400);
                                $data = array(
                                    'fail' => 'Bad Request - Mandatory fields are missing.',
                                    'status' => 0,
                                    'fields' => 'card'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    //Visit Status
    public function getcityvisitstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getcityvisitstatus';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_id = $data['citizen_id'];
                        $visits = array();

                        //visit 21
                        $visit_1 = $this->CoordinatorModel->visitTwentyOneStatus($citizen_id);
                        if($visit_1) {
                            $status = $visit_1->status;
                            if($visit_1->modified_at == null) {
                                $visit_time = $visit_1->created_at;
                            }else {
                                $visit_time = $visit_1->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_1'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_1'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 22
                        $visit_2 = $this->CoordinatorModel->visitTwentyTwoStatus($citizen_id);
                        if($visit_2) {
                            $status = $visit_2->status;
                            if($visit_2->modified_at == null) {
                                $visit_time = $visit_2->created_at;
                            }else {
                                $visit_time = $visit_2->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_2'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_2'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 23
                        $visit_3 = $this->CoordinatorModel->visitTwentyThreeStatus($citizen_id);
                        if($visit_3) {
                            $status = $visit_3->status;
                            if($visit_3->modified_at == null) {
                                $visit_time = $visit_3->created_at;
                            }else {
                                $visit_time = $visit_3->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_3'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_3'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 24
                        $visit_4 = $this->CoordinatorModel->visitTwentyFourStatus($citizen_id);
                        if($visit_4) {
                            $status = $visit_4->status;
                            if($visit_4->modified_at == null) {
                                $visit_time = $visit_4->created_at;
                            }else {
                                $visit_time = $visit_4->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_4'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_4'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 25
                        $visit_5 = $this->CoordinatorModel->visitTwentyFiveStatus($citizen_id);
                        if($visit_5) {
                            $status = $visit_5->status;
                            if($visit_5->modified_at == null) {
                                $visit_time = $visit_5->created_at;
                            }else {
                                $visit_time = $visit_5->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_5'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_5'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 6
                        $visit_6 = $this->CoordinatorModel->visitSixStatus($citizen_id);
                        if($visit_6) {
                            $status = $visit_6->status;
                            if($visit_6->modified_at == null) {
                                $visit_time = $visit_6->created_at;
                            }else {
                                $visit_time = $visit_6->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_6'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_6'] = array(
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
                            'success' => 'Visits Status.',
                            'status' => 1,
                            'data' => array(
                                'visits' => $visits
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

    /**
     * Other Service Request
     */
    public function servicerequest() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/servicerequest';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id', 'visit_id', 'service');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']);
                        if($citizen_exists) {
                            $save_request = $this->CoordinatorModel->saveServiceRequest($data);
                            if($save_request) {
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Success. Request saved successfully',
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
                            //activity log
                            $activity['http_status'] = 403;
                            $activity['status'] = 0;
                            $this->__activityLog($data['user_id'], $activity);

                            $this->_httpStatus = http_response_code(403);
                            $data = array(
                                'fail' => 'Forbidden - Citizen Not exists',
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

    //Visit 21 - 25 get services
    public function getcityservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getcityservice';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $services = array();
                    $modi_schemes = $this->CoordinatorModel->getVisit21ServiceCount($data['user_id']);
                    $services['modi_schemes']['id'] = 21;
                    $services['modi_schemes']['count'] = $modi_schemes;
                    //echo json_encode($modi_schemes); exit;
                    
                    $health = $this->CoordinatorModel->getServiceCountByCityService($data['user_id'], 'tbl_visit_22', 'tbl_visit22_options');
                    $services['health']['id'] = 22;
                    $services['health']['count'] = $health;
                   

                    $job = $this->CoordinatorModel->getServiceCountByCityService($data['user_id'], 'tbl_visit_23', 'tbl_visit23_options');
                    $services['job']['id'] = 23;
                    $services['job']['count'] = $job;

                    $certificate = $this->CoordinatorModel->getServiceCountByCityService($data['user_id'], 'tbl_visit_24', 'tbl_visit24_options');
                    $services['certificate']['id'] = 24;
                    $services['certificate']['count'] = $certificate;

                    $id_service = $this->CoordinatorModel->getServiceCountByCityService($data['user_id'], 'tbl_visit_25', 'tbl_visit25_options');
                    $services['id_cards']['id'] = 25;
                    $services['id_cards']['count'] = $id_service;
                    //activity log
                    $activity['http_status'] = 200;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'success' => 'Success',
                        'status' => 1,
                        'data' => $services     
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

    //citizen list by service
    public function getcitizenbycityservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getcitizenbycityservice';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('service');
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
                        $members = $this->CoordinatorModel->getMemberByCityService($data['user_id'], $data['service']);
                        if($members) {
                            foreach($members as $k => $v) {
                                if($v->photo != '') {
                                    $v->photo = base_url($this->config->item('assets_voters')).$v->photo;
                                }else {
                                    if($v->gender == 4) {
                                        $v->photo = base_url($this->config->item('assets_male'));
                                    }else {
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
                                'success' => 'No Content - Token failed. Login again.',
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

    /* Visit 27 - 30 & 33 - 34 */
    public function schemeflashvisit() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/schemeflashvisit';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id','visit_no');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']); 
                        if($citizen_exists) {
                            //echo json_encode('exists'); exit;
                            $save_data = $this->CoordinatorModel->saveSchemeFlashData($data);
                            if($save_data){
                                //activity log
                                $activity['http_status'] = 200;
                                $activity['status'] = 1;
                                $this->__activityLog($data['user_id'], $activity);

                                $this->_httpStatus = http_response_code(200);
                                $data = array(
                                    'success' => 'Success. Visit saved successfully',
                                    'status' => 1 
                                );
                                echo $this->__json_output('Success', $data);
                            }
                            else{
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Visit 31 */
    public function govtbeneficiary() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/govtbeneficiary';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']);
                        if($citizen_exists) {
                            if($this->input->post('schemes')) {
                                foreach($data['schemes'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['schemes'][$k]);
                                    }
                                }
                                $count_v = count($data['schemes']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitThirtyOne($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                    'fields' => 'schemes'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Visit 31 */
    public function pensionbeneficiary() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/pensionbeneficiary';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']);
                        if($citizen_exists) {
                            if($this->input->post('pension')) {
                                foreach($data['pension'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['pension'][$k]);
                                    }
                                }
                                $count_v = count($data['pension']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitThirtyTwo($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                    'fields' => 'schemes'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Visit 27 - 32 status */
    public function getflashvisitstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getflashvisitstatus';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_id = $data['citizen_id'];
                        $visits = array();

                        //visit 27
                        $visit_1 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_27');
                        if($visit_1) {
                            $status = $visit_1->status;
                            if($visit_1->modified_at == null) {
                                $visit_time = $visit_1->created_at;
                            }else {
                                $visit_time = $visit_1->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_1'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_1'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 28
                        $visit_2 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_28');
                        if($visit_2) {
                            $status = $visit_2->status;
                            if($visit_2->modified_at == null) {
                                $visit_time = $visit_2->created_at;
                            }else {
                                $visit_time = $visit_2->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_2'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_2'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 29
                        $visit_3 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_29');
                        if($visit_3) {
                            $status = $visit_3->status;
                            if($visit_3->modified_at == null) {
                                $visit_time = $visit_3->created_at;
                            }else {
                                $visit_time = $visit_3->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_3'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_3'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 30
                        $visit_4 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_30');
                        if($visit_4) {
                            $status = $visit_4->status;
                            if($visit_4->modified_at == null) {
                                $visit_time = $visit_4->created_at;
                            }else {
                                $visit_time = $visit_4->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_4'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_4'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 31
                        // $visit_5 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_31');
                        // if($visit_5) {
                        //     $status = $visit_5->status;
                        //     if($visit_5->modified_at == null) {
                        //         $visit_time = $visit_5->created_at;
                        //     }else {
                        //         $visit_time = $visit_5->modified_at;
                        //     }
                        //     if($status == 0) {
                        //         $st_msg = 'Incomplete';
                        //     }else {
                        //         $st_msg = 'Completed';
                        //     }
                        //     $visits['visit_5'] = array(
                        //         'status_code' => $status,
                        //         'status' => $st_msg,
                        //         'last_visit' => $visit_time
                        //     );
                        // }else {
                        //     $visits['visit_5'] = array(
                        //         'status_code' => 2,
                        //         'status' => 'pending'
                        //     );
                        // }

                        //visit 32
                        // $visit_6 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_32');
                        // if($visit_6) {
                        //     $status = $visit_6->status;
                        //     if($visit_6->modified_at == null) {
                        //         $visit_time = $visit_6->created_at;
                        //     }else {
                        //         $visit_time = $visit_6->modified_at;
                        //     }
                        //     if($status == 0) {
                        //         $st_msg = 'Incomplete';
                        //     }else {
                        //         $st_msg = 'Completed';
                        //     }
                        //     $visits['visit_6'] = array(
                        //         'status_code' => $status,
                        //         'status' => $st_msg,
                        //         'last_visit' => $visit_time
                        //     );
                        // }else {
                        //     $visits['visit_6'] = array(
                        //         'status_code' => 2,
                        //         'status' => 'pending'
                        //     );
                        // }

                        //visit 33
                        $visit_5 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_33');
                        if($visit_5) {
                            $status = $visit_5->status;
                            if($visit_5->modified_at == null) {
                                $visit_time = $visit_5->created_at;
                            }else {
                                $visit_time = $visit_5->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_5'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_5'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 34
                        $visit_6 = $this->CoordinatorModel->getFlashVisitStatus($citizen_id, 'tbl_visit_34');
                        if($visit_6) {
                            $status = $visit_6->status;
                            if($visit_6->modified_at == null) {
                                $visit_time = $visit_6->created_at;
                            }else {
                                $visit_time = $visit_6->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_6'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_6'] = array(
                                'status_code' => 2,
                                'status' => 'pending'
                            );
                        }

                        //visit 35
                        $visit_7 = $this->CoordinatorModel->visitThirtyFiveStatus($citizen_id);
                        if($visit_7) {
                            $status = $visit_7->status;
                            if($visit_7->modified_at == null) {
                                $visit_time = $visit_7->created_at;
                            }else {
                                $visit_time = $visit_7->modified_at;
                            }
                            if($status == 0) {
                                $st_msg = 'Incomplete';
                            }else {
                                $st_msg = 'Completed';
                            }
                            $visits['visit_7'] = array(
                                'status_code' => $status,
                                'status' => $st_msg,
                                'last_visit' => $visit_time
                            );
                        }else {
                            $visits['visit_7'] = array(
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
                            'success' => 'Visits Status.',
                            'status' => 1,
                            'data' => array(
                                'visits' => $visits
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

    /* Status */
    public function overallstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/overallstatus';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $status_d = array();
                    //registration
                    $status_d['registration_target'] = 150;
                    $status_d['registration_achieved'] = $this->CoordinatorModel->getVotersCountByCoordinator($data['user_id']);

                    //visits
                    $visits = $this->CoordinatorModel->getVisitsCount($data['user_id']);
                    $total = 0;
                    $pending = 0;
                    $completed = 0;
                    if($visits) {
                        $total = count($visits);
                        foreach($visits as $v) {
                            if($v->status == 1) {
                                $completed += 1;
                            }else {
                                $pending += 1;
                            }
                        }
                    }
                    $status_d['visits_total'] = $total;
                    $status_d['visits_pending'] = $pending;
                    $status_d['visits_completed'] = $completed;

                    //voice messages
                    $status_d['voice_message'] = $this->CoordinatorModel->getVoiceMessageCount($data['user_id']);

                    //smart media likes
                    $status_d['likes'] = $this->CoordinatorModel->getSmartMediaLikes($data['user_id']);

                    //voter status
                    $voters = $this->CoordinatorModel->getVotersByCoordinator($data['user_id']);
                    $positive = 0;
                    $negative = 0;
                    $neutral = 0;
                    if($voters) {
                        foreach($voters as $v) {
                            if($v->voter_status == 12) {
                                $positive += 1;
                            }elseif($v->voter_status == 14) {
                                $neutral += 1;
                            }elseif($v->voter_status == 13)  {
                                $negative += 1;
                            }
                        }
                    }
                    $status_d['positive'] = $positive;
                    $status_d['negative'] = $negative;
                    $status_d['neutral'] = $neutral;
                    //activity log
                    $activity['http_status'] = 200;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'fail' => 'Success.',
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

    /*Outstation Citizen */
    public function getoutstation() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getoutstation';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $os = $this->CoordinatorModel->getOutStationMemberByCoord($data['user_id']);
                    if($os) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $os     
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'No Content.',
                            'status' => 2,
                                    
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

    /*Neighbourhood Citizen */
    public function getneighbourcitizen() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/getneighbourcitizen';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $neighbour = $this->CoordinatorModel->getNeighbouringCitizen($data['user_id']);
                    if($neighbour) {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 1;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'Success.',
                            'status' => 1,
                            'data' => $neighbour     
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        //activity log
                        $activity['http_status'] = 200;
                        $activity['status'] = 2;
                        $this->__activityLog($data['user_id'], $activity);

                        $this->_httpStatus = http_response_code(200);
                        $data = array(
                            'success' => 'No Content.',
                            'status' => 2,
                                    
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

    /* Visit 35 - Personal Service visit 7 */
    public function personalservice() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/personalservice';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('citizen_id');
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
                        $citizen_exists = $this->CoordinatorModel->citizenExistsByCoordinator($data['citizen_id'], $data['user_id']);
                        //echo json_encode($citizen_exists); exit;
                        if($citizen_exists) {
                            if($this->input->post('service')) {
                                foreach($data['service'] as $k => $v) {
                                    if($v == 0) {
                                        unset($data['service'][$k]);
                                    }
                                }
                                $count_v = count($data['service']);
                                if($count_v > 0) {
                                    $save_v = $this->CoordinatorModel->saveVisitThirtyFive($data);
                                    if($save_v) {
                                        //activity log
                                        $activity['http_status'] = 200;
                                        $activity['status'] = 1;
                                        $this->__activityLog($data['user_id'], $activity);

                                        $this->_httpStatus = http_response_code(200);
                                        $data = array(
                                            'success' => 'Success. Visit saved successfully',
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
                                    'fields' => 'schemes'    
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
                                'fail' => 'Forbidden - Citizen Not exists',
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

    /* Demo Video */
    public function appdemo() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/appdemo';
            if($this->input->post('user_id') && $this->input->post('token')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $data['video'] = base_url('Coordinator/videosource/').$data['user_id'].'/'.$data['token'];
                    $this->load->view('common/appdemo.php', $data);
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

    public function videosource($user_id, $token) {
        $verified = $this->__authenticate_user($user_id, $token);
        if($verified) {
            $file = "videos/coordinator.mp4";
            $file_size = filesize($file);
            $fp = fopen($file, "rb");
            $data = fread ($fp, $file_size);
            fclose($fp);
            header ("Content-type: video/mp4");
            echo $data;
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

    /**
     * Date : 31-12-2018
     */

    public function addfamilyhead() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/addfamilyhead';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'lastname', 'gender', 'age', 'voter_id', 'polling_station', 'mobile', 'local_status', 'group_id', 'relationship', 'voter_status');
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
                        $voter_exists = $this->CoordinatorModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $is_registered = $this->CoordinatorModel->voterRegisteredByCoordinator($data['voter_id']);
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
                                    'user_id' => $data['user_id']
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
                            $data['parent_id'] = $data['user_id'];
                            $data['user_role'] = 46;
                            
                        }
                        
                        $usr_id = $this->CoordinatorModel->addCitizen($data);
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

    public function addvoter() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/addvoter';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));
                if($verified && $active && $this->_authorized) {
                    $error_count = 0;
                    $error = array();
                    $fields = array('firstname', 'lastname', 'gender', 'age', 'voter_id', 'polling_station',  'local_status', 'group_id', 'relationship', 'voter_status');
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
                        $voter_exists = $this->CoordinatorModel->voterExists($data['voter_id']);
                        if($voter_exists) {
                            $is_registered = $this->CoordinatorModel->voterRegisteredByCoordinator($data['voter_id']);
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
                                    'user_id' => $data['user_id']
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
                            $data['parent_id'] = $data['user_id'];
                            $data['user_role'] = 17;
                        }
                        
                        $usr_id = $this->CoordinatorModel->addCitizen($data);
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

    public function familyhead() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/familyhead';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    
                    $volunteer = $this->CoordinatorModel->getVolunteerBySP($data['user_id']);
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
     * Date : 02-01-2019
     */

    public function inbox() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/inbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $inbox = $this->CoordinatorModel->getSms($data['user_id']);
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

    public function outbox() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/outbox';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $sent_sms = $this->CoordinatorModel->getSentSms($data['user_id']);
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

    public function groupsms() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/groupsms';
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
                            
                            if($data['receiver_group'] == 136 && $data['user_role'] == 46) { //family head
                                $family_head = $this->CoordinatorModel->getVolunteerBySP($data['user_id']);
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
                                    $sms_store = $this->CoordinatorModel->saveSms($data);    
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
            $activity['request'] = 'Coordinator/singlesms';
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
                            $sms_store = $this->CoordinatorModel->saveSms($data);

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

    public function msgread() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/msgread';
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
                        $read = $this->CoordinatorModel->smsread($data['sms_id'], $data['read'], $data['user_id']);
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
            $activity['request'] = 'Coordinator/help';
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
                        $help = $this->CoordinatorModel->saveHelpQuery($data);
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

    public function appupdate($user_id, $token, $app_id, $device_id) {
        if($user_id != '' && $token != '' && $app_id != '' && $device_id != '') {
            $verified = $this->__authenticate_user($user_id, $token);
            $active = $this->__isactive(array('user-id' => $user_id, 'app_id' => $app_id, 'device_id' => $device_id));

            if($verified && $active && $this->_authorized) { //user is active and token verified
                $file = 'apps/sheetcoordinator.apk';
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

    public function votersbyfh() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Coordinator/votersbyfh';
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
                        $v_members = $this->CoordinatorModel->getVotersByFH($data['fhid']);
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
 } //Class Ends