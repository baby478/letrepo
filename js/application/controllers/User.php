<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    private $_id;
    private $_usession;

    public function __construct() {
        parent::__construct();
		if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }
        $this->load->model('userModel');
        $this->_usession = $this->session->userdata('user');
        $this->_id = $this->session->userdata('user')->id;
    }
    

    public function addUser() {
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
               $insert = $this->userModel->registerUser($data);
			   
                if($insert) {
                    $this->session->set_flashdata('add_user', '<div class="alert alert-success fade in"><strong>Success!</strong> User registered successfully.</div>');
                }else {
                    $this->session->set_flashdata('add_user', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
                redirect(base_url('user/adduser'));
            }
            
        }
		$userrole = $this->_usession->user_role;
		if($userrole == 137) {
			$data['header_css'] = array('admin.css');
			$data['plugins'] = array('js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
			$this->load->view('includes/header.php', $data);
			$this->load->view('manager/top-nav.php');
			$this->load->view('manager/side-nav.php');
			
			$this->load->view('common/add-user.php');
			
			
			$this->load->view('includes/page-footer.php');
			$this->load->view('manager/shortcut-nav.php');
			$this->load->view('includes/plugins.php', $data);
			$this->load->view('common/adduser-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
			$this->load->view('includes/footer.php');
		}elseif($userrole == 44) {
			$data['header_css'] = array('admin.css');
			$data['plugins'] = array('js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
			$this->load->view('includes/header.php', $data);
			$this->load->view('seniormanager/top-nav');
			$this->load->view('seniormanager/side-nav.php');
			
			$this->load->view('common/add-user.php');
			
			
			$this->load->view('includes/page-footer.php');
			$this->load->view('seniormanager/shortcut-nav.php');
			$this->load->view('includes/plugins.php', $data);
			$this->load->view('common/adduser-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
			$this->load->view('includes/footer.php');
		}elseif($userrole == 1) {
            $data['header_css'] = array('clientadmin.css');
            $data['plugins'] = array('js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
            $this->load->view('includes/header.php', $data);
            $this->load->view('administrator/top-nav.php');
            $this->load->view('administrator/side-nav.php');
            $this->load->view('common/add-user.php');
            $this->load->view('includes/page-footer.php');
            //$this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/adduser-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
			$this->load->view('includes/footer.php');
        }
    }

    public function _email_exists($email) {
        if(!empty($email)) {
            $exists = $this->userModel->emailExists($email);
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
            $this->form_validation->set_message('_phone_exists', 'The {field} is already exists');
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

    public function suspend() {
        if($this->input->post()) {
            $user_id = $this->input->post('user_id');
            $suspend_user = $this->userModel->suspendUser($user_id);
            if($suspend_user) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    }

    /* EDIT USER */
	public function edit($id) {
		$data['userdata'] = $this->userModel->getUserData($id);
		
		 if($this->input->post()) {
			 //if($this->form_validation->run() === TRUE) {
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
			  $update = $this->userModel->updateData($data,$id);
			  if($update) 
				{ 
					$this->session->set_flashdata('edit_user', '<div class="alert alert-success fade in"><strong>Success!</strong> User Updated successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('edit_user', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('user/edit/'.$id));
			// }
		 }
		 $userrole=$this->session->userdata('user')->user_role;
		if($userrole==2) {
			$data['header_css'] = array('admin.css','dashboard.css');
			$this->load->view('includes/header.php', $data);
			$this->load->view('manager/top-nav.php');
			$this->load->view('manager/side-nav.php');
			$this->load->view('common/edit.php', $data);
			$this->load->view('includes/page-footer.php');
			$this->load->view('manager/shortcut-nav.php');
			$this->load->view('includes/plugins.php', $data);
			$this->load->view('common/widget-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
			$this->load->view('includes/footer.php');
		}
		else {
			$data['header_css'] = array('admin.css','dashboard.css');
			$this->load->view('includes/header.php', $data);
			$this->load->view('seniormanager/top-nav.php');
			$this->load->view('seniormanager/side-nav.php');
			$this->load->view('common/edit.php', $data);
			$this->load->view('includes/page-footer.php');
			$this->load->view('seniormanager/shortcut-nav.php');
			$this->load->view('includes/plugins.php', $data);
			$this->load->view('common/widget-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
			$this->load->view('includes/footer.php');
		}
		
    }
}