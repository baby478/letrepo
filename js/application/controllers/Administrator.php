<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Administrator extends CI_Controller {

    private $_id;
    private $_usession;

    public function __construct() {
        parent::__construct();
		if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }
        
		$this->load->model('administratorModel');
        $this->_usession = $this->session->userdata('user');
        $this->_id = $this->session->userdata('user')->id;
    }

    public function index() {
        $loc = $this->_usession->location_id;
        //Recruitment Count
        $data['mp'] = $this->administratorModel->getMPCountByState($loc);
        $data['mla'] = $this->administratorModel->getMLACountByState($loc);
        $data['district_president'] = $this->administratorModel->getDstPresidentCountByState($loc);
        $data['const_president'] = $this->administratorModel->getConstPresidentCountByState($loc);
        $data['division_president'] = $this->administratorModel->getDvPresidentCountByState($loc);
        $data['division_coordinator'] = $this->administratorModel->getDvCoordinatorCountByState($loc);
        $data['booth_coordinator'] = $this->administratorModel->getSPSUserCountByState($loc, 55);
        $data['booth_president'] = $this->administratorModel->getSPSUserCountByState($loc, 18);
        $data['street_president'] = $this->administratorModel->getSPSUserCountByState($loc, 3);
        $data['tele_caller'] = $this->administratorModel->getSPSUserCountByState($loc, 138);

        //Registration
        $data['total_voters'] = $this->administratorModel->getTotalVotersByState($loc);
        $data['family_head'] = $this->administratorModel->getTotalVotersByState($loc, 46);
        $data['voters'] = $this->administratorModel->getTotalVotersByState($loc, 17);

        $data['header_css'] = array('clientadmin.css','dashboard.css');
        $data['plugins'] = array('js/plugin/moment/moment.min.js','js/plugin/fullcalendar/fullcalendar.min.js','js/plugin/number-animate/jquery.easy_number_animate.min.js'); 
		$this->load->view('includes/header.php', $data);
        $this->load->view('administrator/top-nav.php');
        $this->load->view('administrator/side-nav.php');
        $this->load->view('administrator/dashboard.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/administrator/increment.php', $data);
        $this->load->view('includes/footer.php');    
    }

    public function assignrole() {
        if($this->input->post()) {
            // echo '<pre>'; print_r($this->input->post()); exit;
            if($this->input->post('district')) {
                if($this->input->post('user-role') == 143) {
                    $this->form_validation->set_rules('district', 'District', 'required|callback__district_allocate');
                }elseif($this->input->post('user-role') == 144 || $this->input->post('user-role') == 44) {
                    $this->form_validation->set_rules('district', 'District', 'required');
                    $this->form_validation->set_rules('constituency', 'constituency', 'required|callback__constituency_allocate');
                }elseif($this->input->post('user-role') == 137 || $this->input->post('user-role') == 145) {
                    $this->form_validation->set_rules('district', 'District', 'required');
                    $this->form_validation->set_rules('constituency', 'Constituency', 'required');
                    $this->form_validation->set_rules('mandal', 'Mandal', 'required|callback__mandal_allocate');
                }elseif($this->input->post('user-role') == 2) {
                    $this->form_validation->set_rules('district', 'District', 'required');
                    $this->form_validation->set_rules('constituency', 'constituency', 'required');
                    $this->form_validation->set_rules('mandal', 'Mandal', 'required');
                    if(is_array($this->input->post('mPollingstation[]'))) {
                        $this->form_validation->set_rules('mPollingstation[]', 'Polling Station','required|callback__ps_exists');
                    }
                }elseif($this->input->post('user-role') == 55 || $this->input->post('user-role') == 18 || $this->input->post('user-role') == 138) {
                    $this->form_validation->set_rules('district', 'District', 'required');
                    $this->form_validation->set_rules('constituency', 'constituency', 'required');
                    $this->form_validation->set_rules('mandal', 'Mandal', 'required');
                    $this->form_validation->set_rules('sPollingstation', 'Polling Station', 'required|callback__sps_exists');
                }elseif($this->input->post('user-role') == 3) {
                    $this->form_validation->set_rules('district', 'District', 'required');
                    $this->form_validation->set_rules('constituency', 'constituency', 'required');
                    $this->form_validation->set_rules('mandal', 'Mandal', 'required');
                    $this->form_validation->set_rules('sPollingstation', 'Polling Station', 'required');
                    $this->form_validation->set_rules('bpuser', 'Booth President', 'required');
                }    
            }

            if($this->input->post('user-role') == 57) {
                $this->form_validation->set_rules('lsconstituency', 'LS Constituency', 'required|callback__lsconstituency_exists');
            }

            if($this->form_validation->run() === TRUE) {
               
                $data = $this->input->post();
                
                $update_role = $this->administratorModel->assignUserRole($data);
                if($update_role) {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-success fade in"><strong>Success!</strong> User role assigned successfully.</div>');
                }else {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
            }
        }
		$data['user_roles'] = $this->administratorModel->getAssignRole();
        $data['users'] = $this->administratorModel->getUsersDataByRole(17);
        $location = $this->_usession->location_id;
        $data['districts'] = $this->administratorModel->getDistricts($location);
        $data['lsconstituency'] = $this->administratorModel->getLSConstituency($location);
		
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('administrator/top-nav.php');
        $this->load->view('administrator/side-nav.php');
        $this->load->view('administrator/assign-user-location.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/administrator/assign-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getConstByDistrict($id) {
        $result = $this->administratorModel->getConstituenceByDistrict($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function getMandalsByCon($id) {
        $result = $this->administratorModel->getMandalsByConstituence($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function getPollingStationByMandal($id) {
        $result = $this->administratorModel->getPSByMandal($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function getbpbyps($id) {
        $result = $this->administratorModel->getBPBypsid($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    /* App Download */
    public function appdownload($app_id) {
        $id = $this->_id;
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('administrator/top-nav.php');
        $this->load->view('administrator/side-nav.php');
        if($app_id == 79 || $app_id == 78 || $app_id == 81 || $app_id == 86 || $app_id == 84 || $app_id == 85 || $app_id == 89) {
            if($app_id == 86) {
                $data['app_id'] = 86;
                $data['app'] = 'Division President App';
                $data['user_role'] = 137;
            }elseif($app_id == 84) {
                $data['app_id'] = 84;
                $data['app'] = 'Division Coordinator App';
                $data['user_role'] = 2;
            }elseif($app_id == 81) {
                $data['app_id'] = 81;
                $data['app'] = 'Booth Coordinator App';
                $data['user_role'] = 55;
            }elseif($app_id == 85) {
                $data['app_id'] = 85;
                $data['app'] = 'Telecaller App';
                $data['user_role'] = 138;
            }elseif($app_id == 79) {
                $data['app_id'] = 79;
                $data['app'] = 'Booth President App';
                $data['user_role'] = 18;
            }elseif($app_id == 78) {
                $data['app_id'] = 78;
                $data['app'] = 'Street President App';
                $data['user_role'] = 3;
            }elseif($app_id == 89) {
                $data['app_id'] = 89;
                $data['app'] = 'App Coordinator App';
                $data['user_role'] = 145;
            }
            $location = $this->_usession->location_id;
            $data['districts'] = $this->administratorModel->getDistricts($location);
            $this->load->view('administrator/appdownload.php', $data);
            
        }else {
            $data['content'] = 'No Content Here';
            $this->load->view('common/no-data.php', $data);
        }
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/administrator/download-script.php');
        $this->load->view('includes/footer.php');
    }

    /**
     * Date : 11-03-2019
     * Author : Anees
     */
    public function _district_allocate($dist) {
        $exists = $this->administratorModel->districtAllocateExists($dist);
        if($exists) {
            $this->form_validation->set_message('_district_allocate', 'This district is already assigned');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function _constituency_allocate($const) {
        $user_role = $this->input->post('user-role');
        $exists = $this->administratorModel->asConstituencyExists($const, $user_role);
        if($exists) {
            $this->form_validation->set_message('_constituency_allocate', 'This constituency is already assigned');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function _mandal_allocate($location) {
        $role = $this->input->post('user-role');
        $exists = $this->administratorModel->mandalExists($location, $role);
        if($exists) {
            $this->form_validation->set_message('_mandal_allocate', 'This location is already assigned');
            return FALSE;
        }else {
            return TRUE;
        }      
    }

    public function _arrayinput() {
        $arr_location = $this->input->post('mPollingstation[]');
        if(empty($arr_location)) {
            $this->form_validation->set_rules('mPollingstation[]','Select at least one PS');
            return false;
        }    
    }

    public function _ps_exists() {
        $ps_array = $this->input->post('mPollingstation[]');
        $user_role = $this->input->post('user-role');
        if(count($ps_array) > 0) {
            $ps_exists = array();
            foreach($ps_array as $ps) {
                $exists = $this->administratorModel->psExists($ps, $user_role);
                if($exists) {
                    $ps_exists[] = $exists->ps_no;
                }
            }
            if(count($ps_exists) > 0) {
                $ps = implode(',', $ps_exists);
                $this->form_validation->set_message('_ps_exists', 'Following ps already assigned - '.$ps);
                return FALSE;
            }else {
                return TRUE;
            }
        }else {
            $this->form_validation->set_rules('mPollingstation[]','Select at least one PS');
            return false;
        }
    }

    public function _sps_exists($ps) {
        $user_role = $this->input->post('user-role');
        $exists = $this->administratorModel->psExists($ps, $user_role);
        if($exists) {
            $this->form_validation->set_message('_sps_exists', 'This ps is already assigned');
            return FALSE;
        }else {
            return TRUE;
        }      
    }

    public function _lsconstituency_exists($const) {
        $exists = $this->administratorModel->lsConstituencyExists($const);
        if($exists) {
            $this->form_validation->set_message('_lsconstituency_exists', 'This constituency is already assigned');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    /**
     * Date : 15-03-2019
     * Author : Anees
     */
    public function getUsersByDApp($role, $app, $did, $cid, $mid) {
        $result = $this->administratorModel->getUsersByAppDownload($role, $app, $did, $cid, $mid);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function sendapp() {
        if($this->input->post()) {
            $data = $this->input->post();
            echo '<pre>'; print_r($data); exit;

        }
    }
}