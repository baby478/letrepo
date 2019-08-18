<?php
class KioskModel extends CI_Model {

    public function __construct() {
        parent::__construct();   
    }

    public function getRecruitCount($id, $role) {
        $this->db->db_select('citizenconnect_analytics');
        $this->db->select('id');
        $this->db->from('tbla_recruitment');
        $this->db->where('const_location', $id);
        $this->db->where('user_role', $role);
        $this->db->where('status', 1);
        $result = $this->db->get();
        return $result->num_rows();   
    }

    public function getMandals($id) {
        $this->db->db_select('citizenconnect_d');
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('cl.parent_id', $id);
        $this->db->where('cl.level_id', 45);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getRecruitCountByMandal($id, $mandal_id, $role) {
        if($role == 3 || $role == 18) {
            $this->db->select('l.id');
            $this->db->from('citizenconnect_analytics.tbla_recruitment as r');
            $this->db->join('citizenconnect_d.tbl_team_mng as t', 'r.user_id = t.user_id');
            $this->db->join('citizenconnect_d.tbl_locations as l', 'l.id = t.location');
            $this->db->where('r.user_role', $role);
            $this->db->where('r.const_location', $id);
            $this->db->where('l.parent_id', $mandal_id);
            $this->db->where('r.status', 1);
            $result = $this->db->get();
            return $result->num_rows();
        }
        if($role == 2) {
            $this->db->select('l.id');
            $this->db->from('citizenconnect_analytics.tbla_recruitment as r');
            $this->db->join('citizenconnect_d.tbl_team_mng as t', 'r.user_id = t.user_id');
            $this->db->join('citizenconnect_d.tbl_locations as l', 'l.id = t.location');
            $this->db->where('r.user_role', $role);
            $this->db->where('r.const_location', $id);
            $this->db->where('l.id', $mandal_id);
            $this->db->where('r.status', 1);
            $result = $this->db->get();
            return $result->num_rows();
        }
        if($role == 46) {
            $this->db->select('l.id');
            $this->db->from('citizenconnect_analytics.tbla_recruitment as r');
            $this->db->join('citizenconnect_d.tbl_citizen_mng as c', 'r.user_id = c.citizen_id');
            $this->db->join('citizenconnect_d.tbl_team_mng as t', 'c.user_id = t.user_id');
            $this->db->join('citizenconnect_d.tbl_locations as l', 'l.id = t.location');
            $this->db->where('r.user_role', $role);
            $this->db->where('r.const_location', $id);
            $this->db->where('l.parent_id', $mandal_id);
            $this->db->where('r.status', 1);
            $result = $this->db->get();
            return $result->num_rows();
        }
    }

    public function getRegisterCount($id) {
        $this->db->db_select('citizenconnect_analytics');
        $this->db->select('id');
        $this->db->from('tbla_registration');
        $this->db->where('const_location', $id);
        $this->db->where('status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getRegisterCountByMandal($id, $mandal_id) {
        $this->db->select('l.id');
        $this->db->from('citizenconnect_analytics.tbla_registration as r');
        $this->db->join('citizenconnect_d.tbl_team_mng as t', 'r.user_id = t.user_id');
        $this->db->join('citizenconnect_d.tbl_locations as l', 'l.id = t.location');
        $this->db->where('r.const_location', $id);
        $this->db->where('l.parent_id', $mandal_id);
        $this->db->where('r.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getVotersByKiosky($id, $filters = array()) {
        $this->db->select('v.firstname, v.lastname, v.dob, v.age, v.voter_id, v.gender, lu.value as voter_status');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id'); //Mandal or Division
        $this->db->join('tbl_locations as l2', 'l2.parent_id = cl.location_id'); //village or colony
        $this->db->join('tbl_team_mng as t2', 't2.location = l2.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as ct', 'ct.user_id = t2.user_id');
        $this->db->join('tbl_voters as v', 'v.id = ct.citizen_id');
        $this->db->join('tbl_lookup as lu', 'v.voter_status = lu.id');
        $this->db->where('ct.user_role', 17);
        $this->db->where('t1.user_id', $id);
        if(count($filters) > 0 ) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        return $this->db->get();
    }

    public function getVotersByDateRegister($id) {
        $this->db->select('count(ct.citizen_id) as ctzn, date(ct.created_at) as date');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id'); //Mandal or Division
        $this->db->join('tbl_locations as l2', 'l2.parent_id = cl.location_id'); //village or colony
        $this->db->join('tbl_team_mng as t2', 't2.location = l2.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as ct', 'ct.user_id = t2.user_id');
        $this->db->where('ct.user_role', 17);
        $this->db->where('t1.user_id', $id);
        $this->db->where('ct.created_at >= DATE_ADD(CURDATE(), INTERVAL -10 DAY)', NULL, FALSE);
        $this->db->group_by('date(ct.created_at)');
        $result = $this->db->get();
        if($result->num_rows()) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getRegistrationCountByMandal($id) {
        $this->db->select('l2.id, l2.name as mandal');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id');
        $this->db->join('tbl_locations as l2', 'cl.location_id = l2.id'); // Mandal
        $this->db->where('t1.user_id', $id);
        $result_m = $this->db->get();
        
        if($result_m->num_rows()) {
            $result = $result_m->result();
            foreach($result as $m) {
                $this->db->select();
                $this->db->from('tbl_team_mng as t1');
                $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
                $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id');
                $this->db->join('tbl_locations as l2', 'cl.location_id = l2.id'); // Mandal
                $this->db->join('tbl_locations as l3', 'l3.parent_id = l2.id'); //village or colony
                $this->db->join('tbl_team_mng as t2', 't2.location = l3.id');
                $this->db->join('tbl_users as u', 't2.user_id = u.id');
                $this->db->join('tbl_citizen_mng as ct', 'ct.user_id = t2.user_id');
                $this->db->where('ct.user_role', 17);
                $this->db->where('t1.user_id', $id);
                $this->db->where('l2.id', $m->id);
                $m->ctzn = $this->db->get()->num_rows();
            }
            return $result;
        }else {
            return false;
        }
    }

    public function getCoordPerformanceByKiosk($id) {
        $this->db->select('t2.user_id, count(ct.user_id) as registered');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id'); //Mandal or Division
        $this->db->join('tbl_locations as l2', 'l2.parent_id = cl.location_id'); //village or colony
        $this->db->join('tbl_team_mng as t2', 't2.location = l2.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as ct', 'ct.user_id = t2.user_id');
        $this->db->where('ct.user_role', 17);
        $this->db->where('t1.user_id', $id);
        $this->db->group_by('ct.user_id');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getCountManagersByKiosk($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id');
        $this->db->join('tbl_locations as l2', 'cl.location_id = l2.id');//Mandal
        $this->db->join('tbl_team_mng as t2', 't2.location = l2.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $this->db->where('t1.user_id', $id);
        return $this->db->get()->num_rows(); 
    }

    public function getCountTLByKiosk($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id');
        $this->db->join('tbl_locations as l2', 'cl.location_id = l2.id');//Mandal
        $this->db->join('tbl_locations as l3', 'l3.parent_id = l2.id'); //village or colony
        $this->db->join('tbl_team_mng as t2', 't2.location = l3.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $this->db->where('t1.user_id', $id);
        return $this->db->get()->num_rows(); 
    }

    public function getCountCoordinatorByKiosk($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as t1');
        $this->db->join('tbl_locations as l1', 't1.location = l1.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l1.id');
        $this->db->join('tbl_locations as l2', 'cl.location_id = l2.id');//Mandal
        $this->db->join('tbl_locations as l3', 'l3.parent_id = l2.id'); //village or colony
        $this->db->join('tbl_team_mng as t2', 't2.location = l3.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('t1.user_id', $id);
        return $this->db->get()->num_rows(); 
    }
}