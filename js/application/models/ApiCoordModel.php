<?php
class ApiCoordModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function voterExists() {
        $voter_id = $this->input->post('voter_id');
        $this->db->select('id');
        $this->db->where('voter_id', $voter_id);
        $result = $this->db->get('tbl_voters')->result();
        if(count($result) > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function addVoterDetails($data) {
        $data = $this->sanitizeInput($data);
        $id = $this->db->insert('tbl_voters', $data);
       
        if($id) {
            return $id;
        }else {
            return false;
        }
    }

    public function sanitizeInput(array $data) {
        foreach($data as $dt) {
            $this->db->escape($dt);
        }
        return $data;
    }

    public function addEvent($data) {
        $data = $this->sanitizeInput($data);
        $id = $this->db->insert('tbl_events', $data);
       
        if($id) {
            return $id;
        }else {
            return false;
        }
    }

}