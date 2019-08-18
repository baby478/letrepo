<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    
    private $allocation_status;
    private $_id;

    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 44) {
            redirect(base_url());
        }
		// load pagination library
        $this->load->library('pagination');
        $this->load->helper("url");
        
        $this->load->model('loginModel');
        $this->load->model('SeniorManagerModel');
		$this->load->model('userModel');
        $this->load->model('managerModel');
        $this->load->model('SMDashboardModel');
        $this->load->model('apiModel');
		$this->load->model('CoordinatorModel');
		$this->load->model('CommunicationModel');
        $this->_id = $this->session->userdata('user')->id;
       // $this->_alloc_status();
    }
	
	//MANDAL
	public function mandal() {
        $data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id; 
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/mandal.php', $data);
        //$this->load->view('seniormanager/digitalbooth/voter-list-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }

    public function pollingstationlist($id) {
        $data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        // $data['villages'] = $this->apiModel->getAllVillageByMandal($location_id);
        $data['ps'] = $this->SMDashboardModel->getPollingStaionsByDivision($id);
        // echo '<pre>'; print_r($data['ps']); exit;
		//echo $this->db->last_query();exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['ps']) {
            $this->load->view('seniormanager/dashboard/polling-station-list.php', $data);
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
	
	//MANDAL DESCRIPTION
	public function mandaldetails() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$data['ward_info'] = $this->SeniorManagerModel->getWardInfo();
		$data['counselor'] = $this->SeniorManagerModel->getCounselor();
		$data['xparty'] = $this->SeniorManagerModel->getXpartyData();
		$data['events'] = $this->SeniorManagerModel->getMandalEvents();
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/mandal-detail.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/seniormanager/voter-script.php');
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	//TEAM ANALYSIS
	public function teamanalysis() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/team-analysis.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//STRATEGY
	public function strategy() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/strategy.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//TEXT MESSAGE
	public function textmessages() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$locationid = $this->session->userdata('user')->location_id;
		$data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
		if($data['srm_mandal']!="")
			{foreach($data['srm_mandal'] as $k => $man) 
				{$man->totalmandal = $this->SMDashboardModel->getTotalTextmsgMandalBySM($man->id);}
			}
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/textmessage-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function totaltextmessage($locationid) {
        $data['header_css'] = array('admin.css','dashboard.css');
		
		$data['total_manager'] = $this->SMDashboardModel->getTotalTextmsgByRole($locationid,2);
		$data['total_teamleader'] = $this->SMDashboardModel->getTotalTextmsgByRole($locationid,18);
		$data['total_coordinator'] = $this->SMDashboardModel->getTotalTextmsgByRole($locationid,3);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/total-textmessage.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//CALL RECORDING
	public function callrecordings() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$locationid = $this->session->userdata('user')->location_id;
		$data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
		if($data['srm_mandal']!="")
			{foreach($data['srm_mandal'] as $k => $man) 
				{$man->totalmandal = $this->SMDashboardModel->getTotalCallrecordMandalBySM($man->id);}
			}
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/callrecording-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function totalcallrecordings($locationid) {
        $data['header_css'] = array('admin.css','dashboard.css');
		
		$data['total_manager'] = $this->SMDashboardModel->getTotalCallrecordByRole($locationid,2);
		$data['total_teamleader'] = $this->SMDashboardModel->getTotalCallrecordByRole($locationid,18);
		$data['total_coordinator'] = $this->SMDashboardModel->getTotalCallrecordByRole($locationid,3);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/total-callrecording.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//PARTY LEADER
	public function partyleader() {
      
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js','js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js',
								'js/plugin/fuelux/wizard/wizard.min.js');
        
		 if($this->input->post()) {
			 $data = $this->input->post();
			 
			 $insert = $this->SMDashboardModel->registerPartyLeader($data);
			  if($insert) 
				{ 
					$this->session->set_flashdata('add_partyleader', '<div class="alert alert-success fade in"><strong>Success!</strong> Party Leader registered successfully.</div>');
                }else 
				{
                    $this->session->set_flashdata('add_partyleader', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
			 redirect(base_url('Dashboard/partyleader'));
		 }
		 $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/party-leader.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/partyleader-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	//Datatable for xparty
    public function getPartyLeaderData() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id=$this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getPartyLeader($id);
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->first_name,
                $r->age,
                $r->email,
				$r->mobile,
                $r->designation,
                $r->voterid,
				$r->membership_no,
				$r->community,
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
	//X PARTY
	public function xparty() {
		
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['xparty'] = $this->SeniorManagerModel->getPartyName();
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/xparty-info/xparty.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/xparty-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function xpartymandal($id) {
		
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
        $locationid = $this->session->userdata('user')->location_id;
		$data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
		$data['partyid'] = $id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/xparty-info/xparty-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/xparty-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	public function xpartyvillage($location,$partyid) {
		
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['partyid'] = $partyid;
		$data['villages'] = $this->apiModel->getAllVillageByMandal($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/xparty-info/xparty-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		//$this->load->view('scripts/seniormanager/xparty-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	//X PARTY INFORMATYION
	public function partyinfo($location,$partyid) {
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['xpid'] = $partyid;
		$data['villageid'] = $location;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/party-info.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/seniormanager/xparty-script.php', $data);
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

	//SCHEDULES
	public function schedules() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js' ,'js/plugin/summernote/summernote.min.js',
								'js/plugin/markdown/markdown.min.js','js/plugin/markdown/to-markdown.min.js','js/plugin/markdown/bootstrap-markdown.min.js',
								'js/plugin/bootstrap-wizard/jquery.bootstrap.wizard.min.js', 'js/plugin/fuelux/wizard/wizard.min.js');
		$data['reciverid'] = $this->session->userdata('user')->id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/schedules.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/schedules-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php'); 
		
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
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/smart-media.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	//OTHERS
	public function others() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/others.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }

	//MESSENGER
	public function messenger() {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $locationid = $this->session->userdata('user')->location_id;
        $data['mandals'] = $this->SeniorManagerModel->getMandalsByConstituence($locationid);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/messenger.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('scripts/seniormanager/messenger-script.php');
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	//DATATABLE FOR TEXT MESSAGE
    public function gettextmessages() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getMessageDetails($id);
	    if(!is_array($users)) {
			$users = array();
		}
        $data = array();
        foreach($users as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->text_message,
                $r->mobile,
                $r->first_name . ' ' .$r->last_name,
				$r->user_role,
				($r->created_at == '') ? '' : date('F j, Y g:i a', strtotime($r->created_at)), 
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => count($users),
            "recordsFiltered" => count($users),
            "data" => $data
        );
        echo json_encode($output);
        exit();    	
    }
	
	//DATATABLE FOR VOICE MESSAGE
    public function getvoicemessage() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getVoiceMessageDetails($id);
        //var_dump($users); exit;
		if(!is_array($users)) {
			$users = array();
		}
        $data = array();
        foreach($users as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' .$r->lastname,
				//gmdate("H:i:s", $r->duration),
				/* '<a href="'. base_url('uploads/voice-messages/').$r->voice_message.'" target="_blank">
				<button type="button">Play <i class="fa fa-play"></i></button></a> */
				'<a href="'. base_url('uploads/voice-messages/').$r->voice_message.'" download><button type="button">Download <i class="fa fa-cloud-download"></i></button></a>',
				$r->first_name . ' ' .$r->last_name,
				($r->created_at == '') ? '' : date('F j, Y g:i a', strtotime($r->created_at)), 
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => count($users),
            "recordsFiltered" => count($users),
            "data" => $data
        );
        echo json_encode($output);
        exit();    	
    }
	
	//DATATABLE FOR CALL RECORD
    public function getcallrecords() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getCallrecordingDetails($id);
		if(!is_array($users)) {
			$users = array();
		}
        $data = array();
        foreach($users as $r) {
            $i = 1;
            $data[] = array(
                $i,
				$r->mobile,
                $r->first_name . ' ' .$r->last_name,
				
				gmdate("H:i:s", $r->call_duration),
				'<a href="'. base_url('uploads/call-recordings/').$r->recording_path.'" target="_blank">
				<button type="button">Play <i class="fa fa-play"></i></button></a>
				<a href="'. base_url('uploads/call-recordings/').$r->recording_path.'" download><button type="button">Download <i class="fa fa-cloud-download"></i></button></a>',
				$r->user_role,
				$r->created_at,
            );
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => count($users),
            "recordsFiltered" => count($users),
            "data" => $data
        );
        echo json_encode($output);
        exit();    	
    }
	
	//MOBILE TEAM
	public function mobileteam() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/mobile-team/mobile-team.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobileteamdetails($location) {
		$data['header_css'] = array('admin.css','dashboard.css','myteam.css');
		
        $data['mobile_team'] = $this->SeniorManagerModel->getMobileTeamByMandal($location);
	
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['mobile_team']) {
            $this->load->view('seniormanager/dashboard/mobile-team/mobile-team-details.php', $data);
        }else {
            $data['content'] = 'No Booth Observer Assigned.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobileteamevent($mid) {
		$data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/fullcalendar/fullcalendar.min.js');
		$id = $this->session->userdata('user')->id;
		$data['mtid'] = $mid;
		$data['observation_msg'] = $this->SMDashboardModel->getMobileTeamMessage($mid,1);
		$data['observation_call'] = $this->SMDashboardModel->getMobileTeamVoiceCall($mid,1);
		$data['feedback_msg'] = $this->SMDashboardModel->getMobileTeamMessage($mid,2);
		$data['feedback_call'] = $this->SMDashboardModel->getMobileTeamVoiceCall($mid,2);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/mobile-team/mobile-team-addevent.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		//$this->load->view('scripts/seniormanager/calendar-script.php');
		$this->load->view('scripts/seniormanager/mobile-team-script.php',$data);
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	/** Lead Assistance - Visit 1
     * 
     * Common functionality and each visit options
     */
    public function leadassistance() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/lead-assistance.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
        
    }

    /* Common Functionality */

    /* SERVICES MANDAL*/
    public function mandals($service) {
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
                $data['url'] = base_url('dashboard/govschemesvillage');
            }
			if($service == 'govt-projects') {
                $data['url'] = base_url('dashboard/govprojectvillage');
            }
			if($service == 'bangaru-telangana') {
                $data['url'] = base_url('dashboard/bangarutelanganavillage');
            }
			if($service == 'govt-achievements') {
                $data['url'] = base_url('dashboard/govtachievvillage');
            }
			// if($service == 'scheme-beneficiary') {
            //     $data['url'] = base_url('dashboard/schemebenifivillage');
            // }
			// if($service == 'pension-beneficiary') {
            //     $data['url'] = base_url('dashboard/pensionbenifivillage');
            // }

            if($service == 'govt-failure') {
                $data['url'] = base_url('dashboard/govtfailure');
            }
			if($service == 'manifesto') {
                $data['url'] = base_url('dashboard/partymanifesto');
            }
            if($service == 'services') {
                $data['url'] = base_url('dashboard/personalservices');
            }

            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/lead-assistance/service-mandals.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    }

    public function servicedetails($id, $service_id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getservice/').$id. '/'.$service_id;
        $data['service'] = $this->SMDashboardModel->getServicename($service_id)->name;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
    //datatable services
    public function getservice($id, $service_id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getServiceDetails($id, $service_id);
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->name,
                /* ($r->status == 0) ? 'Pending' : 'Solved', */
                $r->coord_name,
                (isset($r->vlt_name)) ? $r->vlt_name : '',
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

    /* HOME ASSISTANCE */
    public function homeassistancevillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 7);
        $data['service_id'] = 7;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
        
    }
    /* HOME ASSISTANCE END */

    /* EDUCATION */ 
    public function educationvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 8);
        $data['service_id'] = 8;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
        
    }
    /* EDUCATION END */

    /* HEALTH */
    public function healthvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 9);
        $data['service_id'] = 9;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
        
    }
    /* HEALTH END */

    /* JOB */
    public function jobvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 10);
        $data['service_id'] = 10;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    /* JOB END*/

    /* Training Support */
    public function trainingvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 11);
        $data['service_id'] = 11;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    /* Training Support End*/

    /* Id Cards */
    public function idvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 12);
        $data['service_id'] = 12;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    /* Id Cards End*/

    /* Certificates */
    public function certificatevillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 13);
        $data['service_id'] = 13;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    /* Certificates End*/

    /* Govt. Schemes */
    public function gschemesvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceByVillage($location_id, 14);
        $data['service_id'] = 14;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    /* Govt. Schemes End*/


