<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manager extends CI_Controller {
    
    private $allocation_status;
    private $_id;
    
    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 137) {
            redirect(base_url());
        }
        $this->load->model('loginModel');
        $this->load->model('managerModel');
		$this->load->model('SeniorManagerModel');
		$this->load->model('CoordinatorModel');
        $this->load->model('apiModel');
		$this->load->model('adminModel');
		$this->load->model('GeneralManagerModel');
		$this->load->model('SMDashboardModel');
        $this->_id = $this->session->userdata('user')->id;
        $this->_alloc_status();
    }

    private function _alloc_status() {
        $id = $this->session->userdata('user')->id;
        $status = $this->managerModel->checkAllocStatus($id);
        if($status > 0) {
            $this->allocation_status = true;
        }else {
            $this->allocation_status = false;
        }
    }

    public function index() {
        $user_data = $this->session->userdata('user');
        $id = $user_data->id;
        $data['total_voters'] = $this->managerModel->getVotersByManager($id)->num_rows();
        $data['pos_voters'] = $this->managerModel->getVotersByManager($id, array('v.voter_status' => 12))->num_rows();
        $data['neu_voters'] = $this->managerModel->getVotersByManager($id, array('v.voter_status' => 14))->num_rows();
        $count_village = $this->managerModel->countVotersByVillage($id);
        $count_village_status = $this->managerModel->countVotersByStatusVillage($id, 12);
        $village = array();
        
        foreach($count_village as $k => $vill) {
            $village[$k]['id'] = $vill->id;
            $village[$k]['name'] = $vill->name;
            $village[$k]['total'] = $vill->total;
            $village[$k]['positive'] = 0;
            $village[$k]['level'] = $vill->level_id;
            foreach($count_village_status as $kk => $st) {
                if($vill->id === $st->id) {
                    $village[$k]['id'] = $vill->id;
                    $village[$k]['name'] = $vill->name;
                    $village[$k]['total'] = $vill->total;
                    $village[$k]['positive'] = $st->positive;
                }
            }
        }
        $data['villages'] = $village;
        // echo '<pre>'; print_r($village); exit;
        $data['header_css'] = array('admin.css');
        $data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  'js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/dashboard.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/manager/dashboard.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function _email_exists($email) {
        $exists = $this->loginModel->emailExists($email);
        if($exists) {
            $this->form_validation->set_message('_email_exists', 'The {field} is already exists');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function _phone_exists($phone) {
        $phone = str_replace(array( '(', ')', '-', ' ' ), '', $phone);
        $exists = $this->managerModel->phoneExists($phone);
        if($exists) {
            $this->form_validation->set_message('_phone_exists', 'The {field} is already exists');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function _arrayinput() {
        $arr_location = $this->input->post('mlocation[]');
        if(empty($arr_location)) {
            $this->form_validation->set_rules('mlocation[]','Select at least one PS');
            return false;
        }    
    }

    public function assignRole() {
        if($this->input->post()) {
            if(is_array($this->input->post('mlocation[]'))) {
                $location = $this->input->post('mlocation'); 
                $this->form_validation->set_rules('mlocation[]', 'Multiple Location','required|callback__arrayinput');
            }else {
                $this->form_validation->set_rules('slocation', 'sLocation', 'required');
                $location = $this->input->post('slocation');
            }
            if($this->form_validation->run() === TRUE) {
                // echo '<pre>'; print_r($this->input->post()); exit;
                $user_id = $this->input->post('user');
                $role_id = $this->input->post('user-role');
                $update_role = $this->managerModel->assignUserRole($user_id, $role_id ,$location);
                // $update_role = $this->managerModel->assignUserRole($user_id, $role_id);
                if($update_role) {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-success fade in"><strong>Success!</strong> User role assigned successfully.</div>');
                }else {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
            }
        }
        $id = $this->session->userdata('user')->id;
        $ps = $this->managerModel->getPollingStationsByManager($id);
        // echo '<pre>'; print_r($ps); exit;
        $data['ps'] = $ps;
		$data['location'] = $this->managerModel->getAllocatedLocation($id);
        $location_id = $data['location'][0]->lc_id;
        $data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
        $data['user_roles'] = $this->managerModel->getAssignRole();
        $data['users'] = $this->managerModel->getUserByRole(17);
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
           $this->load->view('common/no-access.php');
        }else {
            $this->load->view('manager/users/assign-role.php', $data);
        }
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/assign-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
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

    //datatable
    public function getusers() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->managerModel->getUsers();

        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->email,
                $r->mobile,
                ($r->ps_no != null) ? $r->ps_no . ' - ' . $r->ps_name : '-',
                ($r->location != null) ? $r->location : '-',
                $r->user_role,
                '<ul class="demo-btns">
                     <li>
                         <a href="' . base_url('user/edit/'.$r->id). '" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
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

    public function inbox() {
        $data['plugins'] = array('js/plugin/delete-table-row/delete-table-row.min.js', 
                                'js/plugin/summernote/summernote.min.js', 'js/plugin/select2/select2.min.js');
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/inbox/inbox.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('scripts/manager/inbox-script.php');
        $this->load->view('includes/footer.php'); 
    }

    public function calendar() {
		$data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/calendar/calendar.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/calendar-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

    /* DIVISION HEAD TEAM */
    public function myteam() {
        // $data['plugins'] = array('');
		$data['header_css'] = array('admin.css', 'myteam.css');
        $id = $this->session->userdata('user')->id;
        /* $data['tl_data'] = $this->managerModel->getboothPresidentByManager($id);
		
        if(is_array($data['tl_data'])) {
            foreach($data['tl_data'] as $k => $vot) {
                $vot->voter = $this->managerModel->votersByTeamLeader($vot->id);
                $vot->positive_voters = $this->managerModel->votersByStatusTL($vot->id, 12);
                $vot->neutral_voters = $this->managerModel->votersByStatusTL($vot->id, 14);
            }
        } */
		$data['cr_data'] = $this->managerModel->getDivisionIncharge($id);
            
			if(is_array($data['cr_data'])) {
                foreach($data['cr_data'] as $k => $vot)  {
                    $vot->voter = $this->managerModel->getVotersCountByDI($vot->id);
                    $vot->positive_voters = $this->managerModel->getVotersCountByDI($vot->id, array('v.voter_status' => 12));
                    $vot->neutral_voters = $this->managerModel->getVotersCountByDI($vot->id, array('v.voter_status' => 14));
                    $vot->ps_no = $this->managerModel->getDivisionInchargePS($vot->id);
                }
            }
		
		
        $data['header_css'] = array('admin.css', 'myteam.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
         }else {
            $this->load->view('manager/myteam/myteam.php', $data);
			
         }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');  
    }

    public function divisionincharge($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            //$data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            
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
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/myteam/division-incharge.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/manager/tl-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

    public function boothpresident($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
            //$data['qualification'] = $this->SeniorManagerModel->userQualification($id);
            
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
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/myteam/booth-president.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/manager/tl-script.php');
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
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/myteam/coordinator-profile.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			$this->load->view('scripts/manager/coordinator-script.php',$data);
            $this->load->view('scripts/manager/cf-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
     
			}
    }
    
    public function teamleader($id) {
        
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
            $data['tl_profile'] = $this->managerModel->userProfile($id);
			
            $data['cr_data'] = $this->managerModel->teamProfile($id);
			
			if($data['cr_data']!="") {
				foreach($data['cr_data'] as $k => $vot)  {
                    $vot->voter = $this->managerModel->getTotalVote($vot->id);
                    $vot->positive_voters = $this->managerModel->votersByStatusCr($vot->id, 12);
                    $vot->neutral_voters = $this->managerModel->votersByStatusCr($vot->id, 14);
                    //$vot->negartive_voters = $this->managerModel->votersByStatusCr($vot->id, 13);
				}
			}
            //$data['qualification'] = $this->managerModel->userQualification($id);
            $data['voters'] = $this->managerModel->votersByTeamLeader($id);
            $data['positive_voters'] = $this->managerModel->votersByStatusTL($id, 12);
            $this->load->view('includes/header.php', $data);
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/myteam/team-leader.php', $data);
				
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			$this->load->view('common/widget-script.php');
            $this->load->view('scripts/manager/tl-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
          
    }

    public function coordinator($id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
			$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                    'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                    'js/plugin/datatable-responsive/datatables.responsive.min.js','js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
			
            $data['profile'] = $this->managerModel->userProfile($id);
			
           // $data['qualification'] = $this->managerModel->userQualification($id);
            $data['voters'] = $this->managerModel->votersByUser($id);
            $data['positive_voters'] = $this->managerModel->votersByStatusCr($id, 12);
            
            $data['cr_data'] = $this->managerModel->getVolunteerByCoordinator($id);
			$data['pid'] = $this->managerModel->getParentId($id);
			
			if($data['cr_data']!="") {
				foreach($data['cr_data'] as $k => $vot)  {
                    $vot->voter = $this->managerModel->getVolunteerTotalVote($vot->id)->num_rows();
                    $vot->positive_voters = $this->managerModel->getVolunteerTotalVote($vot->id, array('v.voter_status' => 12))->num_rows();
                    $vot->neutral_voters = $this->managerModel->getVolunteerTotalVote($vot->id, array('v.voter_status' => 14))->num_rows();    
				}	
			}
			
			$data['family']=$this->SeniorManagerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->SeniorManagerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->SeniorManagerModel->getCitizenByrelation($id, 49);
            $data['known']=$this->SeniorManagerModel->getCitizenByrelation($id, 50);
            
            $data['vid']=$id;
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/myteam/coordinator-profile.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			 $this->load->view('scripts/manager/coordinator-script.php');
            $this->load->view('scripts/manager/cf-script.php');
			$this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }
	
	public function teammembers($role,$id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
             $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js','js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
			$data['family']=$this->managerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->managerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->managerModel->getCitizenByrelation($id, 49);
			$data['known']=$this->managerModel->getCitizenByrelation($id, 50);
			
			if($role==46)
			{$data['profile'] = $this->managerModel->getVolunteerProfile($id);}
		    if($role==3)
			{$data['profile'] = $this->managerModel->userProfile($id);}
						
			//$data['qualification'] = $this->managerModel->userQualification($id);
			$data['voters'] = $this->managerModel->votersByUser($id);
            $data['positive_voters'] = $this->managerModel->votersByStatusCr($id, 12);
            $data['cr_data'] = $this->managerModel->getMembersByVolunteer($id);
			$data['vid']=$id;
            $this->load->view('includes/header.php', $data);
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/myteam/team-members.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/manager/member-script.php',$data);
			$this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
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
        $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $data['location'] = $this->managerModel->getAllocatedLocation($id);
            $location_id = $data['location'][0]->lc_id;
            $data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
            $this->load->view('manager/village-analytics.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/v-analytics-script.php', $data);
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function analytics() {
        $data['header_css'] = array('admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$user_data = $this->session->userdata('user');
        $id = $user_data->id;
		$data['coordinator'] = $this->managerModel->getCoordinatorsByManager($id);
        $data['volunteer'] = $this->managerModel->getVolunteerByManager($id);

        $family_4 = 0; $family_6 = 0; $family_8 = 0; $family_10 = 0; $family_12 = 0;
        $relative_6 = 0; $relative_9 = 0; $relative_12 = 0; $relative_15 = 0;
        $friend_5 = 0; $friend_10 = 0; $friend_15 = 0; $friend_20 = 0;

		if(is_array($data['coordinator'])) {
            $allusers = array_merge($data['coordinator']);
            
            foreach($allusers as $u) {
                $u->family = $this->managerModel->getCitizenByrelation($u->id, 47);
                $u->relative = $this->managerModel->getCitizenByrelation($u->id, 48);
                $u->friend = $this->managerModel->getCitizenByrelation($u->id, 49);
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
		
        $data['total_voters'] = $this->managerModel->getVotersByManager($id)->num_rows();
        $data['pos_voters'] = $this->managerModel->getVotersByManager($id, array('v.voter_status' => 12))->num_rows();
        $data['neu_voters'] = $this->managerModel->getVotersByManager($id, array('v.voter_status' => 14))->num_rows();
		$data['neg_voters'] = $this->managerModel->getVotersByManager($id, array('v.voter_status' => 13))->num_rows();
		$data['male_voters'] = $this->managerModel->getVotersByManager($id, array('v.gender' => 4))->num_rows();
        $data['female_voters'] = $this->managerModel->getVotersByManager($id, array('v.gender' => 5))->num_rows();
        $data['other_gender'] = $this->managerModel->getVotersByManager($id, array('v.gender' => 77))->num_rows();
		$data['Schedule_Caste'] = $this->managerModel->getVotersByManager($id, array('v.category' => 30))->num_rows();
		$data['Schedule_Tribe'] = $this->managerModel->getVotersByManager($id, array('v.category' => 31))->num_rows();
		$data['Backward_Classes'] = $this->managerModel->getVotersByManager($id, array('v.category' => 32))->num_rows();
		$data['Other_BC'] = $this->managerModel->getVotersByManager($id, array('v.category' => 33))->num_rows();
		$data['Other_Category'] = $this->managerModel->getVotersByManager($id, array('v.category' => 34))->num_rows();
		$data['Forward_Classes'] = $this->managerModel->getVotersByManager($id, array('v.category' => 51))->num_rows();
		$data['Minority'] = $this->managerModel->getVotersByManager($id, array('v.category' => 52))->num_rows();
		
		$data['coordinators'] = $this->managerModel->getCoordinatorsByManager($id);
        $outstation = 0;
        $neighbourhood = 0;
        $mobileuser = 0;
        $smartphone = 0;
        $twowheeler = 0;
        $fourwheeler = 0;
        $television = 0;
        $fridge = 0;

        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value) {   
                $outstation_count = $this->managerModel->getOutstationByCoodinator($value->id);
                $outstation+=$outstation_count;
            }
            foreach($data['coordinators'] as $key=>$value){   
                $neighbourhood_count = $this->managerModel->getNeibourhoodByCoodinator($value->id);
                $neighbourhood+=$neighbourhood_count;
            }
            foreach($data['coordinators'] as $key=>$value) {   
                $mobileuser_count = $this->managerModel->getVisitTwoCount($value->id,105);
                $mobileuser+=$mobileuser_count;
            }
            foreach($data['coordinators'] as $key=>$value){   
                $smartphone_count = $this->managerModel->getVisitTwoCount($value->id,106);
                $smartphone+=$smartphone_count;
            }
            foreach($data['coordinators'] as $key=>$value){   
                $twowheeler_count = $this->managerModel->getVisitTwoCount($value->id,107);
                $twowheeler+=$twowheeler_count;
            }
            foreach($data['coordinators'] as $key=>$value){   
                $fourwheeler_count = $this->managerModel->getVisitTwoCount($value->id,107);
                $fourwheeler+=$fourwheeler_count;
            }
            foreach($data['coordinators'] as $key=>$value){   
                $television_count = $this->managerModel->getVisitTwoCount($value->id,120);
                $television+=$television_count;
            }
            foreach($data['coordinators'] as $key=>$value){   
                $fridge_count = $this->managerModel->getVisitTwoCount($value->id,128);
                $fridge+=$fridge_count;
            }    
        }
        $data['outstation'] = $outstation;
		$data['neighbourhood'] = $neighbourhood;
		$data['mobileuser'] = $mobileuser;
		$data['smartphone'] = $smartphone;
		$data['twowheeler'] = $twowheeler;
		$data['fourwheeler'] = $fourwheeler;
		$data['television'] = $television;
		$data['fridge'] = $fridge;
		
		$location_id = $this->session->userdata('user')->location_id;
		$data['jan_dhan'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'jan_dhan');
		$data['beti_bachao'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'beti_bachao'); 
		$data['make_india'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'make_india'); 
		$data['swatch_bhart'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'swatch_bhart'); 
		$data['digital_india'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'digital_india'); 
		$data['build_toilets'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'build_toilets'); 
		$data['one_pension'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'one_pension'); 
		$data['seventh_pay'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'seventh_pay'); 
		$data['suraksha_beema'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'suraksha_beema'); 
        $data['jeevan_jyoti_beema'] = $this->SeniorManagerModel->getModiSchemesCount($location_id, 'jeevan_jyoti_beema');
        
        //Performance
        $performance = $this->managerModel->getCoordPerformanceBySM($id);
        $poor_p = 0; $good_p = 0; $vgood_p = 0; $exc_p = 0; $iconic_p = 0;
        if(is_array($performance)) {
            foreach($performance as $p) {
                if($p->registered < 50) {
                    $poor_p += 1;
                }
                if($p->registered > 50 && $p->registered < 100) {
                    $good_p += 1;
                }
                if($p->registered > 101 && $p->registered < 140) {
                    $vgood_p += 1;
                }
                if($p->registered > 141 && $p->registered < 175) {
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
        $age = $this->managerModel->getVotersByManager($id);
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

        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/analytics/analytics.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/manager/analytics.php',$data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
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
        $this->load->view('manager/other/demographics.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        //$this->load->view('manager/analytics-script.php');
        $this->load->view('includes/footer.php'); 
    }

    public function assignTeam() {
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $id = $this->session->userdata('user')->id;
            $data['team_leaders'] = $this->managerModel->getUserByRole(18);
            $data['coordinators'] = $this->managerModel->getUserByRole(3);
            $data['location'] = $this->managerModel->getAllocatedLocation($id);
            $location_id = $data['location'][0]->lc_id;
            $data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
            $data['allocated_tl'] = $this->managerModel->getTlByManager($id);
            $data['users'] = $this->managerModel->getUsers();
            $this->load->view('manager/users/assign-team.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/assign-team-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

    public function allocatevillage() {
        if($this->input->post()) {
            $insert = $this->managerModel->allocateVillageByManager();
            if($insert) {
                $this->session->set_flashdata('allocate-village', '<div class="alert alert-success fade in"><strong>Success!</strong> Village has been allocated to team leader.</div>');
            }else {
                $this->session->set_flashdata('allocate-village', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('manager/assignteam'));
        }
    }

    public function allocatetl() {
        if($this->input->post()) {
            $insert = $this->managerModel->allocateTlByManager();
            if($insert) {
                $this->session->set_flashdata('allocate-tl', '<div class="alert alert-success fade in"><strong>Success!</strong> Team leader has been allocated to coordinator.</div>');
            }else {
                $this->session->set_flashdata('allocate-tl', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('manager/assignteam'));
        }
    }

    public function voters() {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css', 'myteam.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $this->load->view('manager/voterlist/voters.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/voter-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

    //datatable
    public function getvoters() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $id = $this->session->userdata('user')->id;
        $voter = $this->managerModel->getAllVoters($id);
        
        $data = array();
		
        foreach($voter->result() as $r) {
            $start++;
            $data[] = array(
                $start,
                $r->firstname . ' ' . $r->lastname,
                ($r->dob == '') ? $r->age : date_diff(date_create($r->dob), date_create('today'))->y,
                ($r->gender == 4) ? 'Male' : 'Female',
                $r->voter_id,
                $r->voter_status,
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
    public function getmembers($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
       // $id = $this->session->userdata('user')->id;
        $voter = $this->managerModel->getMembersByVolunteer($id);
       
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
            $start++;
            $data[] = array(
                $start,
                $r->firstname . ' ' . $r->lastname,
				'<img src="'. $img.'" height="50" width="50" align="center">',
				// $r->age,
                ($r->gender == 4) ? 'Male' : 'Female',
				$r->relationship,
                $r->voter_id,
                $r->voter_status,
				date('F j Y, h:i a', strtotime($r->date_registered)),
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

    public function userprofile() {
        $id = $this->session->userdata('user')->id;
        $data['profile'] = $this->managerModel->userProfile($id);
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/users/user-profile.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function events() {
        $data['my_events'] = $this->managerModel->getEvents(35);
        $data['x_events'] = $this->managerModel->getEvents(36);
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/events/events.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function evm() {
		$data['header_css'] = array('admin.css', 'dashboard.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$locationid = $this->session->userdata('user')->location_id;
		
		$data['contestants'] = $this->managerModel->getContestants($locationid);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/evm.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function voterlist() {
		$location_id = $this->session->userdata('user')->location_id;
        $data['villages'] =$this->managerModel->getAllVillageByVoterStatus($location_id);
        // echo '<pre>'; print_r($data['villages']); exit;
        $data['header_css'] = array('admin.css', 'priority-list.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        if($data['villages']) {
            $this->load->view('manager/digitalbooth/voter-list-village.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function voterlistcoordinator($location_id) {
        $data['coordinators'] = $this->SeniorManagerModel->getCoordinatorsByPS($location_id);
		$id = $this->session->userdata('user')->id;
		$data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css','myteam.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        if($data['coordinators']) {
            foreach($data['coordinators'] as $k => $mg) {
                $mg->total_member = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($mg->id)->num_rows();
                $mg->total_attendant = $this->SeniorManagerModel->getTotalAttendant($mg->id)->num_rows();
            }
            $this->load->view('manager/digitalbooth/voter-list-coordinator.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
	    
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
        $this->load->view('includes/footer.php'); 
    }
	
	public function voterpriority($id) {
        
		$data['priority_list'] = $this->SeniorManagerModel->getVolunteerByCoordinator($id);
		$data['mygrouptotal'] = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($id)->num_rows();
		$data['mygroupattendant'] = $this->SeniorManagerModel->getTotalAttendant($id)->num_rows();
		if($data['priority_list'] != "") { 
            foreach($data['priority_list'] as $k => $mg)  {
                $mg->total_member = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($mg->id)->num_rows();
                $mg->total_attendant = $this->SeniorManagerModel->getTotalAttendant($mg->id)->num_rows();
            }
        }
        // echo '<pre>'; print_r($data['mygrouptotal']); exit; 
		$data['header_css'] = array('admin.css', 'priority-list.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
	    $this->load->view('manager/digitalbooth/db-voter-list.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
        $this->load->view('includes/footer.php'); 
    }
	public function mygroupmembers($id) {
		
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
            $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            //check permission
			$data['vid']=$id;
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('manager/digitalbooth/mygroup-team-members.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/manager/mygroup-member-script.php',$data);
            $this->load->view('includes/footer.php');
        }
    }
	
	
	//datatable
    public function getmygroupmembers($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $voter = $this->SeniorManagerModel->getMyGroupMembersByVolunteer($id);
       //echo '<pre>'; print_r($voter->result()); exit;
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
				'<img src="'. $img. '" height="50" width="50" align="center">',
				$r->attend,
				($r->attend_time != '') ? date('F j, Y g:i a', strtotime($r->attend_time)) : '',
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

    public function prioritylist($id, $list) {
        if(isset($id) && isset($list)) {
            $data['coordinator'] = $this->managerModel->userprofile($id);
            $data['list'] = $list;
            $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
            $this->load->view('includes/header.php', $data);
            $this->load->view('manager/top-nav.php');
            $this->load->view('manager/side-nav.php');
            $this->load->view('manager/digitalbooth/cr-pr-list.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('manager/shortcut-nav.php');
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
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/other/voter-mobilisation.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
        $this->load->view('includes/footer.php');
    }

    public function pollingbooth() {
        $booth_agents = $this->managerModel->getPSMember($this->_id, 37);
        $data['agents'] = $booth_agents;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/polling-booth.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

    public function assignpollingstation() {
        $data['header_css'] = array('jquery-confirm.min.css','admin.css');
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-confirm/jquery-confirm.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
        }else {
            $id = $this->session->userdata('user')->id;
            $ps = $this->managerModel->getPollingStationsByManager($id);
            $data['polling_station'] = $ps;
            $request = $this->managerModel->getDBRoleRequest($id);
            // echo '<pre>'; print_r($request); exit;
           // $data['booth_agent'] = $this->managerModel->getPSMemberByManager($id, 37);
			$this->load->view('manager/users/assign-polling-station.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/assign-polling-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function getpsmember() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $id = $this->session->userdata('user')->id;
        $users = $this->managerModel->getDBRoleRequest($id);
        // echo '<pre>'; print_r($users->result()); exit;
        $data = array();
        foreach($users->result() as $r) {
            if($r->status == 1) {
                $status = 'Booth: '. $r->booth_no;
                $action = '';
            }elseif($r->status == 0) {
                $status = 'Pending';
                $action = '<a href="" class="btn btn-success btn-xs accept" data-ps="'.$r->pid.'" data-psname="'.$r->ps_name.'" data-vid="'.$r->vid.'" data-rid="'.$r->role_id.'" data-role="'.$r->role.'" title="Accept"><i class="fa fa-check" aria-hidden="true"></i></a> 
                    <a href="" class="btn btn-danger btn-xs reject" title="Reject" data-request="'.$r->rid.'"><i class="fa fa-times" aria-hidden="true"></i></i></a>';
            }
            $start++;
            $data[] = array(
                $start,
                $r->first_name . ' ' . $r->last_name,
                $r->firstname . ' ' . $r->lastname,
                $r->ps_no . ' - ' . $r->ps_name,
                $r->role,
                $status,
                $action,
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

    public function assignpsmember() {
        if($this->input->post()) {
            $data = $this->input->post();
            $assign_member = $this->managerModel->allocatePSMember($data);
            if($assign_member) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    }

    public function declinerolerequest() {
        if($this->input->post()) {
            $rid = $this->input->post('rid');
            $decline = $this->managerModel->declinePSMemberRequest($rid);
            if($decline) {
                echo json_encode(true);
            }else {
                echo json_encode('failure');
            }
        }
    }

    public function allocateBAgent() {
        if($this->input->post()) {
            $insert = $this->managerModel->allocateBAgentByManager();
            if($insert) {
                $this->session->set_flashdata('allocate-agent', '<div class="alert alert-success fade in"><strong>Success!</strong> Polling Station has been allocated to Booth Agent.</div>');
            }else {
                $this->session->set_flashdata('allocate-agent', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('manager/assignpollingstation'));
        }
    }
	
	public function allocateObserver() {
        if($this->input->post()) {
            $insert = $this->managerModel->allocateObserverByManager();
            if($insert) {
                $this->session->set_flashdata('allocate-agent', '<div class="alert alert-success fade in"><strong>Success!</strong> Polling Station has been allocated to Observer.</div>');
            }else {
                $this->session->set_flashdata('allocate-agent', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('manager/assignpollingstation'));
        }
    }

    public function pollingstation() {
        if($this->input->post()) {
            $ps_id = $this->input->post('polling-station');
            $ps = $this->managerModel->getPollingStation($ps_id);
            $ps_img = $this->managerModel->getPollingStationImage($ps_id);
            $data['ps_details'] = $ps;
            $data['ps_img'] = $ps_img;
           
        }
        $data['lc_id'] = $this->session->userdata('user')->location_id;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/superbox/superbox.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/polling-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/ps-script.php', $data);
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

   /*  public function livebooth() {
        if($this->input->post()) {
            $ps_id = $this->input->post('polling-station');
            $ps = $this->managerModel->getPollingStation($ps_id);
            $data['bth_agent'] = $this->managerModel->getPollingStationMember($ps[0]->id, 37);
            $data['bth_observer'] = $this->managerModel->getPollingStationMember($ps[0]->id, 38);
            $data['coordinators'] = $this->managerModel->getCoordinatorsByPS($ps[0]->lc_id);
            $data['ps_details'] = $ps; 
        }
        $data['lc_id'] = $this->session->userdata('user')->location_id;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'live-booth.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/live-booth.php', $data); //content page
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/live-booth-script.php', $data); //page script
        $this->load->view('includes/footer.php');
    } */
	//Branding
	 public function branding() {
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/other/branding.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	//Ideas
	 public function ideas() {
		$id = $this->session->userdata('user')->id;
        $data['header_css'] = array('admin.css', 'slide.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $data['tl_data'] = $this->managerModel->teamProfile($id);
        $this->load->view('manager/other/ideas.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function ecommerce() {
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/other/ecommerce.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
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
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/other/estimated-cost.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('manager/other/estimatedcost-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	//BOOTH MANAGEMENT CHANGES
	public function boothmanagement() {
       
        $data['header_css'] = array('admin.css', 'priority-list.css', 'cr-pr-list.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
		$location_id = $this->session->userdata('user')->location_id;
        // $data['villages'] = $this->managerModel->getAllVillageByVoterStatus($locationid);
        // echo '<pre>'; print_r($data['ps']); exit;
        $data['villages'] = $this->managerModel->getPollingStationsByMandals($location_id);
        // echo '<pre>'; print_r($data['ps']); exit;
        /* $data['tl_data'] = $this->managerModel->getTeamLeaderData($id);
		
		foreach($data['tl_data'] as $key=>$value){   
		    $value->total_coordinator = $this->SeniorManagerModel->getTotalCoordinatorsByTL($value->id);
			
			} */
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/booth-management.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }

	public function managerpollingstation($id) {
        $data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
        $data['ps_details'] = $this->SeniorManagerModel->getBoothPSByVillageId($id);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/manager-polling-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
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
		$data['pl_coordinator'] = $this->SeniorManagerModel->getCoordinatorsByVillageId($id);
		if($data['pl_coordinator'] != "") {
            foreach($data['pl_coordinator'] as $k => $vot)  {
                $vot->voter = $this->SeniorManagerModel->getTotalVote($vot->user_id);
                $vot->positive_voters = $this->SeniorManagerModel->votersByStatusCr($vot->user_id, 12);
                $vot->neutral_voters = $this->SeniorManagerModel->votersByStatusCr($vot->user_id, 14);
            }	
        }
		$data['pl_boothagent'] = $this->SeniorManagerModel->getPollingStationMember($id, 37);
        $data['pl_boothobserver'] = $this->SeniorManagerModel->getPollingStationMember($id, 38);
		$data['pl_agent'] = $this->SeniorManagerModel->getPollingAgent($id);
        // $data['ps_img'] = $this->SeniorManagerModel->getPollingStationImage($id);
		
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        if($data['tl_profile'] || $data['pl_coordinator']) {
            $this->load->view('manager/digitalbooth/polling-station-details.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
        $this->load->view('includes/footer.php'); 
    }
	public function tmpollingstation($id) {
        $data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
		
        $data['ps_details'] = $this->managerModel->getPollingStation($id);
		//echo $this->db->last_query();exit;
		$data['tl_coordinator'] = $this->managerModel->getCoordinatorsByVillageId($id);
		$data['bth_agent'] = $this->managerModel->getBoothAgentByVillageId($id, 37);
		$data['bth_observer'] = $this->managerModel->getBoothAgentByVillageId($id, 38);
		
        $data['ps_img'] = $this->managerModel->getPollingStationImage($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/tm-polling-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function livebooth() {
        
        $data['lc_id'] = $this->session->userdata('user')->location_id;
        $data['header_css'] = array('admin.css', 'priority-list.css', 'live-booth.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$id = $this->session->userdata('user')->id;
		$location_id= $this->session->userdata('user')->location_id;
		$registration = $this->SMDashboardModel->getLiveTotalRegistrationMandal($location_id)->result();
		$data['total_register'] = count($registration);
		$data['total_attendant'] = $this->SMDashboardModel->getByMandalLiveTotalAttendant($location_id, array())->num_rows();
		$data['total_male'] = $this->SMDashboardModel->getByMandalLiveTotalAttendant($location_id, array('v.gender' => 4))->num_rows();
		$data['total_female'] = $this->SMDashboardModel->getByMandalLiveTotalAttendant($location_id, array('v.gender' => 5))->num_rows();
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/live-booth.php', $data); //content page
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/live-booth-mandal-script.php', $data); //page script
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    } 
	
	public function liveboothteamleader($location_id) {
        $data['header_css'] = array('admin.css');
		$data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  'js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
		//$location_id= $this->session->userdata('user')->location_id;
		$data['pollingstation']=$this->SMDashboardModel->getPollingStationsByMandals($location_id);
		if($data['pollingstation']!="") {  
            foreach($data['pollingstation'] as $k => $mg)  {
                $mg->total_t = $this->SMDashboardModel->getLiveTotalRegistrationVillage($mg->ps_no)->num_rows();
                $mg->total_at = $this->SMDashboardModel->getLiveTotalAttendantVillage($mg->ps_no, array())->num_rows();
                $mg->total_m = $this->SMDashboardModel->getLiveTotalAttendantVillage($mg->ps_no, array('v.gender' => 4))->num_rows();
                $mg->total_f = $this->SMDashboardModel->getLiveTotalAttendantVillage($mg->ps_no, array('v.gender' => 5))->num_rows();
            }
        }  
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/live-booth-teamleader.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/live-booth-village-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	public function liveboothcoordinator() {
        $data['header_css'] = array('admin.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/live-booth-coordinator.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/live-booth-coordinate-script.php');
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
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/digitalbooth/ps-live-booth.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/manager/live-booth-coordinate-script.php',$data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
    // Generate password
    public function demo() {
        $password_dm = 'janasena@avani';
        $salt = uniqid();
        $password = password_hash($password_dm.$salt, PASSWORD_BCRYPT);
        $password_verify = password_verify($password_dm.$salt, $password);
        echo  $salt. '<br>' . $password . '<br>';

        var_dump($password_verify);
        exit;
    }
	//SETTINGS
	public function settingspw() {
        $id = $this->session->userdata('user')->id;
        $data['profile'] = $this->managerModel->userProfile($id);
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/other/settings.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    
	public function tasks() {
        $data['header_css'] = array('admin.css','bootstrap-datetimepicker.css');
		$data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/markdown/markdown.min.js',
								'js/plugin/markdown/to-markdown.min.js','js/plugin/markdown/bootstrap-markdown.min.js',
								'js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
		$data['user_roles'] = $this->managerModel->getAssignRole();
		$data['user_groups'] = $this->managerModel->getGroups();
		$data['reciverid'] = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/task/tasks.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/common/editor-script.php'); 
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function allocateTaskByGroup() {
        if($this->input->post()) {
				$group = 62;
				$insert = $this->managerModel->allocateGroupTaskBySeniorManager($group);
				if($insert) {
					
					$this->session->set_flashdata('assign-grouptask', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created .</div>');
				}else {
					
					$this->session->set_flashdata('assign-grouptask', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
				}
				redirect(base_url('Manager/tasks'));
        }
    }
	
	public function allocateTaskByIndividual() {
		
        if($this->input->post()) {

			$group = 63;
           
		   $insert = $this->managerModel->allocateGroupTaskBySeniorManager($group);
            if($insert) {
                $this->session->set_flashdata('assign-individual', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created.</div>');
            }else {
                $this->session->set_flashdata('assign-individual', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
           redirect(base_url('Manager/tasks'));
        }
    }
	
	public function allocateMyTasks() {
        if($this->input->post()) {
			$group = 64;
         
			$insert = $this->managerModel->allocateGroupTaskBySeniorManager($group);
            if($insert) {
                $this->session->set_flashdata('assign-mytask', '<div class="alert alert-success fade in"><strong>Success!</strong>Task Created.</div>');
            }else {
                $this->session->set_flashdata('assign-mytask', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
            }
            redirect(base_url('Manager/tasks'));
        }
    }
	
	public function getallevents() {
		 // Our Start and End Dates
        $start = $this->input->get("start");
        $end = $this->input->get("end");
        $startdt = new DateTime('now'); // setup a local datetime
        $startdt->setTimestamp($start); // Set the date based on timestamp
        $start_format = $startdt->format('Y-m-d');
        $enddt = new DateTime('now'); // setup a local datetime
        $enddt->setTimestamp($end); // Set the date based on timestamp
        $end_format = $enddt->format('Y-m-d');
        $id = $this->session->userdata('user')->id;
        //echo $id;exit;
        $events = $this->managerModel->getMyTasks($id);
        // echo '<pre>'; print_r($events); exit;    
        // echo '<pre>'; var_dump($events); exit;
        
        $data_events = array();
		if(is_array($events)) {
            foreach($events as $r) {
                $task_type = '';
                if($r->task_group == 64) {
                    $task_type = 'Self';
                }
                if($r->task_group == 62) {
                    $task_type = 'Group Task';
                }
                if($r->task_group == 63) {
                    $task_type = 'Individual Task';
                }
    
                if($r->created_by == $id && $r->task_group == 64) {
                    $task_by = '<p>Assigned To : Self';
                }elseif($r->created_by == $id && $r->task_group == 62) {
                    $task_by = ($r->receiver_id == 67) ? '<p>Assigned To : All Team Leaders' : '<p>Assigned To : All Coordinators';    
                }elseif($r->created_by == $id && $r->task_group == 63) {
                    $task_by = ($r->user_role == 3) ? '<p>Assigned To : ' . $r->first_name . ' ' . $r->last_name . ' (' . $r->role . ' )' : 
                    '<p>Assigned To : ' . $r->first_name . ' ' . $r->last_name . ' (' . $r->role . ' )';
                }else {
                    $task_by = '<p>Assigned By : ' .$r->first_name . ' ' . $r->last_name . ' (' . $r->role . ' )';
                }
    
                if($r->created_by == $id && $r->task_group == 62) {
                    $classname = 'bg-color-red txt-color-white'; //Group event
                }
                if($r->created_by == $id && $r->task_group == 63) {
                    $classname = 'bg-color-greenLight txt-color-white'; //Member event
                }
                if($r->receiver_id == $id || $r->receiver_id == 66) {
                    $classname = 'bg-color-blue txt-color-white'; //Self task event
                } 
                $data_events[] = array(
                    "id" => $r->id,
                    "title" => $r->task_name,
                    "description" => $r->task_description . '<p>Task Type :' . $task_type.'</p>' . $task_by . '</p>',
                    "end" => $r->date_to,
                    "start" => $r->date_from,
                    "className" => $classname
                );
            }
        }
        

        echo json_encode(array("events" => $data_events));
        exit();
	}
	
	public function userprofileforc() {
        $data['header_css'] = array('admin.css','bootstrap-datetimepicker.css');
		$data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/summernote/moment.js','js/plugin/summernote/bootstrap-datetimepicker.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/users/userprofileforc.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function forgotpw() {
        $data['header_css'] = array('admin.css','bootstrap-datetimepicker.css');
		$data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/summernote/moment.js','js/plugin/summernote/bootstrap-datetimepicker.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/other/forgotpw.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    
	public function userprof() {
        $data['header_css'] = array('admin.css','bootstrap-datetimepicker.css');
		$data['plugins'] = array('js/plugin/summernote/summernote.min.js','js/plugin/summernote/moment.js','js/plugin/summernote/bootstrap-datetimepicker.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('manager/top-nav.php');
        $this->load->view('manager/side-nav.php');
        $this->load->view('manager/users/user-profile-new.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    
}