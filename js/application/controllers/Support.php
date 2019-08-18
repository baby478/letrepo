<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Support extends CI_Controller {

    public function __construct() {
		parent::__construct();
		if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }
        $this->load->model('supportModel');
    }

    public function index() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/dashboard.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('includes/footer.php');
    }
	
	public function users() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/viewuser.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
		$this->load->view('scripts/support/datatable-script.php');
        $this->load->view('includes/footer.php');
    }
	
	//datatable for users
    public function getusersdata() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->supportModel->getUsersData();
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
                
                '<ul class="demo-btns">
                    <li>
                         <a href="' . base_url('support/userprofile/'.$r->id). '" class="btn btn-default btn-xs">View Profile</a>
                    </li>
					<li>
                         <a href="' . base_url('support/feedback/'.$r->id). '" class="btn btn-sm btn-primary"><span class="fa fa-plus"></span></a>
                    </li>
				</ul>', 
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
	
	public function userprofile($id) {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$data['profile'] = $this->supportModel->userProfile($id);
		$this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/userprofile.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
		$this->load->view('scripts/support/datatable-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function voters() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		
		$location = $this->session->userdata('user')->location_id;
		$data['polling_station'] = $this->supportModel->getPSByMandal($location);
		
		if($this->input->post()) {
             $psno = $this->input->post('polling-station');
			 $data['voters'] = $this->supportModel->getAllVotersByPS($psno);
			
        }
		$this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/voters.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php');
		$this->load->view('scripts/support/report-script.php');
        $this->load->view('includes/footer.php');
    }
	
	//datatable for voters
    /*  public function getvoters() {
		 
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $id = $this->session->userdata('user')->id;
        $voter = $this->supportModel->getAllVoters($id);
        
        $data = array();
		
        foreach($voter->result() as $r) {
            $start++;
            $data[] = array(
                $start,
                $r->firstname . ' ' . $r->lastname,
                $r->mobile,
                $r->age,
                $r->voter_id,
                $r->first_name . ' ' . $r->last_name,
				
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
    } */
	
	public function getvoters() {
		
        if($this->input->post()) {	
        $data = $this->input->post();
        $psno = $data['polling-station'];
        $voter = $this->supportModel->getAllVotersByPS($psno);
		$result = array();
        echo json_encode($result);   
        }
    }

	
	public function feedback($id) {
		$data['header_css'] = array('support.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
		
		
		if($this->input->post()) {
			//if($this->form_validation->run() === TRUE) {
			 
			 $data = $this->input->post();
			 $data['user_id'] = $id;
		     $data['agent_id'] = $this->session->userdata('user')->id;
			 $data['feedbackdate'] = date('Y-m-d', strtotime($data['feedbackdate']));
			 $insert = $this->supportModel->insertFeedbackDetails($data);
			// echo $this->db->last_query();exit;
			  if($insert) 
				{ 
					$this->session->set_flashdata('feedback', '<div class="alert alert-success fade in"><strong>Success!</strong> Feedback Added successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('feedback', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('support/feedbackreport/'));
			//}
		 }					
		$this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/feedback.php', $data);
        $this->load->view('includes/page-footer.php');
		$this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/support/datatable-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function feedbackreport() {
		$data['header_css'] = array('support.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
		
		$this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/feedback-report.php', $data);
        $this->load->view('includes/page-footer.php');
		$this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/support/datatable-script.php');
        $this->load->view('includes/footer.php');
		
	}
	
	public function getfeedback() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->supportModel->getAllFeedback($id);

        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->title,
				$r->first_name .  ' '  .$r->last_name,
                $r->feeddate,
				$r->duration,
				$r->description,
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
    
    //Generate Tickets
	public function addticket() {
        $user_data = $this->session->userdata('user');
		
		 if($this->input->post()) {
			 $data = $this->input->post();
			
			 $insert = $this->supportModel->generateTickets($data);
			 
			  if($insert) 
				{ 
					$this->session->set_flashdata('generate-tickets', '<div class="alert alert-success fade in"><strong>Success!</strong> Task Added successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('generate-tickets', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('support/addticket'));
		 }
        $data['header_css'] = array('support.css');
        $data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/markdown/markdown.min.js',
								'js/plugin/markdown/to-markdown.min.js','js/plugin/markdown/bootstrap-markdown.min.js',
								'js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
								
        $this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/add-ticket.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
		$this->load->view('scripts/support/form-validation-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function getUserMobileData() {
        if($this->input->post()) {
            $data = $this->input->post();
            $mob = $data['mobileno'];
            $result = $this->supportModel->getUserByPhone($mob);
            echo json_encode($result); 
        }
    }
	
	public function viewticket() {
        $user_data = $this->session->userdata('user');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js');
		//$data['users'] = $this->supportModel->getTicketData();
        $data['header_css'] = array('jquery-confirm.min.css','support.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('support/top-nav.php');
        $this->load->view('support/side-nav.php');
        $this->load->view('support/view-ticket.php');
		$this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/support/datatable-script.php');
      //  $this->load->view('support/custom-scripts.php');
        $this->load->view('includes/footer.php');
		
    }
	public function getticket() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->supportModel->getAllTickets();
		//print_r($users);exit;
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->title,
				$r->mobile,
				($r->issue_type==1) ? 'App Issue' : 'Portal Issue',
				($r->priority == 1) ? 'High' : 'Low',
				$r->description,
				($r->status == 0) ? 'Open' : 'Closed',
				 $r->created_at,
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
	
	
}