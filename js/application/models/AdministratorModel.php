<?php
class AdministratorModel extends CI_Model {
    private $_sdb;
    
    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getAssignRole() {
        $this->db->select('l.id, l.value');
        $this->db->from('tbl_ac_role as r');
        $this->db->join('tbl_acl as ac', 'r.acl_id = ac.id');
        $this->db->join('tbl_lookup as l', 'ac.value = l.id');
        $this->db->where('ac.gen_id', 1);
        $this->db->where('r.user_role', 1);
        $this->db->order_by('l.value');
        $result = $this->db->get()->result();	
        return $result;
    }

    public function getUsersDataByRole($role) {
        //$user_id = $this->session->userdata('user')->id;
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, t.status');
        $this->db->from('tbl_users as u');
        $this->db->join('tbl_team_mng as t', 'u.id = t.user_id', 'left');
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', $role);
        $result = $this->db->get()->result();
        if($result) {
            $data = array();
            foreach($result as $i => $r) {
                if($r->status == '' || $r->status == 0) {
                    $data[] = $result[$i];
                }
            }
            return $data;
            
        }else {
            return false;
        }
    }

    public function getDistricts($id) {
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_locations as l');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->order_by('l.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getConstituenceByDistrict($id) {
        $this->db->select();
        $this->db->from('tbl_locations as l');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 11);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getMandalsByConstituence($location) {
        $this->db->select('l.id, l.name, l.level_id');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_locations as l', 'cl.location_id = l.id');
        $this->db->where('cl.parent_id', $location);
        $this->db->where('cl.level_id', 45);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }	
    }

    public function getPSByMandal($id) {
        $this->db->select('p.id, p.ps_name, p.ps_no, p.ps_area, l2.name as village, l2.id as location, l2.level_id');
        $this->db->from('tbl_locations as l1');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->join('tbl_ps as p', 'l2.id = p.village_id');
        $this->db->where('l1.id', $id);
        $this->db->order_by('p.ps_no');
        $result = $this->db->get()->result();
        return $result;
    }

    public function getBPBypsid($id) {
        $this->db->select('u.id, u.first_name, u.last_name');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('tp.status', 1);
        $this->db->where('u.status', 1);
        $result = $this->db->get()->result();
        return $result;
    }

