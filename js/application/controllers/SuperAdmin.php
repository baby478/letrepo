<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
    public function __construct() {
        parent::__construct();
		if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }
        $this->load->model('adminModel');
    }

    public function index() {
        $user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  'js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/dashboard.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('admin/custom-scripts.php');
        $this->load->view('includes/footer.php');
        
    }

    public function adduser() {
        $user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/add-user.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('admin/adduser-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function viewuser() {
        $user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		$data['users'] = $this->adminModel->getUsersData();
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/viewuser.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('admin/adduser-script.php');
        $this->load->view('includes/footer.php');
    }

    public function pollingstation() {
        if($this->input->post()) {
           $data = $this->input->post();
           $ps = $data['polling-station'];
           
           $ps_insert = $this->adminModel->addPsDetails($data);
           
           if($ps_insert) {
                //upload photo
                $config['upload_path']   = 'assets/images/ps/';
                $config['allowed_types'] = 'jpeg|jpg|png';
                $config['max_size']  = 1024;
                $this->load->library('upload', $config);
                $error_count = 0; 
                $images = array();
                $files = $_FILES['photo'];
                foreach($files['name'] as $key => $image) {
                        $_FILES['images[]']['name']= $files['name'][$key];
                        $_FILES['images[]']['type']= $files['type'][$key];
                        $_FILES['images[]']['tmp_name']= $files['tmp_name'][$key];
                        $_FILES['images[]']['error']= $files['error'][$key];
                        $_FILES['images[]']['size']= $files['size'][$key];

                        $fileName = $ps .'_'. time(). '_' .$image;

                        $images[] = $fileName;

                        $config['file_name'] = $fileName;

                        $this->upload->initialize($config);

                        if ($this->upload->do_upload('images[]')) {
                            $this->upload->data();
                        } else {
                            $error_count += 1;
                        }
                }
                if($error_count > 1) {
                    $this->session->set_flashdata('ps_update', '<div class="alert alert-danger fade in"><strong>Error!</strong> Photos could not be updated.</div>'); 
                }else {
                    $ps_image = $this->adminModel->addPsImages($ps, $images);
                    if($ps_image) {
                        $this->session->set_flashdata('ps_update', '<div class="alert alert-success fade in"><strong>Success!</strong> Details updated successfully.</div>');
                    }else {
                        $this->session->set_flashdata('ps_update', '<div class="alert alert-danger fade in"><strong>Error!</strong> Photos could not be updated.</div>');
                    }
                }
           }else {
            $this->session->set_flashdata('ps_update', '<div class="alert alert-danger fade in"><strong>Error!</strong> Details could not be updated.</div>');
           }
           
           
        }
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/polling-station.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('admin/ps-script.php');
        $this->load->view('includes/footer.php');
    }
	
	 //datatable for admin
    public function getusersdata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getUsersData();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name,
                $r->last_name,
                $r->email,
                $r->mobile,
                $r->gender,
                $r->user_role,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $users->num_rows(),
            "recordsFiltered" => $users->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	// Look up 
	public function lookup(){
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/lookup.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for lookup
    public function getlookupdata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $lookup = $this->adminModel->getLookupData();
		//print_r($users);exit;
        $data = array();
        foreach($lookup->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->lid,
				$r->value,
                $r->pvalue,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $lookup->num_rows(),
            "recordsFiltered" => $lookup->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	
	// State Data
	public function state(){
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/location/state.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for State Data
    public function getstatedata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $state = $this->adminModel->getAllStateData();
		//print_r($users);exit;
        $data = array();
        foreach($state->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->name,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $state->num_rows(),
            "recordsFiltered" => $state->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	
	// District Data
	public function district(){
		$user_data = $this->session->userdata('user');
		
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/location/district.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for District Data
    public function getdistrictdata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $district = $this->adminModel->getAllDistrictData();
		//print_r($users);exit;
        $data = array();
        foreach($district->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->name,
				$r->state,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $district->num_rows(),
            "recordsFiltered" => $district->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	
	// Mandal Data
	public function mandal(){
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/location/mandal.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for Manadal Data
    public function getmandaldata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $mandal = $this->adminModel->getAllMandalData();
		//print_r($users);exit;
        $data = array();
        foreach($mandal->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->name,
				$r->district,
				$r->state,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $mandal->num_rows(),
            "recordsFiltered" => $mandal->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	
	// Village Data
	public function village(){
		$user_data = $this->session->userdata('user');
		//$this->adminModel->getAllVillageData();
		//echo $this->db->last_query();exit;
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/location/village.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for Village Data
    public function getvillagedata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $village = $this->adminModel->getAllVillageData();
		//print_r($users);exit;
        $data = array();
        foreach($village->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->name,
				$r->mandal,
				$r->district,
				$r->state,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $village->num_rows(),
            "recordsFiltered" => $village->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	
	// Division Data
	public function division(){
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/location/division.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for Division Data
    public function getdivisiondata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $division = $this->adminModel->getAllDivisionData();
		//print_r($users);exit;
        $data = array();
        foreach($division->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->name,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $division->num_rows(),
            "recordsFiltered" => $division->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
	
	// Colonies Data
	public function colonies(){
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/location/colonies.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('includes/footer.php');
	}
	//datatable for Colonies Data
    public function getcoloniesdata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $colonies = $this->adminModel->getLocationData(74);
		//print_r($users);exit;
        $data = array();
        foreach($colonies->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->name,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $colonies->num_rows(),
            "recordsFiltered" => $colonies->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();    
    }
}