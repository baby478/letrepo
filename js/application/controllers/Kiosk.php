<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kiosk extends CI_Controller {
    private $_authorized;
    private $_user_id;
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('AuthModel');
        $this->load->model('KioskModel');
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

    public function genderslide() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/genderslide';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $data['male_voters'] = $this->KioskModel->getVotersByKiosky($data['user_id'], array('v.gender' => 4))->num_rows();
                    $data['female_voters'] = $this->KioskModel->getVotersByKiosky($data['user_id'], array('v.gender' => 5))->num_rows();
                    $data['other_gender'] = $this->KioskModel->getVotersByKiosky($data['user_id'], array('v.gender' => 77))->num_rows();

                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/gender.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/gender-script.php', $data);
                    $this->load->view('includes/footer.php');
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

    public function ageslide() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/ageslide';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    //Age wise
                    $age = $this->KioskModel->getVotersByKiosky($data['user_id']);
                    $age_youth = 0; $age_working = 0; $age_middle = 0; $age_old = 0;
                    if($age->num_rows() > 0) {
                        foreach($age->result() as $ag) {
                            if($ag->age >= 18 && $ag->age <= 25) {
                                $age_youth += 1;
                            }
                            if($ag->age >= 26 && $ag->age <= 40) {
                                $age_working += 1;
                            }
                            if($ag->age >= 41 && $ag->age <= 55) {
                                $age_middle += 1;
                            }
                            if($ag->age > 55) {
                                $age_old += 1;
                            }
                        }
                    }
                    $data['age_youth'] = $age_youth;
                    $data['age_working'] = $age_working;
                    $data['age_middle'] = $age_middle;
                    $data['age_old'] = $age_old;

                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/age.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/age-script.php', $data);
                    $this->load->view('includes/footer.php');
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

    /* Registration */
    //Total Registration Count
    public function registrationcount() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/registrationcount';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $registered_u = $this->KioskModel->getVotersByKiosky($data['user_id'])->num_rows();
                    $data['total_register'] = $registered_u;
                    
                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
                    $data['header_css'] = array('admin.css','dashboard.css');
                    $this->load->view('includes/header.php', $data);
                    $this->load->view('kiosk/registration-count.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/registration-count-script.php', $data);
                    $this->load->view('includes/footer.php');
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
    
    //registration by mandal
    public function registrationbymandal() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/registrationbymandal';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $registration_m = $this->KioskModel->getRegistrationCountByMandal($data['user_id']);
                    $data_m = array();
                    $data_ctzn = array();
                    if($registration_m) {
                        foreach($registration_m as $m) {
                            $data_m[] = $m->mandal;
                            $data_ctzn[] = $m->ctzn;
                        }
                    }
                    $data['mandal'] = $data_m;
                    $data['ctzn'] = $data_ctzn;
                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                    $data['header_css'] = array('admin.css','dashboard.css');
                    $this->load->view('includes/header.php', $data);
                    $this->load->view('kiosk/registration-mandal.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/registration-mandal-script.php', $data);
                    $this->load->view('includes/footer.php');
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

    //Last 10 days Registration
    public function registrationslide() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/registrationslide';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $registered_u = $this->KioskModel->getVotersByDateRegister($data['user_id']);
                    $day_array = array();
                    $data_array = array();
                    if($registered_u) {
                        foreach($registered_u as $u) {
                            $day_array[] = date('F j, Y', strtotime($u->date));
                            $data_array[] = $u->ctzn;
                        }
                    }
                    $data['day'] = $day_array;
                    $data['data_count'] = $data_array;
                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/registration.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/registration-script.php', $data);
                    $this->load->view('includes/footer.php');
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

    //voter status
    public function voterstatus() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/voterstatus';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $data['pos_voters'] = $this->KioskModel->getVotersByKiosky($data['user_id'], array('v.voter_status' => 12))->num_rows();
                    $data['neu_voters'] = $this->KioskModel->getVotersByKiosky($data['user_id'], array('v.voter_status' => 14))->num_rows();
                    $data['neg_voters'] = $this->KioskModel->getVotersByKiosky($data['user_id'], array('v.voter_status' => 13))->num_rows();
                    
                    
                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/voterstatus.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/voterstatus-script.php', $data);
                    $this->load->view('includes/footer.php');
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

    //Registration Performance
    public function performance() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/performance';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $performance = $this->KioskModel->getCoordPerformanceByKiosk($data['user_id']);
                    $poor_p = 0; $good_p = 0; $vgood_p = 0; $exc_p = 0; $iconic_p = 0;
                    if($performance) {
                        foreach($performance as $p) {
                            if($p->registered < 50) {
                                $poor_p += 1;
                            }
                            if($p->registered >= 50 && $p->registered <= 100) {
                                $good_p += 1;
                            }
                            if($p->registered >= 101 && $p->registered <= 140) {
                                $vgood_p += 1;
                            }
                            if($p->registered >= 141 && $p->registered <= 175) {
                                $exc_p += 1;
                            }
                            if($p->registered > 175) {
                                $iconic_p += 1;
                            }
                        }
                    }
                    
                    $data['poor_p'] = $poor_p;
                    $data['good_p'] = $good_p;
                    $data['vgood_p'] = $vgood_p;
                    $data['exc_p'] = $exc_p;
                    $data['iconic_p'] = $iconic_p;

                    $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/performance.php');
                    $this->load->view('includes/plugins.php', $data);
                    $this->load->view('scripts/kiosk/performance-script.php', $data);
                    $this->load->view('includes/footer.php');
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

    public function recruitmentslide() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/performance';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $managers = $this->KioskModel->getCountManagersByKiosk($data['user_id']);
                    $tl = $this->KioskModel->getCountTLByKiosk($data['user_id']);
                    $coordinator = $this->KioskModel->getCountCoordinatorByKiosk($data['user_id']);

                    //activity log
                    $activity['http_status'] = 200;
                    $activity['status'] = 1;
                    $this->__activityLog($data['user_id'], $activity);

                    $this->_httpStatus = http_response_code(200);
                    $data = array(
                        'success' => 'Success.',
                        'status' => 1,
                        'data' => array(
                            'managers' => $managers,
                            'teamleader' => $tl,
                            'coordinator' => $coordinator
                        )     
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

    public function teamdetail() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/registrationslide';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $file = "team.jpg";
                    $data['image'] = base_url('Kiosk/imagesource/').$data['user_id'].'/'.$data['token'].'/'.$file;
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/image-page.php', $data);
                    $this->load->view('includes/footer.php');
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

    public function teamhierarchy() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            $activity = array();
            $activity['request'] = 'Kiosk/registrationslide';
            if($this->input->post('user_id') && $this->input->post('token') && $this->input->post('app_id') && $this->input->post('device_id')) {
                $verified = $this->__authenticate_user($data['user_id'], $data['token']);
                $active = $this->__isactive(array('user-id' => $data['user_id'], 'app_id' => $data['app_id'], 'device_id' => $data['device_id']));

                if($verified && $active && $this->_authorized) { //user is active and token verified
                    $file = "team_hierarchy.jpg";
                    $data['image'] = base_url('Kiosk/imagesource/').$data['user_id'].'/'.$data['token'].'/'.$file;
                    $this->load->view('includes/header.php');
                    $this->load->view('kiosk/image-page.php', $data);
                    $this->load->view('includes/footer.php');
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

    public function imagesource($user_id, $token, $file) {
        $verified = $this->__authenticate_user($user_id, $token);
        if($verified) {
            $file = 'videos/'.$file;
            $file_size = filesize($file);
            $fp = fopen($file, "rb");
            $data = fread ($fp, $file_size);
            fclose($fp);
            header ("Content-type: image");
            echo $data;
        }
           
    }
}