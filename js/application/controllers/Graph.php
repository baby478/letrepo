<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Graph extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{   $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$this->load->view('includes/header.php');
		$this->load->view('bargraph',$data);
		$this->load->view('includes/page-footer.php');
		//$this->load->view('scripts/graph/anaiytic-reg-script.php',$data);
		//$this->load->view('scripts/graph/chart-script.php',$data);
	}
	public function pie()
	{   $data['plugins'] = array('js/plugin/jquery-form/jquery-form.min.js', 'js/plugin/moment/moment.min.js', 'js/plugin/chartjs/chart.min.js');
		$this->load->view('includes/header.php');
		$this->load->view('piechart',$data);
		$this->load->view('includes/page-footer.php');
		//$this->load->view('scripts/graph/anaiytic-reg-script.php',$data);
		//$this->load->view('scripts/graph/chart-script.php',$data);
	}
}
