<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends CI_Controller {
    
    private $allocation_status;
    private $_id;
    
    public function __construct() {
        parent::__construct();
        if(!$this->session->has_userdata('user')) {
            redirect(base_url());
        }elseif($this->session->userdata('user')->user_role != 2) {
            redirect(base_url());
        }
        $this->load->model('loginModel');
        $this->load->model('EmailModel');
        $this->load->model('apiModel');
        $this->_id = $this->session->userdata('user')->id;
        $this->_alloc_status();
    }

    private function _alloc_status() {
        $id = $this->session->userdata('user')->id;
        $status = $this->EmailModel->checkAllocStatus($id);
        if($status > 0) {
            $this->allocation_status = true;
        }else {
            $this->allocation_status = false;
        }
    }

    public function index() {
        $user_data = $this->session->userdata('user');
        $id = $user_data->id;
        $data['total_voters'] = $this->EmailModel->getVotersByManager($id)->num_rows();
        $data['pos_voters'] = $this->EmailModel->getVotersByManager($id, array('v.voter_status' => 12))->num_rows();
        $data['neu_voters'] = $this->EmailModel->getVotersByManager($id, array('v.voter_status' => 14))->num_rows();
        $count_village = $this->EmailModel->countVotersByVillage($id);
        $count_village_status = $this->EmailModel->countVotersByStatusVillage($id, 12);
        $village = array();
        
        foreach($count_village as $k => $vill) {
            $village[$k]['id'] = $vill->id;
            $village[$k]['name'] = $vill->name;
            $village[$k]['total'] = $vill->total;
            $village[$k]['positive'] = 0;
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
        $exists = $this->EmailModel->phoneExists($phone);
        if($exists) {
            $this->form_validation->set_message('_phone_exists', 'The {field} is already exists');
            return FALSE;
        }else {
            return TRUE;
        }
    }

    public function assignRole() {
        if($this->input->post()) {
            if($this->form_validation->run() === TRUE) {
                $user_id = $this->input->post('user');
                $role_id = $this->input->post('user-role');
                $update_role = $this->EmailModel->assignUserRole($user_id, $role_id);
                if($update_role) {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-success fade in"><strong>Success!</strong> User role assigned successfully.</div>');
                }else {
                    $this->session->set_flashdata('user_role', '<div class="alert alert-danger fade in"><strong>Error!</strong> An error occurred.</div>');
                }
            }
        }
        $data['user_roles'] = $this->EmailModel->getAssignRole();
        $data['users'] = $this->EmailModel->getUserByRole(17);
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
        $this->load->view('includes/footer.php');
    }

    //datatable
    public function getusers() {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $users = $this->EmailModel->getUsers();

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
        $this->load->view('scripts/manager/inbox-script.php');
        $this->load->view('includes/footer.php'); 
    }

    

    public function composeemail(){
            $user_data = $this->session->userdata('user');
            $id = $user_data->id;
            $userole=$user_data->user_role;
            //print_r($user_data);
            //exit;
            $data['emaildata'] = $this->EmailModel->emailsList($userole);
                 //$emaildata= $this->EmailModel->emailsList();
      //print_r($data);
      //exit;
        $this->load->view('email/email-compose',$data);
    }


     public function emaillist(){
        $user_data = $this->session->userdata('user');
        $id = $user_data->id;
         $data['emailslist'] = $this->EmailModel->inboxmails($id);
         //print_r($data);
         //exit;
        $this->load->view('email/email-list.php',$data);


    }

    public function reply_email(){
        //$user_data = $this->session->userdata('user');
        //$id = $user_data->id;
         //$data['emailslist'] = $this->EmailModel->inboxmails($id);
         //print_r($data);
         //exit;
        $this->load->view('email/email-reply.php');


    }
    public function folderdatas(){
           $user_data = $this->session->userdata('user');
           $id = $user_data->id;
           $data['emailslist'] = $this->EmailModel->inboxmails($id);
         
          $this->load->view('email/folder-list.php',$data); 
    }


     public function trashdata(){
        $user_data = $this->session->userdata('user');
        $id = $user_data->id;
         $data['emailslist'] = $this->EmailModel->inboxmails($id);
         //print_r($data);
         //exit;
        $this->load->view('email/trash-list.php',$data);


    }
     public function important(){
       $data12=$this->input->post('maildata');
             foreach($data12 as $datavalue){
              $this->EmailModel->important($datavalue);
             }
    }
    public function sentmaildata(){
        $user_data = $this->session->userdata('user');
        $id = $user_data->id;
          $data['emailslist'] = $this->EmailModel->sentFolderData($id);
         //print_r($data);
         //exit;
        $this->load->view('email/sent-list.php',$data);
    }

   public function emailviewdata(){
         
       $value=$this->input->post('value');
         $data['emailcontent']=$this->EmailModel->particularContentdata($value);
         $this->EmailModel->flag($value);
         $this->load->view('email/email-opened.php',$data);     
//$this->load->view('email-opened.php');

   }

    public function sentmail(){
        $user_data = $this->session->userdata('user');
        $id = $user_data->id;
      //$data['emaildata'] = $this->EmailModel->countVotersByVillage($id);
        $this->load->view('email/email-list.php');
    }


    public function viewmail(){
           $this->load->view('email/email-opened.php');
    }


    public function trash(){
            $data12=$this->input->post('maildata');
             foreach($data12 as $datavalue){
              $this->EmailModel->trashData($datavalue);
             }
            
    }

    public function folderstatus(){
            $data12=$this->input->post('maildata');
             foreach($data12 as $datavalue){
              $this->EmailModel->folderdata($datavalue);
             }
            
    }

     


    public function sendemail(){
             $user_data = $this->session->userdata('user');
             $senderid = $user_data->id;

            $alldata=$this->input->post();
            $emailids=$this->input->post('emails');
            $cc=json_encode($this->input->post('emailscc'));
            $bcc=json_encode($this->input->post('emailsbcc'));
            $subject=$this->input->post('subject');
            $emailbody=$this->input->post('emailbody');
            $date=date('Y-m-d H:i:s');   

            
            if($cc!=null){
                 $cc=json_encode($this->input->post('emailscc'));  
            }else{
                $cc=0;
            }

            if($bcc!=null){
                 $bcc=json_encode($this->input->post('emailscc'));  
            }else{
                $bcc=0;
            }
            //print_r($alldata);
            //exit;
             $data = array(
             'subject'=>$subject,
             'description'=>$emailbody,
             'created_date'=>$date
            );

            $insertval = $this->EmailModel->enterEmailContent($data);

          //for image uploads
            
          $data = array();
          if(!empty($_FILES['userFiles']['name'])){
            $filesCount = count($_FILES['userFiles']['name']);
            for($i = 0; $i < $filesCount; $i++){
                $_FILES['userFile']['name'] = rand('0000','9999').$_FILES['userFiles']['name'][$i];
                $_FILES['userFile']['type'] = $_FILES['userFiles']['type'][$i];
                $_FILES['userFile']['tmp_name'] = $_FILES['userFiles']['tmp_name'][$i];
                $_FILES['userFile']['error'] = $_FILES['userFiles']['error'][$i];
                $_FILES['userFile']['size'] = $_FILES['userFiles']['size'][$i];
 
                $uploadPath = 'uploads/';
                $config['upload_path'] = $uploadPath;
                $config['allowed_types'] = 'gif|jpg|png';
                
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if($this->upload->do_upload('userFile')){
                    $fileData = $this->upload->data();
                    $date=date("Y-m-d H:i:s");
                    $filenamedata=$fileData['file_name'];

                    
                    $uploadData[$i]['file_name'] = $fileData['file_name'];
                    $uploadData[$i]['created'] = date("Y-m-d H:i:s");
                    $uploadData[$i]['modified'] = date("Y-m-d H:i:s");
                    $uploadData[$i]['filesize'] = $fileData['file_size'];
                }
            }

            print_r($uploadData);
            //exit;

            foreach($uploadData as $data12){
              echo $data12['file_name'];

               $data = array(
                'file_name'=>$data12['file_name'],
                'email_id'=>$insertval,
                'file_size'=>$data12['filesize'],
                 'updated_at'=>$data12['modified']
            );
             $insert = $this->EmailModel->filesUpload($data);
            }
            //exit;
            
           $statusMsg = "Files uploaded successfully.':'Some problem occurred, please try again";
                 $this->session->set_flashdata('statusMsg',$statusMsg);
        }
           
      
          //image upload ends


            
            foreach($emailids as $emailId){
               
                   $enterdata = $this->EmailModel->IndividualEmailEnter($senderid,$emailId,$cc,$bcc,$insertval); 
            }

            redirect(base_url('email/inbox'));
            //print_r($insertval);
            //$this->load->view('email-list.php');
            //print_r($alldata);
           // exit;


    }

public function test() {
    echo 'hi';
}    
}