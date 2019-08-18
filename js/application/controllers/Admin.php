<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
		if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }
        $this->load->library('communication');
        $this->load->model('loginModel');
		$this->load->model('adminModel');
        $this->load->model('SeniorManagerModel');
        $this->load->model('managerModel');
        $this->load->model('userModel');
        $this->load->model('apiModel');
        $this->_alloc_status();
    }
    
    private function _alloc_status() {
        $id = $this->session->userdata('user')->id;
        $status = $this->userModel->checkAllocStatus($id);
        if($status > 0) {
            $this->allocation_status = true;
        }else {
            $this->allocation_status = false;
        }
    }
    
    public function index() {
        $user_data = $this->session->userdata('user');
		/* counter function*/
		$registration = $this->adminModel->getTotalRegistration()->result();
		$data['total_register'] = count($registration);
		$lid = $this->session->userdata('user')->location_id;
		$data['mandals']=$this->adminModel->getTotalMandalBySM()->result();
		
		 if($data['mandals'] != "") { 
            foreach($data['mandals'] as $k => $mg)  {
                $mg->total_register = $this->adminModel->getTotalRegistrationMandal($mg->id)->result();
                $mg->total_r = count($mg->total_register);
            }
        }
		$manager = $this->adminModel->getTotalMandalBySM()->result();
		$data['total_mandals'] = count($manager);
        
        $data['total_teamlead'] = $this->adminModel->getPSMemberByConstituencyRole($lid, 18)->num_rows();
        $data['total_coordinator'] = $this->adminModel->getPSMemberByConstituencyRole($lid, 3)->num_rows();
        $data['total_boothobserver'] = $this->adminModel->getBoothObserverCount($lid)->num_rows();
        // $volunteer = $this->adminModel->getTotalVolunteerBySM()->result();
        // $data['total_volunteer'] = count($volunteer);
        
		
		/*counter end */
        $data['header_css'] = array('clientadmin.css','dashboard.css');
        $data['plugins'] = array('js/plugin/moment/moment.min.js','js/plugin/fullcalendar/fullcalendar.min.js','js/plugin/number-animate/jquery.easy_number_animate.min.js'); 
		$this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/dashboard.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
        //$this->load->view('admin/custom-scripts.php');
		$this->load->view('scripts/admin/calendar-script');
		$this->load->view('scripts/admin/increment.php', $data);
        $this->load->view('includes/footer.php');
        
    }

    public function adduser() {
        $user_data = $this->session->userdata('user');
		 if($this->input->post()) {
			 
            if($this->form_validation->run() === TRUE) {
                $qualification = array();
                //$caste_head = array();
                if($this->input->post('qualification')) {
                    foreach($this->input->post('qualification') as $k => $qv) {
                        $qualification[$k]['q'] = $qv;
                    }
                }
                
                if($this->input->post('course')) {
                    foreach($this->input->post('course') as $k => $v) {
                        if(!empty($v)) {
                            $qualification[$k]['c'] = $v;
                        }
                        
                    }
                }

                if($this->input->post('college')) {
                    foreach($this->input->post('college') as $k => $v) {
                        if(!empty($v)) {
                            $qualification[$k]['clg'] = $v;
                        }
                    }
                }
              

                foreach($qualification as $k =>$v) {
                    if(!isset($qualification[$k]['c'])) {
                        $qualification[$k]['c'] = null;
                    }
                    if(!isset($qualification[$k]['clg'])) {
                        $qualification[$k]['clg'] = null;
                    }
                }
                
                $data = $this->input->post();
                $data['phone'] = str_replace(array( '(', ')', '-', ' ' ), '', $data['phone']);
                if($this->input->post('dob')) {
                    $data['dob'] = date('Y-m-d', strtotime($data['dob']));
                }
                $data['qualification'] = $qualification;
                //$data['caste_head'] = $caste_head;

                if($_FILES['photo']['name'] !== '') {
                    //upload photo
                    $config['upload_path']   = $this->config->item('assets_users');
                    $config['allowed_types'] = 'jpeg|jpg|png';
                    $config['max_size']  = 1024;
                    $config['file_name'] = time().$data['phone'];
                    $this->load->library('upload', $config);

                    if($this->upload->do_upload('photo')){
                        $uploadData = $this->upload->data();
                        $uploadedFile = $uploadData['file_name'];
                        $data['photo'] = $uploadedFile;
                    }else {
                        
                        $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
                    }
                }else {
                    $data['photo'] = null;
                }
                //  $insert = true;
                $insert = $this->adminModel->registerUser($data);
			  
                if($insert) {
				 
                    $this->session->set_flashdata('add_user', '<div class="alert alert-success fade in"><strong>Success!</strong> User registered successfully.</div>');
                }else {
                    $this->session->set_flashdata('add_user', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
                redirect(base_url('admin/adduser'));
            }
            
        }
        $data['header_css'] = array('clientadmin.css');
		$data['plugins'] = array('js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
		$this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/add-user.php');
        $this->load->view('includes/page-footer.php');
		//$this->load->view('manager/shortcut-nav.php');
		$this->load->view('includes/plugins.php', $data);
        $this->load->view('admin/adduser-script.php');
        $this->load->view('includes/footer.php');
    }

	public function _email_exists($email) {
        if(!empty($email)) {
            $exists = $this->loginModel->emailExists($email);
            if($exists) {
                $this->form_validation->set_message('_email_exists', 'The {field} is already exists');
                return FALSE;
            }else {
                return TRUE;
            }
        }else {
            return TRUE;
        }
        
    }

    public function _phone_exists($phone) {
        $phone = str_replace(array( '(', ')', '-', ' ' ), '', $phone);
        $exists = $this->userModel->phoneExists($phone);
        if($exists) {
            $this->form_validation->set_message('_phone_exists', 'The {field} number is already exists');
            return FALSE;
        }else {
            return TRUE;
        }
    }

	public function _voter_exists($phone) {
        $exists = $this->userModel->voterExists($phone);
        if($exists) {
            $this->form_validation->set_message('_voter_exists', 'The {field} is already exists');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function _file_check($file) {
        $allowed_mime_type_arr = array('image/jpeg','image/jpg','image/png');
        $mime = get_mime_by_extension($_FILES['photo']['name']);
        if(isset($_FILES['photo']['name']) && $_FILES['photo']['name']!=""){
            if(in_array($mime, $allowed_mime_type_arr)){
                return true;
            }else{
                $this->form_validation->set_message('_file_check', 'Please select only jpeg/jpg/png file.');
                return false;
            }
        }
    }
	
	public function viewuser() {
        $user_data = $this->session->userdata('user');
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		$data['users'] = $this->adminModel->getUsersData();
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/viewuser.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
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
                ($r->active_status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' ,
                '<ul class="demo-btns">
                    <li>
                         <a href="' . base_url('admin/edit/'.$r->id). '" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                    </li>
				</ul>'  
                // '<ul class="demo-btns">
                //     <li>
                //         <a href="' . base_url('admin/edit/'.$r->id). '" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                //     </li>
                //     <li>
                //         <a href="" class="deactivate btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>
                //     </li> 
				// 	<li>
				// 		<a href="" class="btn btn-xs" data-msg="'.$r->id.'" data-toggle="modal" data-target="#myModal"><i class="fa fa-comments"></i></a>
                //     </li> 
                // </ul>'
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
	
	public function assignrole() {
        $user_data = $this->session->userdata('user');
        $uid = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		if($this->input->post()) {
            if($this->input->post('mandal')) {
                if(!$this->input->post('pollingstation')) {
                    $this->form_validation->set_rules('mandal', 'Mandal', 'required|callback__mandal_allocate');
                }elseif($this->input->post('pollingstation')) {
                    //$this->form_validation->set_rules('pollingstation', 'Polling Station', 'required|callback__village_allocate');
                    //$this->form_validation->set_rules('pollingstation', 'Polling Station', 'required|callback__village_allocate');
                }
            }
            if($this->form_validation->run() === TRUE) {
                
                $data = $this->input->post();
                
                // $user_id = $this->input->post('user');
                // $role_id = $this->input->post('user-role');
                
				// if($role_id == 2) {
                //     $location = $this->input->post('mandal');
                //     $parent_id = $uid;
                // }
				// else if($role_id  == 18) {
                //     $location = $this->input->post('village');
                //     $parent_id = $this->input->post('teamleader');
                // }
			    // else {
                //     $location = $this->input->post('village');
                //     $parent_id = 1;
                // }
                $update_role = $this->adminModel->assignUserRole($data);
                if($update_role) {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-success fade in"><strong>Success!</strong> User role assigned successfully.</div>');
                }else {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
            }
        }
		$data['user_roles'] = $this->adminModel->getAssignRole();
        $data['users'] = $this->adminModel->getUsersDataByRole(17);
        $data['manager_mandal'] = $this->adminModel->getMandalsByConstituence($location);
		
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/assign-user-location.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/assign-script.php');
        $this->load->view('includes/footer.php');
    }

    /* Validation */
    public function _mandal_allocate($location) {
        $user_role = $this->input->post('user-role');
        if($user_role == 2) {
            $exists = $this->SeniorManagerModel->mandalAllocateExists($location);
            if($exists) {
                $this->form_validation->set_message('_mandal_allocate', 'This location is already assigned');
                return FALSE;
            }else {
                return TRUE;
            }    
        }else {
            return TRUE;
        }
    }

    public function _village_allocate($location) {
        $user_role = $this->input->post('user-role');
        if($user_role == 18) {
            $exists = $this->managerModel->mandalAllocateExists($location);
            if($exists) {
                $this->form_validation->set_message('_village_allocate', 'This location is already assigned');
                return FALSE;
            }else {
                return TRUE;
            }    
        }else {
            return TRUE;
        }
    }

    /* Mandal or Division by constituency */
    public function getMandalsByCon($id) {
        $result = $this->adminModel->getMandalsByConstituence($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function getPollingStationByMandal($id) {
        $result = $this->adminModel->getPSByMandal($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function getbpbyps($id) {
        $result = $this->adminModel->getBPBypsid($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
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
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/polling-station.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('admin/ps-script.php');
        $this->load->view('includes/footer.php');
    }

	public function edit($id) {
        $data['userdata'] = $this->adminModel->getUserDataById($id);
        $location = $this->session->userdata('user')->location_id;
        $u_data = $this->adminModel->getUserDataById($id);
        if($u_data) {
            if($u_data->user_role == 18 || $u_data->user_role == 3 || $u_data->user_role == 55) {
                $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
                $ps_details = $this->adminModel->getPsAssignedByUser($u_data->id);
                if($ps_details) {
                    $u_data->pid = $ps_details;
                    $data['mid'] = $ps_details[0]->mid;
                    $data['ps'] = $this->adminModel->getPollingStationByMandal($data['mid']);
                    $data['pid'] = $ps_details[0]->pid;  
                }
            }
        }
        // echo '<pre>'; print_r($u_data); exit;
		if($this->input->post()) {
            if($u_data->mobile != $this->input->post('phone')) {
                $this->form_validation->set_rules('phone', 'Phone', 'required|callback__phone_exists');
            }
            if($u_data->email != $this->input->post('email')) {
                $this->form_validation->set_rules('email', 'Email', 'required|callback__email_exists');
            }
            if($this->form_validation->run() === TRUE) {
                $data = $this->input->post();
			    if($_FILES['photo']['name'] !== '') {
                    //upload photo
                    $config['upload_path']   = $this->config->item('assets_users');
                    $config['allowed_types'] = 'jpeg|jpg|png';
                    $config['max_size']  = 1024;
                    $config['file_name'] = time().$data['phone'];
                    $this->load->library('upload', $config);

                    if($this->upload->do_upload('photo')){
                        $uploadData = $this->upload->data();
                        $uploadedFile = $uploadData['file_name'];
                        $data['photo'] = $uploadedFile;
                    }else {
                        $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
                    }
                }else {
                    $data['photo'] = null;
                }
			$update = $this->adminModel->updateData($data,$id);
			if($update)  { 
                $this->session->set_flashdata('edit_user', '<div class="alert alert-success fade in"><strong>Success!</strong> User Updated successfully.</div>');
            }else {
                $this->session->set_flashdata('edit_user', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
			 redirect(base_url('admin/edit/').$id);
			}
		}
		$data['header_css'] = array('clientadmin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/edit.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/edit-script.php');
        $this->load->view('includes/footer.php');
		
    }
	
	public function contestant() {
         $user_data = $this->session->userdata('user');
		 if($this->input->post()) {
			 if($this->form_validation->run() === TRUE) 
			  {
           $data = $this->input->post();
		   if($_FILES['photo']['name'] !== '') {
                    //upload photo
                    $config['upload_path']   = 'assets/images/contestants/';
                    $config['allowed_types'] = 'jpeg|jpg|png';
                    $config['max_size']  = 1024;
                    $config['file_name'] = time().$data['contestant'];
                    $this->load->library('upload', $config);

                    if($this->upload->do_upload('photo')){
                        $uploadData = $this->upload->data();
                        $uploadedFile = $uploadData['file_name'];
                        $data['photo'] = $uploadedFile;
                    }else {
                        $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
                    }
                }else {
                    $data['photo'] = null;
                }
				
				$insert = $this->adminModel->addContestant($data);
				
                 if($insert) {
                    $this->session->set_flashdata('add_contestant', '<div class="alert alert-success fade in"><strong>Success!</strong> Contestant Added successfully.</div>');
                }else {
                    $this->session->set_flashdata('add_contestant', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                } 
                redirect(base_url('admin/contestant'));
			  }
		 }
        $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/add-contestant.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getcontestant() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getContestant();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->contestants_name,
                $r->age,
				'<img src="'. base_url('assets/images/contestants/').$r->contestant_photo.'" height="50" width="50" align="center">',
				'<img src="'. base_url('assets/images/dashboard/party/').$r->party_icon.'" height="50" width="50" align="center">',
                $r->name,
				'<a href="" class="deactivateconts btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>',
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
	
	public function smartmedia() {
        $user_data = $this->session->userdata('user');
		if($this->input->post()) {
           $data = $this->input->post();
		   if($_FILES['photo']['name'] !== '') {
                    //upload photo
                    $config['upload_path']   = $this->config->item('assets_smedia');
                    $config['allowed_types'] = 'jpeg|jpg|png';
                    $config['max_size']  = 1024;
                    $config['file_name'] = time();
                    $this->load->library('upload', $config);

                    if($this->upload->do_upload('photo')){
                        $uploadData = $this->upload->data();
                        $uploadedFile = $uploadData['file_name'];
                        $data['photo'] = $uploadedFile;
                    }else {
                        
                        $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
                    }
                }else {
                    $data['photo'] = null;
                }
		        $data['publishdate'] = date('Y-m-d', strtotime($data['publishdate']));
				
				$insert = $this->adminModel->addSmartMedia($data);
				
                 if($insert) {
				 
                    $this->session->set_flashdata('add_contestant', '<div class="alert alert-success fade in"><strong>Success!</strong> Media Added successfully.</div>');
                }else {
					
                    $this->session->set_flashdata('add_contestant', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                } 
                redirect(base_url('admin/smartmedia'));
		  
		 }
        $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
								
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/smart-media.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/admin/datatable-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
         $this->load->view('includes/footer.php');
    }
	
	public function getsmartmedia() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getSmartMedias();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				'<img src="'. base_url($this->config->item('assets_images')).$r->media_path.'" height="50" width="50" align="center">',
                $r->publish_date,
				($r->status == 1) ? 'Publish' : 'Not Publish',
				'<a href="" class="deactivatesmm btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>',
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
	
	public function polingagent() {
        $user_data = $this->session->userdata('user');
		//$ps = $this->apiModel->getPollingStations();
		
        //$data['polling_station'] = $ps;
		 if($this->input->post()) {
			  if($this->form_validation->run() === TRUE) 
			  { 
			   $data = $this->input->post();
			  
			   if($_FILES['photo']['name'] !== '') {
						//upload photo
						$config['upload_path']   = 'assets/images/polling-agent/';
						$config['allowed_types'] = 'jpeg|jpg|png';
						$config['max_size']  = 1024;
						$config['file_name'] = time().$data['pollingno'];
						$this->load->library('upload', $config);

						if($this->upload->do_upload('photo')){
							$uploadData = $this->upload->data();
							$uploadedFile = $uploadData['file_name'];
							$data['photo'] = $uploadedFile;
						}else {
							
							$this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
						}
					}else {
						$data['photo'] = null;
					}
				   
					$insert = $this->adminModel->addPollingAgent($data);
					
					if($insert) {
					 
						$this->session->set_flashdata('add_PA', '<div class="alert alert-success fade in"><strong>Success!</strong> Media Added successfully.</div>');
					}else {
						
						$this->session->set_flashdata('add_PA', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
					} 
					redirect(base_url('admin/polingagent'));
			 }
		 }
        $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/add-polling-agent.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getpollingagent() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getPollingAgent();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->first_name . ' ' . $r->last_name,
				$r->ps_no,
				$r->ps_name,
				$r->mobile,
				'<img src="'. base_url('assets/images/polling-agent/').$r->photo.'" height="50" width="50" align="center">',
				'<a href="" class="deactivatepolagt btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>',
               
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
    
    public function bulksms() {
        $user_data = $this->session->userdata('user');
		$data['bulksms'] = $this->adminModel->getBulkSms();
		
		 if($this->input->post()) {
			 $data = $this->input->post();
			 $insert = $this->adminModel->insertBulkSms($data);
			  if($insert) 
				{ 
					$this->session->set_flashdata('add_sms', '<div class="alert alert-success fade in"><strong>Success!</strong> Bulk Sms Updated successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('add_sms', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('admin/bulksms'));
		 }
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
								
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/bulk-sms.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getbulksms() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getBulkSms();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->language,
				$r->value,
				$r->message,
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
	
	public function smslimit() {
        $user_data = $this->session->userdata('user');
		 if($this->input->post()) {
			 $data = $this->input->post();
			 $insert = $this->adminModel->insertSmsLimit($data);
			
			  if($insert) 
				{ 
					$this->session->set_flashdata('add_sms', '<div class="alert alert-success fade in"><strong>Success!</strong> Limit Updated successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('add_sms', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('admin/smslimit'));
		 }
        $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
								
         $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/sms-limit.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }
    
	public function getsmslimit() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getSmsLimit();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->value,
				$r->smslimit,
				'<a href="" class="deactivate btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>',
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
	
	public function addevents() {
        $user_data = $this->session->userdata('user');
		
		 if($this->input->post()) {
			 $data = $this->input->post();
			 if($_FILES['photo']['name'] !== '') {
                    //upload photo
                    $config['upload_path']   = $this->config->item('assets_events');
                    $config['allowed_types'] = 'jpeg|jpg|png';
                    $config['max_size']  = 1024;
                    $config['file_name'] = time().$data['eventname'];
                    $this->load->library('upload', $config);

                    if($this->upload->do_upload('photo')){
                        $uploadData = $this->upload->data();
                        $uploadedFile = $uploadData['file_name'];
                        $data['photo'] = $uploadedFile;
                    }else {
                        
                        $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
                    }
                }else {
                    $data['photo'] = null;
                }
			 $data['eventdate'] = date('Y-m-d', strtotime($data['eventdate']));
			 $insert = $this->adminModel->insertEvents($data);
			  if($insert) 
				{ 
					$this->session->set_flashdata('add_events', '<div class="alert alert-success fade in"><strong>Success!</strong> Event Added successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('add_events', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('admin/addevents'));
		 }
        $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
								
         $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/add-events.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/datatable-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getevents() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getAllEvents();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->value,
				$r->event_name,
				$r->event_description,
				$r->event_date,
				'<img src="'. base_url($this->config->item('assets_events')).$r->event_img.'" height="50" width="50" align="center">',
				'<a href="" class="deactivateevent btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>',
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
	
	public function addtask() {
        $user_data = $this->session->userdata('user');
		
		 if($this->input->post()) {
			 $data = $this->input->post();
			 $insert = $this->adminModel->allocateGroupTaskBySeniorManager($data,62);
			  if($insert) 
				{ 
					$this->session->set_flashdata('assign-grouptask', '<div class="alert alert-success fade in"><strong>Success!</strong> Task Added successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('assign-grouptask', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('admin/addtask'));
		 }
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/markdown/markdown.min.js',
								'js/plugin/markdown/to-markdown.min.js','js/plugin/markdown/bootstrap-markdown.min.js',
								'js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
								
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/add-tasks.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/admin/task-script.php');
		$this->load->view('scripts/admin/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }

	public function viewtask() {
        $user_data = $this->session->userdata('user');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		$data['users'] = $this->adminModel->getUsersData();
        $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/view-task.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/admin/datatable-script.php');
        $this->load->view('admin/custom-scripts.php');
        $this->load->view('includes/footer.php');
		
    }

	public function gettasks() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getAllEventTasks();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->task_name,
				$r->value,
				$r->date_from,
				$r->date_to,
				($r->priority == 1) ? 'High' : 'Low',
				$r->task_description,
				'<a href="" class="deactivatetask btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>',
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
	/** Delete and Deactivate **/
	
	public function delsmslimit() {
        if($this->input->post()) {
            $del_id = $this->input->post('del_id');
            $deletedata = $this->adminModel->deleteSmsLimit($del_id);
            if($deletedata) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    } 

	public function delevent() {
        if($this->input->post()) {
            $del_id = $this->input->post('del_id');
            $deletedata = $this->adminModel->delEvents($del_id);
            if($deletedata) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    } 
	
	public function deltask() {
        if($this->input->post()) {
            $del_id = $this->input->post('del_id');
            $deletedata = $this->adminModel->delTasks($del_id);
            if($deletedata) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    } 
	
	public function delsmartmedia() {
        if($this->input->post()) {
            $del_id = $this->input->post('del_id');
            $deletedata = $this->adminModel->delSmartMediaa($del_id);
            if($deletedata) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    } 
	
	public function delcontestant() {
        if($this->input->post()) {
            $del_id = $this->input->post('del_id');
            $deletedata = $this->adminModel->delContestants($del_id);
            if($deletedata) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    } 
	
	public function delpollingagt() {
        if($this->input->post()) {
            $del_id = $this->input->post('del_id');
            $deletedata = $this->adminModel->delPollingAgent($del_id);
            if($deletedata) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    }
		
	public function getallevents() {
        $start = $this->input->get("start");
        $end = $this->input->get("end");
        $startdt = new DateTime('now'); // setup a local datetime
        $startdt->setTimestamp($start); // Set the date based on timestamp
        $start_format = $startdt->format('Y-m-d H:i:s');
        $enddt = new DateTime('now'); // setup a local datetime
        $enddt->setTimestamp($end); // Set the date based on timestamp
        $end_format = $enddt->format('Y-m-d H:i:s');
        $id = $this->session->userdata('user')->id;
    
        $events = $this->adminModel->getAllEventss();

        $data_events = array();
    
        foreach($events as $r) {
            if($r->task_group == 62) {
                $classname = 'bg-color-red txt-color-white'; //Group event
				$task_type = '<p>Task Type : Group<br><p>Assigned to : ' . $r->assigned. '</p>';
            }
            if($r->task_group == 63) {
                $classname = 'bg-color-greenLight txt-color-white'; //Member event
				$task_type = '<p>Task Type : Individual <br><p>Assigned to : ' . $r->assigned. '</p>';
            }
            if($r->task_group == 64) {
                $task_type = '<p>Task Type : Self <br><p>Assigned to : ' . $r->assigned. '</p>';
            } 
            $data_events[] = array(
                "id" => $r->id,
                "title" => $r->task_name,
                "description" => $r->task_description . $task_type,
                "end" => $r->date_to,
                "start" => $r->date_from,
                "className" => $classname
            );
        }

        echo json_encode(array("events" => $data_events));
        exit();
    }

    public function addbranding() {
    
        if($this->input->post()) {
            $data = $this->input->post();
            
            if($_FILES['photo']['name'] !== '') {
                     //upload photo
                     $config['upload_path']   = 'assets/images/branding-banners/smedia/';
                     $config['allowed_types'] = 'jpeg|jpg|png';
                     $config['max_size']  = 1024;
                     //$config['file_name'] = time();
                     $this->load->library('upload', $config);
 
                     if($this->upload->do_upload('photo')){
                         $uploadData = $this->upload->data();
                         $uploadedFile = $uploadData['file_name'];
                         $data['photo'] = $uploadedFile;
                     }else {
                         
                         $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
                     }
                 }else {
                     $data['photo'] = null;
                 }
                 
                 
                 $insert = $this->adminModel->insertBranding($data);
                 
                  if($insert) {
                  
                     $this->session->set_flashdata('add_brand', '<div class="alert alert-success fade in"><strong>Success!</strong> Media Added successfully.</div>');
                 }else {
                     
                     $this->session->set_flashdata('add_brand', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                 } 
              redirect(base_url('admin/addbranding'));
           
         
          }
         $data['header_css'] = array('jquery-confirm.min.css','clientadmin.css');
                                 
         $this->load->view('includes/header.php', $data);
         $this->load->view('admin/top-nav.php');
         $this->load->view('admin/side-nav.php');
         $this->load->view('admin/add-branding.php');
         $this->load->view('includes/page-footer.php');
       $this->load->view('includes/plugins.php', $data);
         //$this->load->view('scripts/admin/datatable-script.php');
         //$this->load->view('scripts/admin/form-validation-script.php');
         $this->load->view('includes/footer.php');
    }

    /* App Download */
    public function appdownload($app_id) {
        $id = $this->session->userdata('user')->id;
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        if($app_id == 79 || $app_id == 78 || $app_id == 81) {
            if($app_id == 79) {
                $data['app_id'] = 79;
                $data['app'] = 'BLO App';
                $data['users'] = $this->adminModel->getTLByApp($id);
            }
            if($app_id == 78) {
                $data['app_id'] = 78;
                $data['app'] = 'Street President App';
                $data['users'] = $this->adminModel->getCoordByApp($id);
            }
            if($app_id == 81) {
                $data['app_id'] = 81;
                $data['app'] = 'Booth Observer App';
                $data['users'] = $this->adminModel->getBOByApp($id);
            }
            $this->load->view('admin/appdownload.php', $data);
        }else {
            $data['content'] = 'No Content Here';
            $this->load->view('common/no-data.php', $data);
        }
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/download-script.php');
        $this->load->view('includes/footer.php');
    }
    
    public function sendapp() {
        $id = $this->session->userdata('user')->id;
        if($this->input->post()) {
            $data = $this->input->post();
            // echo '<pre>'; print_r($data); exit;
            $sendlink = $this->adminModel->saveAppDownload($data);
            // $sendlink = true;
            if($sendlink) {
                $mobile = '';
                if($data['user-group'] == 'all') {
                    if($data['app'] == 78) {
                        $users = $this->adminModel->getCoordByApp($id);
                        $message = "Please click or copy below link to download Street President app. \n\n".base_url('download/app/sheetpresident');    
                    }elseif($data['app'] == 79) {
                        $users = $this->adminModel->getTLByApp($id);
                        $message = "Please click or copy below link to download Booth President app. \n\n".base_url('download/app/boothpresident'); 
                    }elseif($data['app'] == 81) {
                        $users = $this->adminModel->getBOByApp($id);
                        $message = "Please click or copy below link to download Booth Observer app. \n\n".base_url('download/app/boothobserver');
                    }
                }elseif($data['user-group'] == 'individual') {
                    $user_id = $data['user'];
                    $users = $this->adminModel->getUser($user_id);
                    if($data['app'] == 78) {
                        $message = "Please click or copy below link to download Street President app. \n\n".base_url('download/app/sheetpresident');
                    }elseif($data['app'] == 79) {
                        $message = "Please click or copy below link to download Booth President app. \n\n".base_url('download/app/boothpresident');
                    }elseif($data['app'] == 81) {
                        $message = "Please click or copy below link to download Booth Observer app. \n\n".base_url('download/app/boothobserver');
                    }
                }
                if($users) {
                    $numItems = count($users);
                    $i = 0;
                    foreach($users as $user) {
                        if(++$i === $numItems) {
                            $mobile .= $user->mobile;
                        }else {
                            $mobile .= $user->mobile. ',';
                        }    
                    }
                    $sms = $this->communication->sendsms($message, $mobile, 'return_type', '3');
                }

            }
            if($sendlink) {
                $this->session->set_flashdata('appdownload', '<div class="alert alert-success fade in"><strong>Success!</strong> Download link successfully sent to selected users.</div>');
            }else {
                $this->session->set_flashdata('appdownload', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('admin/appdownload/').$data['app']);
        }
    }

    public function recruitmentreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $data['user_roles'] = $this->adminModel->getAssignRole();
        
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/recruitment-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/recruitment-report-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getusersbyps($id, $mandal) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        
        $user_m = $this->adminModel->getManagerByMandal($mandal);
        $users = $this->adminModel->getAllUsersByPS($id);
        
        $data = array();
        
        if($user_m) {
            foreach($user_m->result() as $u) {
                $start++;
                $data[] = array(
                    $start,
                    $u->first_name . ' ' . $u->last_name,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    ($u->email == '') ? ' - ' : $u->email,
                    $u->mobile,
                    // $u->ps_no . ' - ' . $u->ps_name,
                    // $u->location,
                    $u->user_role,
                );
            }
        }
        
        if($users) {
            foreach($users->result() as $u) {
                $start++;
                $data[] = array(
                    $start,
                    $u->first_name . ' ' . $u->last_name,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    ($u->email == '') ? ' - ' : $u->email,
                    $u->mobile,
                    // $u->ps_no . ' - ' . $u->ps_name,
                    // $u->location,
                    $u->user_role,
                );
            }
        }
        
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $users->num_rows() + $user_m->num_rows(),
            "recordsFiltered" => $users->num_rows() + $user_m->num_rows(),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }

    public function getrecruitmentdetails() {
        if($this->input->post()) {
            $data = $this->input->post();
            $mandal = $data['mandal'];
            
            if($this->input->post('ps')) {
                $ps = $data['ps'];
            }else {
                $ps = null;
            }
            if($this->input->post('user-role')) {
                $role = $data['user-role'];
                $u_role = $this->adminModel->getRoleById($role)->value;
            }else {
                $role = null;
            }
            
            $result = array();
            
            $m_loc = $this->adminModel->getLocationById($mandal);
            $user_m = $this->adminModel->getManagerByMandal($mandal)->num_rows();
            $n_booth = $this->adminModel->getPSCountByMandal($mandal);
            if($ps != null) {
                $ps_loc = $this->adminModel->getPSById($ps);
                $r_users = $this->adminModel->getAllUsersByPS($ps)->num_rows();
                $user_bo = $this->adminModel->getAllUsersByPs($ps, array('u.user_role' => 55))->num_rows();
                $user_bp = $this->adminModel->getAllUsersByPs($ps, array('u.user_role' => 18))->num_rows();
                $user_sp = $this->adminModel->getAllUsersByPs($ps, array('u.user_role' => 3))->num_rows();
                $result = (object) array_merge((array)$m_loc, (array)$ps_loc);
                $result->no = $r_users + $user_m;
                $result->bo = $user_bo;
                $result->bp = $user_bp;
                $result->sp = $user_sp;    
            }elseif($ps == null) {
                if($role != null) {
                    $r_users = $this->adminModel->getAllUsersByMandal($mandal, $role)->num_rows();
                    $d_users = $this->adminModel->getAllUsersByMandal($mandal, $role)->result();
                    $app_download = $this->adminModel->getAppDownloadCount($mandal, $role);
                    $ps_exists = array();
                    $ps = array();
                    if($d_users) {
                        $assigned = 0;
                        $unassigned = 0;
                        foreach($d_users as $k => $u) {
                            if($u->first_name != '') {
                                $ps_exists[] = $u->ps_no;
                            }
                            if($u->first_name == '' && in_array($u->ps_no, $ps_exists)) {
                                unset($d_users[$k]);    
                            }    
                        }
                        
                        foreach($d_users as $u) {
                            if($u->first_name == '') {
                                $unassigned += 1;
                            }elseif($u->first_name != '') {
                                $assigned += 1;
                            }
                        }
                    }
                
                    $result = (object) $m_loc;
                    $result->no = $assigned + $unassigned;
                    $result->role = $u_role;
                    $result->tbooth = $n_booth;
                    $result->assigned = $assigned;
                    $result->unassigned = $unassigned;
                    $result->downloads = $app_download;
                }
            }
            echo json_encode($result);    
        }
    }

    public function getlocationName() {
        if($this->input->post()) {
            $data = $this->input->post();
            $loc = $data['location'];
            $result = $this->adminModel->getLocationById($loc);
            echo json_encode($result); 
        }
    }

    public function registrationreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/registration-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/registration-report-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getvotersbyps($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $voters = $this->adminModel->getAllVotersByPS($id);

        if($voters) {
            foreach($voters->result() as $v) {
                $start++;
                $data[] = array(
                    $start,
                    $v->firstname . ' ' . $v->lastname,
                    $v->voter_id,
                    ($v->vmobile == '') ? ' - ' : $v->vmobile,
                    ($v->first_name == '') ? ' - ' : $v->first_name . ' ' . $v->last_name,
                    ($v->umobile == '') ? ' - ' : $v->umobile,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    // $u->ps_no . ' - ' . $u->ps_name,
                    // $u->location,
                    ($v->r_date == '') ? ' - ' : date('F j, Y', strtotime($v->r_date))
                );
            }
        }
        
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $voters->num_rows(),
            "recordsFiltered" => $voters->num_rows(),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }

    public function getregistrationdetails() {
        if($this->input->post()) {
            $data = $this->input->post();
            $mandal = $data['mandal'];
            $ps = $data['ps'];
            $result = array();
            
            $m_loc = $this->adminModel->getLocationById($mandal);
            $ps_loc = $this->adminModel->getPSById($ps);
            $r_users = $this->adminModel->getAllVotersByPS($ps)->num_rows();
            
            $v_reg = $this->adminModel->getAllVotersByPS($ps, array('v.user_id !=' => null))->num_rows();
            $v_avl = $this->adminModel->getAllVotersByPS($ps, array('v.user_id' => null))->num_rows();
            
            
            $result = (object) array_merge((array)$m_loc, (array)$ps_loc);
            $result->no = $r_users;
            $result->reg = $v_reg;
            $result->avl = $v_avl;
            
            echo json_encode($result);    
        }
    }

    //group sms
    public function groupsms() {
		$data['user_roles'] = $this->adminModel->getAssignRole();
        if($this->input->post()) {
            $data = $this->input->post();
            
            $users = $this->adminModel->getUserByRole($data['user-role']);
            
            if($users) {
                $numItems = count($users);
                $i = 0;
                foreach($users as $user) {
                    if(++$i === $numItems) {
                        $mobile .= $user->mobile;
                    }else {
                        $mobile .= $user->mobile. ',';
                    }    
                }
                $sms = $this->communication->languagesms($data['message'], $mobile, 'return_type');
                // $sms = true;
                $save_sms = $this->adminModel->groupsms($users, $data['message']);
                if($sms && $save_sms) {
                    $this->session->set_flashdata('send_sms', '<div class="alert alert-success fade in"><strong>Success!</strong> Sms Sent successfully.</div>');
                }else {
                    $this->session->set_flashdata('send_sms', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
                redirect(base_url('admin/groupsms'));
            }
            
            
            
        }
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
								
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/group-sms.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/groupsms-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getusersbymandalrole($mandal, $role = false) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        
        $user_m = $this->adminModel->getManagerByMandal($mandal);
        $users = $this->adminModel->getAllUsersByMandal($mandal, $role);
        
        
        $data = array();
        
        if($user_m) {
            foreach($user_m->result() as $u) {
                $start++;
                $data[] = array(
                    $start,
                    '-',
                    '-',
                    $u->first_name . ' ' . $u->last_name,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    // ($u->email == '') ? ' - ' : $u->email,
                    $u->mobile,
                    '-',
                    $u->user_role,
                    '-'
                    
                    // $u->ps_no . ' - ' . $u->ps_name,
                    // $u->location,
                    
                );
            }
        }
        
        if($users) {
            $ps_exists = array();
            $u_data = $users->result();
            
            foreach($u_data as $k => $u) {
                if($u->first_name != '') {
                    $ps_exists[] = $u->ps_no;
                }
                if($u->first_name == '' && in_array($u->ps_no, $ps_exists)) {
                    unset($u_data[$k]);    
                }
                if($u->id) {
                    $app_d = $this->adminModel->getAppDownloadStatus($u->id, $role);
                    if($app_d) {
                        if($app_d->status == 1) {
                            $u->download = '<span class="label label-success">Complete</span>';
                        }elseif($app_d->status == 0) {
                            $u->download = '<span class="label label-danger">Pending</span>';
                        }
                    }else {
                        $u->download = '<span class="label label-default">Not Sent</span>';
                    }
                }else {
                    $u->download = '';
                }
                
            }

            foreach($u_data as $u) {
                $start++;
                $data[] = array(
                    $start,
                    $u->ps_no,
                    $u->ps_name,
                    $u->first_name . ' ' . $u->last_name,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    // ($u->email == '') ? ' - ' : $u->email,
                    $u->mobile,
                    $u->location,
                    $u->user_role,
                    $u->download
                    
                    
                );
            }
            // echo '<pre>'; print_r($ps_exists); print_r($u_data); exit;
        }
        
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $users->num_rows() + $user_m->num_rows(),
            "recordsFiltered" => $users->num_rows() + $user_m->num_rows(),
            "data" => $data
        );
		
        echo json_encode($output);
        exit(); 
    }
	
	public function otpreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        //$data['otp'] = $this->adminModel->getAllOtp();

        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/otp-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/otp-list-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getOtp() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->adminModel->getAllOtp();
		
        $data = array();
        if($users) {
            foreach($users as $r) {
                $i = 1;
                $start++;
                $data[] = array(
                    $start,
                    $r->name,
                    $r->mobile,
                    $r->role,
                    $r->otp,
                    date('F j, Y g:i a', strtotime($r->created_at)),
                );
            }
        }
        
        $output = array(
            "draw" => $draw,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }
	
	public function downloads() {
		
        $data['header_css'] = array('clientadmin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');

		$data['total_boothpresident'] = $this->adminModel->getAllDownload(79)->num_rows();
		$data['total_boothobserver'] = $this->adminModel->getAllDownload(81)->num_rows();
		$data['total_sheetpresident'] = $this->adminModel->getAllDownload(78)->num_rows();
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/downloads.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/admin/download-count-script.php', $data);
        $this->load->view('includes/footer.php');
    }

    public function downloadreport($appid) {
        $data['header_css'] = array('buttons.dataTables.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        //$data['downloads'] = $this->adminModel->getDownloadDetails($appid);
		//echo '<pre>';print_r($data);exit;
		$data['app_id'] = $appid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/download-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/download-list-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getDownloads($appid) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->adminModel->getDownloadDetails($appid);
		
       $data = array();
        if($users) {
            foreach($users as $r) {
                $i = 1;
                $start++;
                $data[] = array(
                    $start,
                    $r->name,
                    $r->mobile,
                    $r->role,
                    ($r->status == 1) ? 'Downloaded' : 'Pending',
                    date('F j, Y g:i a', strtotime($r->download_at)) ,
                );
            }
        }
        
        $output = array(
            "draw" => $draw,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }

    public function performancebp() {
        $data['header_css'] = array('buttons.dataTables.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/performance-bp.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/performance-bp-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getbpperformance($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getBPPerformanceByMandal($id);

        if($users) {
            foreach($users->result() as $u) {
                $start++;
                $data[] = array(
                    $start,
                    $u->ps_no,
                    $u->first_name . ' ' . $u->last_name,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    // ($u->email == '') ? ' - ' : $u->email,
                    $u->mobile,
                    $u->village,
                    $u->user_role,
                    $u->downloadstatus,
                    $u->spcount,
                    $u->voterscount,
                    
                );
            }
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

    public function performancesp() {
        $data['header_css'] = array('buttons.dataTables.min.css','clientadmin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/performance-sp.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/performance-sp-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getspperformance($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->adminModel->getSPPerformanceByMandal($id);

        if($users) {
            foreach($users->result() as $u) {
                $start++;
                $data[] = array(
                    $start,
                    $u->ps_no,
                    $u->first_name . ' ' . $u->last_name,
                    // ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                    // ($u->email == '') ? ' - ' : $u->email,
                    $u->mobile,
                    $u->village,
                    $u->user_role,
                    $u->downloadstatus,
                    $u->voterscount,
                    $u->positivecount,
                    $u->neutralcount,
                    $u->negativecount
                );
            }
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

    public function sharecontact() {
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $data['user_roles'] = $this->adminModel->getAssignRole();
        if($this->input->post()) {
            if($data['rolefrom'] == $data['roleto']) {
                
            }
        }
        $data['header_css'] = array('clientadmin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
								
        $this->load->view('includes/header.php', $data);
        $this->load->view('admin/top-nav.php');
        $this->load->view('admin/side-nav.php');
        $this->load->view('admin/share-contact.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/admin/sharecontact-script.php');
        $this->load->view('includes/footer.php');
    }

    public function getbpperformanceDetails($mandal) {
        $users = $this->adminModel->getBPPerformanceByMandal($mandal);
        $weak = 0;
        $average = 0;
        $strong = 0;
        foreach($users->result() as $u) {
            if($u->spcount > 0 && $u->spcount <= 1) {
                $weak += 1;
            }
            if($u->spcount >= 2 && $u->spcount <= 8) {
                $average += 1;
            }
            if($u->spcount >= 9) {
                $strong += 1;
            }
        }
        
        $spcount = array(
            'weak' => $weak,
            'average' => $average,
            'strong' => $strong
        );
        
        echo json_encode($spcount); 
    }

    public function getQuestionsByUserRole($role) {
        $result = $this->adminModel->getQuestion($role);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
}