<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branding extends CI_Controller {
    
    private $allocation_status;
    private $_id;

    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 2) {
            redirect(base_url());
        }
		// load pagination library
        $this->load->library('pagination');
		$this->load->helper("url");
		
        $this->load->model('loginModel');
        $this->load->model('SeniorManagerModel');
		$this->load->model('userModel');
		$this->load->model('managerModel');
        $this->load->model('apiModel');
        $this->_id = $this->session->userdata('user')->id;
       // $this->_alloc_status();
    }
	//HOME ASSISTANCE
	public function social() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/branding/social.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function smedia() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/smedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobilec() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/mobilec.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function smmedia() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/smmedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function cable() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/cable.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function pmedia() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/pmedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function giftm() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/giftm.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function sprojects() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/sprojects.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function govtsch() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/govtsch.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function manifesto() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/manifesto.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function develop() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/develop.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function welfare() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/welfare.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function mycon() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/mycon.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobiletext() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/mobiletext.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobilevoice() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/mobilevoice.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function giftw() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/giftw.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function gifthw() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/gifthw.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function gifty() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/gifty.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function giftc() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/giftc.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function giftsc() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/giftsc.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function gifto() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('branding/gifto.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
		
    }
 }