    public function getLSConstituency($id) {
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_locations as l');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 58);
        $this->db->order_by('l.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
    /**
     * Date : 11-03-2019
     * Author : Anees
     */
    public function districtAllocateExists($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 143);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function asConstituencyExists($const, $role) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $const);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', $role);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function mandalExists($id, $role) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', $role);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function psExists($id, $role) {
        $this->db->select('u.id, p.ps_no');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_ps as p', 'tp.ps_id = p.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('tp.status', 1);
        $this->db->where('u.user_role', $role);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function lsConstituencyExists($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 57);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function assignUserRole($data) {
        $admin_id = $this->session->userdata('user')->id;
        
        $this->db->trans_begin();
        //update role
        $this->db->set('user_role', $data['user-role']);
        $this->db->where('id', $data['user']);
        $this->db->update('tbl_users');

        //District President
        if($data['user-role'] == 143) {
            $tm_data = array(
                'user_id' => $data['user'],
                'parent_id' => $admin_id,
                'location' => $data['district'],
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_mng', $tm_data);
        }elseif($data['user-role'] == 144 || $data['user-role'] == 44) { //const president or MLA
            $tm_data = array(
                'user_id' => $data['user'],
                'parent_id' => $admin_id,
                'location' => $data['constituency'],
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id
            );
            $i_id = $this->db->insert('tbl_team_mng', $tm_data);
            if($i_id && $data['user-role'] == 44) {
                $smloc = $this->getDivPresidentByConstPN($data['constituency']);
                if($smloc) {
                    foreach($smloc as $u) {
                        $this->db->set('parent_id', $data['user']);
                        $this->db->where('user_id', $u->id);
                        $this->db->update('tbl_team_mng');
                    }
                }
            }
        }elseif($data['user-role'] == 137 || $data['user-role'] == 145) { //Division president or App Coordinator
            $const_id = $this->input->post('constituency');
            $sm = $this->getSMByConstituency($const_id);
            if($sm) {
                $smid = $sm->id;
            }else {
                $smid = null;
            }
            
            $m_data = array(
                'user_id' => $data['user'],
                'parent_id' => $smid,
                'location' => $data['mandal'],
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id

            );
            $i_id = $this->db->insert('tbl_team_mng', $m_data);
            if($i_id && $data['user-role'] == 137) {
                $smloc = $this->getDivisionCoordinatorByMandalPN($data['mandal']);
                if($smloc) {
                    foreach($smloc as $u) {
                        $this->db->set('parent_id', $data['user']);
                        $this->db->where('user_id', $u->id);
                        $this->db->update('tbl_team_mng');
                    }
                }
            }
        }elseif($data['user-role'] == 2) { //Division Coordinator
            $mid = $this->input->post('mandal');
            $dp = $this->getDPByMandal($mid);
            if($dp) {
                $dpid = $dp->id;
            }else {
                $dpid = null;
            }
            $m_data = array(
                'user_id' => $data['user'],
                'parent_id' => $dpid,
                'location' => $data['mandal'],
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id

            );
            $i_id = $this->db->insert('tbl_team_mng', $m_data);
            //team ps
            $ps_array = $this->input->post('mPollingstation[]');
            foreach($ps_array as $ps) {
                $p_data = array(
                    'user_id' => $data['user'],
                    'ps_id' => $ps,
                    'status' => 1,
                    'created_by' => $admin_id
                );
                $this->db->insert('tbl_team_ps', $p_data);
            }
            
            if($i_id) {
                $dcloc = $this->getDCChildByPsPN($ps_array);
                if($dcloc) {
                    foreach($dcloc as $u) {
                        $this->db->set('parent_id', $data['user']);
                        $this->db->where('user_id', $u->id);
                        $this->db->update('tbl_team_mng');
                    }
                }
            }

        }elseif($data['user-role'] == 55 || $data['user-role'] == 18 || $data['user-role'] == 138) {
            $psid = $data['sPollingstation'];
            $loc = $this->getLocByPs($psid)->village_id;
            $dc = $this->getDCByPs($psid);
            if($dc) {
                $dcid = $dc->id;
            }else {
                $dcid = null;
            }
            $m_data = array(
                'user_id' => $data['user'],
                'parent_id' => $dcid,
                'location' => $loc,
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id

            );
            $this->db->insert('tbl_team_mng', $m_data);

            $p_data = array(
                'user_id' => $data['user'],
                'ps_id' => $psid,
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_ps', $p_data);
        }elseif($data['user-role'] == 3) { // Street President
            $psid = $data['sPollingstation'];
            $loc = $this->getLocByPs($psid)->village_id;
            $m_data = array(
                'user_id' => $data['user'],
                'parent_id' => $data['bpuser'],
                'location' => $loc,
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id

            );
            $this->db->insert('tbl_team_mng', $m_data);

            $p_data = array(
                'user_id' => $data['user'],
                'ps_id' => $psid,
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_ps', $p_data);
        }elseif($data['user-role'] == 57) { //MP
            $tm_data = array(
                'user_id' => $data['user'],
                'parent_id' => $admin_id,
                'location' => $data['lsconstituency'],
                'date_from' => date('Y-m-d'),
                'status' => 1,
                'created_by' => $admin_id
            );
            $this->db->insert('tbl_team_mng', $tm_data);
        }

        if($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }else {
            $this->db->trans_commit();
            return true;
        }
        
        
    }

    public function getSMByConstituency($const) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $const);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getDPByMandal($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getDCByPs($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('tp.ps_id', $id);
        $this->db->where('tp.status', 1);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getLocByPs($id) {
        $this->db->select('tp.id, tp.village_id');
        $this->db->from('tbl_ps as tp');
        $this->db->where('tp.id', $id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getDivisionCoordinatorByMandalPN($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('tm.location', $id);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $this->db->where('tm.parent_id', null);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDivPresidentByConstPN($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_const_location as cl');
        $this->db->join('tbl_team_mng as tm', 'cl.location_id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('cl.parent_id', $id);
        $this->db->where('cl.level_id', 45);
        $this->db->where('tm.status', 1);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $this->db->where('tm.parent_id', null);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDCChildByPsPN($ps) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_ps as tp');
        $this->db->join('tbl_team_mng as tm', 'tp.user_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where_in('tp.ps_id', $ps);
        $this->db->where('tm.parent_id', null);
        $this->db->where('u.status', 1);
        $this->db->where('u.user_role', '55 || 18 || 138');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    /**
     * Date : 12-03-2019
     * Author : Anees
     */
    public function getMPCountByState($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_team_mng as tm', 'l.id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 58);
        $this->db->where('u.user_role', 57);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getMLACountByState($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
        $this->db->join('tbl_team_mng as tm', 'l2.id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getDstPresidentCountByState($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_team_mng as tm', 'l.id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('u.user_role', 143);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getConstPresidentCountByState($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id');
        $this->db->join('tbl_team_mng as tm', 'l2.id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('u.user_role', 144);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getDvPresidentCountByState($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l'); //district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l2.id'); //const mandal
        $this->db->join('tbl_team_mng as tm', 'cl.location_id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('cl.level_id', 45);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getDvCoordinatorCountByState($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l'); //district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l2.id'); //const mandal
        $this->db->join('tbl_team_mng as tm', 'cl.location_id = tm.location');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('cl.level_id', 45);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getSPSUserCountByState($id, $role) {
        $this->db->select('u.id');
        $this->db->from('tbl_locations as l'); //district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l2.id'); //const mandal
        $this->db->join('tbl_locations as l3', 'l3.parent_id = cl.location_id');
        $this->db->join('tbl_ps as p', 'p.village_id = l3.id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('cl.level_id', 45);
        $this->db->where('u.user_role', $role);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getTotalVotersByState($id, $role = false) {
        $this->db->select('cm.id');
        $this->db->from('tbl_locations as l'); //district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id'); //constituency
        $this->db->join('tbl_const_location as cl', 'cl.parent_id = l2.id'); //const mandal
        $this->db->join('tbl_locations as l3', 'l3.parent_id = cl.location_id');
        $this->db->join('tbl_ps as p', 'p.village_id = l3.id');
        $this->db->join('tbl_team_ps as tp', 'tp.ps_id = p.id');
        $this->db->join('tbl_users as u', 'tp.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        if($role) {
            $this->db->where('cm.user_role', $role);
        }
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('cl.level_id', 45);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status',1);
        return $this->db->get()->num_rows();
    }

    public function getUsersByAppDownload($role, $app, $did, $cid, $mid) {
        if($cid == 'all') {
            if(($app == 86 && $role == 137) || ($app == 89 && $role == 145) || ($app == 84 && $role == 2)) {
                $this->db->select('u.id as uid, u.first_name, u.last_name, u.mobile');
                $this->db->from('tbl_locations as l'); //district
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id'); //constituency
                $this->db->join('tbl_const_location as cl', 'cl.parent_id = l2.id'); //constituency mandal
                $this->db->join('tbl_team_mng as tm', 'cl.location_id = tm.location');
                $this->db->join('tbl_users as u', 'tm.user_id = u.id');
                $this->db->where('l.id', $did);
                $this->db->where('l2.level_id', 11);
                $this->db->where('cl.level_id', 45);
                $this->db->where('u.user_role', $role);
                $this->db->where('u.status', 1);
                $res = $this->db->get();
                if($res->num_rows() > 0) {
                    $users = $res->result();
                    foreach($users as $k=> $v) {
                        $this->_sdb->select('d.id as did, d.status');
                        $this->_sdb->from('tbl_download as d');
                        $this->_sdb->where('d.user_id', $v->uid);
                        $res_d = $this->_sdb->get();

                        if($res_d->num_rows() > 0) {
                            $user_d = $res_d->row();
                            if($user_d->status == 1) {
                                unset($users[$k]);
                            }
                        }
                    }
                
                    return $users;    
                }else {
                    return false;
                }
            }
        }elseif($cid > 0) {
            if(($app == 86 && $role == 137) || ($app == 89 && $role == 145) || ($app == 84 && $role == 2)) {
                $this->db->select('u.id as uid, u.first_name, u.last_name, u.mobile');
                $this->db->from('tbl_locations as l'); //district
                $this->db->join('tbl_locations as l2', 'l2.parent_id = l.id'); //constituency
                $this->db->join('tbl_const_location as cl', 'cl.parent_id = l2.id'); //constituency mandal
                $this->db->join('tbl_team_mng as tm', 'cl.location_id = tm.location');
                $this->db->join('tbl_users as u', 'tm.user_id = u.id');
                $this->db->where('l.id', $did);
                $this->db->where('l2.id', $cid);
                $this->db->where('l2.level_id', 11);
                $this->db->where('cl.level_id', 45);
                $this->db->where('u.user_role', $role);
                $this->db->where('u.status', 1);
                $res = $this->db->get();
                if($res->num_rows() > 0) {
                    $users = $res->result();
                    foreach($users as $k=> $v) {
                        $this->_sdb->select('d.id as did, d.status');
                        $this->_sdb->from('tbl_download as d');
                        $this->_sdb->where('d.user_id', $v->uid);
                        $res_d = $this->_sdb->get();

                        if($res_d->num_rows() > 0) {
                            $user_d = $res_d->row();
                            if($user_d->status == 1) {
                                unset($users[$k]);
                            }
                        }
                    }
                
                    return $users;    
                }else {
                    return false;
                }
            }
        }
    }
}