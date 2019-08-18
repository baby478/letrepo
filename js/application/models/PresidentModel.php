<?php
class PresidentModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function getConstituencyDistrict($id, $const_type) {
        $this->db->select('l.id, l.name, lm.map');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->join('tbl_location_map as lm', 'lm.location_id = l.id');
        $this->db->where('cl.level_id', $const_type);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getLokSabhaConstituency($id) {
        $this->db->select('l.id, l.name, lm.map');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_location_map as lm', 'lm.location_id = l.id');
        $this->db->where('l.level_id', 58);
        $this->db->where('l.parent_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getConstituencyByDistrict($id) {
        $this->db->select('id, name');
        $this->db->from('tbl_locations');
        $this->db->where('parent_id', $id);
        $this->db->where('level_id', 11);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getConstituencyDemographics($id) {
        $this->db->select('const_id, turn_out, data_year, voters, reservation');
        $this->db->from('tbl_const_demographic');
        $this->db->where('const_id', $id);
        $this->db->order_by('data_year');
        $this->db->limit(1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getLocation($id) {
        $this->db->select('l.id, l.name, l1.name as district');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_locations as l1', 'l.parent_id = l1.id');
        $this->db->where('l.id', $id);
        $this->db->where('l.level_id', 11);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getLastResult($id) {
        $this->db->select('r.const_id, r.candidate, r.votes, r.majority, r.winner, p.party_slug');
        $this->db->from('tbl_last_result as r');
        $this->db->join('tbl_party as p', 'r.party_id = p.id');
        $this->db->where('r.const_id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getElectedMember($id) {
        $this->db->select('m.const_id, m.member_name, m.member_photo, m.elected_from, p.party_slug, p.party_icon');
        $this->db->from('tbl_elected_member as m');
        $this->db->join('tbl_party as p', 'm.party_id = p.id');
        $this->db->where('m.const_id', $id);
        $this->db->where('m.status', 1);
        $this->db->order_by('m.elected_from');
        $this->db->limit(1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
}