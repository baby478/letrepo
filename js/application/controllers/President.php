<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class President extends CI_Controller {
    private $allocation_status;
    private $_id;
    private $_location_id;

    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 59) {
            redirect(base_url());
        }
        $this->load->model('PresidentModel');
        $this->_id = $this->session->userdata('user')->id;
        $this->_location_id = $this->session->userdata('user')->location_id;
       // $this->_alloc_status();
    }

    public function index() {
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('president/top-nav.php');
        $this->load->view('president/side-nav.php');
        $this->load->view('president/dashboard.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('president/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function assemblyconstituencydistrict() {
        $location = $this->_location_id;
        $data['districts'] = $this->PresidentModel->getConstituencyDistrict($location, 60);
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('president/top-nav.php');
        $this->load->view('president/side-nav.php');
        $this->load->view('president/assembly-constituency-district.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('president/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function loksabhaconstituencies() {
        $location = $this->_location_id;
        $data['constituency'] = $this->PresidentModel->getLokSabhaConstituency($location);
        // echo '<pre>'; print_r($data['constituency']); exit;
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('president/top-nav.php');
        $this->load->view('president/side-nav.php');
        $this->load->view('president/loksabha-constituency.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('president/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function assemblyconstituency($id) {
        $data['constituency'] = $this->PresidentModel->getConstituencyByDistrict($id);
        // echo '<pre>'; print_r($data['constituency']); exit;
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('president/top-nav.php');
        $this->load->view('president/side-nav.php');
        $this->load->view('president/assembly-constituency-list.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('president/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function constituencydetails($id) {
        $data['const_demo'] = $this->PresidentModel->getConstituencyDemographics($id);
        $data['location'] = $this->PresidentModel->getLocation($id);
        $data['last_result'] = $this->PresidentModel->getLastResult($id);
        $data['elected_member'] = $this->PresidentModel->getElectedMember($id);
        // echo '<pre>';
        // print_r($data['elected_member']);
        // exit;
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('president/top-nav.php');
        $this->load->view('president/side-nav.php');
        $this->load->view('president/assembly-constituency.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('president/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function recruitment() {
        $location = $this->_location_id;
        $data['districts'] = $this->PresidentModel->getConstituencyDistrict($location, 60);
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('president/top-nav.php');
        $this->load->view('president/side-nav.php');
        $this->load->view('president/recruitment/district.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('president/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
}