/* Lead Assistance - Visit 1 Ends
*/
	
	/* OUT STATION */
	public function outstation() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/outstation/out-station.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function outstationinfo() {
		$location_id = $this->session->userdata('user')->location_id;
		$data['district'] = $this->SMDashboardModel->getOutstationDistrictBySM($location_id);
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['district']) {
            $this->load->view('seniormanager/dashboard/outstation/out-station-info.php', $data);
        }else {
            $data['content'] = 'No Outstation Content.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function outstationmandal($disid) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$location_id = $this->session->userdata('user')->location_id;
		$data['srm_mandal'] = $this->SMDashboardModel->getMandalByDistrict($location_id,$disid);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/outstation/outstation-mandal.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function outstationvillage($mid,$id) {
		$location_id = $this->session->userdata('user')->location_id;
		$data['villages'] = $this->SMDashboardModel->getOutstationVillages($location_id,$id,$mid);
		//echo $this->db->last_query();exit;
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/outstation/out-station-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function outstationvillagedetail($vid,$mid,$disid) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                    'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                    'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$location_id = $this->session->userdata('user')->location_id;
        $data['url'] = base_url('dashboard/getoutstations/').$location_id. '/'.$vid. '/'.$mid. '/'.$disid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/outstation/outstation-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/smdash/outstation-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function getoutstations($location_id,$vid,$mid,$disid) {
            $draw = intval($this->input->get("draw"));
            $start = intval($this->input->get("start"));
            $length = intval($this->input->get("length"));
			
            $outstation = $this->SMDashboardModel->getOutstationVillageTotal($location_id,$vid,$mid,$disid);
            $data = array();
            foreach($outstation->result() as $r) {
                
                $i = 1;
                $data[] = array(
                    $i,
                    $r->firstname . ' ' . $r->lastname,
                    $r->mobile,
                    $r->os_village,
                    $r->first_name . ' ' . $r->last_name,
                );
            }
            $output = array(
                "draw" => $draw,
                "recordsTotal" => $outstation->num_rows(),
                "recordsFiltered" => $outstation->num_rows(),
                "data" => $data
            );
            
            echo json_encode($output);
            exit();
        }
	/*End of Outstation */
	
	/** Family Status - Visit 2
     * 
     */
	    // FOR FAMILY STATUS
        public function familystatus() {
            $data['header_css'] = array('admin.css','dashboard.css');
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/family-status/family-status.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        /* Common Functionality */
        public function familystatusmandal($status) {
            if($status == 'family-status' || $status == 'family-health' || $status == 'family') {
                $data['header_css'] = array('admin.css','dashboard.css');
                $id = $this->session->userdata('user')->id;
                $location = $this->session->userdata('user')->location_id;
                $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
                if($status == 'family-status') {
                    $data['url'] = base_url('dashboard/familystatusvillage');
                }
                if($status == 'family-health') {
                    $data['url'] = base_url('dashboard/familyhealthvillage');
                }
                if($status == 'family') {
                    $data['url'] = base_url('dashboard/familyusagevillage');
                }
                $this->load->view('includes/header.php', $data);
                $this->load->view('seniormanager/top-nav.php');
                $this->load->view('seniormanager/side-nav.php');
                $this->load->view('seniormanager/dashboard/family-status/mandals.php', $data);
                $this->load->view('includes/page-footer.php');
                $this->load->view('seniormanager/shortcut-nav.php');
                $this->load->view('includes/plugins.php', $data);
                $this->load->view('common/widget-script.php');
                $this->load->view('scripts/common/modal-script.php');  //modal script
                $this->load->view('includes/footer.php');
            }
            
        }
                
        public function statusdetails($id, $status) {
            // $status = $this->SMDashboardModel->getStatusDetails($id, $status);
            $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                    'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                    'js/plugin/datatable-responsive/datatables.responsive.min.js');
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['url'] = base_url('dashboard/getstatus/').$id. '/'.$status;
            $data['status'] = $this->SMDashboardModel->getServicename($status)->name;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/family-status/status-village-details.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/smdash/status-script.php', $data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
            
        }

        public function getstatus($id, $status) {
            $draw = intval($this->input->get("draw"));
            $start = intval($this->input->get("start"));
            $length = intval($this->input->get("length"));
            $status = $this->SMDashboardModel->getStatusDetails($id, $status);
            $data = array();
            foreach($status->result() as $r) {
                if(isset($r->status) && is_array($r->status)) {
                    $j = 0;
                    $cnt = count($r->status);
                    $status_d = '<ol class="list-inline">';
                    foreach($r->status as $s) {
                        if($s->icon != null) {
                            $status_d .= '<li><i><img src="'.base_url($this->config->item('assets_images').'dashboard/status/').$s->icon . '" title="'.$s->name.'" width="25"></i></li>';
                        }else {
                            if($j == $cnt - 1) {
                                $status_d .= '<li>'.$s->name.'</li>';
                            }else {
                                $status_d .= '<li>'.$s->name.' | </li>';
                            }
                            
                        }
                        $j++;
                    }
                    $status_d .= '</ol>';
                }
                $i = 1;
                $data[] = array(
                    $i,
                    $r->firstname . ' ' . $r->lastname,
                    $status_d,
                    $r->coord_name,
                    $r->vlt_name,
                );
            }
            $output = array(
                "draw" => $draw,
                "recordsTotal" => $status->num_rows(),
                "recordsFiltered" => $status->num_rows(),
                "data" => $data
            );
            
            echo json_encode($output);
            exit();
        }
        /* Common Functionality End */

        /* Family Status */
        public function familystatusvillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['villages'] = $this->SMDashboardModel->getStatusByVillage($location_id, 102);
            $data['status_id'] = 102;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/family-status/status-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        /* Family Status End*/

        /* Family Health*/
        public function familyhealthvillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['villages'] = $this->SMDashboardModel->getStatusByVillage($location_id, 103);
            $data['status_id'] = 103;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/family-status/status-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        /* Family Health End*/

        /* Family Usage */
        public function familyusagevillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['villages'] = $this->SMDashboardModel->getStatusByVillage($location_id, 104);
            $data['status_id'] = 104;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/family-status/status-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        /* Family Usage End*/
        
    /* Family Status Ends*/
	
	/** Govt. Public Welfare - Visit 3
     * 
     */
	    //GOVT PUBLIC WELFARE
        public function govtpublicwelfare() {
            $data['header_css'] = array('admin.css','dashboard.css');
            $id = $this->session->userdata('user')->id;
            $location = $this->session->userdata('user')->location_id;
            $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/govt-welfare/govt-welpublic-mandal.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        
        public function govtpublicwelfvillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $this->load->view('includes/header.php', $data);
            $data['villages'] = $this->SMDashboardModel->getPublicWelfareByVillage($location_id);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/govt-welfare/govt-publicwelf-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        
        public function govtpublicvillagedetail($location_id) {
            $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['location'] = $this->SMDashboardModel->getLocationByID($location_id)->name;
            $data['current_supply'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'current_supply');
            $data['water_supply'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'water_supply');
            $data['pention'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'pention');
            $data['subsidy'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'subsides');
            $data['ration_supply'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'ration_supply');
            $data['runamafi'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'runamafi');
            $data['rythu_bandhu'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'rythu_bandhu');
            $data['rythu_beema'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'rythu_beema');
            $data['govt_schemes'] = $this->SMDashboardModel->getPublicWelfare($location_id, 'govt_schemes');
            // echo '<pre>';
            // print_r($data['location']);
            // exit;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/govt-welfare/govtwelfare-analytics.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/smdash/govtwelfare-script.php', $data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    /* Govt. Public Welfare End*/
	
	/** Govt Projects - Visit 4
     * 
     */
        //GOVT PROJECTS
        public function govtprojects() {
            $data['header_css'] = array('admin.css','dashboard.css');
            $id = $this->session->userdata('user')->id;
            $location = $this->session->userdata('user')->location_id;
            $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/govt-project/govt-project-mandal.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
	
        public function govtprojectvillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $this->load->view('includes/header.php', $data);
            $data['villages'] = $this->SMDashboardModel->getGvtProjectByVillage($location_id);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/govt-project/govt-project-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
	
        public function govtprojectvillagedetail($location_id) {
            $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['location'] = $this->SMDashboardModel->getLocationByID($location_id)->name;
            $data['mission_bhagirath'] = $this->SMDashboardModel->getGovtProject($location_id, 'mission_bhagirath');
            $data['mission_kakatiya'] = $this->SMDashboardModel->getGovtProject($location_id, 'mission_kakatiya');
            $data['kaleshwaram_project'] = $this->SMDashboardModel->getGovtProject($location_id, 'kaleshwaram_project');
            $data['rangareddy_chevella'] = $this->SMDashboardModel->getGovtProject($location_id, 'rangareddy_chevella');
            $data['tsi_pass'] = $this->SMDashboardModel->getGovtProject($location_id, 'tsi_pass');
            $data['t_hub'] = $this->SMDashboardModel->getGovtProject($location_id, 't_hub');
            $data['metro_rail'] = $this->SMDashboardModel->getGovtProject($location_id, 'metro_rail');
            $data['softnet'] = $this->SMDashboardModel->getGovtProject($location_id, 'softnet');
            $data['she_teams'] = $this->SMDashboardModel->getGovtProject($location_id, 'she_teams');
            $data['she_cabs'] = $this->SMDashboardModel->getGovtProject($location_id, 'she_cabs');
            // echo '<pre>';
            // print_r($data['mission_bhagirath']);
            // exit;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            // $this->load->view('seniormanager/dashboard/govt-project/govtproj-analytics.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/smdash/govtproject-script.php', $data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    /* govt Projects End */

	/** Personal Info - Visit 5
     * 
     */
        //PERSONAL INFO
        public function personalinfo() {
            $data['header_css'] = array('admin.css','dashboard.css');
            $id = $this->session->userdata('user')->id;
            $location = $this->session->userdata('user')->location_id;
            $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/personal-info/personal-info-mandal.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        
        public function personalinfovillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $this->load->view('includes/header.php', $data);
            $data['villages'] = $this->SMDashboardModel->getPersonalInfoByVillage($location_id);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/personal-info/personal-info-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        
        public function personalinfovillagedetail($location_id) {
            $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['location'] = $this->SMDashboardModel->getLocationByID($location_id)->name;
            $data['education'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'education');
            $data['profession'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'profession');
            $data['monthly_income'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'monthly_income');
            $data['caste_activity'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'caste_activity');
            $data['political_sympathser'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'political_sympathser');
            $data['last_time_vote'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'last_time_vote');
            $data['digital_village_activity'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'digital_village_activity');
            $data['associations'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'associations');
            $data['hobbies'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'hobbies');
            $data['mobile_data'] = $this->SMDashboardModel->getPersonalInfo($location_id, 'mobile_data');
            // echo '<pre>';
            // print_r($data['caste_activity']);
            // exit;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/personal-info/personalinfo-analytics.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/smdash/personalinfo-script.php', $data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
    /* Personal Info End */
	
	/** Neighbourhood - Visit 6
     * 
     */
        //NEIGHBOURHOOD
        public function neighbourhood() {
            $data['header_css'] = array('admin.css','dashboard.css');
            $id = $this->session->userdata('user')->id;
            $location = $this->session->userdata('user')->location_id;
            $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/neighbourhood/neighbourhood-mandal.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        
        public function neighbourhoodvillage($location_id) {
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['villages'] = $this->SMDashboardModel->getNeighbourReferenceVillages($location_id);
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/neighbourhood/neighbourhood-village.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('common/widget-script.php');
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }
        
        public function neighbourhoodvillagedetail($location_id) {
            $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                    'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                    'js/plugin/datatable-responsive/datatables.responsive.min.js');
            $data['header_css'] = array('admin.css','dashboard.css');
            $data['location'] = $this->SMDashboardModel->getLocationByID($location_id)->name;
            $data['url'] = base_url('dashboard/getreference/').$location_id;
            
            // echo '<pre>';
            // print_r($reference->result());
            // exit;
            $this->load->view('includes/header.php', $data);
            $this->load->view('seniormanager/top-nav.php');
            $this->load->view('seniormanager/side-nav.php');
            $this->load->view('seniormanager/dashboard/neighbourhood/neighbourhood-village-details.php', $data);
            $this->load->view('includes/page-footer.php');
            $this->load->view('seniormanager/shortcut-nav.php');
            $this->load->view('includes/plugins.php', $data);
            $this->load->view('scripts/smdash/neighbour-script.php', $data);
            $this->load->view('scripts/common/modal-script.php');  //modal script
            $this->load->view('includes/footer.php');
        }

        public function getreference($id) {
            $draw = intval($this->input->get("draw"));
            $start = intval($this->input->get("start"));
            $length = intval($this->input->get("length"));
            $reference = $this->SMDashboardModel->getNeighgourReference($id);
            $data = array();
            foreach($reference->result() as $r) {
                
                $i = 1;
                $data[] = array(
                    $i,
                    $r->firstname . ' ' . $r->lastname,
                    $r->ref_name,
                    $r->relationship,
                    $r->mobile,
                    $r->location,
                    $r->coord_name,
                    $r->vlt_name,
                );
            }
            $output = array(
                "draw" => $draw,
                "recordsTotal" => $reference->num_rows(),
                "recordsFiltered" => $reference->num_rows(),
                "data" => $data
            );
            
            echo json_encode($output);
            exit();
        }
    /* Neighbourhood End */
	
	
	//DASHBOARD BRANDINGS
	public function brandings() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/dash-branding.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function social() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/social.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	public function smedia() {
        $data['header_css'] = array('admin.css','dashboard.css');
        //$data['brandimg'] = $this->SMDashboardModel->getBrandingImg();
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/smedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/mobilec.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/smdash/mobilecommunication-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	public function sendsms(){
		if($this->input->post()) {
			$this->load->library('communication');
            $data = $this->input->post();
            $mobile = $data['mobile'];
			$message = $data['message'];
			$send_sms = $this->communication->sendsms($message, $mobile, 'return', '203');
            $storesms = $this->CommunicationModel->storeSMS($data);

            if($storesms && $send_sms) {
                echo json_encode(array('status' => 1, 'status_message' => 'success'));
            }else {
                echo json_encode(array('status' => 0, 'status_message' => 'failed'));
            }
        }
	}
	
	public function smmedia() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/smmedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function cable() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/cable.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function pmedia() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/pmedia.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function giftm() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/giftm.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function sprojects() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/sprojects.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function govtsch() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/govtsch.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function manifesto() {
		$data['header_css'] = array('admin.css','branding.css');
      $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/manifesto.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function develop() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/develop.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function welfare() {
	    $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/welfare.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function mycon() {
	    $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/mycon.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobiletext() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/mobiletext.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function mobilevoice() {
		$data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/branding/mobilevoice.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	
	public function comingsoon() {
		$data['header_css'] = array('admin.css','branding.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        //$this->load->view('seniormanager/branding/gifto.php', $data);
		$this->load->view('seniormanager/dashboard/comingsoon.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('manager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
		
    }
	
	public function recmandal() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/mandal-det.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitmentstatusvillage() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/recruitment-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function registration() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
        $this->load->view('includes/header.php', $data);
		$id = $this->session->userdata('user')->id;
		$registration = $this->SMDashboardModel->getTotalRegistration($id)->result();
		$data['total_register'] = count($registration);
		$lid = $this->session->userdata('user')->location_id;
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
		
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/register-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('scripts/seniormanager/registration-script.php', $data);
        $this->load->view('includes/footer.php');
    }
	public function registerforvillage() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/register-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
		$this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitment() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/number-animate/jquery.easy_number_animate.min.js');
		$id = $this->session->userdata('user')->id;
		$lid = $this->session->userdata('user')->location_id;
		$divisionhead = $this->SMDashboardModel->getTotalDivheadBySM($id)->result();
		$data['total_divhead'] = count($divisionhead);
		$manager = $this->SMDashboardModel->getTotalMandalBySM($id)->result();
		$data['total_mandals'] = count($manager);
		$boothobserver= $this->SMDashboardModel->getBoothObserverCount($lid)->result();	
		$data['total_boothobserver'] = count($boothobserver);
		$tl = $this->SMDashboardModel->getTotalTeamLeaderBySM($id)->result();
		$data['total_Tm'] = count($tl);
		$coordinator = $this->SMDashboardModel->getTotalCoordinatorBySM($id)->result();
		$data['total_coordinator'] = count($coordinator);
		$volunteer = $this->SMDashboardModel->getTotalVolunteerBySM($id)->result();
		$data['total_volunteer'] = count($volunteer);
		$telecaller = $this->SMDashboardModel->getTotalTelecallerBySM($id)->result();
		$data['total_telecaller'] = count($telecaller);
		
		//echo "<pre>"; print_r($data);exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/team-recruitment.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/seniormanager/recruitment-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function recruitmentmanager() {
        $data['header_css'] = array('admin.css','buttons.dataTables.min.css','dashboard.css');
		/* $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js'); */
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
		$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/recruitment-manager.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/seniormanager/team-analysis/manager-list-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//Datatable for recruitment mandal
    public function getRecruitmentMandal() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getTotalMandalBySM($id);
	
        $data = array();
       
        foreach($users->result() as $r) {
            $i = 1;
			$start++;
            $data[] = array(
                $start,
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
    
    // Datatable for recruitment booth observer
	public function recruitmentboothobserver() {
        $data['header_css'] = array('admin.css','dashboard.css','buttons.dataTables.min.css');
		/* $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js'); */
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
		$id = $this->session->userdata('user')->id;
		//$lid = $this->session->userdata('user')->location_id;
		//$users = $this->SMDashboardModel->getTotalBoothObserversBySM($lid);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/recruitment-boothobserver.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        //$this->load->view('scripts/seniormanager/recruitment-view-script.php', $data);
		$this->load->view('scripts/seniormanager/team-analysis/boothobserver-list-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }

    public function getRecruitmentBO() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
		$lid = $this->session->userdata('user')->location_id;
        $users = $this->SMDashboardModel->getTotalBoothObserversBySM($lid);
        $data = array();
		
        foreach($users->result() as $r) {
            $i = 1;
			$start++;
            $data[] = array(
                $start,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                $r->ps_no,
				$r->ps_name,
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

	public function recruitmenttm() {
        $data['header_css'] = array('admin.css','dashboard.css','buttons.dataTables.min.css');
		/* $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js'); */
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
		$id = $this->session->userdata('user')->id;
		$data['mid']=$id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/recruitment-teamleader.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
       // $this->load->view('scripts/seniormanager/recruitment-view-script.php', $data);
	    $this->load->view('scripts/seniormanager/team-analysis/boothpresident-list-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	//Datatable for recruitment TeamLeader
    public function getRecruitmentTeamLeader() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getTotalTeamLeaderBySM($id);
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
			$start++;
            $data[] = array(
                $start,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                $r->ps_no,
				$r->ps_name,
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
	
	public function recruitmentcoordinator() {
        $data['header_css'] = array('admin.css','dashboard.css','buttons.dataTables.min.css');
		/* $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js'); */
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js', 'js/plugin/datatables/dataTables.buttons.min.js', 'js/plugin/datatables/buttons.flash.min.js',
                                'js/plugin/datatables/jszip.min.js', 'js/plugin/datatables/pdfmake.min.js', 'js/plugin/datatables/vfs_fonts.js', 'js/plugin/datatables/buttons.html5.min.js',
                                'js/plugin/datatables/buttons.print.min.js');
		$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/recruitment-coordinator.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        //$this->load->view('scripts/seniormanager/recruitment-view-script.php', $data);
		 $this->load->view('scripts/seniormanager/team-analysis/sheetpresident-list-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	
	//Datatable for recruitment Coordinator
    public function getRecruitmentCoordinator() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getTotalCoordinatorBySM($id);
	
        $data = array();
        foreach($users->result() as $r) {
            $i = 1;
			$start++;
            $data[] = array(
                $start,
                $r->first_name . ' ' . $r->last_name,
                $r->mobile,
                $r->ps_no,
				$r->ps_name,
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
	
	public function recruitmentvolunteer() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
		$id = $this->session->userdata('user')->id;
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/recruitment-volunteer.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/seniormanager/recruitment-view-script.php', $data);
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	
	
	//Datatable for recruitment Volunteer
    public function getRecruitmentVolunteer() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
		$id = $this->session->userdata('user')->id;
        $users = $this->SMDashboardModel->getTotalVolunteerBySM($id);
	
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
	
	
	public function performance() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
		$id = $this->session->userdata('user')->id;
		$lid = $this->session->userdata('user')->location_id;
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
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/team-analysis/performance.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
		$this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
	/*-------Changes for Amberpet modules and visits */
	/* MODI SCHEMES
		Visit 21*/
	public function modischemes() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/modi-schemes/modi-schemes.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function modischemescolonies($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $data['colonies'] = $this->SMDashboardModel->getModiSchemesLeadByDivision($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/modi-schemes/modi-schemes-colonies.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	 public function modischemesanalytic($location_id) {
        $data['plugins'] = array('js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['location'] = $this->SMDashboardModel->getLocationByID($location_id)->name;
        $data['jan_dhan'] = $this->SMDashboardModel->getModiSchemes($location_id, 'jan_dhan');
        $data['beti_bachao'] = $this->SMDashboardModel->getModiSchemes($location_id, 'beti_bachao'); 
        $data['make_india'] = $this->SMDashboardModel->getModiSchemes($location_id, 'make_india'); 
        $data['swatch_bhart'] = $this->SMDashboardModel->getModiSchemes($location_id, 'swatch_bhart'); 
        $data['digital_india'] = $this->SMDashboardModel->getModiSchemes($location_id, 'digital_india'); 
        $data['build_toilets'] = $this->SMDashboardModel->getModiSchemes($location_id, 'build_toilets'); 
        $data['one_pension'] = $this->SMDashboardModel->getModiSchemes($location_id, 'one_pension'); 
        $data['seventh_pay'] = $this->SMDashboardModel->getModiSchemes($location_id, 'seventh_pay'); 
        $data['suraksha_beema'] = $this->SMDashboardModel->getModiSchemes($location_id, 'suraksha_beema'); 
        $data['jeevan_jyoti_beema'] = $this->SMDashboardModel->getModiSchemes($location_id, 'jeevan_jyoti_beema'); 
        
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/modi-schemes/modi-schemes-analytics.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/modischeme-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	/* End Visit 21 */
	
	/**
     * Visit 22 - Health Needs
     */
    public function healthneeds() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/health-needs/health-need.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function healthneedscolonies($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $data['colonies'] = $this->SMDashboardModel->getHealthLeadsByDivision($location_id);
        //echo '<pre>'; print_r($data['colonies']); exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/health-needs/health-need-colonies.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function healthanalytics($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                            'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/gethealthservice/').$id;
        //$data['service'] = $this->SMDashboardModel->getServicename($service_id)->name;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/health-needs/health-need-analytic.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function gethealthservice($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->gethealthserviceDetails($id);
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->name,
                ($r->status == 0) ? 'Pending' : 'Solved',
                $r->coord_name,
                $r->vlt_name,
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
    /* Visit 22 Ends*/
	
	
	/*JOB NEEDS Visit 23*/
	
	public function jobneeds() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/job-needs/job-need.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function jobneedscolonies($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $data['colonies'] = $this->SMDashboardModel->getJobNeedsLeadsByDivision($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/job-needs/job-need-colonies.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	 public function jobneedsanalytics($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                            'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getjobneedsservice/').$id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/job-needs/job-need-analytic.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function getjobneedsservice($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getJobneedsserviceDetails($id);
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->name,
                ($r->status == 0) ? 'Pending' : 'Solved',
                $r->coord_name,
                $r->vlt_name,
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
	/*End Visit 23 */
	
	/** Visit 24 CERTIFICATE NEEDS */
	public function certificateneeds() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/certificate-needs/certificate-need.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function certificateneedscolonies($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $data['colonies'] = $this->SMDashboardModel->getCertificateLeadsByDivision($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/certificate-needs/certificate-need-colonies.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function certioficateanalytics($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                            'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getcertificateservice/').$id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/certificate-needs/certificate-need-analytic.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function getcertificateservice($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getCertificateserviceDetails($id);
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->name,
                ($r->status == 0) ? 'Pending' : 'Solved',
                $r->coord_name,
                $r->vlt_name,
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
	/**
     * END Visit 24  */
	 
	 /** Visit 25 IDCARD NEEDS */
	public function idcardneeds() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/idcard-needs/idcard-need.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function idcardneedscolonies($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $data['colonies'] = $this->SMDashboardModel->getIdCardLeadsByDivision($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/idcard-needs/idcard-need-colonies.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    
    public function idcardanalytics($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                            'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getidcardservice/').$id;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/idcard-needs/idcard-need-analytic.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function getidcardservice($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getidcardserviceDetails($id);
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->name,
                ($r->status == 0) ? 'Pending' : 'Solved',
                $r->coord_name,
                $r->vlt_name,
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
	/* End Visit 25 */
	
	/** Other Request */
	public function otherrequest() {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id;
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/other-request/other-request.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function otherrequestcolonies($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $id = $this->session->userdata('user')->id;
        $data['colonies'] = $this->SMDashboardModel->getOtherRequestLeadsByDivision($location_id);
        //echo '<pre>'; print_r($data['colonies']); exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/other-request/other-request-colonies.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }

    public function otherrequestdata($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                            'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                            'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getotherrequestservice/').$id;
        //$data['service'] = $this->SMDashboardModel->getServicename($service_id)->name;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/other-request/other-request-analytic.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function getotherrequestservice($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getotherrequestserviceDetails($id);
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->name,
                $r->status,
                $r->coord_name,
                $r->vlt_name,
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
	/* End Other Request */
    
    /* New Visits */
	/* Visit 27 */
	public function govschemesvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getGovSchemesByPS($location_id);
        // echo '<pre>'; print_r($data['villages']); exit;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('seniormanager/dashboard/lead-assistance/govt-visit-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('seniormanager/dashboard/lead-assistance/govt-project-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
        $data['url'] = base_url('dashboard/getgovtprojectdetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
    
    /* Visit 29 */
	public function bangarutelanganavillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getBangaruTelanganaByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('seniormanager/dashboard/lead-assistance/bengaru-telengana-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function bangarutelanganadetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getbangarutelanganadetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }

	public function getbangarutelanganadetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getBangaruTelanganaDetails($id);
		
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
	/*End Visit 29 */
    
    /* Visit 30 */
	public function govtachievvillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getGovtAchievByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('seniormanager/dashboard/lead-assistance/govt-achive-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    
    public function govtachievdetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getgovtachievdetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
    
    public function getgovtachievdetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getGovtAchievDetails($id);
		
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
	/*End Visit 30 */
    
    /* Visit 31 */
	public function schemebenifivillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getSchemeBanifVillage($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govt-schemebenif-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    
    public function govtschemebenifidetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getgovtschemebenifidetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
    
    public function getgovtschemebenifidetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getGovtSchemesBenificiaryDetails($id);
		
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->coord_name,
                (isset($r->vlt_name)) ? $r->vlt_name : '-',
				//($r->status == 1) ? 'Explained' : 'Pending',
				$r->name,
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
	/*End Visit 31 */
    
    /* Visit 32 */
	public function pensionbenifivillage($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getPensionBanifVillage($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govt-pension-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function govtpensionbenifidetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getgovtpensionbenifidetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtvisit-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
    
	public function getgovtpensionbenifidetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getGovtPensionBenificiaryDetails($id);
		
        $data = array();
        foreach($ha->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->firstname . ' ' . $r->lastname,
                $r->coord_name,
                (isset($r->vlt_name)) ? $r->vlt_name : '-',
				//($r->status == 1) ? 'Explained' : 'Pending',
				$r->name,
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
    /*End Visit 32 */
    
    /* Visit 33 */
	public function govtfailure($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getGovtFailureByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('seniormanager/dashboard/lead-assistance/govt-failure-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
    
    public function govtfailuredetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getgovtfailuredetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtfail-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
    
    public function getgovtfailuredetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getFailureDetails($id);
		
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
    /*End Visit 33 */
    
    /* Visit 34 */
	public function partymanifesto($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getGovtManifestoByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        if($data['villages']) {
            $this->load->view('seniormanager/dashboard/lead-assistance/govt-manifesto-village.php', $data);
        }else {
            $data['content'] = 'No Registrations for this division yet.';
            $this->load->view('common/no-data.php', $data);
        }
        
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');
    }
	
	public function govtmanifestodetails($id) {
        $data['plugins'] = array('js/plugin/datatables/jquery.dataTables.min.js', 'js/plugin/datatables/dataTables.colVis.min.js',
                                'js/plugin/datatables/dataTables.tableTools.min.js', 'js/plugin/datatables/dataTables.bootstrap.min.js',
                                'js/plugin/datatable-responsive/datatables.responsive.min.js');
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['url'] = base_url('dashboard/getgovtmanifestodetails/').$id;
		//$this->SMDashboardModel->getGovtSchemesDetails($id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/govtmanifesto-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/smdash/service-script.php', $data);
        $this->load->view('scripts/common/modal-script.php');  //modal script
        $this->load->view('includes/footer.php');	
    }
	
	public function getgovtmanifestodetails($id) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $ha = $this->SMDashboardModel->getManifestoDetails($id);
		
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
    /*End Visit 34 */
    
    /* Visit 35 */

    /* End Visit 35 */
    public function personalservices($location_id) {
        $data['header_css'] = array('admin.css','dashboard.css');
        $data['villages'] = $this->SMDashboardModel->getServiceVisitByPS($location_id);
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/personal-service-village.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/lead-assistance/personalservice-village-details.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
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
    
    public function validationreports() {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/validation-report/validation-report-for.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/validation-teamreport-script.php');
        $this->load->view('includes/footer.php');
    }

	public function telecaller($rid) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$data['rid'] = $rid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/dashboard/validation-report/telecaller-report.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/validation-teamreport-script.php');
        $this->load->view('includes/footer.php');
    }
	public function telecallermandals($rid,$role) {
        $data['header_css'] = array('admin.css','dashboard.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$id = $this->session->userdata('user')->id;
        $location = $this->session->userdata('user')->location_id; 
        $data['srm_mandal'] = $this->SeniorManagerModel->getMandalsByConstituence($location);
		$data['role'] = $role;
		$data['rid'] = $rid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
		
        $this->load->view('seniormanager/dashboard/validation-report/telecaller-mandal.php', $data);
		
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/validation-teamreport-script.php');
        $this->load->view('includes/footer.php');
    }

	public function tellecallerps($rid,$role,$lid) {
        $data['header_css'] = array('admin.css','myteam.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
	    $data['userps'] = $this->SMDashboardModel->getUserByMandal($lid,$role);
		$data['role']	= $role;
		$data['rid']	= $rid;
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
		
        $this->load->view('seniormanager/dashboard/validation-report/telecaller-ps.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/validation-teamreport-script.php');
        $this->load->view('includes/footer.php');
    }
	
	public function telecallerquest($rid,$id,$role) {
        $data['header_css'] = array('admin.css','myteam.css');
		$data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$data['report1'] = $this->SeniorManagerModel->getQuestionByReport($role,1);
		$data['report2'] = $this->SeniorManagerModel->getQuestionByReport($role,2);
		$data['report3'] = $this->SeniorManagerModel->getQuestionByReport($role,3);
		
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
		if($rid==1)
		{$this->load->view('seniormanager/dashboard/validation-report/telecaller-question.php', $data);}
		else
		{$this->load->view('seniormanager/dashboard/validation-report/validation-details.php', $data);}
        $this->load->view('includes/page-footer.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('scripts/seniormanager/validation-teamreport-script.php');
        $this->load->view('includes/footer.php');
    }

    public function votervisits() {
        $data['header_css'] = array('admin.css', 'priority-list.css','dashboard.css');
        $this->load->view('includes/header.php', $data);
        $this->load->view('seniormanager/top-nav.php');
        $this->load->view('seniormanager/side-nav.php');
        $this->load->view('seniormanager/voter-visits.php', $data);
        $this->load->view('includes/page-footer.php');
        $this->load->view('seniormanager/shortcut-nav.php');
        $this->load->view('includes/plugins.php', $data);
        $this->load->view('common/widget-script.php');
        $this->load->view('scripts/common/modal-script.php'); //modal script
        $this->load->view('includes/footer.php');
    }
 }