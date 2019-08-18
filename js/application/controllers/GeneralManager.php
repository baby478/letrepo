<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GeneralManager extends CI_Controller {
    private $allocation_status;
    private $_id;
	

    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 57) {
            redirect(base_url());
        }
		
        //$this->_id = $this->session->userdata('user')->id;
       // $this->_alloc_status();
	    
		// load pagination library
        $this->load->library('pagination');
		$this->load->helper("url");
		$this->load->model('GeneralManagerModel');
        $this->load->model('loginModel');
        $this->load->model('SeniorManagerModel');
		$this->load->model('CoordinatorModel');
		$this->load->model('userModel');
		$this->load->model('managerModel');
        $this->load->model('apiModel');
        $this->load->model('adminModel');
		 $this->load->model('SMDashboardModel');
        $this->_id = $this->session->userdata('user')->id;
       // $this->_alloc_status();
    }

        /* public function index() {
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  );
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/gmdashboard.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    } */
	
	public function index() {
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  );
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		$data['constit'] = $this->GeneralManagerModel->getAllConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        //$this->load->view('generalmanager/gmdashboard.php', $data);
		 $this->load->view('generalmanager/assem-constitueny.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function gmdashboard($lid) {
		$user_data = $this->session->userdata('user');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins']  = array('js/plugin/flot/jquery.flot.cust.min.js', 'js/plugin/flot/jquery.flot.resize.min.js', 
                                  'js/plugin/flot/jquery.flot.time.min.js', 'js/plugin/flot/jquery.flot.tooltip.min.js',
                                  'js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', 'js/plugin/vectormap/jquery-jvectormap-world-mill-en.js',
                                  );
        $data['lid'] = $lid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/gdashboard.php', $data);
		// $this->load->view('generalmanager/assem-constitueny.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function constituency($service) {
		if( $service == 'dasconstituency' || $service == 'recruitmentconst' || $service == 'registered' || $service == 'analyticconsy')
		{
        $data['header_css'] = array('admin.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		$data['constit'] = $this->GeneralManagerModel->getAllConstituence($location);
		//echo $this->db->last_query();exit;
		if($service == 'dasconstituency') {
                $data['url'] = base_url('generalmanager/pollingstationlist');
            }
			if($service == 'recruitmentconst') {
                $data['url'] = base_url('generalmanager/recruitment');
            }
			if($service == 'registered') {
                $data['url'] = base_url('generalmanager/registerforrecruit');
            }
			if($service == 'analyticconsy') {
                $data['url'] = base_url('generalmanager/analytics');
            }
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/constitueny-all.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		}
    }
	
	public function mandals($id) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['mandals'] = $this->GeneralManagerModel->mandalByConstituency($id);
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	public function dasconstituency() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		$data['constit'] = $this->GeneralManagerModel->getAllConstituence($location);
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/constitueny-all.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function pollingstationlist($id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['ps'] = $this->GeneralManagerModel->getPollingStaionsByDivision($id);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        if($data['ps']) {
            $this->load->view('generalmanager/polling-station-list.php', $data);
        }else {
            $data['content'] = 'No Content.';
            $this->load->view('common/no-data.php', $data);
        }
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function myteam($lid) {
        $data['header_css'] = array('admin.css', 'myteam.css');
        //$id = $this->session->userdata('user')->location_id;
        $data['tl_data'] = $this->GeneralManagerModel->teamManager($lid);
		$count_voter   = $this->GeneralManagerModel->teamManager($lid);
		if($data['tl_data']!="")
			{ 
				foreach($data['tl_data'] as $k => $vot) 
				{
				$vot->voter = $this->GeneralManagerModel->getTotalVotersBySManager($vot->id);
				$vot->positive_voters = $this->GeneralManagerModel->getPositiveNegVotersBySManager($vot->id, 12);
				$vot->neutral_voters = $this->GeneralManagerModel->getPositiveNegVotersBySManager($vot->id, 14);
				}
			} 
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
         }else {
            $this->load->view('generalmanager/myteam.php', $data);
         }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php');  
    }
	
	public function managerdetails($id) {
        if(isset($id)) {
            $data['header_css'] = array('admin.css', 'myteam.css');
            $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
			
			$data['tl_data'] = $this->GeneralManagerModel->DivisionHeadManager($id);
		
			$data['tl_profile'] = $this->GeneralManagerModel->userProfile($id);

            // $data['qualification'] = $this->GeneralManagerModel->userQualification($id);
			$data['voters'] = $this->GeneralManagerModel->getTotalVotersBySManager($id);
			
            $data['positive_voters'] = $this->GeneralManagerModel->getPositiveNegVotersBySManager($id, 12);
			$data['cr_data'] = $this->GeneralManagerModel->teamProfile($id);
			if($data['tl_data']!="")
			{
				foreach($data['tl_data'] as $k => $vot) 
				{
				$vot->voter = $this->GeneralManagerModel->getTotalVotersByManager($vot->id);  
				$vot->positive_voters = $this->GeneralManagerModel->getPositiveNegVotersByManager($vot->id, 12);
				$vot->neutral_voters = $this->GeneralManagerModel->getPositiveNegVotersByManager($vot->id, 14);
				} 
			}
			$this->load->view('includes/header.php', $data);
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/myteam/manager-details.php', $data);
            }         
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			//$this->load->view('common/widget-script.php');
            $this->load->view('scripts/gm/tl-script.php');
			$this->load->view('scripts/common/modal-script.php');
            $this->load->view('includes/footer.php');
        
		} 
    }
	
	public function tlcoordinator($id){
		
		$data['header_css'] = array('admin.css', 'myteam.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
		
		 $data['profile'] = $this->GeneralManagerModel->userProfile($id);
		
		// $data['qualification'] = $this->GeneralManagerModel->userQualification($id);
		$data['voters'] = $this->GeneralManagerModel->getTotalVotersByManager($id);
        $data['positive_voters'] = $this->GeneralManagerModel->getPositiveNegVotersByManager($id, 12);
		
        $data['tl_coordinator'] = $this->GeneralManagerModel->getTlByManager($id);
		
		if($data['tl_coordinator']!="")
			{
				foreach($data['tl_coordinator'] as $k => $vot) 
				{
				$vot->voter = $this->GeneralManagerModel->votersByTeamLeader($vot->id);
				$vot->positive_voters = $this->GeneralManagerModel->votersByStatusTL($vot->id, 12);
				$vot->neutral_voters = $this->GeneralManagerModel->votersByStatusTL($vot->id, 14);
				}
				
			} 
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        //check permission
        if($this->allocation_status === FALSE) {
            $this->load->view('common/no-access.php');
         }else {
            $this->load->view('generalmanager/teamlead-coordinator.php', $data);
         }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/tl-script.php');
		$this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php');  
			
	}
	
	public function coordinator($id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
			$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
									
			$data['profile'] = $this->GeneralManagerModel->userProfile($id);
            // $data['qualification'] = $this->GeneralManagerModel->userQualification($id);
            $data['voters'] = $this->GeneralManagerModel->votersByTeamLeader($id);
            $data['positive_voters'] = $this->GeneralManagerModel->votersByStatusTL($id, 12);
            $data['cr_data'] = $this->GeneralManagerModel->getCoordinatorsByTeamleader($id);
			//$data['pid'] = $this->SeniorManagerModel->getParentId($id);
			if($data['cr_data']!="")
			{
				foreach($data['cr_data'] as $k => $vot) 
				{
				$vot->voter = $this->GeneralManagerModel->getTotalVote($vot->id);
				$vot->positive_voters = $this->GeneralManagerModel->votersByStatusCr($vot->id, 12);
				$vot->neutral_voters = $this->GeneralManagerModel->votersByStatusCr($vot->id, 14);
				
				}
				
			}						
			$this->load->view('includes/header.php', $data);
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/coordinator-profile.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			 $this->load->view('scripts/manager/coordinator-script.php');
            $this->load->view('scripts/seniormanager/cf-script.php');
			$this->load->view('scripts/common/modal-script.php');
            $this->load->view('includes/footer.php');
        }
	}
	
	public function coordinatorp($id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
			$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
            'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
													
            $data['profile'] = $this->GeneralManagerModel->userProfile($id);
            // $data['qualification'] = $this->GeneralManagerModel->userQualification($id);
            $data['voters'] = $this->GeneralManagerModel->votersByUser($id);
            $data['positive_voters'] = $this->GeneralManagerModel->votersByStatusCr($id, 12);
            $data['cr_data'] = $this->GeneralManagerModel->getVolunteerByCoordinator($id);
			$data['mymembers'] = $this->GeneralManagerModel->getMyGroupMembersByVolunteer($id);
			
			if($data['cr_data']!="") {
                foreach($data['cr_data'] as $k => $vot) {
                    $vot->voter = $this->GeneralManagerModel->getVolunteerTotalVote($vot->id)->num_rows();
                    $vot->positive_voters = $this->GeneralManagerModel->getVolunteerTotalVote($vot->id,array('v.voter_status' => 12))->num_rows();
                    $vot->neutral_voters = $this->GeneralManagerModel->getVolunteerTotalVote($vot->id, array('v.voter_status' => 14))->num_rows();
                }
			}
			$data['family']=$this->GeneralManagerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->GeneralManagerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->GeneralManagerModel->getCitizenByrelation($id, 49);
			$data['known']=$this->GeneralManagerModel->getCitizenByrelation($id, 50);
            $data['vid'] = $id;
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
               $this->load->view('generalmanager/coordinator-p.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			$this->load->view('scripts/gm/coordinator-script.php',$data);
			$this->load->view('scripts/gm/cf-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }
	
	public function analyticconsy() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		$data['constit'] = $this->GeneralManagerModel->getAllConstituence($location);
		
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/analytic-constitueny.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function analytics($lid) {
		$data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$user_data = $this->session->userdata('user');
        $id = $user_data->id;
		//$lid = $user_data->location_id;
     
        $data['coordinator'] = $this->GeneralManagerModel->getCoordinatorsBySeniorManager($lid);
		
       // $data['volunteer'] = $this->GeneralManagerModel->getVolunteerBySeniorManager($id);
      //  echo $this->db->last_query();exit;
        $family_4 = 0; $family_6 = 0; $family_8 = 0; $family_10 = 0; $family_12 = 0;
        $relative_6 = 0; $relative_9 = 0; $relative_12 = 0; $relative_15 = 0;
        $friend_5 = 0; $friend_10 = 0; $friend_15 = 0; $friend_20 = 0;

        if(is_array($data['coordinator'])) {
            $allusers = array_merge($data['coordinator']);
            
            foreach($allusers as $u) {
                $u->family = $this->GeneralManagerModel->getCitizenByrelation($u->id, 47);
				
                $u->relative = $this->GeneralManagerModel->getCitizenByrelation($u->id, 48);
                $u->friend = $this->GeneralManagerModel->getCitizenByrelation($u->id, 49);
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

        $data['total_voters'] = $this->GeneralManagerModel->getVotersByManager($lid)->num_rows();
        $data['pos_voters'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.voter_status' => 12))->num_rows();
        $data['neu_voters'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.voter_status' => 14))->num_rows();
		$data['neg_voters'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.voter_status' => 13))->num_rows();
		$data['male_voters'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.gender' => 4))->num_rows();
        $data['female_voters'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.gender' => 5))->num_rows();
        $data['other_gender'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.gender' => 77))->num_rows();
		$data['Schedule_Caste'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 30))->num_rows();
		$data['Schedule_Tribe'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 31))->num_rows();
		$data['Backward_Classes'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 32))->num_rows();
		$data['Other_BC'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 33))->num_rows();
		$data['Other_Category'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 34))->num_rows();
		$data['Forward_Classes'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 51))->num_rows();
		$data['Minority'] = $this->GeneralManagerModel->getVotersByManager($lid, array('v.category' => 52))->num_rows();
		
		$data['coordinators'] = $this->GeneralManagerModel->getCoordinatorsByManager($id);
        $outstation = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value) {   
                $outstation_count = $this->GeneralManagerModel->getOutstationByCoodinator($value->id);
                $outstation+=$outstation_count;
		    }
        }
		
		$data['outstation'] = $outstation;
		
        $neighbourhood = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $neighbourhood_count = $this->GeneralManagerModel->getNeibourhoodByCoodinator($value->id);
                $neighbourhood+=$neighbourhood_count;
            }
        }
		
		$data['neighbourhood'] = $neighbourhood;
		
        $mobileuser = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $mobileuser_count = $this->GeneralManagerModel->getVisitTwoCount($value->id,105);
                $mobileuser+=$mobileuser_count;
            }
        }
		
		$data['mobileuser'] = $mobileuser;
		
        $smartphone = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $smartphone_count = $this->GeneralManagerModel->getVisitTwoCount($value->id,106);
                $smartphone+=$smartphone_count;
            }
        }
		
		$data['smartphone'] = $smartphone;
		
        $twowheeler = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $twowheeler_count = $this->GeneralManagerModel->getVisitTwoCount($value->id,107);
                $twowheeler+=$twowheeler_count;
            }
        }
		
		$data['twowheeler'] = $twowheeler;
		
        $fourwheeler = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $fourwheeler_count = $this->GeneralManagerModel->getVisitTwoCount($value->id,107);
                $fourwheeler+=$fourwheeler_count;
            }
        }
		
		$data['fourwheeler'] = $fourwheeler;
		
        $television = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $television_count = $this->GeneralManagerModel->getVisitTwoCount($value->id,120);
                $television+=$television_count;
            }
        }
		
		$data['television'] = $television;
		
        $fridge = 0;
        if(is_array($data['coordinators'])) {
            foreach($data['coordinators'] as $key=>$value){   
                $fridge_count = $this->GeneralManagerModel->getVisitTwoCount($value->id,128);
                $fridge+=$fridge_count;
            }
        }
		
		$data['fridge'] = $fridge;
		
		$location_id = $this->session->userdata('user')->location_id;
        
        //Performance
        $performance = $this->GeneralManagerModel->getCoordPerformanceBySM($lid);
		
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
        $age = $this->GeneralManagerModel->getVotersByManager($lid);

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
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/analytics.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
       // $this->load->view('scripts/gm/anaiytic-reg-script.php');
       // $this->load->view('scripts/gm/chart-script.php');
	    $this->load->view('scripts/gm/analytics.php');
        $this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php');
	}

	/*--------------------------------------------------------------------------*/
	
	public function recruitmentconst() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		$data['constit'] = $this->GeneralManagerModel->getAllConstituence($location);
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/recruitment-constituency.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitment($locid) {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
		$id = $this->session->userdata('user')->id;
		$lid = $this->session->userdata('user')->location_id;
		
		$divisionhead = $this->GeneralManagerModel->getTotalDivheadBySM($locid)->result();	
		$data['total_divhead'] = count($divisionhead);
		$manager = $this->GeneralManagerModel->getTotalMandalBySM($locid)->result();
		$data['total_mandals'] = count($manager);
		$boothobserver= $this->GeneralManagerModel->getBoothObserverCount($locid)->result();
		$data['total_boothobserver'] = count($boothobserver);
		$tl = $this->GeneralManagerModel->getTotalTeamLeaderBySM($locid)->result();
		$data['total_Tm'] = count($tl);
		$coordinator = $this->GeneralManagerModel->getTotalCoordinatorBySM($locid)->result();
		$data['total_coordinator'] = count($coordinator);
		$volunteer = $this->GeneralManagerModel->getTotalVolunteerBySM($locid)->result();	
		
		$data['total_volunteer'] = count($volunteer);
		$telecaller = $this->SMDashboardModel->getTotalTelecallerBySM($id)->result();
		$data['total_telecaller'] = count($telecaller);
		//echo "<pre>";print_r($data);exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        //$this->load->view('generalmanager/dashboard/team-recruitment1.php', $data);
		$this->load->view('generalmanager/team-analysis/team-recruitment.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/recruitment-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }

	public function registration($lid) {
     
		$data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
        $this->load->view('includes/header.php', $data);
		$id = $this->session->userdata('user')->id;
		$registration = $this->SMDashboardModel->getTotalRegistration($id)->result();
		$data['total_register'] = count($registration);
		//$lid = $this->session->userdata('user')->location_id;
		//$data['mandals']=$this->SMDashboardModel->getTotalMandalBySM($id)->result();
		$data['mandals']=$this->SMDashboardModel->getMandalsRegist($lid)->result();
		
		
				if($data['mandals']!="")
			{ 
				foreach($data['mandals'] as $k => $mg) 
				{
				$mg->total_register = $this->SMDashboardModel->getTotalRegistrationMandal($mg->id)->result();
				$mg->total_r = count($mg->total_register);
				}
			} 

        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/register-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('scripts/seniormanager/registration-script.php', $data);
        $this->load->view('includes/footer.php');
    }
	
	
	/*==========================================*/
	public function settingspw() {
        $id = $this->session->userdata('user')->id;
        $data['header_css'] = array('admin.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/settings.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

	public function managerpollingstation($id) {
        $data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
		$data['ps_details'] = $this->GeneralManagerModel->getBoothPSByVillageId($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/manager-polling-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php'); 
    }
	
	public function pollingstationdetails($id,$location) {
        $data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
		$data['tl_profile'] = $this->GeneralManagerModel->getTeamleaderByVillageId($location);
		$data['psdl'] = $this->GeneralManagerModel->getPollingStation($id);
		
        $data['total_voters'] = $this->GeneralManagerModel->getPollingStationCount($id)->num_rows();
		$data['male_voters'] = $this->GeneralManagerModel->getPollingStationCount($id, array('v.gender' => 4))->num_rows();
		$data['female_voters'] = $this->GeneralManagerModel->getPollingStationCount($id, array('v.gender' => 5))->num_rows();
		$data['other_voters'] = $this->GeneralManagerModel->getPollingStationCount($id, array('v.gender' => 0))->num_rows();
		
		$data['pl_coordinator'] = $this->GeneralManagerModel->getCoordinatorsByVillageId($id);
		
		if($data['pl_coordinator']!="")
			{
				foreach($data['pl_coordinator'] as $k => $vot) 
				{
				$vot->voter = $this->GeneralManagerModel->getTotalVote($vot->user_id);
			
				$vot->positive_voters = $this->GeneralManagerModel->votersByStatusCr($vot->user_id, 12);
				$vot->neutral_voters = $this->GeneralManagerModel->votersByStatusCr($vot->user_id, 14);
				
				}
				
			}
		$data['pl_boothagent'] = $this->GeneralManagerModel->getPollingStationMember($id, 37);
		
        $data['pl_boothobserver'] = $this->GeneralManagerModel->getPollingStationMember($id, 38);
        $data['ps_img'] = $this->GeneralManagerModel->getPollingStationImage($id);
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/polling-station-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php'); 
    }
	
	
	public function coordinatorbooth($id) {
        if(isset($id)) {
			$data['header_css'] = array('admin.css', 'myteam.css');
			$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js',
									'js/plugin/number-animate/jquery.easy_number_animate.min.js');
									
			$data['profile'] = $this->GeneralManagerModel->userProfile($id);
            $data['qualification'] = $this->GeneralManagerModel->userQualification($id);
            $data['voters'] = $this->GeneralManagerModel->getTotalVote($id);
            $data['positive_voters'] = $this->GeneralManagerModel->votersByStatusCr($id, 12);
            //$data['cr_data'] = $this->GeneralManagerModel->getCoordinatorsByTeamleader($id);
			//$data['pid'] = $this->SeniorManagerModel->getParentId($id);
			$data['tl_coordinator'] = $this->GeneralManagerModel->getVolunteerByCoordinator($id);
			$data['mymembers'] = $this->GeneralManagerModel->getMyGroupMembersByVolunteer($id);
			if($data['tl_coordinator']!="")
			{
				foreach($data['tl_coordinator'] as $k => $vot) 
				{
				$vot->voter = $this->GeneralManagerModel->getVolunteerTotalVote($vot->id)->num_rows();
				$vot->positive_voters = $this->GeneralManagerModel->getVolunteerTotalVote($vot->id,array('v.voter_status' => 12))->num_rows();
				$vot->neutral_voters = $this->GeneralManagerModel->getVolunteerTotalVote($vot->id, array('v.voter_status' => 14))->num_rows();;
				
				}
				
			} 
			
			$data['family']=$this->GeneralManagerModel->getCitizenByrelation($id, 47);
			$data['relative']=$this->GeneralManagerModel->getCitizenByrelation($id, 48);
			$data['friend']=$this->GeneralManagerModel->getCitizenByrelation($id, 49);
			$data['known']=$this->GeneralManagerModel->getCitizenByrelation($id, 50);
			
			$this->load->view('includes/header.php', $data);
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/coordinator-booth.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			 $this->load->view('scripts/gm/coordinator-script.php');
            $this->load->view('scripts/seniormanager/cf-script.php');
			$this->load->view('scripts/common/modal-script.php');
            $this->load->view('includes/footer.php');
        }
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
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/myteam/team-members.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/gm/member-script.php',$data);
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

	//SCHEDULES
	public function schedules() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js' ,'js/plugin/summernote/summernote.min.js',
								'js/plugin/markdown/markdown.min.js','js/plugin/markdown/to-markdown.min.js','js/plugin/markdown/bootstrap-markdown.min.js',
								'js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
		$data['reciverid'] = $this->session->userdata('user')->id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/schedules.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/schedules-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
		
    }

	public function voterlist() {
		$id = $this->session->userdata('user')->id;
		$data['srm_mandal'] = $this->GeneralManagerModel->mandalByGeneralManagerv($id);
         $data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/voter-list-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
    }
	
	
	
	
	public function voterlistvillage($location_id) {
		$data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
		$data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
	    $this->load->view('generalmanager/voter-list-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php'); 
    }
	
	public function voterlistcoordinator($location_id) {
        $data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css','myteam.css');
		$data['coordinators'] = $this->GeneralManagerModel->getCoordinatorsByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
	    $this->load->view('generalmanager/voter-list-coordinator.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/vl-script.php');
		$this->load->view('scripts/common/modal-script.php');
        $this->load->view('includes/footer.php'); 
    }
	
	
	public function mygroupmembers($id) {
		
        
			$data['header_css'] = array('admin.css', 'myteam.css','dashboard.css');
            $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
			
            $this->load->view('includes/header.php', $data);
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
			//check permission
			$data['vid']=$id;
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/mygroup-team-members.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/gm/mygroup-member-script.php',$data);
			$this->load->view('scripts/common/modal-script.php');
            $this->load->view('includes/footer.php');
        
    }
	
	public function registered() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
		$data['constit'] = $this->GeneralManagerModel->getAllConstituence($location);
        $this->load->view('includes/header.php', $data);
		//$data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/register-constituent-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function regismandals($id) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['mandals'] = $this->GeneralManagerModel->mandalByConstituency($id);
		$this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/register-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function registerforrecruit($id) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
        $this->load->view('includes/header.php', $data);
		//$id = $this->session->userdata('user')->id;
		$registration = $this->GeneralManagerModel->getTotalRegistration($id)->result();
		
		$data['total_register'] = count($registration);
		
		//$lid = $this->session->userdata('user')->location_id;
		$data['mandals']=$this->GeneralManagerModel->getTotalMandalBySMa($id)->result();
		
		if($data['mandals']!="")
			{ 
				foreach($data['mandals'] as $k => $mg) 
				{
				$mg->total_register = $this->GeneralManagerModel->getTotalRegistrationMandal($mg->id)->result();
				$mg->total_r = count($mg->total_register);
				}
			} 

		
		$this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/register-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
		$this->load->view('scripts/gm/registration-script.php', $data);
        $this->load->view('includes/footer.php');
    }
	
	public function registerforvillage() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
		//$data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/register-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function comingsoon() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/comingsoon.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
		//DASHBOARD BRANDINGS
	public function brandings() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/dash-branding.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function social() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/branding/social.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	public function smedia() {
        $data['header_css'] = array('admin.css','dashboard.css');
        //$data['brandimg'] = $this->SMDashboardModel->getBrandingImg();
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/smedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobilec() {
        $data['plugins'] = array('js/plugin/bootstrap-progressbar/bootstrap-progressbar.min.js');
        
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['messages'] = $this->SMDashboardModel->getMessageTemplates();
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/mobilec.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/smdash/mobilecommunication-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function boothcoordinatorreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['constituency'] = $this->GeneralManagerModel->getConstituence($location);
        $data['user_roles'] = $this->GeneralManagerModel->getAssignRole();
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/validation-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/validation-report-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function boothpresidentreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['constituency'] = $this->GeneralManagerModel->getConstituence($location);
        $data['user_roles'] = $this->GeneralManagerModel->getAssignRole();
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/validation-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/validation-report-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function sheetpresidentreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['constituency'] = $this->GeneralManagerModel->getConstituence($location);
       // $data['user_roles'] = $this->GeneralManagerModel->getAssignRole();
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/validation-report-sp.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/validation-sp-report-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function familyheadreport() {
        $data['header_css'] = array('buttons.dataTables.min.css','admin.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $location = $this->session->userdata('user')->location_id;
        $data['constituency'] = $this->GeneralManagerModel->getConstituence($location);
        //$data['user_roles'] = $this->GeneralManagerModel->getAssignRole();
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/validation-report-familyhead.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/gm/validation-familyhead-report-script.php');
        $this->load->view('includes/footer.php');
    }
	
	
	public function xparty($lid) {
		
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['xparty'] = $this->GeneralManagerModel->getPartyName();
		$data['lid'] = $lid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/xparty.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/xparty-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function xpartymandal($id,$lid) {
		
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $locationid = $this->session->userdata('user')->location_id;
		$data['mandals'] = $this->GeneralManagerModel->getMandalsByConstituences($lid);
		//$data['srm_mandal'] = $this->GeneralManagerModel->getMandalsByConstituence($locationid);
		$data['partyid'] = $id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
       // $this->load->view('generalmanager/dashboard/xparty-mandal.php', $data);
		$this->load->view('generalmanager/dashboard/xparty-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/gm/xparty-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	//Datatable for xparty
    public function getxpartyData($partyid,$location) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $users = $this->GeneralManagerModel->getXpartyById($partyid,$location);
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
	
	public function xpartyvillage($location,$partyid) {
		
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['partyid'] = $partyid;
		$data['villages'] = $this->apiModel->getAllVillageByMandal($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/xparty-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		//$this->load->view('scripts/seniormanager/xparty-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function partyinfo($location,$partyid) {
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['xpid'] = $partyid;
		$data['villageid'] = $location;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/party-info.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/gm/xparty-script.php', $data);
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	
	public function recruitmentsmanager() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/recruitment-smanager.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/recruitment-view-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitmentmanager() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		//$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/recruitment-manager.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/recruitment-view-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitmenttm() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$id = $this->session->userdata('user')->id;
		$data['mid']=$id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/recruitment-teamleader.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/recruitment-view-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitmentcoordinator() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/recruitment-coordinator.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/recruitment-view-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitmentvolunteer() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/recruitment-volunteer.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/gm/recruitment-view-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }

	
	
	//datatable for voters
    public function getvoters() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $id = $this->session->userdata('user')->id;
        $voter = $this->GeneralManagerModel->getVotersByManager($id);
        
        $data = array();
		
        foreach($voter->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
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
            "recordsTotal" => $voter->num_rows(),
            "recordsFiltered" => $voter->num_rows(),
            "data" => $data
        );
		
        echo json_encode($output);
        exit();    
    }
	
	//Datatable for recruitment senior manager
    public function getSmRecruitmentMandal() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->GeneralManagerModel->getTotalSManagerByGM($id);
	
        $data = array();
        $data = array();
		
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                ($r->gender == 4) ? 'Male' : 'Female',
				$r->name,
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
	
	//Datatable for recruitment mandal
    public function getRecruitmentMandal1() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->GeneralManagerModel->getTotalManagerBySM($id);
	
        $data = array();
        $data = array();
		
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                ($r->gender == 4) ? 'Male' : 'Female',
				$r->name,
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
	
	//Datatable for recruitment mandal
    public function getRecruitmentMandal() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->GeneralManagerModel->getTotalMandalBySM($id);
	
        $data = array();
        $data = array();
		
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                ($r->gender == 4) ? 'Male' : 'Female',
				$r->name,
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
	
	//Datatable for recruitment TeamLeader
    public function getRecruitmentTeamLeader() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->GeneralManagerModel->getTotalTeamLeaderBySMa($id);
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                ($r->gender == 4) ? 'Male' : 'Female',
                $r->village,
                $r->Mandal,
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
	
	//Datatable for recruitment Coordinator
    public function getRecruitmentCoordinator() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->GeneralManagerModel->getTotalCoordinatorBySMa($id);
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                ($r->gender == 4) ? 'Male' : 'Female',
                $r->village,
                $r->Mandal,
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
	
	//Datatable for recruitment Volunteer
    public function getRecruitmentVolunteer() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->GeneralManagerModel->getTotalVolunteerBySMa($id);
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                ($r->gender == 4) ? 'Male' : 'Female',
                $r->village,
                $r->Mandal,
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
	
	//SAMART MEDIA
	public function smartmedia() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['smartmedia'] = $this->CoordinatorModel->getSmartMedia();
		if($data['smartmedia']!="")
			{foreach($data['smartmedia'] as $k => $man) 
				{$man->likes = $this->SMDashboardModel->getLikes($man->id);}
			}
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/dashboard/smart-media.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
    
	public function getMandalsByConstituence($id) {
        $result = $this->GeneralManagerModel->getMandalsByConstituences($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
	
	/* public function getPollingStationByMandal($id) {
        $result = $this->GeneralManagerModel->getPollingStationByMandals($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    } */
	public function getPollingStationByMandal($id) {
        $result = $this->adminModel->getPSByMandal($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
	
	public function getBoothPresidentByPs($id) {
        $result = $this->GeneralManagerModel->getBoothPresidentByPss($id);
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
	
	public function getSheetPresidentByBP($id) {
        $result = $this->GeneralManagerModel->getSheetPresidentByBPs($id);
		//echo $this->db->last_query();exit;
        if($result == true && count($result) > 0) {
            header('Content-Type: application/json');
            echo json_encode($result);     
        }else {
            return false;
        }
    }
	/* CHANGES IN GENERAL MANAGER */
	
	public function votervisits() {
        $data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/voter-visits.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	 /* SERVICES MANDAL*/
    public function vmandals($service) {
        if( $service == 'govt-schemes' || $service == 'govt-projects' || $service == 'bangaru-telangana' || $service == 'govt-achievements' || $service == 'govt-failure' || $service == 'manifesto' || $service == 'services')
			{
            $data['header_css'] = array('admin.css','dashboard.css');
            $id = $this->session->userdata('user')->id;
            $location = $this->session->userdata('user')->location_id;
            $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
            
            // if($service == 'home-assistance') {
            //     $data['url'] = base_url('dashboard/homeassistancevillage');
            // }
            // if($service == 'education') {
            //     $data['url'] = base_url('dashboard/educationvillage');
            // }
            // if($service == 'health') {
            //     $data['url'] = base_url('dashboard/healthvillage');
            // }
            // if($service == 'job') {
            //     $data['url'] = base_url('dashboard/jobvillage');
            // }
            // if($service == 'training') {
            //     $data['url'] = base_url('dashboard/trainingvillage');
            // }
            // if($service == 'id-cards') {
            //     $data['url'] = base_url('dashboard/idvillage');
            // }
            // if($service == 'certificates') {
            //     $data['url'] = base_url('dashboard/certificatevillage');
            // }
            /* if($service == 'govt-schemes') {
                $data['url'] = base_url('dashboard/gschemesvillage');
            } */
			/*new visits*/
			if($service == 'govt-schemes') {
                $data['url'] = base_url('generalmanager/govschemesvillage');
            }
			if($service == 'govt-projects') {
                $data['url'] = base_url('generalmanager/govprojectvillage');
            }
			if($service == 'bangaru-telangana') {
                $data['url'] = base_url('generalmanager/bangarutelanganavillage');
            }
			if($service == 'govt-achievements') {
                $data['url'] = base_url('generalmanager/govtachievvillage');
            }
			// if($service == 'scheme-beneficiary') {
            //     $data['url'] = base_url('dashboard/schemebenifivillage');
            // }
			// if($service == 'pension-beneficiary') {
            //     $data['url'] = base_url('dashboard/pensionbenifivillage');
            // }

            if($service == 'govt-failure') {
                $data['url'] = base_url('generalmanager/govtfailure');
            }
			if($service == 'manifesto') {
                $data['url'] = base_url('generalmanager/partymanifesto');
            }
            if($service == 'services') {
                $data['url'] = base_url('generalmanager/personalservices');
            }

            $this->load->view('includes/header.php', $data);
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            $this->load->view('generalmanager/lead-assistance/service-mandals.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }
	
	 /* New Visits */
	/* Visit 27 */
	public function govschemesvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getGovSchemesByPS($location_id);
        // echo '<pre>'; print_r($data['villages']); exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('generalmanager/lead-assistance/govt-visit-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function govtschemedetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getgovtschemedetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
    
    public function getgovtschemedetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getGovtSchemesDetails($id);
		
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->coord_name,
                // (isset($r->vlt_name)) ? $r->vlt_name : '-',
				($r->status == 1) ? 'Explained' : 'Pending',
				($r->created_at == '') ? '' : date('F j, Y g:i a', strtotime($r->created_at)), 
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $ha->num_rows(),
            "recordsFiltered" => $ha->num_rows(),
            "data" => $data
        );
        
        echo json_encode($output);
        exit();
    }
	/*End Visit 27 */
	
	
    
    /* Visit 28 */
	public function govprojectvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getGovtProjByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('generalmanager/lead-assistance/govt-project-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

	public function govtprojectdetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('generalmanager/getgovtprojectdetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }

	public function getgovtprojectdetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getGovtProjectDetails($id);
		
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->coord_name,
                // (isset($r->vlt_name)) ? $r->vlt_name : '-',
				($r->status == 1) ? 'Explained' : 'Pending',
				($r->created_at == '') ? '' : date('F j, Y g:i a', strtotime($r->created_at)), 
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $ha->num_rows(),
            "recordsFiltered" => $ha->num_rows(),
            "data" => $data
        );
        
        echo json_encode($output);
        exit();
    }
	/*End Visit 28 */
	/* End Visit 35 */
    public function personalservices($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceVisitByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/lead-assistance/personal-service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function personalservicedetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getpersonalservicedetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/lead-assistance/personalservice-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function getpersonalservicedetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getPersonalServiceDetails($id);
		
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->coord_name,
                // (isset($r->vlt_name)) ? $r->vlt_name : '-',
				
                $r->name,
                ($r->service_status == 1) ? 'Solved' : 'Pending',
				($r->created_at == '') ? '' : date('F j, Y g:i a', strtotime($r->created_at)), 
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $ha->num_rows(),
            "recordsFiltered" => $ha->num_rows(),
            "data" => $data
        );
        
        echo json_encode($output);
        exit();
    }
	/* End */
	
	//TEAM ANALYSIS
	public function teamanalysis($lid) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['lid']=$lid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/team-analysis/team-analysis.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//STRATEGY
	public function strategy() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/team-analysis/strategy.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	public function performance($lid) {
       
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
		$id = $this->session->userdata('user')->id;
		//$lid = $this->session->userdata('user')->location_id;
		$registration = $this->SMDashboardModel->getTotalRegistration($id)->result();
		$data['total_register'] = count($registration);
		//$data['mandals']=$this->SMDashboardModel->getTotalMandalBySM($id)->result();
		$data['mandals']=$this->SMDashboardModel->getMandalsRegist($lid)->result();
		
		if($data['mandals']!="")
			{ 
				foreach($data['mandals'] as $k => $mg) 
				{
				$mg->total_register = $this->SMDashboardModel->getTotalRegistrationMandal($mg->id)->result();
				$mg->total_r = count($mg->total_register);
				}
			} 
        $this->load->view('generalmanager/top-nav.php');
        $this->load->view('generalmanager/side-nav.php');
        $this->load->view('generalmanager/team-analysis/performance.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('generalmanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	/**
     * Date : 25-01-2019
	 MYTEAM CHANGES
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
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/myteam/division-head.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/gm/tl-script.php');
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
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/myteam/division-incharge.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/gm/tl-script.php');
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
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/myteam/booth-president.php', $data);
            }
            
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/gm/tl-script.php');
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
            //$data['qualification'] = $this->SeniorManagerModel->userQualification($id);
			
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
            $this->load->view('generalmanager/top-nav.php');
            $this->load->view('generalmanager/side-nav.php');
            //check permission
            if($this->allocation_status === FALSE) {
                $this->load->view('common/no-access.php');
            }else {
                $this->load->view('generalmanager/myteam/coordinator-profile.php', $data);
            }
            $this->load->view('includes/page-footer.php');
            $this->load->view('generalmanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
			$this->load->view('scripts/gm/coordinator-script.php',$data);
            $this->load->view('scripts/gm/cf-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
     
			}
	}

    public function telecallerprofile($id) {
        if(isset($id)) {
           $data['header_css'] = array('admin.css', 'myteam.css');
           $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
           $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
           $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
           
           
           
           
           $data['voters'] = $this->SeniorManagerModel->getVotersCountByDI($id);
           $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByDI($id, array('v.voter_status' => 12));
           
           
           $this->load->view('includes/header.php', $data);
           $this->load->view('generalmanager/top-nav.php');
           $this->load->view('generalmanager/side-nav.php');
           //check permission
           if($this->allocation_status === FALSE) {
               $this->load->view('common/no-access.php');
           }else {
               $this->load->view('generalmanager/myteam/telecaller-profile.php', $data);
           }
           
           $this->load->view('includes/page-footer.php');
           $this->load->view('generalmanager/shortcut-nav.php');
           $this->load->view('includes/plugins.php', $data);
           $this->load->view('scripts/gm/tl-script.php');
           $this->load->view('scripts/common/modal-script.php');  //modal script
           $this->load->view('includes/footer.php');
       }
   }
   
   public function boothcoordinatorpr($id) {
        if(isset($id)) {
           $data['header_css'] = array('admin.css', 'myteam.css');
           $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
           $data['tl_profile'] = $this->SeniorManagerModel->userProfile($id);
           $data['qualification'] = $this->SeniorManagerModel->userQualification($id);
           
           
           
           
           $data['voters'] = $this->SeniorManagerModel->getVotersCountByDI($id);
           $data['positive_voters'] = $this->SeniorManagerModel->getVotersCountByDI($id, array('v.voter_status' => 12));
           
           
           $this->load->view('includes/header.php', $data);
           $this->load->view('generalmanager/top-nav.php');
           $this->load->view('generalmanager/side-nav.php');
           //check permission
           if($this->allocation_status === FALSE) {
               $this->load->view('common/no-access.php');
           }else {
               $this->load->view('generalmanager/myteam/boothcoordinator-profile.php', $data);
           }
           
           $this->load->view('includes/page-footer.php');
           $this->load->view('generalmanager/shortcut-nav.php');
           $this->load->view('includes/plugins.php', $data);
           $this->load->view('scripts/gm/tl-script.php');
           $this->load->view('scripts/common/modal-script.php');  //modal script
           $this->load->view('includes/footer.php');
       }
   }
   
   public function reports($lid) {
       $data['header_css'] = array('admin.css','dashboard.css');
       $data['lid']=$lid;
       $this->load->view('includes/header.php', $data);
       $this->load->view('generalmanager/top-nav.php');
       $this->load->view('generalmanager/side-nav.php');
       $this->load->view('generalmanager/reports.php', $data);
       $this->load->view('includes/page-footer.php');
       $this->load->view('generalmanager/shortcut-nav.php');
       $this->load->view('includes/plugins.php', $data);
       $this->load->view('common/widget-script.php');
       $this->load->view('scripts/common/modal-script.php'); //modal script
       $this->load->view('includes/footer.php');
   }
   
   public function recruitmentreport($lid) {
       $data['header_css'] = array('admin.css');
       $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                               'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                               'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                               'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                               'js/plugin/datatables/buttons.print.min.js');
       $location = $this->session->userdata('user')->location_id;
       $data['mandals'] = $this->adminModel->getMandalsByConstituence($lid);
       $data['user_roles'] = $this->adminModel->getAssignRole();
       
       $this->load->view('includes/header.php', $data);
       $this->load->view('generalmanager/top-nav.php');
       $this->load->view('generalmanager/side-nav.php');
       $this->load->view('generalmanager/reports/recruitment-report.php', $data);
       $this->load->view('includes/page-footer.php');
       $this->load->view('includes/plugins.php', $data);
       $this->load->view('scripts/gm/reports/recruitment-report-script.php');
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
           $constituency = $data['constituency'];
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
   
   public function registrationreport($lid) {
       $data['header_css'] = array('admin.css');
       $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                               'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                               'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                               'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                               'js/plugin/datatables/buttons.print.min.js');
       $location = $this->session->userdata('user')->location_id;
       $data['mandals'] = $this->adminModel->getMandalsByConstituence($lid);
       $this->load->view('includes/header.php', $data);
       $this->load->view('generalmanager/top-nav.php');
       $this->load->view('generalmanager/side-nav.php');
       $this->load->view('generalmanager/reports/registration-report.php', $data);
       $this->load->view('includes/page-footer.php');
       $this->load->view('includes/plugins.php', $data);
       $this->load->view('scripts/gm/reports/registration-report-script.php');
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
   
   public function telecallingreport($lid) {
       $data['header_css'] = array('admin.css');
       $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
       
       $data['familyhead'] = $this->SeniorManagerModel->getQuestionByRole(46);
       $data['boothobserver'] = $this->SeniorManagerModel->getQuestionByRole(55);
       $data['streetpresident'] = $this->SeniorManagerModel->getQuestionByRole(3);
       
       $this->load->view('includes/header.php', $data);
       $this->load->view('generalmanager/top-nav.php');
       $this->load->view('generalmanager/side-nav.php');
       $this->load->view('generalmanager/reports/tellicalling-report.php', $data);
       $this->load->view('includes/page-footer.php');
       $this->load->view('includes/plugins.php', $data);
       $this->load->view('scripts/gm/tellicalling-report-script.php');
       $this->load->view('includes/footer.php');
   }

   public function getlocationName() {
       if($this->input->post()) {
           $data = $this->input->post();
           $loc = $data['location'];
           $result = $this->SeniorManagerModel->getLocationById($loc);
           echo json_encode($result); 
       }
   }
   
   public function validationteamreport($lid) {
       $data['header_css'] = array('admin.css');
       $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                               'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                               'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                               'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                               'js/plugin/datatables/buttons.print.min.js');
      // $data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($this->_usession->location_id);
       $data['mandals'] = $this->GeneralManagerModel->getMandalsByConstituences($lid);
       $this->load->view('includes/header.php', $data);
       $this->load->view('generalmanager/top-nav.php');
       $this->load->view('generalmanager/side-nav.php');
       $this->load->view('generalmanager/reports/validation-team-report.php', $data);
       $this->load->view('includes/page-footer.php');
       $this->load->view('includes/plugins.php', $data);
       $this->load->view('scripts/gm/reports/validation-team-script.php');
       $this->load->view('includes/footer.php');
   }
   
   //Datatable for validation
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
               $r->govt_schemes,
               /* $r->ysr_schemes */
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
   
   public function telereport($lid) {
       $data['header_css'] = array('admin.css');
       $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                               'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                               'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                               'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                               'js/plugin/datatables/buttons.print.min.js');
       $data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($lid);
       $this->load->view('includes/header.php', $data);
       $this->load->view('generalmanager/top-nav.php');
       $this->load->view('generalmanager/side-nav.php');
       $this->load->view('generalmanager/reports/tc-report.php', $data);
       $this->load->view('includes/page-footer.php');
       $this->load->view('includes/plugins.php', $data);
       $this->load->view('scripts/gm/reports/telecalling-team-script.php');
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
}