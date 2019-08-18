<?php
class SPresidentModel extends CI_Model {
    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getDistricts($id) {
        $this->db->select('l2.id, l2.name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l1', 'tm.location = l1.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l2.level_id', 8);
        $this->db->order_by('l2.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getLSConstitency($id) {
        $this->db->select('l2.id, l2.name');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l1', 'tm.location = l1.id');
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l2.level_id', 58);
        $this->db->order_by('l2.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getAsConstituencyByDistrict($id) {
        $this->db->select('l.id, l.name');
        $this->db->from('tbl_locations as l');
        $this->db->where('l.parent_id', $id);
        $this->db->where('l.level_id', 11);
        $this->db->order_by('l.name');
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getMPByConstituency($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.dob, u.mobile, u.gender, u.photo, l.name as location, lu.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 57);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getMLAByConstituency($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.dob, u.mobile, u.gender, u.photo, l.name as location, lu.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_users as u', 'tm.user_id = u.id');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDPresidentByConstituency($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.dob, u.mobile, u.gender as gid, u.photo, l.name as location, lu.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm1', 'tm1.parent_id = tm.user_id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->join('tbl_locations as l', 'tm1.location = l.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('tm.location', $id);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
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

    public function getMPCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');//state
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//ls constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l1.id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l1.level_id', 58);
        $this->db->where('u.user_role', 57);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getMLACount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');//state
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');//as constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l2.id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l1.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getDPCount($id) {
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');//state
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');//as constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l2.id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l1.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        return $this->db->get()->num_rows();
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

    public function getMP($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.gender as gid, u.photo, u.mobile, l1.name as location');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');//state
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//ls constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l1.id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l1.level_id', 58);
        $this->db->where('u.user_role', 57);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getMla($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');//state
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');//as constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l2.id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l1.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getDpresident($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as l', 'tm.location = l.id');//state
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//district
        $this->db->join('tbl_locations as l2', 'l2.parent_id = l1.id');//as constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l2.id');
        $this->db->join('tbl_team_mng as tm2', 'tm2.parent_id = tm1.user_id');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('l1.level_id', 8);
        $this->db->where('l2.level_id', 11);
        $this->db->where('u.user_role', 137);
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

        if($single) {
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

    public function getMLAByDistrict($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.gender as gid, u.photo, l1.name as location');
        $this->db->from('tbl_locations as l');//district
        $this->db->join('tbl_locations as l1', 'l1.parent_id = l.id');//constituency
        $this->db->join('tbl_team_mng as tm1', 'tm1.location = l1.id');
        $this->db->join('tbl_users as u', 'tm1.user_id = u.id');
        $this->db->where('l.id', $id);
        $this->db->where('l.level_id', 8);
        $this->db->where('l1.level_id', 11);
        $this->db->where('u.user_role', 44);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }
}