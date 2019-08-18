<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SeniorManager extends CI_Controller {
    
    private $allocation_status;
    private $_id;
    private $_usession;

    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 44) {
            redirect(base_url());
        }
		// load pagination library
        $this->load->library('pagination');
        $this->load->model('SeniorManagerModel');
		$this->load->model('managerModel');
        $this->load->model('apiModel');
        $this->load->model('SMDashboardModel');
        $this->load->model('adminModel');
        $this->_usession = $this->session->userdata('user');
        $this->_id = $this->session->userdata('user')->id;
       // $this->_alloc_status();
    }

    public function index() {
		$Lid = $this->_usession->location_id;
		$data['parent_id'] = $this->SeniorManagerModel->getChildId($Lid);
        $data['header_css'] = array('admin.css','dashboard.css');
		$this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard.php');
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
        
    }
	
	
    // public function addUser() {
    //     if($this->input->post()) {
    //         if($this->form_validation->run() === TRUE) {
				
    //             $qualification = array();
    //             //$caste_head = array();
                
    //             foreach($this->input->post('qualification') as $k => $qv) {
    //                 $qualification[$k]['q'] = $qv;
    //             }
                
    //             foreach($this->input->post('course') as $k => $v) {
    //                 if(!empty($v)) {
    //                     $qualification[$k]['c'] = $v;
    //                 }
                    
    //             }
    //             foreach($this->input->post('college') as $k => $v) {
    //                 if(!empty($v)) {
    //                     $qualification[$k]['clg'] = $v;
    //                 }
    //             }
               

    //             foreach($qualification as $k =>$v) {
    //                 if(!isset($qualification[$k]['c'])) {
    //                     $qualification[$k]['c'] = null;
    //                 }
    //                 if(!isset($qualification[$k]['clg'])) {
    //                     $qualification[$k]['clg'] = null;
    //                 }
    //             }
                
    //             $data = $this->input->post();
    //             $data['phone'] = str_replace(array( '(', ')', '-', ' ' ), '', $data['phone']);
    //             $data['dob'] = date('Y-m-d', strtotime($data['dob']));
    //             $data['qualification'] = $qualification;
    //             //$data['caste_head'] = $caste_head;

    //             if($_FILES['photo']['name'] !== '') {
    //                 //upload photo
    //                 $config['upload_path']   = $this->config->item('assets_users');
    //                 $config['allowed_types'] = 'jpeg|jpg|png';
    //                 $config['max_size']  = 1024;
    //                 $config['file_name'] = time().$data['phone'];
    //                 $this->load->library('upload', $config);

    //                 if($this->upload->do_upload('photo')){
    //                     $uploadData = $this->upload->data();
    //                     $uploadedFile = $uploadData['file_name'];
    //                     $data['photo'] = $uploadedFile;
    //                 }else {
                        
    //                     $this->session->set_flashdata('upload_error', '<div class="alert alert-danger fade in"><strong>Error!</strong> File not uploaded .</div>');
    //                 }
    //             }else {
    //                 $data['photo'] = null;
    //             }
    //             //  $insert = true;
    //            $insert = $this->userModel->registerUser($data);
    //             if($insert) {
    //                 //Isaac Code
    //                 // $config = Array(        
    //                 //     'protocol' => 'sendmail',
    //                 //     'smtp_host' => 'smtp.gmail.com',
    //                 //     'smtp_port' => 587,
    //                 //     'smtp_user' => 'smartadmin@citizeninfo.in',
    //                 //     'smtp_pass' => 'smartadmin@123',
    //                 //     'mailtype'  => 'html', 
    //                 //     'charset'   => 'iso-8859-1'
    //                 // );
    //                 // $this->load->library('email', $config);
    //                 // $this->email->set_newline("\r\n");
    //                 // $this->email->from('smartadmin@citizeninfo.in', 'Citizeninfo');
    //                 // $data = array('userName'=>$data['firstname']);
    //                 // $this->email->to($userEmail);  
    //                 // $this->email->subject("Registation Confirmation");
    //                 // $body = $this->load->view('email/email-registration.php',$data,TRUE);
    //                 // $this->email->message($body);   
    //                 // $this->email->send();
    //                 // $emailConfig = [
    //                 //     'protocol' => 'smtp', 
    //                 //     'smtp_host' => 'smtp.googlemail.com', 
    //                 //     'smtp_port' => 465, 
    //                 //     'smtp_user' => 'smartadmin@citizeninfo.in', 
    //                 //     'smtp_pass' => 'password', 
    //                 //    'charset' => 'utf-8',
    //                 //    'mailtype' => 'html',
    //                 //    'smtp_crypto' => 'ssl',
    //                 // ];
    //                 // // Load CodeIgniter Email library
    //                 // $this->load->library('email', $emailConfig);
    //                 // $this->email->set_header('MIME-Version', '1.0; charset=utf-8');
    //                 // $this->email->set_header('Content-type', 'text/html');
    //                 // $from = [
    //                 //     'email' => 'syedaneesahmedhashmi@gmail.com',
    //                 //     'name' => 'smartadmin'
    //                 // ];
    //                 // $data = array('userName'=>$data['firstname']); 
    //                 // $usermail = $this->input->post('email');   
    //                 // $to = array( $usermail);
    //                 // $subject = 'CitizenInfo Registration Successful';
    //                 // //  $message = 'Type your gmail message here';
    //                 // $message =  $this->load->view('email/email-registration', $data,true);
    //                 // // Sometimes you have to set the new line character for better result
    //                 // $this->email->set_newline("\r\n");
    //                 // // Set email preferences
    //                 // $this->email->from($from['email'], $from['name']);
    //                 // $this->email->to($to);
    //                 // $this->email->subject($subject);
    //                 // $this->email->message($message);
    //                 // $this->email->send();
    //                 // exit;
    //                 //Isaac Code ends
    //                 $this->session->set_flashdata('add_user', '<div class="alert alert-success fade in"><strong>Success!</strong> User registered successfully.</div>');
    //             }else {
    //                 $this->session->set_flashdata('add_user', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
    //             }
                
    //         }
            
    //     }
    //     $data['header_css'] = array('admin.css');
    //     $data['plugins'] = array('js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
    //     $this->load->view('includes/header.php', $data);
    //     $this->load->view('seniormanager/top-nav');
    //     $this->load->view('seniormanager/side-nav.php');
    //     //check permission
    //     if($this->allocation_status === FALSE) {
    //         $this->load->view('common/no-access.php');
    //      }else {
    //         $this->load->view('common/add-user.php');
    //      }
        
    //     $this->load->view('includes/page-footer.php');
    //     $this->load->view('seniormanager/shortcut-nav.php');
    //     $this->load->view('includes/plugins.php', $data);
    //     $this->load->view('common/adduser-script.php');
    //     $this->load->view('includes/footer.php');
    // }
	
	public function assignRole() {
        if($this->input->post()) {
            if($this->form_validation->run() === TRUE) { 
                $user_id = $this->input->post('user');
                $role_id = $this->input->post('user-role');
                $location = $this->input->post('location');
				$uid = $this->session->userdata('user')->id;
				$exist = $this->SeniorManagerModel->managerMandalExist($location,$uid,137);
				
				if($exist)
					{
						$this->session->set_flashdata('user_role', '<div class="alert alert-danger fade in"><strong>Error!</strong> Mandal Already Assign To Division Head.</div>');
					}
				else
					{	
						$update_role = $this->SeniorManagerModel->assignUserRole($user_id, $role_id, $location);
						if($update_role) {
						$this->session->set_flashdata('user_role', '<div class="alert alert-success fade in"><strong>Success!</strong> User role assigned successfully.</div>');
						}else {
						$this->session->set_flashdata('user_role', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
					}
				}
                
            }
        }
        $data['user_roles'] = $this->SeniorManagerModel->getAssignRole();
        $data['users'] = $this->managerModel->getUserByRole(17);
        $id = $this->session->userdata('user')->id;
		$locationid = $this->session->userdata('user')->location_id;
        $data['manager_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
        
        $data['header_css'] = array('jquery-confirm.min.css','admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
           $this->load->view('common/no-access.php');
        }else {
            $this->load->view('seniormanager/users/assign-role.php', $data);
        }
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/assign-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function assignmandal(){
		$data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
		$data['managers'] = $this->SeniorManagerModel->getUserByRole(2);
		$data['mobileteam'] = $this->SeniorManagerModel->getUserByRole(55);
		$id = $this->session->userdata('user')->id;
		$locationid = $this->session->userdata('user')->location_id;
		$data['manager_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
        //check permission
        if($this->allocation_status === FALSE) {
           $this->load->view('common/no-access.php');
        }else {
            $this->load->view('seniormanager/users/assign-mandal.php', $data);
        }
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/assign-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
	}
	
	public function assignMandalToManager() {
        if($this->input->post()) {
			$mid = $this->input->post('user-mandal');
			$uid = $this->session->userdata('user')->id;
			$exist = $this->SeniorManagerModel->managerMandalExist($mid,$uid,2);
			if($exist)
			{
				$this->session->set_flashdata('mandal-assign-manager', '<div class="alert alert-danger fade in"><strong>Error!</strong> Mandal Already Assign To Manager.</div>');
				redirect(base_url('SeniorManager/assignmandal'));
			}
			else
			{
				$insert = $this->SeniorManagerModel->assignManagerMandal();
				if($insert) {
					$this->session->set_flashdata('mandal-assign-manager', '<div class="alert alert-success fade in"><strong>Success!</strong> Mandal Assign To Manager.</div>');
				}else {
					$this->session->set_flashdata('mandal-assign-manager', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
				}
				redirect(base_url('SeniorManager/assignmandal'));
			}
        }
    }

    public function assignMandalToMobileTeam() {
        if($this->input->post()) {
			$mid = $this->input->post('user-mandal');
			$uid = $this->session->userdata('user')->id;
			$exist = $this->SeniorManagerModel->managerMandalExist($mid,$uid,55);
			if($exist)
			{
				$this->session->set_flashdata('mandal-assign-manager', '<div class="alert alert-danger fade in"><strong>Error!</strong> Mandal Already Assign To Mobile Team.</div>');
				redirect(base_url('SeniorManager/assignmandal'));
			}
			else
			{
            $insert = $this->SeniorManagerModel->assignManagerMandal();
            if($insert) {
                $this->session->set_flashdata('mandal-assign-mobileteam', '<div class="alert alert-success fade in"><strong>Success!</strong> Mandal Assign To Mobile Team.</div>');
            }else {
                $this->session->set_flashdata('mandal-assign-mobileteam', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('SeniorManager/assignmandal'));
           }
       }
	}
	
	public function _email_exists($email) {
        $exists = $this->SeniorManagerModel->emailExists($email);
        if($exists) {
            $this->form_validation->set_message('_email_exists', 'The {field} is already exists');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function _phone_exists($phone) {
        $phone = str_replace(array( '(', ')', '-', ' ' ), '', $phone);
        $exists = $this->SeniorManagerModel->phoneExists($phone);
        if($exists) {
            $this->form_validation->set_message('_phone_exists', 'The {field} is already exists');
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
	
	public function userprofile() {
        $id = $this->session->userdata('user')->id;
        $data['profile'] = $this->managerModel->userProfile($id);
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/user-profile.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        //$this->load->view('manager/calendar-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function inbox() {
        $data['plugins'] = array('js/plugin/delete-table-row/delete-table-row.min.js', 
                                'js/plugin/summernote/summernote.min.js', 'js/plugin/select2/select2.min.js');
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/inbox/inbox.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/inbox-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

    public function calendar() {
		$data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
		$id = $this->session->userdata('user')->id;
		//$data['mytasks'] = $this->SeniorManagerModel->getMyTasks($id);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/calendar/calendar.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/calendar-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

    public function myteam() {
        // $data['plugins'] = array('');
		$data['header_css'] = array('admin.css', 'myteam.css');
        
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
         }else {
            $id = $this->_id;
            $data['dh'] = $this->SeniorManagerModel->getDivisionHead($id);
            // echo '<pre>'; print_r($data['dh']); exit;
		
		    if($data['dh']) { 
                foreach($data['dh'] as $k => $vot) {
                    $vot->voter = $this->SeniorManagerModel->getVotersCountByDH($vot->id);
                    $vot->positive_voters = $this->SeniorManagerModel->getVotersCountByDH($vot->id, array('v.voter_status' => 12));
                    $vot->neutral_voters = $this->SeniorManagerModel->getVotersCountByDH($vot->id, array('v.voter_status' => 14));
                }
            }  
            $this->load->view('seniormanager/myteam/myteam.php', $data);
         }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        //$this->load->view('manager/calendar-script.php');
        $this->load->view('includes/footer.php');  
    }

    public function managerdetails($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
			$data['cr_data'] = $this->SeniorManagerModel->teamProfile($id);
			if(is_array($data['cr_data'])) {
                foreach($data['cr_data'] as $k => $vot)  {
                    $vot->voter = $this->SeniorManagerModel->votersByTeamLeader($vot->id);
                    $vot->positive_voters = $this->SeniorManagerModel->votersByStatusTL($vot->id, 12);
                    $vot->neutral_voters = $this->SeniorManagerModel->votersByStatusTL($vot->id, 14);
                    $vot->ps_no = $this->SeniorManagerModel->userprofile($vot->id)->ps_no;
                    $vot->ps_name = $this->SeniorManagerModel->userprofile($vot->id)->ps_name;
                }
            }
			
			
            $data['voters'] = $this->SeniorManagerModel->getTotalVotersByManager($id);
            $data['positive_voters'] = $this->SeniorManagerModel->getPositiveNegVotersByManager($id, 12);
			
			//pagination starts
			//$config['base_url'] = base_url() . "seniormanager/managerdetails/".($id);
			//$config['total_rows'] = $this->SeniorManagerModel->countTeamLeader($id);
			//$config['per_page'] = 4;
			//$data['uri_segment']=4;
			//$limit_per_page = 8;
			//$start_index = ($this->uri->segment(4));
			//$data['cr_data'] = $this->SeniorManagerModel->teamLeaderProfile($id,$limit_per_page, $start_index);
			//$this->pagination->initialize($config);
			//$data['page_links']=$this->pagination->create_links();
			
			//pagination ends
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/manager-details.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/tl-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
          
    }
	
	public function coordinator($id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
			$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
            'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
													
            $data['profile'] = $this->SeniorManagerModel->userProfile($id);
            $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            $data['voters'] = $this->SeniorManagerModel->votersByUser($id);
            $data['positive_voters'] = $this->SeniorManagerModel->votersByStatusCr($id, 12);
            $data['cr_data'] = $this->SeniorManagerModel->getVolunteerByCoordinator($id);
			$data['mymembers'] = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($id);
			
			if($data['cr_data']!="") {
                foreach($data['cr_data'] as $k => $vot) {
                    $vot->voter = $this->SeniorManagerModel->getVolunteerTotalVote($vot->id)->num_rows();
                    $vot->positive_voters = $this->SeniorManagerModel->getVolunteerTotalVote($vot->id,array('v.voter_status' => 12))->num_rows();
                    $vot->neutral_voters = $this->SeniorManagerModel->getVolunteerTotalVote($vot->id, array('v.voter_status' => 14))->num_rows();
                }
			}
			$data['family']=$this->SeniorManagerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->SeniorManagerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->SeniorManagerModel->getCitizenByrelation($id, 49);
			$data['known']=$this->SeniorManagerModel->getCitizenByrelation($id, 50);
            
            $data['vid'] = $id;
			
			
			
			//echo "<pre>";print_r($data);exit;
			//echo $this->db->last_query();exit;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/coordinator-profile.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			$this->load->view('scripts/seniormanager/coordinator-script.php',$data);
            $this->load->view('scripts/seniormanager/cf-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }
	
	public function tlcoordinator($id) {
		//$id = $this->session->userdata('user')->id;
		$data['header_css'] = array('admin.css', 'myteam.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');	
        $data['profile'] = $this->SeniorManagerModel->userProfile($id);
		$data['qualification'] = $this->SeniorManagerModel->userQualification($id);
		$data['voters'] = $this->SeniorManagerModel->votersByTeamLeader($id);
        $data['positive_voters'] = $this->SeniorManagerModel->votersByStatusTL($id, 12);
        $data['tl_coordinator'] = $this->SeniorManagerModel->getCoordinatorsByTeamleader($id);
		
		if($data['tl_coordinator']!="") {
            foreach($data['tl_coordinator'] as $k => $vot) {
                $vot->voter = $this->SeniorManagerModel->getTotalVote($vot->id);
                $vot->positive_voters = $this->SeniorManagerModel->votersByStatusCr($vot->id, 12);
                $vot->neutral_voters = $this->SeniorManagerModel->votersByStatusCr($vot->id, 14);
            }	
		}
		
        $data['header_css'] = array('admin.css', 'myteam.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
         }else {
            $this->load->view('seniormanager/myteam/teamlead-coordinator.php', $data);
         }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/seniormanager/tl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');  
			
	}
	
	public function teammembers($role,$id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js ,js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
			
			$data['family']=$this->SeniorManagerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->SeniorManagerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->SeniorManagerModel->getCitizenByrelation($id, 49);
			$data['known']=$this->SeniorManagerModel->getCitizenByrelation($id, 50);
			
			$voter = $this->SeniorManagerModel->getMembersByVolunteer($id);
			//echo $this->db->last_query();exit;
			
			if($role == 46) {
                $data['profile'] = $this->SeniorManagerModel->getVolunteerProfile($id);
			}
			if($role == 3) {
                $data['profile'] = $this->SeniorManagerModel->userProfile($id);
            }
			// $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
			$data['voters'] = $this->SeniorManagerModel->votersByUser($id);
            $data['positive_voters'] = $this->SeniorManagerModel->votersByStatusCr($id, 12);
            $data['cr_data'] = $this->SeniorManagerModel->getMembersByVolunteer($id);
			$data['vid']=$id;

            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/team-members.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/member-script.php',$data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

	//datatable
    public function getmembers($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
       // $id = $this->session->userdata('user')->id;
        $voter = $this->SeniorManagerModel->getMembersByVolunteer($id);
       
        $data = array();
		
        foreach($voter->result() as $r) {
            if($r->photo == '') {
                if($r->gender == 4) {
                    $img = base_url($this->config->item('assets_male'));
                }elseif($r->gender == 5) {
                    $img = base_url($this->config->item('assets_female'));
                }
            }else {
                $img = base_url($this->config->item('assets_voters')).$r->photo;
            }
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
				'<img src="'. $img .'" height="50" width="50" align="center">',
				$r->age,
                ($r->gender == 4) ? 'Male' : 'Female',
				$r->relationship,
                $r->voter_id,
                $r->voter_status,
				date('F j, Y g:i a', strtotime($r->created_at)),
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $voter->num_rows(),
            "recordsFiltered" => $voter->num_rows(),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }
	
	//datatable
    public function getmygroupmembers($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $voter = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($id);
       
        $data = array();
		
        foreach($voter->result() as $r) {
            if($r->photo == '') {
                if($r->gender == 4) {
                    $img = base_url($this->config->item('assets_male'));
                }elseif($r->gender == 5) {
                    $img = base_url($this->config->item('assets_female'));
                }
            }else {
                $img = base_url($this->config->item('assets_voters')).$r->photo;
            }

            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
				'<img src="'. $img .'" height="50" width="50" align="center">',
				$r->attend,
				($r->attend_time != '') ? date('F j, Y, g:i a', strtotime($r->attend_time)) : '',
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $voter->num_rows(),
            "recordsFiltered" => $voter->num_rows(),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }
	
	public function villageAnalytics() {
        if($this->input->post()) {
            if($this->form_validation->run() === TRUE) {
                $location_id = $this->input->post('village');
                $v_analytics = $this->managerModel->getVillageAnalytics($location_id);
                $c_analytics = $this->managerModel->getCasteByVillage($location_id);
                if(!$v_analytics && !$c_analytics) {
                    $this->session->set_flashdata('error', '<div class="alert alert-success">Sorry! We could not find a relevant data.</div>');
                }elseif($v_analytics) {
                    $data['v_analytics'] = $v_analytics;
                }
                if($c_analytics) {
                    $data['c_analytics'] = $c_analytics;
                }
            }
        }
        $id = $this->session->userdata('user')->id;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'live-booth.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $data['location'] = $this->managerModel->getAllocatedLocation($id);
            $location_id = $data['location'][0]->lc_id;
            $data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
            $this->load->view('seniormanager/village-analytics.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/v-analytics-script.php', $data);
        $this->load->view('includes/footer.php');
    }
	
	public function voters() {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css', 'myteam.css');
        $lid = $this->session->userdata('user')->location_id;
        $data['parent_id'] = $this->SeniorManagerModel->getChildId($lid);
        // echo '<pre>'; print_r($data['parent_id']); exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $this->load->view('seniormanager/voterlist/voters.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/voter-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	//datatable for voters
    public function getvoters() {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $id = $this->session->userdata('user')->id;
        $voter = $this->SeniorManagerModel->getVoterList($id);
        
        $data = array();
		
        foreach($voter as $r) {
            $start++;
            $i = 1;
            $data[] = array(
                $start,
                $r->firstname . ' ' . $r->lastname,
                ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                ($r->gender == 4) ? 'Male' : 'Female',
                $r->voter_id,
                $r->mandal,
                $r->village,
                $r->voter_status,
            );
            
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->SeniorManagerModel->voters_count_all($id),
            "recordsFiltered" => $this->SeniorManagerModel->voters_count_filtered($id),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }
	
	// datatable for users
    public function getusers() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->SeniorManagerModel->getUsers();
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->email,
                $r->mobile,
                ($r->location != '') ? $r->location : '',
                $r->user_role,
                '<ul class="demo-btns">
                     <li>
                         <a href="' . base_url('user/edit/'.$r->id). '" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                     </li>
				</ul>',
                // ($r->active_status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' ,  
                // '<ul class="demo-btns">
                //     <li>
                //         <a href="' . base_url('user/edit/'.$r->id). '" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                //     </li>
                //     <li>
                //         <a href="" class="deactivate btn btn-danger btn-xs" data-user="' . $r->id.'"><i class="fa fa-trash"></i></a>
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
	
	public function analytics() { 
		$data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$user_data = $this->session->userdata('user');
        $id = $user_data->id;
        
        $data['coordinator'] = $this->SeniorManagerModel->getCoordinatorsBySeniorManager($id);
        $data['volunteer'] = $this->SeniorManagerModel->getVolunteerBySeniorManager($id);
        
        $family_4 = 0; $family_6 = 0; $family_8 = 0; $family_10 = 0; $family_12 = 0;
        $relative_6 = 0; $relative_9 = 0; $relative_12 = 0; $relative_15 = 0;
        $friend_5 = 0; $friend_10 = 0; $friend_15 = 0; $friend_20 = 0;

        if(is_array($data['coordinator'])) {
            $allusers = array_merge($data['coordinator']);
            
            foreach($allusers as $u) {
                $u->family = $this->SeniorManagerModel->getCitizenByrelation($u->id, 47);
                $u->relative = $this->SeniorManagerModel->getCitizenByrelation($u->id, 48);
                $u->friend = $this->SeniorManagerModel->getCitizenByrelation($u->id, 49);
            }
            
            
            foreach($allusers as $u) {
                if($u->family == 4) {
                    $family_4 = $family_4 + 1;
                }
                if($u->family > 4 && $u->family <= 6) {
                    $family_6 = $family_6 + 1;
                }
                if($u->family > 6 && $u->family <= 8) {
                    $family_8 = $family_8 + 1;
                }
                if($u->family > 8 && $u->family <= 10) {
                    $family_10 = $family_10 + 1;
                }
                if($u->family > 10) {
                    $family_12 = $family_12 + 1;
                }
            }

            foreach($allusers as $u) {
                if($u->relative == 6) {
                    $relative_6 = $relative_6 + 1;
                }
                if($u->relative > 6 && $u->relative <= 9) {
                    $relative_9 = $relative_9 + 1;
                }
                if($u->relative > 9 && $u->relative <= 12) {
                    $relative_12 = $relative_12 + 1;
                }
                if($u->relative > 15) {
                    $relative_15 = $relative_15 + 1;
                }
            }

            foreach($allusers as $u) {
                if($u->friend == 5) {
                    $friend_5 = $friend_5 + 1;
                }
                if($u->friend > 5 && $u->friend <= 10) {
                    $friend_10 = $friend_10 + 1;
                }
                if($u->friend > 10 && $u->friend <= 15) {
                    $friend_15 = $friend_15 + 1;
                }
                if($u->friend > 20) {
                    $friend_20 = $friend_20 + 1;
                }
            }
            
        }
        $data['family_4'] = $family_4;
        $data['family_6'] = $family_6;
        $data['family_8'] = $family_8;
        $data['family_10'] = $family_10;
        $data['family_12'] = $family_12;
        $data['relative_6'] = $relative_6;

        $data['relative_9'] = $relative_9;
        $data['relative_12'] = $relative_12;
        $data['relative_15'] = $relative_15;

        $data['friend_5'] = $friend_5;
        $data['friend_10'] = $friend_10;
        $data['friend_15'] = $friend_15;
        $data['friend_20'] = $friend_20;

        $data['total_voters'] = $this->SeniorManagerModel->getVotersByManager($id)->num_rows();
        $data['pos_voters'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.voter_status' => 12))->num_rows();
        $data['neu_voters'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.voter_status' => 14))->num_rows();
		$data['neg_voters'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.voter_status' => 13))->num_rows();
		$data['male_voters'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.gender' => 4))->num_rows();
        $data['female_voters'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.gender' => 5))->num_rows();
        $data['other_gender'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.gender' => 77))->num_rows();
		$data['Schedule_Caste'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 30))->num_rows();
		$data['Schedule_Tribe'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 31))->num_rows();
		$data['Backward_Classes'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 32))->num_rows();
		$data['Other_BC'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 33))->num_rows();
		$data['Other_Category'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 34))->num_rows();
		$data['Forward_Classes'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 51))->num_rows();
		$data['Minority'] = $this->SeniorManagerModel->getVotersByManager($id, array('v.category' => 52))->num_rows();
		
		$data['coordinators'] = $this->SeniorManagerModel->getCoordinatorsByManager($id);
        
		
        
		
		$location_id = $this->session->userdata('user')->location_id;
        
        //Performance
        $performance = $this->SeniorManagerModel->getCoordPerformanceBySM($id);
        $poor_p = 0; $good_p = 0; $vgood_p = 0; $exc_p = 0; $iconic_p = 0;
        if(is_array($performance)) {
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

        //Age wise
        $age = $this->SeniorManagerModel->getVotersByManager($id);
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
        
		//echo "<pre>";print_r($data);exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/analytics/analytics.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/analytics.php', $data);
        $this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php');
    }

    public function location() {
        if($this->input->post()) {
            if($this->form_validation->run() === TRUE) {
                $const_id = $this->input->post('constituency');
                $const_demo = $this->managerModel->getDemographicByConst($const_id);
                if($const_demo) {
                    $data['demography'] = $const_demo;
                }else {
                    $this->session->set_flashdata('error', '<div class="alert alert-success">Sorry! We could not find a relevant data.</div>');
                }
                
            }
        }
        $data['constituencies'] = $this->apiModel->getAllConstituencyByState(3);
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/demographics.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        //$this->load->view('manager/analytics-script.php');
        $this->load->view('includes/footer.php'); 
    }

    public function events() {
		$data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/superbox/superbox.min.js');
		$locationid = $this->session->userdata('user')->location_id;
		$data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
        $data['my_events'] = $this->SeniorManagerModel->getEvents(35);
        $data['x_events'] = $this->SeniorManagerModel->getEvents(36);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/events/events.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/event-script.php'); 
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function evm() {
        $data['header_css'] = array('admin.css', 'dashboard.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$locationid = $this->session->userdata('user')->location_id;
		$data['contestants'] = $this->SeniorManagerModel->getContestants($locationid);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['contestants']) {
            $this->load->view('seniormanager/digitalbooth/evm.php', $data);
        }else {
            $data['content'] = 'No content.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/seniormanager/live-booth-script.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function voterlist() {
		$id = $this->session->userdata('user')->id;
		$data['srm_mandal'] = $this->SeniorManagerModel->mandalBySeniorManager($id);
		$data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
	    $this->load->view('seniormanager/digitalbooth/voter-list-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function voterlistvillage($location_id) {
		$data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        // $data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
        $data['ps'] = $this->SMDashboardModel->getPollingStationsByMandals($location_id);
        // echo '<pre>'; print_r($data['ps']); exit;
		//echo $this->db->last_query();exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['ps']) {
            $this->load->view('seniormanager/digitalbooth/voter-list-village.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
	    
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
    
	public function voterlistcoordinator($location_id) {
        $id = $this->session->userdata('user')->id;
        $data['coordinators'] = $this->SeniorManagerModel->getCoordinatorsByPS($location_id);
		$data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css','myteam.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        // echo '<pre>'; print_r($data['coordinators']); exit;
        if($data['coordinators']) {
            foreach($data['coordinators'] as $k => $mg) {
                $mg->total_member = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($mg->id)->num_rows();
                $mg->total_attendant = $this->SeniorManagerModel->getTotalAttendant($mg->id)->num_rows();
            }
            $this->load->view('seniormanager/digitalbooth/voter-list-coordinator.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
	    
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function voterpriority($id) {
        $data['profile'] = $this->SeniorManagerModel->userProfile($id);
		$data['priority_list'] = $this->SeniorManagerModel->getVolunteerByCoordinator($id);
		$data['mygrouptotal'] = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($id)->num_rows();
		$data['mygroupattendant'] = $this->SeniorManagerModel->getTotalAttendant($id)->num_rows();
		if($data['priority_list'] != "") { 
            foreach($data['priority_list'] as $k => $mg)  {
                $mg->total_member = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($mg->id)->num_rows();
                $mg->total_attendant = $this->SeniorManagerModel->getTotalAttendant($mg->id)->num_rows();
            }
        } 
		$data['header_css'] = array('admin.css', 'priority-list.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
	    $this->load->view('seniormanager/digitalbooth/db-voter-list.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function mygroupmembers($id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
            $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
			$data['vid']=$id;
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/digitalbooth/mygroup-team-members.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/mygroup-member-script.php',$data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }
	
    public function prioritylist($id, $list) {
        if(isset($id) && isset($list)) {
            $data['coordinator'] = $this->managerModel->userprofile($id);
            $data['list'] = $list;
            $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/cr-pr-list.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('includes/footer.php');
        }
    }

    public function voterreach() {
        if($this->input->post()) {
            $id = $this->input->post('coordinator');
            $data['priority_list'] = $this->managerModel->userprofile($id);
        }
        $id = $this->session->userdata('user')->id;
        $data['coordinators'] = $this->managerModel->getCoordinatorsByManager($id);
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/voter-mobilisation.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('includes/footer.php');
    }

    public function pollingbooth() {
        $booth_agents = $this->SeniorManagerModel->getPSMember($this->_id, 37);
        $data['agents'] = $booth_agents;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/polling-booth.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('includes/footer.php'); 
    }

	//BOOTH MANAGEMENT CHANGES
	public function boothmanagement() {
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
        $data['srm_mandal'] = $this->SeniorManagerModel->mandalBySeniorManager($id);
		if(is_array($data['srm_mandal'])) {
            foreach($data['srm_mandal'] as $key=>$value){   
                $value->total_voters = $this->managerModel->getVotersByManager($value->id)->num_rows();
                }
        }
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/booth-management.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function boothmanagervillages($id) {
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css','dashboard.css');
		// $data['villages'] = $this->apiModel->getAllVillageByMandal($id);
        $data['ps'] = $this->SMDashboardModel->getPollingStationsByMandals($id);
        // echo '<pre>'; print_r($data['ps']); exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/booth-manager-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function managerpollingstation($id) {
        $data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
        $data['ps_details'] = $this->SeniorManagerModel->getBoothPSByVillageId($id);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/manager-polling-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function pollingstationdetails($id,$location) {
        $data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
		$data['tl_profile'] = $this->SeniorManagerModel->getTeamLeaderByPs($id);
		$data['psdl'] = $this->SeniorManagerModel->getPollingStation($id);
        $data['total_voters'] = $this->SeniorManagerModel->getPollingStationCount($id)->num_rows();
		// $data['male_voters'] = $this->SeniorManagerModel->getPollingStationCount($id, array('v.gender' => 4))->num_rows();
		// $data['female_voters'] = $this->SeniorManagerModel->getPollingStationCount($id, array('v.gender' => 5))->num_rows();
		// $data['other_voters'] = $this->SeniorManagerModel->getPollingStationCount($id, array('v.gender' => 0))->num_rows();
		$data['pl_coordinator'] = $this->SeniorManagerModel->getCoordinatorByPS($id);

		if($data['pl_coordinator'] != "") {
            foreach($data['pl_coordinator'] as $k => $vot) {
                $vot->voter = $this->SeniorManagerModel->getTotalVote($vot->id);
                $vot->positive_voters = $this->SeniorManagerModel->votersByStatusCr($vot->id, 12);
                $vot->neutral_voters = $this->SeniorManagerModel->votersByStatusCr($vot->id, 14);
            }	
        }
		// echo '<pre>'; print_r($data['tl_profile']); exit;
		$data['pl_boothagent'] = $this->SeniorManagerModel->getPollingStationMember($id, 37);
        $data['pl_boothobserver'] = $this->SeniorManagerModel->getPollingStationMember($id, 38);
		$data['pl_agent'] = $this->SeniorManagerModel->getPollingAgent($id);
        // $data['ps_img'] = $this->SeniorManagerModel->getPollingStationImage($id);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['tl_profile'] || $data['pl_coordinator']) {
            $this->load->view('seniormanager/digitalbooth/polling-station-details.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function liveboothmandal() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$id = $this->session->userdata('user')->id;
		$location_id= $this->session->userdata('user')->location_id;
		$registration = $this->SMDashboardModel->getLiveTotalRegistration($location_id)->result();
		$data['total_register'] = count($registration);
		$data['total_attendant'] = $this->SMDashboardModel->getLiveTotalAttendant($location_id, array())->num_rows();
		$data['total_male'] = $this->SMDashboardModel->getLiveTotalAttendant($location_id, array('v.gender' => 4))->num_rows();
		$data['total_female'] = $this->SMDashboardModel->getLiveTotalAttendant($location_id, array('v.gender' => 5))->num_rows();
		
		$data['mandals']=$this->SMDashboardModel->getTotalMandalBySM($id)->result();
        $data['allmandals'] = $this->SeniorManagerModel->getMandalsByConstituence($location_id);
        if(is_array($data['allmandals'])) {
            $data['totalmandal'] = count($data['allmandals']);
            if($data['allmandals']!="") {  
                    foreach($data['allmandals'] as $k => $mg) {
                        $mg->total_t = $this->SMDashboardModel->getLiveTotalRegistrationMandal($mg->id)->num_rows();
                        $mg->total_at = $this->SMDashboardModel->getByMandalLiveTotalAttendant($mg->id, array())->num_rows();
                        $mg->total_m = $this->SMDashboardModel->getByMandalLiveTotalAttendant($mg->id, array('v.gender' => 4))->num_rows();
                        $mg->total_f = $this->SMDashboardModel->getByMandalLiveTotalAttendant($mg->id, array('v.gender' => 5))->num_rows();
                    }
            }
        }
		  
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/live-booth-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/live-booth-mandal-script.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function liveboothteamleader($location) {
        $data['header_css'] = array('admin.css');
		$data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  'js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
		$data['pollingstation']=$this->SMDashboardModel->getPollingStationsByMandals($location);
		if($data['pollingstation']!="") {  
            foreach($data['pollingstation'] as $k => $mg) {
                $mg->total_t=$this->SMDashboardModel->getLiveTotalRegistrationVillage($mg->ps_no)->num_rows();
                $mg->total_at = $this->SMDashboardModel->getLiveTotalAttendantVillage($mg->ps_no, array())->num_rows();
                $mg->total_m = $this->SMDashboardModel->getLiveTotalAttendantVillage($mg->ps_no, array('v.gender' => 4))->num_rows();
                $mg->total_f = $this->SMDashboardModel->getLiveTotalAttendantVillage($mg->ps_no, array('v.gender' => 5))->num_rows();
            }
        }  
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/live-booth-teamleader.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/live-booth-village-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function liveboothcoordinator() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/live-booth-coordinator.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        //$this->load->view('scripts/seniormanager/live-booth-coordinate-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function pslivebooth($location) {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$data['total_r']=$this->SMDashboardModel->getLiveTotalRegistrationVillage($location)->num_rows();
		$data['total_at'] = $this->SMDashboardModel->getLiveTotalAttendantVillage($location, array())->num_rows();
		$data['total_m'] = $this->SMDashboardModel->getLiveTotalAttendantVillage($location, array('v.gender' => 4))->num_rows();
		$data['total_f'] = $this->SMDashboardModel->getLiveTotalAttendantVillage($location, array('v.gender' => 5))->num_rows();
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/ps-live-booth.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/live-booth-coordinate-script.php',$data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
    public function assignpollingstation() {
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $id = $this->session->userdata('user')->id;
            $ps = $this->SeniorManagerModel->getPollingStationsByManager($id);
            $data['polling_station'] = $ps;
            $data['booth_agent'] = $this->SeniorManagerModel->getPSMemberByManager($id, 37);
            
            $this->load->view('seniormanager/assign-polling-station.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/assign-polling-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
    public function allocateBAgent() {
        if($this->input->post()) {
            $insert = $this->SeniorManagerModel->allocateBAgentByManager();
            if($insert) {
                $this->session->set_flashdata('allocate-agent', '<div class="alert alert-success fade in"><strong>Success!</strong> Polling Station has been allocated to Booth Agent.</div>');
            }else {
                $this->session->set_flashdata('allocate-agent', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('seniormanager/assignpollingstation'));
        }
    }
	
	public function pollingstation() {
		$id = $this->session->userdata('user')->id;
		$data['polling_st'] = $this->SeniorManagerModel->getPollingstationBySeniorManager($id);
        if($this->input->post()) {
            $ps_id = $this->input->post('polling-station');
            $ps = $this->SeniorManagerModel->getPollingStation($ps_id);
            $ps_img = $this->SeniorManagerModel->getPollingStationImage($ps_id);
            $data['ps_details'] = $ps;
            $data['ps_img'] = $ps_img;
           
        }
        $data['lc_id'] = $this->session->userdata('user')->location_id;
		
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/superbox/superbox.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/polling-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/ps-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function livebooth() {
		$id = $this->session->userdata('user')->id;
		$data['polling_st'] = $this->SeniorManagerModel->getPollingstationBySeniorManager($id);
        if($this->input->post()) {
            $ps_id = $this->input->post('polling-station');
            $ps = $this->SeniorManagerModel->getPollingStation($ps_id);
			if($ps!=''){
            $data['bth_agent'] = $this->SeniorManagerModel->getPollingStationMember($ps[0]->id, 37);
            $data['bth_observer'] = $this->SeniorManagerModel->getPollingStationMember($ps[0]->id, 38);
            $data['coordinators'] = $this->SeniorManagerModel->getCoordinatorsByPS($ps[0]->lc_id);
            $data['ps_details'] = $ps; 
			}
        }
        $data['lc_id'] = $this->session->userdata('user')->location_id;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'live-booth.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/digitalbooth/live-booth.php', $data); //content page
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/live-booth-script.php', $data); //page script
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

	//Branding
	public function branding() {
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	//for branding Ideas
	public function ideas() {
		$id = $this->session->userdata('user')->id;
        $data['header_css'] = array('admin.css', 'slide.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $data['tl_data'] = $this->managerModel->teamProfile($id);
        $this->load->view('seniormanager/other/ideas.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

	// for branding ecommerce
	public function ecommerce() {
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/other/ecommerce.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

	// For Abstract Estimated Cost
	public function estimatedCost() {
		if($this->input->post()) {
			
			$estimated_cost = array();
			foreach($this->input->post('item') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['itm'] = $v;
                }
            }
            foreach($this->input->post('itemDescription') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['itd'] = $v;
                }
            }
			foreach($this->input->post('quantity') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['qty'] = $v;
                }
            }
			foreach($this->input->post('unit') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['ut'] = $v;
                }
            }
			foreach($this->input->post('rate') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['rt'] = $v;
                }
            }
			foreach($this->input->post('perUnit') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['put'] = $v;
                }
            }
			foreach($this->input->post('amount') as $k => $v) {
                if(!empty($v)) {
                    $estimated_cost[$k]['amt'] = $v;
                }
            }
			
			$data = $this->input->post();
			$id = $this->session->userdata('user')->id;
			$data['amount_total'] = array_sum($this->input->post('amount'));
			$data['estimated_cost'] = $estimated_cost;
			$insertcost = $this->managerModel->userEstimatedCost($data,$id);
			
		}
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
		$data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  'js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/other/estimated-cost.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('seniormanager/other/estimatedcost-script.php');
        $this->load->view('includes/footer.php');
    }

	public function testpagination($id) {
        $config = array();
        $config["base_url"] = base_url() . "seniormanager/managerdetails";
        $config["total_rows"] = $this->SeniorManagerModel->countTeamLeader($id);
        $config["per_page"] = 10;
        $config["uri_segment"] = 3;
		

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data["results"] = $this->Countries->
            fetch_countries($config["per_page"], $page);
        $data["links"] = $this->pagination->create_links();

        $this->load->view("seniormanager/managerdetails", $data);
    }
	
	//Datatable for xparty
    public function getxpartyData($partyid,$location) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->SeniorManagerModel->getXpartyById($partyid,$location);
		//echo $this->db->last_query();exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->party_name,
                $r->name,
                $r->age,
                $r->designation,
                $r->followers,
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

	//TASK FOR TEAMS OF SENIOR MANAGER
	public function tasks() {
		$id = $this->session->userdata('user')->id;
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/markdown/markdown.min.js',
								'js/plugin/markdown/to-markdown.min.js','js/plugin/markdown/bootstrap-markdown.min.js',
								'js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
		$data['user_roles'] = $this->SeniorManagerModel->getAssignRole();
		
		$data['user_groups'] = $this->SeniorManagerModel->getGroups();
		$data['reciverid'] = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/task/tasks.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/editor-script.php'); 
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function allocateTaskByGroup() {
        if($this->input->post()) {
				$group = 62;
				$insert = $this->SeniorManagerModel->allocateGroupTaskBySeniorManager($group);
				if($insert) {
					
					$this->session->set_flashdata('assign-grouptask', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created .</div>');
				}else {
					
					$this->session->set_flashdata('assign-grouptask', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
				}
				redirect(base_url('SeniorManager/tasks'));
        }
    }
	
	public function allocateTaskByIndividual() {
        if($this->input->post()) {
			$group = 63;
		    $insert = $this->SeniorManagerModel->allocateGroupTaskBySeniorManager($group);
            if($insert) {
                $this->session->set_flashdata('assign-individual', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created.</div>');
            }else {
                $this->session->set_flashdata('assign-individual', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
           redirect(base_url('SeniorManager/tasks'));
        }
    }
	
	public function allocateMyTasks() {
        if($this->input->post()) {
			$group = 64;
        
			$insert = $this->SeniorManagerModel->allocateGroupTaskBySeniorManager($group);
            if($insert) {
                $this->session->set_flashdata('assign-mytask', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created.</div>');
            }else {
                $this->session->set_flashdata('assign-mytask', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('SeniorManager/tasks'));
        }
    }
	
	public function myScheduleEvents() {
		
        if($this->input->post()) {

			$group = 64;
           
		   $insert = $this->SeniorManagerModel->allocateGroupTaskBySeniorManager($group);
            if($insert) {
                $this->session->set_flashdata('assign-mytask', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created.</div>');
            }else {
                $this->session->set_flashdata('assign-mytask', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
           redirect(base_url('Dashboard/schedules'));
        }
    }
	
	public function getmyevents() {
        // Our Start and End Dates
		 $start = $this->input->get("start");
		 $end = $this->input->get("end");

		 $startdt = new DateTime('now'); // setup a local datetime
		 $startdt->setTimestamp($start); // Set the date based on timestamp
		 $start_format = $startdt->format('Y-m-d H:i:s');

		 $enddt = new DateTime('now'); // setup a local datetime
		 $enddt->setTimestamp($end); // Set the date based on timestamp
		 $end_format = $enddt->format('Y-m-d H:i:s');
        $id = $this->session->userdata('user')->id;
    
        $events = $this->SeniorManagerModel->getMyTasks($id);

        $data_events = array();
    
        foreach($events as $r) {
            $data_events[] = array(
                "id" => $r->id,
                "title" => $r->task_name,
                "description" => $r->task_description,
                "end" => $r->date_to,
                "start" => $r->date_from,
                "className" => 'bg-color-red txt-color-white'
            );
        }

        echo json_encode(array("events" => $data_events));
        exit();
    }
		 
	public function getallevents() {
        // Our Start and End Dates
        $start = $this->input->get("start");
        $end = $this->input->get("end");

        $startdt = new DateTime('now'); // setup a local datetime
        $startdt->setTimestamp($start); // Set the date based on timestamp
        $start_format = $startdt->format('Y-m-d H:i:s');

        $enddt = new DateTime('now'); // setup a local datetime
        $enddt->setTimestamp($end); // Set the date based on timestamp
        $end_format = $enddt->format('Y-m-d H:i:s');
        $id = $this->session->userdata('user')->id;
    
        $events = $this->SeniorManagerModel->getAllEventTasks($id);

        $data_events = array();
        if(is_array($events)) {
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
                    $classname = 'bg-color-blue txt-color-white'; //Self task event
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
        }
        echo json_encode(array("events" => $data_events));
        exit();
    }

	public function mobileTeamEvents($mtid) {
        // Our Start and End Dates
        $start = $this->input->get("start");
        $end = $this->input->get("end");

        $startdt = new DateTime('now'); // setup a local datetime
        $startdt->setTimestamp($start); // Set the date based on timestamp
        $start_format = $startdt->format('Y-m-d H:i:s');

        $enddt = new DateTime('now'); // setup a local datetime
        $enddt->setTimestamp($end); // Set the date based on timestamp
        $end_format = $enddt->format('Y-m-d H:i:s');
        $id = $this->session->userdata('user')->id;
    
        $events = $this->SeniorManagerModel->getMobileTeamTasks($id,$mtid);
        
        $data_events = array();
    
        foreach($events as $r) {
        
            $data_events[] = array(
                "id" => $r->id,
                "title" => $r->task_name,
                "description" => $r->task_description,
                "end" => $r->date_to,
                "start" => $r->date_from,
                "className" => 'bg-color-red txt-color-white'
            );
        }

        echo json_encode(array("events" => $data_events));
        exit();
    }
	
	public function testcalender() {
        $data['header_css'] = array('admin.css', 'slide.css');
		$data['plugins'] = array('js/plugin/calender/daypilot-all.min.js','js/plugin/calenderscript.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        
        $this->load->view('seniormanager/testcalender.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('includes/footer.php');
    }
	
	
	/* public function captcha(){
		$this->load->helper('captcha');
		$vals = array(
				'img_path'      => './captcha/',
				'img_url'       => 'http://example.com/captcha/'
		);

		$cap = create_captcha($vals);
		$data = array(
				'captcha_time'  => $cap['time'],
				'ip_address'    => $this->input->ip_address(),
				'word'          => $cap['word']
		);

		$query = $this->db->insert_string('captcha', $data);
		$this->db->query($query);

		echo 'Submit the word you see below:';
		echo $cap['image'];
		echo '<input type="text" name="captcha" value="" />';
	}
    */

    /*==================REPORTS===============*/
	public function teamreport() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		if($this->input->post()) {
            $role = $this->input->post('userrole');
		/*PROFFESSION*/
			$data['housewife'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 84))->num_rows();
		    $data['student'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 85))->num_rows();
			$data['farmer'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 86))->num_rows();
			$data['casteprofession'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 87))->num_rows();
			$data['employee'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 88))->num_rows();
			$data['agriculturelabour'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 89))->num_rows();
			$data['unemployee'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 90))->num_rows();
		    $data['business'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 91))->num_rows();
			$data['others'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.profession' => 92))->num_rows();
		/*PARTY PARTICIPATION*/
			$data['partyleader'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.party_participation' => 93))->num_rows();
			$data['partymember'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.party_participation' => 94))->num_rows();
			$data['partysympathiser'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.party_participation' => 95))->num_rows();
			$data['neutralworker'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.party_participation' => 96))->num_rows();
		/*PERSONAL STATUS*/
			$data['jointfamily'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.personal_status' => 97))->num_rows();
			$data['independentfamily'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.personal_status' => 98))->num_rows();
			$data['ownvehicle'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.personal_status' => 99))->num_rows();
			$data['ownhouse'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.personal_status' => 100))->num_rows();
			$data['rentalhouse'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.personal_status' => 101))->num_rows();
			$data['noneoftheabove'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.personal_status' => 102))->num_rows();
		/*TOTAL FAMILY VOTERS*/
			$data['tenvoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 103))->num_rows();
			$data['sixvoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 104))->num_rows();
			$data['fourvoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 105))->num_rows();
			$data['ninevoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 106))->num_rows();
			$data['eightvoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 107))->num_rows();
			$data['sevenvoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 108))->num_rows();
			$data['fivevoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 109))->num_rows();
			$data['threevoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 110))->num_rows();
			$data['twovoters'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.family_voters' => 110))->num_rows();
		/*POSITIVE FAMILY VOTE COMMITMENT*/
			$data['hundredcommitment'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.vote_commitment' => 132))->num_rows();
			$data['fiftycommitment'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.vote_commitment' => 133))->num_rows();
			$data['stillinneutralmode'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.vote_commitment' => 134))->num_rows();
			$data['cancelcommitment'] = $this->SeniorManagerModel->getValidationAnalyticData($role, array('v.vote_commitment' => 135))->num_rows();
	    /*PRESENT GOVT. SCHEMES*/
			$data['NTRHousing'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 112))->num_rows();	
			$data['farmerloanwaiver'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 113))->num_rows();
			$data['chandrannapellikanuka'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 114))->num_rows();
			$data['pension'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 115))->num_rows();
			$data['unemployeebenefit'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 116))->num_rows();
			$data['chandrannainsurancescheme'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 117))->num_rows();
			$data['cropinsurance'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 118))->num_rows();
			$data['freebicycle'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 119))->num_rows();
			$data['freemobile'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 120))->num_rows();
			$data['arogyarakshahealth'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 121))->num_rows();
			$data['otherschemes'] = $this->SeniorManagerModel->getPresentGovtValidationAnalyticData($role, array('vo.option_id' => 122))->num_rows();
		/*UPCOMING GOVT. SCHEMES */
			//$data['pensionincrement'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 123))->num_rows();	
			//$data['arogyasree'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 124))->num_rows();
			//$data['farmerwelfare'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 125))->num_rows();
			//$data['feereimbursement'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 126))->num_rows();
			//$data['housingscheme'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 127))->num_rows();
			//$data['ysrasara'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 128))->num_rows();
			//$data['waterirrigation'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 129))->num_rows();
			//$data['loantosstbc'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 130))->num_rows();
			//$data['alcoholprohibition'] = $this->SeniorManagerModel->getUpcomingGovtValidationAnalyticData($role, array('vo.option_id' => 131))->num_rows();
		}
	    //	echo $this->db->last_query();exit;
	    // echo "<pre>";print_r($data);exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/reports/team-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/team-report-script.php',$data);
        $this->load->view('includes/footer.php');
    }
	
	public function telecallingreport() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        
        if($this->input->post()) {
            $qid = $this->input->post('question');
            $role = $this->input->post('userrole');
            
            $labels = $this->SeniorManagerModel->getQuestionLabels($qid);
            if($labels) {
                foreach($labels as $lb) {
                    $data['labels'][] = $lb->value;
                    $lb->count = $this->SeniorManagerModel->getCallingReport($qid, $role, $lb->id);
                    $data['values'][] = $lb->count;
                }
            }
            $data['question'] = $this->SeniorManagerModel->getQuestionById($qid);

        }

        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/reports/telecalling-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/reports/telecalling-report-script.php', $data);
        $this->load->view('includes/footer.php');
    }

    public function getQuestionsByUserRole($role, $report) {
        $result = $this->SeniorManagerModel->getQuestionsByRole($role, $report);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }

    public function questionLabel($qid) {
        $result = $this->SeniorManagerModel->getQuestionLabels($qid);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
	
	//Datatable for telecaller
    public function getTelecallerData($role) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->SeniorManagerModel->getTelecallerDataByRole($role);
		//echo $this->db->last_query();exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name .' '.$r->first_name,
                $r->mobile,
                $r->profession,
                $r->party_participation,
                $r->personal_status,
				$r->family_voters,
				$r->vote_commitment,
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
	/*===============END REPORTS=============*/

    /**
     * Date : 16-01-2019
     */
    public function divisionhead($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            // $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            $data['cr_data'] = $this->SeniorManagerModel->getDivisionIncharge($id);
            
			if(is_array($data['cr_data'])) {
                foreach($data['cr_data'] as $k => $vot)  {
                    $vot->voter = $this->SeniorManagerModel->getVotersCountByDI($vot->id);
                    $vot->positive_voters = $this->SeniorManagerModel->getVotersCountByDI($vot->id, array('v.voter_status' => 12));
                    $vot->neutral_voters = $this->SeniorManagerModel->getVotersCountByDI($vot->id, array('v.voter_status' => 14));
                    $vot->ps_no = $this->SeniorManagerModel->getDivisionInchargePS($vot->id);
                }
            }
			
			
            $data['voters'] = $this->SeniorManagerModel->getVotersCountByDH($id);
            $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByDH($id, array('v.voter_status' => 12));
			
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/division-head.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/tl-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

    public function divisionincharge($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            // $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            
            //bp
            $data['booth_president'] = $this->SeniorManagerModel->getBoothPresidentByDI($id);
			if(is_array($data['booth_president'])) {
                foreach($data['booth_president'] as $bp)  {
                    $bp->voter = $this->SeniorManagerModel->getVotersCountByBP($bp->id);
                    $bp->positive_voters = $this->SeniorManagerModel->getVotersCountByBP($bp->id, array('v.voter_status' => 12));
                    $bp->neutral_voters = $this->SeniorManagerModel->getVotersCountByBP($bp->id, array('v.voter_status' => 14));
                }
            }

            //bc
            $data['booth_coordinator'] = $this->SeniorManagerModel->getBoothCoordinatorByDI($id);
            if(is_array($data['booth_coordinator'])) {
                foreach($data['booth_coordinator'] as $bc) {
                    $bc->ps = $this->SeniorManagerModel->getBoothCoordinatorPS($bc->id);
                }

            }

            //tc
            $data['tele_caller'] = $this->SeniorManagerModel->getTelecallerByDI($id);
            // echo '<pre>'; print_r($data['booth_president']); print_r($data['booth_coordinator']); print_r($data['tele_caller']); exit;
			
			
            $data['voters'] = $this->SeniorManagerModel->getVotersCountByDI($id);
            $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByDI($id, array('v.voter_status' => 12));
			
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/division-incharge.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/tl-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

    public function boothpresident($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            // $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            
            //sp
            $data['street_president'] = $this->SeniorManagerModel->getStreetPresidentByBP($id);
			if(is_array($data['street_president'])) {
                foreach($data['street_president'] as $sp)  {
                    $sp->voter = $this->SeniorManagerModel->getVotersCountBySP($sp->id);
                    $sp->positive_voters = $this->SeniorManagerModel->getVotersCountBySP($sp->id, array('v.voter_status' => 12));
                    $sp->neutral_voters = $this->SeniorManagerModel->getVotersCountBySP($sp->id, array('v.voter_status' => 14));
                }
            }
			
			
            $data['voters'] = $this->SeniorManagerModel->getVotersCountByBP($id);
            $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByBP($id, array('v.voter_status' => 12));
			
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/booth-president.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/tl-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

    public function sheetpresident($id){
		
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
			$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
            'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');										
            $data['profile'] = $this->SeniorManagerModel->userProfile($id);
            // $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
			
			//familyhead
            $data['family_head'] = $this->SeniorManagerModel->getFamilyHeadBySP($id);
			//echo $this->db->last_query();exit;
			if(is_array($data['family_head'])) {
                foreach($data['family_head'] as $sp)  {
                    $sp->voter = $this->SeniorManagerModel->getVolunteerTotalVote($sp->id)->num_rows();
                    $sp->positive_voters = $this->SeniorManagerModel->getVolunteerTotalVote($sp->id, array('v.voter_status' => 12))->num_rows();
                    $sp->neutral_voters = $this->SeniorManagerModel->getVolunteerTotalVote($sp->id, array('v.voter_status' => 14))->num_rows();
                }
            }
			
            $data['voters'] = $this->SeniorManagerModel->votersByUser($id);
            $data['positive_voters'] = $this->SeniorManagerModel->votersByStatusCr($id, 12);
           // $data['cr_data'] = $this->SeniorManagerModel->getVolunteerByCoordinator($id);
			//$data['mymembers'] = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($id);
			
			/* if($data['cr_data']!="") {
                foreach($data['cr_data'] as $k => $vot) {
                    $vot->voter = $this->SeniorManagerModel->getVolunteerTotalVote($vot->id)->num_rows();
                    $vot->positive_voters = $this->SeniorManagerModel->getVolunteerTotalVote($vot->id,array('v.voter_status' => 12))->num_rows();
                    $vot->neutral_voters = $this->SeniorManagerModel->getVolunteerTotalVote($vot->id, array('v.voter_status' => 14))->num_rows();
                }
			} */
			$data['family']=$this->SeniorManagerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->SeniorManagerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->SeniorManagerModel->getCitizenByrelation($id, 49);
			$data['known']=$this->SeniorManagerModel->getCitizenByrelation($id, 50);
            
            $data['vid'] = $id;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/coordinator-profile.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			$this->load->view('scripts/seniormanager/coordinator-script.php',$data);
            $this->load->view('scripts/seniormanager/cf-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
     
			}
	}

    public function telecallerprofile($id) {
        if(isset($id)) {
           $data['header_css'] = array('admin.css', 'myteam.css');
           $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
           $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
        //    $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
           
           
           
           
           $data['voters'] = $this->SeniorManagerModel->getVotersCountByDI($id);
           $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByDI($id, array('v.voter_status' => 12));
           
           
           $this->load->view('includes/header.php', $data);
           $this->load->view('seniormanager/top-nav.php');
           $this->load->view('seniormanager/side-nav.php');
           //check permission
           if($this->allocation_status === FALSE) {
               $this->load->view('common/no-access.php');
           }else {
               $this->load->view('seniormanager/myteam/telecaller-profile.php', $data);
           }
           
           $this->load->view('includes/page-footer.php');
           $this->load->view('seniormanager/shortcut-nav.php');
           $this->load->view('includes/plugins.php', $data);
           $this->load->view('scripts/seniormanager/tl-script.php');
           $this->load->view('scripts/common/modal-script.php');  //modal script
           $this->load->view('includes/footer.php');
       }
    }
   
    public function boothcoordinatorpr($id) {
            if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            // $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            
            
            
            
            $data['voters'] = $this->SeniorManagerModel->getVotersCountByDI($id);
            $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByDI($id, array('v.voter_status' => 12));
            
            
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('seniormanager/myteam/boothcoordinator-profile.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/seniormanager/tl-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

    /**
     * Date : 24-01-19
     * Author : Anees
     */
    public function validationteamreport() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($this->_usession->location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/reports/validation-team-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/reports/validation-team-script.php');
        $this->load->view('includes/footer.php');
    }

    //Datatable for Team validation Report
    public function getValidationData($mandal, $role) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->SeniorManagerModel->getTeamValidation($mandal, $role);
		//echo $this->db->last_query();exit;
        $data = array();
        foreach($users->result() as $r) {
            $start++;
            $data[] = array(
                $start,
                $r->first_name .' '.$r->last_name,
                $r->mobile,
                $r->user_role,
                $r->ps_no,
                $r->profession,
                $r->party_participation,
                $r->personal_status,
				$r->family_voters,
                $r->vote_commitment,
                $r->govt_schemes
                //$r->ysr_schemes
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

    public function getlocationName() {
        if($this->input->post()) {
            $data = $this->input->post();
            $loc = $data['location'];
            $result = $this->SeniorManagerModel->getLocationById($loc);
            echo json_encode($result); 
        }
    }

    /**
     * Date : 12-02-2019
     * Author : Anees
     */
    public function telereport() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js', 'js/plugin/datatables/dataTables.rowsGroup.js');
        $data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($this->_usession->location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/reports/tc-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/reports/tc-report-script.php');
        $this->load->view('includes/footer.php');
    }

    //Datatable for TC validation report
    public function getTCValidationData($mandal, $role, $report) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->SeniorManagerModel->getTCValidation($mandal, $role, $report);
		//echo $this->db->last_query();exit;
        $data = array();
        foreach($users->result() as $r) {
            $start++;
            $data[] = array(
                $start,
                $r->first_name .' '.$r->last_name,
                $r->mobile,
                $r->ps_no,
                $r->question,
                $r->answer
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

    /* REPORTS 1-2-2019 FROM ADMIN TO SM RECRUITMENT AND REGISTRATION*/
	public function recruitmentreport() {
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $data['user_roles'] = $this->adminModel->getAssignRole();
        
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/reports/recruitment-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/reports/recruitment-report-script.php');
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
	
	public function getPollingStationByMandal($id) {
        $result = $this->adminModel->getPSByMandal($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
	
	public function registrationreport() {
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/reports/registration-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/reports/registration-report-script.php');
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
    
    /* PERFORMANCE */
	public function performancebp() {
        $data['header_css'] = array('buttons.dataTables.min.css','admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/performance/performance-bp.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/performance/performance-bp-script.php');
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
        $data['header_css'] = array('buttons.dataTables.min.css','admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->adminModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/performance/performance-sp.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/performance/performance-sp-script.php');
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
 }