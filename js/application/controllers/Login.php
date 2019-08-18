<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('loginModel');
    }

    public function index() {
        if($this->input->post()) {
            if($this->form_validation->run() === TRUE) {
                $email = $this->input->post('email');
                $pass = $this->input->post('password');
                $login = $this->loginModel->userLogin($email, $pass);
                // echo '<pre>'; print_r($login); exit;
                if($login) {
                    if($login->status == 0) {
                        $this->session->set_flashdata('login_error', '<div class="alert alert-danger fade in"><i class="fa-fw fa fa-times"></i><strong>Error!</strong> Your account is deactivated. Please contact admin</div>');
                    }else if($login->status == 1) {
                        $this->loginModel->userLog($login->id); //log login time
                        $this->session->set_userdata('user', $login);
                        if($login->user_role == 1) {
                            redirect(base_url('administrator'));
                        }elseif ($login->user_role == 137) {
                            redirect(base_url('manager'));
                        }elseif ($login->user_role == 44) {
                            redirect(base_url('SeniorManager'));
                        }elseif ($login->user_role == 57) {
                            redirect(base_url('GeneralManager'));
                        }elseif ($login->user_role == 59) {
                            redirect(base_url('President'));
                        }elseif ($login->user_role == 81) {
                            redirect(base_url('Support'));
                        }
                    }
                }else {
                    $this->session->set_flashdata('login_error', '<div class="alert alert-danger fade in"><i class="fa-fw fa fa-times"></i><strong>Error!</strong> Invalid email or Password</div>');
                }
    
            }
        }
		$data['bg']='loginbg';
        $data['html_id'] = 'extr-page';
        $this->load->view('includes/header.php', $data);
        $this->load->view('includes/top-nav.php');
        $this->load->view('login.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('login/custom-scripts.php');
        $this->load->view('includes/footer.php');
    }

    public function _email_exists($email) {
        if(is_numeric($email)) {
            if(!preg_match("/^[0-9]{10}$/", $email)) {
                $this->form_validation->set_message('_email_exists', 'Phone number is not valid');
                return FALSE;
              }else {
                  $exists = $this->loginModel->phoneExists($email);
                  if($exists) {
                      return TRUE;
                  }else {
                    $this->form_validation->set_message('_email_exists', 'This number does not exists');
                    return FALSE;
                  }
              }
        }else {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->form_validation->set_message('_email_exists', 'Email is not valid');
                return FALSE;
            }else {
                $exists = $this->loginModel->emailExists($email);
                if($exists) {
                    return TRUE;
                }else {
                    $this->form_validation->set_message('_email_exists', 'The {field} is not exists');
                    return FALSE;
                    
                }
            }
            
        }
        
    }

    public function logout() {
        if($this->session->has_userdata('user')) {
            $id = $this->session->userdata('user')->id;
            $this->loginModel->logoutLog($id);
            $this->session->unset_userdata('user');
            redirect(base_url('login'));
        }
    }

    public function forgetpassword() {
        $data['html_id'] = 'extr-page';
		$this->load->view('includes/header.php', $data);
        $this->load->view('includes/top-nav.php');
        $this->load->view('forget-password.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/common/forgetpassword-script.php');
        $this->load->view('login/custom-scripts.php');
        $this->load->view('includes/footer.php');
	}
	
	public function generatepassword(){
        $data['header_css'] = array('login.css');
		$this->load->view('includes/header.php', $data);
        $this->load->view('includes/top-nav.php');
        $this->load->view('generate-password.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/login/generate-password-script.php');
        $this->load->view('includes/footer.php');
    }
    
    public function verifymobile() {
        if($this->input->post()) {
            $data = $this->input->post();
            $mobile = $data['mobile'];
            $mobile_exists = $this->loginModel->verifyUser($mobile);
            if($mobile_exists['id'] == 1) {
                $send_otp = $this->loginModel->generateOtp($mobile_exists['uid']);
                if($send_otp) {
                    $this->load->library('communication');
                    $message = 'Your One Time Password to generate password is ' . $send_otp . '. Use this to verify.';
                    $sms = $this->communication->sendsms($message, $mobile, 'return', '3');
                }
                $mobile_exists['otp'] = $send_otp;
            }
            echo json_encode($mobile_exists);    
        }
    }
    
    public function verifyotp() {
        if($this->input->post()) {
            $data = $this->input->post();
            $user_id = $data['user'];
            $otp = $data['otp'];
            $verify = $this->loginModel->verifyOtp($user_id, base64_encode($otp));
            if($verify) {
                $result['id'] = 1;    
            }else {
                $result['id'] = 0;
            }
        }
        echo json_encode($result);
    }

    public function generatenewpassword() {
        if($this->input->post()) {
            $data = $this->input->post();
            $user_id = $data['user'];
            $password = $data['password'];
            $verify = $this->loginModel->generatePassword($data);
            if($verify) {
                $result['id'] = 1;
                $result['msg'] = 'Password created successfully. You can login now';
            }else {
                $result['id'] = 0;
                $result['msg'] = 'Internal server error. We could not complete your request';
            }
        }
        echo json_encode($result);
    }
    
}