<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('apiModel');
    }

    public function getStates() {
        $states = $this->apiModel->getAllStates();
        echo $this->__jsonResult($states);
    }

    public function getConstituencyByState($id) {
        $result = $this->apiModel->getAllConstituencyByState($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    //Datatable
    public function getConstituencyDT() {
        //Data variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $constituencies = $this->apiModel->getConstituencyByDetails();
        
        $data = array();

        foreach($constituencies->result() as $r) {
            $i = 1;
            $data[] = array(
                $i,
                $r->Constituency,
                $r->District,
                $r->State,
                '<a href="'. base_url('party/demographics/'. $r->Sid. '/'. $r->id).'" class="btn btn-default"><i class="fa fa-eye"></i></a>
                <a href="" class="btn btn-default"><i class="fa fa-trash-o"></i></a>'
                
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $constituencies->num_rows(),
            "recordsFiltered" => $constituencies->num_rows(),
            "data" => $data
        );
        echo json_encode($output);
        exit();
    }
    
    public function __jsonResult(Array $data) {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function getDistrictByState($id) {
        $result = $this->apiModel->getAllDistrictByState($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getMandalByDistrict($id) {
        $result = $this->apiModel->getAllMandalByDistrict($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getVillageByMandal($id) {
        $result = $this->apiModel->getAllVillageByMandal($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getMandalInchargeByMandal($id) {
        $result = $this->apiModel->getAllMandalInchargeByMandal($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getBManagerByVillage($id) {
        $result = $this->apiModel->getAllBManagerByVillage($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getPollingStationByMandal($id) {
        $result = $this->apiModel->getPSByMandal($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getReligion() {
        $result = $this->apiModel->getLookupValueById(7);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getGroup() {
        $result = $this->apiModel->getLookupValueById(10);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getRelationship() {
        $result = $this->apiModel->getLookupValueById(11);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }

    public function getCategory() {
        $result = $this->apiModel->getLookupValueById(8);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }

    public function getCasteByCategory($id) {
        $result = $this->apiModel->getCaste($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }

    public function getPollingstationByVillage($id) {
        $result = $this->apiModel->getPollingStationByVillage($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }
	// for booth agent
	public function getBoothAgentByPS($id) {
        $result = $this->apiModel->getBoothAgentByPollingStation($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }
	public function getMemberByRole($role) {
        $result = $this->apiModel->getMemberByGroup($role);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function getCoordinatorByManager($id) {
        $result = $this->apiModel->getCoordinatorByMng($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }
	
	public function getMyCalendarTasks() {
		$id = $this->session->userdata('user')->id;
        $result = $this->SeniorManagerModel->getMyTasks($id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }

    public function pageDescription($path) {
        $result = $this->apiModel->getPageDescription($path);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }
	/* changes for admin panel*/
	public function getPartyName() {
        $result = $this->apiModel->getAllParties();
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }
	public function getPS() {
        $result = $this->apiModel->getPollingStations();
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        } 
    }
	public function getMandalsByCon() {
        $result = $this->apiModel->getAllMandalsByConst(3545);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }
	
	public function getVillageByConst() {
        $result = $this->apiModel->getAllVillages();
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }
	public function getUsersByRole($role,$id) {
        $result = $this->apiModel->getUserByRole($role,$id);
        if($result == true && count($result) > 0) {
            echo $this->__jsonResult($result);
        }else {
            return false;
        }
    }
}