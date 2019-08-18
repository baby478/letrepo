<?php
class GManagerModel extends CI_Model {
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    /**
     * Date : 29-01-19
     * Author : Anees
     */
    public function getAssemblyConstituency($id) {
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->order_by('l.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSeniorManager($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.photo, u.mobile, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDivisionHeadBySM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDInchargeByDH($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender, u.photo, u.mobile, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 2);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDivisionInchargePS($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('tp.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothObserverByDM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        } 
    }

    public function getBoothObserverPS($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.user_id', $id);
        $this->db->where('tp.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothPresidentByDM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role, p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_ps as tp', 'tm.user_id = tp.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSPCountByBP($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getSheetPresidentByBP($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.dob, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.parent_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersCountBySP($id) {
        $this->db->select('c.citizen_id');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->where('c.user_id', $id);
        //$this->db->where('c.user_role', 17);
        return $this->db->get()->num_rows();
    }

    public function getFamilyHeadBySP($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->where('cm.user_id', $id);
        $this->db->where('cm.parent_id', $id);
        $this->db->where('cm.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersCountByFH($id) {
        $this->db->select('cm.id');
        $this->db->from('tbl_citizen_mng as cm');
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.parent_id', $id);
        $this->db->where('cm.user_role', 17);
        return $this->db->get()->num_rows();
    }

    public function getVotersBySP($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.lfname, v.llastname, v.f_name, v.lrname, v.gender, v.photo, v.age, v.voter_id, v.hno, v.mobile');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'v.id = c.citizen_id');
        $this->db->where('c.user_id', $id);
        $this->db->where('c.parent_id', $id);
        $this->db->where('c.user_role', 17);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getVotersByFH($fh_id) {
        $this->db->select('v.firstname, v.lastname, v.mobile, v.gender, v.photo, v.voter_id, c.citizen_id, p.ps_no, p.ps_name');
        $this->db->from('tbl_citizen_mng as c');
        $this->db->join('tbl_voters as v', 'c.citizen_id = v.id');
        $this->db->join('tbl_ps as p', 'v.ps_no = p.id');
        $this->db->where('c.parent_id', $fh_id);
        $this->db->where('c.group_id', 40);
        $this->db->where('c.user_role', 17);
        // $this->db->where('c.local_status', 15);
        $result = $this->db->get()->result();
        if($result) {
            return $result;
        }else {
            return false;
        }
    }

    /**
     * Date : 01-02-2019
     * Author : Anees
     */
    public function getSMCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getDHCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getDICount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location'); //SM
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id'); //DH
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id'); //DI
        $this->db->join('tbl_users as u', 'tm3.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getBCCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location'); //SM
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id'); //DH
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id'); //DI
        $this->db->join('tbl_team_mng as tm4', 'tm3.user_id = tm4.parent_id'); //BC
        $this->db->join('tbl_users as u', 'tm4.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getAppVersion($id) {
        $this->_sdb->select('app_id, version, version_date, status');
        $this->_sdb->from('tbl_app_version');
        $this->_sdb->where('app_id', $id);
        $this->_sdb->where('status', 1);
        $this->_sdb->order_by('version_date', 'desc');
        $this->_sdb->limit(1);
        $result = $this->_sdb->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getConstName($id) {
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_locations as l');
        $this->db->where('l.id', $id);
        $this->db->where('l.level_id', 11);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getSMCountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getDHCountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getDICountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_users as u', 'tm3.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getBCCountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id');
        $this->db->join('tbl_users as u', 'tm4.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getBPCountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id');
        $this->db->join('tbl_users as u', 'tm4.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getTCCountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id');
        $this->db->join('tbl_users as u', 'tm4.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 138);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getSPCountByConst($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id');
        $this->db->join('tbl_team_mng as tm5', 'tm5.parent_id = tm4.user_id');
        $this->db->join('tbl_users as u', 'tm5.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getFHCountByConst($id) {
        $this->db->select('cm.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id');
        $this->db->join('tbl_team_mng as tm5', 'tm5.parent_id = tm4.user_id');
        $this->db->join('tbl_users as u', 'tm5.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.user_role', 46);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getVotersCountByConst($id, $filters = array()) {
        $this->db->select('cm.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm.user_id');
        $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id');
        $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id');
        $this->db->join('tbl_team_mng as tm5', 'tm5.parent_id = tm4.user_id');
        $this->db->join('tbl_users as u', 'tm5.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        if(count($filters) > 0) {
            foreach($filters as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getMandalsByConst($id) {
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('cl.parent_id', $id);
        $this->db->where('cl.level_id', 45);
        $this->db->order_by('l.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getPSByMandal($id) {
        $this->db->select('p.id, p.ps_no, p.ps_name, p.ps_area');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_ps as p', 'l.id = p.village_id');
        $this->db->where('l.parent_id', $id);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getXpartyByPS($psid, $party) {
        $this->db->select('x.id, x.name, x.age, x.designation, x.mobile, x.followers');
        $this->db->from('tbl_xparty_info as x');
        $this->db->join('tbl_team_ps as tp', 'x.user_id = tp.user_id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $psid);
        $this->db->where('x.party_id', $party);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function saveSms($data) {
        $this->db->trans_begin();
        $msg_t = array(
            'sms_type' => $data['sms_type'],
            'receiver_user_role' => $data['user_role'],
            'text_message' => $data['message'],
            'language' => $data['language'],
            'created_by' => $data['user_id']
        );
        $this->db->insert('tbl_sms', $msg_t);
        $msg_id = $this->db->insert_id();

        $msg_g = array(
            'sms_id' => $msg_id,
            'receiver_id' => $data['receiver_group'],
            'msg_count' => $data['msg_count']
        );
        $this->db->insert('tbl_sms_mng', $msg_g);

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function getDivisionHeadByGM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDivisionInchargeByGM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothCoordinatorByGM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id');
        $this->db->join('tbl_team_mng as tm4', 'tm3.user_id = tm4.parent_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getBoothPresidentByGM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id');
        $this->db->join('tbl_team_mng as tm4', 'tm3.user_id = tm4.parent_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSheetPresidentByGM($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender, u.photo, l.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id');
        $this->db->join('tbl_team_mng as tm4', 'tm3.user_id = tm4.parent_id');
        $this->db->join('tbl_team_mng as tm5', 'tm4.user_id = tm5.parent_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getFamilyHeadByGM($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.gender, v.mobile, v.voter_id, v.photo, p.id as pid, p.ps_no, p.ps_name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_const_location as cl', 'tm.location = cl.parent_id');
        $this->db->join('tbl_team_mng as tm1', 'cl.location_id = tm1.location');
        $this->db->join('tbl_team_mng as tm2', 'tm1.user_id = tm2.parent_id');
        $this->db->join('tbl_team_mng as tm3', 'tm2.user_id = tm3.parent_id');
        $this->db->join('tbl_team_mng as tm4', 'tm3.user_id = tm4.parent_id');
        $this->db->join('tbl_team_mng as tm5', 'tm4.user_id = tm5.parent_id');
        $this->db->join('tbl_team_ps as tp', 'tm5.user_id = tp.user_id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('cl.level_id', 58);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $this->db->where('cm.group_id', 40);
        $this->db->where('cm.user_role', 46);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getSentSms($id) {
        $outbox = array();
        
        //group sms
        $this->db->select('s.id, s.sms_type, s.text_message, s.receiver_user_role, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_lookup as lu', 'sm.receiver_id = lu.id');
        $this->db->where('s.created_by', $id);
        $this->db->where('s.sms_type', 62);
        $this->db->order_by('s.created_at', 'desc');
        $result_g = $this->db->get();
        if($result_g->num_rows() > 0) {
            $group = $result_g->result();
            foreach($group as $g) {
                $g->title = 'Group sms to ' . $g->value;
            }
        }else {
            $group = false;
        }

        //single sms
        $this->db->select('s.id, s.sms_type, s.text_message, s.receiver_user_role, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_lookup as lu', 's.receiver_user_role = lu.id');
        $this->db->where('s.created_by', $id);
        $this->db->where('s.sms_type', 63);
        $this->db->order_by('s.created_at', 'desc');
        $result_s = $this->db->get();
        if($result_s->num_rows() > 0) {
            $single = $result_s->result();
            foreach($single as $s) {
                if($s->receiver_user_role == 46) {
                    $this->db->select('v.firstname, v.mobile');
                    $this->db->from('tbl_voters as v');
                    $this->db->where('v.id', $s->receiver_id);
                    $result = $this->db->get();
                    if($result->num_rows() > 0) {
                        $rec_d = $result->row();
                        $s->firstname = $rec_d->firstname;
                        $s->mobile = $rec_d->mobile;
                        $s->title = $rec_d->firstname . ' ('. $s->value . ')';
                    }
                }else {
                    $this->db->select('u.first_name, u.mobile');
                    $this->db->from('tbl_users as u');
                    $this->db->where('u.id', $s->receiver_id);
                    $result = $this->db->get();
                    if($result->num_rows() > 0) {
                        $rec_d = $result->row();
                        $s->firstname = $rec_d->first_name;
                        $s->mobile = $rec_d->mobile;
                        $s->title = $rec_d->first_name . ' ('. $s->value . ')';
                    }
                }
            }
        }else {
            $single = false;
        }

        if($group && $single) {
            $outbox = array_merge($group, $single);
            usort($outbox, function($a, $b) {
                $t1 = strtotime($a->created_at);
                $t2 = strtotime($b->created_at);
                return $t2 - $t1;
            });
        }elseif($group && !$single) {
            $outbox = $group;
        }elseif(!$group && $single) {
            $outbox = $single;
        }else {
            $outbox =  false;
        }

        if($outbox) {
            return $outbox;
        }else {
            return false;
        }
    }

    public function getSms($id) {
        $inbox = array();
        
        $sup_id = array();
        //party president
        $this->db->select('t2.user_id as ppid');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_locations as l', 't.location = l.id');//constituency
        $this->db->join('tbl_locations as l2', 'l.parent_id = l2.id');//state
        $this->db->join('tbl_team_mng as t2', 't2.location = l2.id');
        $this->db->join('tbl_users as u', 't2.user_id = u.id');
        $this->db->where('t.user_id', $id);
        $this->db->where('l2.level_id', 7);
        $this->db->where('u.user_role', 59);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $sp = $result->row();
            $sup_id[] = $sp->ppid;
        }

        //group inbox
        if(count($sup_id) > 0) {
            $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
            $this->db->from('tbl_sms as s');
            $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
            $this->db->join('tbl_users as u', 's.created_by = u.id');
            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
            $this->db->where('s.sms_type', 62);
            $this->db->where('sm.receiver_id', 142);
            $this->db->where_in('s.created_by', $sup_id);
            $this->db->order_by('s.created_at', 'desc');
            $result_g = $this->db->get();
            if($result_g->num_rows() > 0) {
                $group = $result_g->result();
                foreach($group as $g) {
                    $g->title = $g->first_name . ' (' . $g->sender . ')';
                    $this->db->select('sr.id, sr.read');
                    $this->db->from('tbl_sms_read as sr');
                    $this->db->where('sr.sms_id', $g->id);
                    $this->db->where('sr.user_id', $id);
                    $this->db->where('sr.read', 1);
                    $result_r = $this->db->get();
                    if($result_r->num_rows() > 0) {
                        $g->read = 1;
                    }else {
                        $g->read = 0;
                    }
                }
            }else {
                $group = false;
            }
        }else {
            $group = false;
        }

        //single inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 63);
        $this->db->where('sm.receiver_id', $id);
        $this->db->order_by('s.created_at', 'desc');
        $result_s = $this->db->get();
        if($result_s->num_rows() > 0) {
            $single = $result_s->result();
            foreach($single as $s) {
                $s->title = $s->first_name . ' (' . $s->sender . ')';
                $this->db->select('sr.id, sr.read');
                $this->db->from('tbl_sms_read as sr');
                $this->db->where('sr.sms_id', $s->id);
                $this->db->where('sr.user_id', $id);
                $this->db->where('sr.read', 1);
                $result_r = $this->db->get();
                if($result_r->num_rows() > 0) {
                    $s->read = 1;
                }else {
                    $s->read = 0;
                }
            }
        }else {
            $single = false;
        }

        if($group && $single) {
            $inbox = array_merge($group, $single);
            usort($inbox, function($a, $b) {
                $t1 = strtotime($a->created_at);
                $t2 = strtotime($b->created_at);
                return $t2 - $t1;
            });
        }elseif($group && !$single) {
            $inbox = $group;
        }elseif(!$group && $single) {
            $inbox = $single;
        }else {
            $inbox =  false;
        }

        if($inbox) {
            return $inbox;
        }else {
            return false;
        }
    }

    public function smsexists($msg_id) {
        $this->db->select('s.id');
        $this->db->from('tbl_sms as s');
        $this->db->where('s.id', $msg_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function smsread($msg_id, $flag, $user_id) {
        $exists = $this->smsexists($msg_id);
        if($exists) {
            $read_d = array(
                'sms_id' => $msg_id,
                'user_id' => $user_id,
                'read' => $flag
            );
            $result = $this->db->insert('tbl_sms_read', $read_d);
            if($result) {
                return true;
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

    /**
     * Date : 07-02-2019
     * Author : Anees
     */
    public function saveHelpQuery($data) {
        $q_data = array(
            'app_id' => $data['app_id'],
            'app_version' => $data['app_version'],
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'attach_file' => $data['attach']
        );
        if($this->input->post('description')) {
            $q_data['description'] = $data['description'];
        }
        $result = $this->_sdb->insert('tbl_app_help', $q_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getSmartMedia() {
        $this->db->select('id, media_path, publish_date');
        $this->db->from('tbl_smart_media');
        $this->db->where('publish_date <= CURRENT_DATE', NULL, FALSE);
        $this->db->where('status', 1);
        $this->db->order_by('publish_date', 'desc');
        $this->db->limit(10);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getPostLikes($id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('post_id', $id);
        $this->db->where('post_like', 1);
        $result = $this->db->get();
        return $result->num_rows();
    }

    public function getPostLikeByUser($id, $user_id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('post_id', $id);
        $this->db->where('user_id', $user_id);
        $this->db->where('post_like', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return 1;
        }else {
            return 0;
        }
    }

    public function postLikeExists($id, $user_id) {
        $this->db->select('id');
        $this->db->from('tbl_sm_likes');
        $this->db->where('post_id', $id);
        $this->db->where('user_id', $user_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }
    
    public function savePostLike($data) {
        $user_id = $data['user_id'];
        $post_id = $data['post_id'];
        $like_exists = $this->postLikeExists($post_id, $user_id);
        if($like_exists == false) {
            $like_save = array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'post_like' => $data['like']
            );
            $result = $this->db->insert('tbl_sm_likes', $like_save);
        }elseif($like_exists == true) {
            if($data['like'] == 1) {
                $like = 1;
            }else {
                $like = 0;
            }
            $like_update = array(
                'post_like' => $like
            );
            $this->db->where('post_id', $post_id);
            $this->db->where('user_id', $user_id);
            $result = $this->db->update('tbl_sm_likes', $like_update);
        }

        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function uploadSmartMedia($data) {
        $m_data = array(
            'media_path' => $data['media'],
            'publish_date' => $data['publish_date'],
            'created_by' => $data['user_id'],
            'status' => 1
        );
        $result = $this->db->insert('tbl_smart_media', $m_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getprofessionreport($cid, $role, $profession) {
        if($role == 55 || $role == 18) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.profession', $profession);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.profession', $profession);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation as v', 'v.user_id = cm.citizen_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.profession', $profession);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getparticipationreport($cid, $role, $participation) {
        if($role == 55 || $role == 18) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.party_participation', $participation);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.party_participation', $participation);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation as v', 'v.user_id = cm.citizen_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.party_participation', $participation);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getPersonalStatusreport($cid, $role, $status) {
        if($role == 55 || $role == 18) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.personal_status', $status);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.personal_status', $status);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation as v', 'v.user_id = cm.citizen_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.personal_status', $status);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getFamilyVotersreport($cid, $role, $voters) {
        if($role == 55 || $role == 18) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.family_voters', $voters);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.family_voters', $voters);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation as v', 'v.user_id = cm.citizen_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.family_voters', $voters);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getGovtSchemereport($cid, $role, $scheme) {
        if($role == 55 || $role == 18) {
            $this->db->select('v1.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation_1 as v1', 'v1.user_id = tm3.user_id');
            $this->db->join('tbl_validation1_options as vo', 'v1.id = vo.validation_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v1.user_role', $role);
            $this->db->where('vo.option_id', $scheme);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v1.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation_1 as v1', 'v1.user_id = tm3.user_id');
            $this->db->join('tbl_validation1_options as vo', 'v1.id = vo.validation_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v1.user_role', $role);
            $this->db->where('vo.option_id', $scheme);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v1.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation_1 as v1', 'v1.user_id = cm.citizen_id');
            $this->db->join('tbl_validation1_options as vo', 'v1.id = vo.validation_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v1.user_role', $role);
            $this->db->where('vo.option_id', $scheme);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getYSRCPSchemereport($cid, $role, $scheme) {
        if($role == 55 || $role == 18) {
            $this->db->select('v2.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation_2 as v2', 'v2.user_id = tm3.user_id');
            $this->db->join('tbl_validation2_options as vo', 'v2.id = vo.validation_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v2.user_role', $role);
            $this->db->where('vo.option_id', $scheme);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v2.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation_2 as v2', 'v2.user_id = tm3.user_id');
            $this->db->join('tbl_validation2_options as vo', 'v2.id = vo.validation_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v2.user_role', $role);
            $this->db->where('vo.option_id', $scheme);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v2.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation_2 as v2', 'v2.user_id = cm.citizen_id');
            $this->db->join('tbl_validation2_options as vo', 'v2.id = vo.validation_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v2.user_role', $role);
            $this->db->where('vo.option_id', $scheme);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getCommitmentreport($cid, $role, $commitment) {
        if($role == 55 || $role == 18) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.vote_commitment', $commitment);
            return $this->db->get()->num_rows();
        }elseif($role == 3) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_validation as v', 'v.user_id = tm3.user_id');
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.vote_commitment', $commitment);
            return $this->db->get()->num_rows();
        }elseif($role == 46) {
            $this->db->select('v.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_validation as v', 'v.user_id = cm.citizen_id');
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            $this->db->where('tm.location', $cid);
            $this->db->where('v.user_role', $role);
            $this->db->where('v.vote_commitment', $commitment);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    /**
     * Date : 11-02-2019
     * Author : Anees
     */
    public function getQuestionsByReport($role, $report) {
        $this->db->select('q.id, q.question, q.t_question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.user_role', $role);
        $this->db->where('q.report', $report);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getQuestionLabels($qid) {
        $this->db->select('ql.id, ql.value');
        $this->db->from('tbl_qa_map as qm');
        $this->db->join('tbl_q_lookup as ql', 'qm.aid = ql.id');
        $this->db->where('qm.qid', $qid);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getCallingReport(array $data, $aid) {
        if($data['user_role'] == 55 || $data['user_role'] == 18) {
            $this->db->select('qr.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_q_result as qr', 'qr.user_id = tm3.user_id');
            $this->db->where('tm.location', $data['cid']);
            $this->db->where('qr.user_role', $data['user_role']);
            $this->db->where('qr.qid', $data['qid']);
            $this->db->where('qr.answer', $aid);
            return $this->db->get()->num_rows();
        }elseif($data['user_role'] == 3) {
            $this->db->select('qr.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_q_result as qr', 'qr.user_id = tm4.user_id');
            $this->db->where('tm.location', $data['cid']);
            $this->db->where('qr.user_role', $data['user_role']);
            $this->db->where('qr.qid', $data['qid']);
            $this->db->where('qr.answer', $aid);
            return $this->db->get()->num_rows();
        }elseif($data['user_role'] == 46) {
            $this->db->select('qr.id');
            $this->db->from('tbl_team_mng as tm');
            $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id'); //Division Head
            $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id'); //Division Incharge
            $this->db->join('tbl_team_mng as tm3', 'tm3.parent_id = tm2.user_id'); //BC or BP
            $this->db->join('tbl_team_mng as tm4', 'tm4.parent_id = tm3.user_id'); // SP
            $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = tm4.user_id'); //FH
            $this->db->join('tbl_q_result as qr', 'qr.user_id = cm.user_id');
            $this->db->where('tm.location', $data['cid']);
            $this->db->where('qr.user_role', $data['user_role']);
            $this->db->where('qr.qid', $data['qid']);
            $this->db->where('qr.answer', $aid);
            $this->db->where('cm.group_id', 40);
            $this->db->where('cm.user_role', 46);
            return $this->db->get()->num_rows();
        }else {
            return 0;
        }
    }

    public function getQuestionById($qid) {
        $this->db->select('q.id, q.question, q.t_question');
        $this->db->from('tbl_questionnaire as q');
        $this->db->where('q.id', $qid);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
}