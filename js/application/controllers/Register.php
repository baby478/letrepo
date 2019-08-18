<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller {
    public function __consturct() {
        parent::__construct();
    }

    public function index() {
        $data['html_id'] = 'extr-page';
        $this->load->view('includes/header.php', $data);
        $this->load->view('includes/top-nav.php');
        $this->load->view('register.php');
        $this->load->view('includes/plugins.php');
        $this->load->view('login/custom-scripts.php');
        $this->load->view('includes/footer.php');
    }
}