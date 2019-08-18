<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('DownloadModel');	
    }

    public function app($app) {
        $data['header_css'] = array('download.css');
        $this->load->view('includes/header.php', $data);
        // $this->load->view('admin/top-nav.php');
        if($app != '') {
            if($app == 'sheetpresident') {
                $data['app'] = 'sheetpresident';
                $data['app_id'] = 78;
            }elseif($app == 'boothpresident') {
                $data['app'] = 'boothpresident';
                $data['app_id'] = 79;
            }elseif($app == 'boothobserver') {
                $data['app'] = 'boothobserver';
                $data['app_id'] = 81;
            }
            $this->load->view('download/appdownload.php', $data);
        }else {
            $data['content'] = 'No Content';
            $this->load->view('common/no-content.php');
        }
        $this->load->view('includes/plugins.php');
        $this->load->view('scripts/download/appdownload-script.php');
        $this->load->view('includes/footer.php');
    }

    public function verifymobile() {
        if($this->input->post()) {
            $data = $this->input->post();
            $mobile = $data['mobile'];
            $app = $data['app'];
            $mobile_exists = $this->DownloadModel->verifyUser($mobile, $app);
            if($mobile_exists['id'] == 1) {
                $send_otp = $this->DownloadModel->generateOtp($mobile_exists['uid']);
                if($send_otp) {
                    $this->load->library('communication');
                    $message = 'Your One Time Password to download app is ' . $send_otp . '. Use this to verify and download.';
                    $sms = $this->communication->sendsms($message, $mobile, 'return', '3');
                }
            }
            echo json_encode($mobile_exists);
            
        }
    }

    public function verifyotp() {
        if($this->input->post()) {
            $data = $this->input->post();
            $user_id = $data['user'];
            $otp = $data['otp'];
            $app_id = $data['app'];
            $verify = $this->DownloadModel->verifyOtp($user_id, base64_encode($otp));
            if($verify) {
                $token = bin2hex(random_bytes(64));
                $hash_token = sha1($token.$user_id);
                $download_update = $this->DownloadModel->downloadUpdate($user_id, $app_id, $hash_token);
                $result['id'] = 1;
                if($app_id == 78) {
                    $result['url'] = base_url('download/appdownload/').$user_id . '/' .$app_id . '/'.$token;
                }else if($app_id == 79) {
                    $result['url'] = base_url('download/appdownload/').$user_id . '/'.$app_id . '/'.$token;
                }else if($app_id == 81) {
                    $result['url'] = base_url('download/appdownload/').$user_id . '/'.$app_id . '/'.$token;
                }
            }else {
                $result['id'] = 0;
            }
        }
        echo json_encode($result);
    }

    public function appdownload($user_id, $app_id, $token) {
        $data['header_css'] = array('download.css');
        $this->load->view('includes/header.php', $data);

        $token = trim($token);
        if($user_id != '' && $app_id != '' && $token != '') {
            $hash = $this->DownloadModel->getDownloadHash($user_id, $app_id);
            if($hash->token == sha1($token.$user_id)) {
                if($app_id == 78) {
                    $file = 'apps/sheetpresident.apk';
                }elseif($app_id == 79) {
                    $file = 'apps/boothpresident.apk';
                }elseif($app_id == 81) {
                    $file = 'apps/boothobserver.apk';
                }
                //echo $file . '<br>';
                // $contentDisposition = 'attachment';
                if(file_exists($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/vnd.android.package-archive');
                    header('Content-Disposition: attachment; filename='.basename($file));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));
                    ob_clean();
                    flush();
                    readfile($file);
                    exit;
                } else {
                    $data['content'] = 'Something went wrong!  <a href="'.base_url('download/app/').$app_id.'">Try again.</a>';
                    $this->load->view('common/no-data.php');
                } 
            }else {
                $data['content'] = 'Something went wrong!  <a href="'.base_url('download/app/').$app_id.'">Try again.</a>';
                $this->load->view('common/no-content.php');
            }
        }
        $this->load->view('includes/plugins.php');
        $this->load->view('includes/footer.php');
    }

    public function sheetcoordinator() {
        $file = 'apps/sheetcoordinator.apk';
        if(file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.android.package-archive');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
    }
}