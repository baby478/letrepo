<?php
class TelecallerModel extends CI_Model {

    private $_sdb;
    /**
     * Date : 07-01-2019
     */
    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getBoothCoordinator($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.user_role, u.gender, u.photo, l.value as designation');
        $this->db->from('tbl_team_ps as tp1');
        $this->db->join('tbl_team_ps as tp2', 'tp1.ps_id = tp2.ps_id');
        $this->db->join('tbl_users as u', 'tp2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tp1.user_id', $id);
        $this->db->where('u.user_role', 55);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getBoothPresident($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.user_role, u.gender, u.photo, l.value as designation');
        $this->db->from('tbl_team_ps as tp1');
        $this->db->join('tbl_team_ps as tp2', 'tp1.ps_id = tp2.ps_id');
        $this->db->join('tbl_users as u', 'tp2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tp1.user_id', $id);
        $this->db->where('u.user_role', 18);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function getStreetPresident($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.user_role, u.gender, u.photo, l.value as designation');
        $this->db->from('tbl_team_ps as tp1');
        $this->db->join('tbl_team_ps as tp2', 'tp1.ps_id = tp2.ps_id');
        $this->db->join('tbl_users as u', 'tp2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.user_role = l.id');
        $this->db->where('tp1.user_id', $id);
        $this->db->where('u.user_role', 3);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function getFamilyHead($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.mobile,  v.gender, v.photo, l.value as designation');
        $this->db->from('tbl_team_ps as tp1');
        $this->db->join('tbl_team_ps as tp2', 'tp1.ps_id = tp2.ps_id');
        $this->db->join('tbl_users as u', 'tp2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->join('tbl_lookup as l', 'cm.user_role = l.id');
        $this->db->where('tp1.user_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('cm.user_role', 46);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function userExists($id, $role) {
        if($role == 46) {
            $this->db->select('cm.citizen_id');
            $this->db->from('tbl_citizen_mng as cm');
            $this->db->where('cm.citizen_id', $id);
            $this->db->where('cm.user_role', 46);
            $this->db->where('cm.status', 1);
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }elseif($role == 3 || $role == 18 || $role == 55) {
            $this->db->select();
            $this->db->from('tbl_users as u');
            $this->db->where('u.id', $id);
            $this->db->where('u.user_role', $role);
            $this->db->where('u.status', 1);
            $result = $this->db->get();
            if($result->num_rows() > 0) {
                return true;
            }else {
                return false;
            }
        }
    }

    public function saveQuestionnaire($data) {
        $q_data = array(
            'user_id' => $data['vid'],
            'user_role' => $data['user_role'],
            'qid' => $data['qid'],
            'answer' => $data['aid'],
            'status' => 1,
            'created_by' => $data['user_id']
        );
        $result = $this->db->insert('tbl_q_result', $q_data);
        if($result) {
            return $this->db->insert_id();
        }else {
            return false;
        }
    }

    public function getQuestionsByReport($role, $report) {
        $this->db->select('q.id, q.user_role, q.report, q.question');
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

    public function isQuestionAnswered($user_id, $user_role, $qid) {
        $this->db->select('qr.id, qr.user_id, qr.status, qr.created_at');
        $this->db->from('tbl_q_result as qr');
        $this->db->where('qr.user_id', $user_id);
        $this->db->where('qr.user_role', $user_role);
        $this->db->where('qr.qid', $qid);
        $this->db->where('qr.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }

    public function saveCallFeedback($data) {
        $f_data = array(
            'user_id' => $data['vid'],
            'user_role' => $data['user_role'],
            'feedback' => $data['feedback'],
            'created_by' => $data['user_id']
        );
        $result = $this->db->insert('tbl_call_feedback', $f_data);
        if($result) {
            return $this->db->insert_id();
        }else {
            return false;
        }
    }

    public function getQuestionsCountByTC($id) {
        $this->db->select('qr.id');
        $this->db->from('tbl_q_result as qr');
        $this->db->where('qr.created_by', $id);
        $this->db->where('qr.status', 1);
        return $this->db->get()->num_rows();
    }

    public function getCallsCount($id) {
        $this->db->select('cf.id');
        $this->db->from('tbl_call_feedback as cf');
        $this->db->where('cf.created_by', $id);
        return $this->db->get()->num_rows();
    }

    public function getCallsAnsweredCount($id) {
        $this->db->select('cf.id');
        $this->db->from('tbl_call_feedback as cf');
        $this->db->where('cf.created_by', $id);
        $this->db->where('cf.feedback', 33);
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

    public function getSms($id) {
        $inbox = array();

        $sup_id = array();
        $this->db->select('t2.user_id as di_id, t3.user_id as mng_id, t4.user_id as smng_id');
        $this->db->from('tbl_team_mng as t');
        $this->db->join('tbl_team_mng as t2', 't.parent_id = t2.user_id');
        $this->db->join('tbl_team_mng as t3', 't2.parent_id = t3.user_id');
        $this->db->join('tbl_team_mng as t4', 't3.parent_id = t4.user_id');
        $this->db->where('t.user_id', $id);
        $result = $this->db->get()->row();
        $sup_id[] = $result->di_id;
        $sup_id[] = $result->mng_id;
        $sup_id[] = $result->smng_id;

        //group inbox
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, u.first_name, lu.value as sender');
        $this->db->from('tbl_sms as s');
        $this->db->join('tbl_sms_mng as sm', 's.id = sm.sms_id');
        $this->db->join('tbl_users as u', 's.created_by = u.id');
        $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
        $this->db->where('s.sms_type', 62);
        $this->db->where('sm.receiver_id', 139);
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

    public function getSentSms($id) {
        $outbox = array();
        
        //group sms
        $this->db->select('s.id, s.sms_type, s.text_message, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
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
        $this->db->select('s.id, s.sms_type, s.receiver_user_role, s.text_message, s.language, s.created_at, sm.receiver_id, sm.msg_count, lu.value');
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

    /**
     * Date : 10-01-2019
     */
    public function getVoters($id) {
        $this->db->select('v.id, v.firstname, v.lastname, v.mobile,  v.gender, v.photo, l.value as designation');
        $this->db->from('tbl_team_ps as tp1');
        $this->db->join('tbl_team_ps as tp2', 'tp1.ps_id = tp2.ps_id');
        $this->db->join('tbl_users as u', 'tp2.user_id = u.id');
        $this->db->join('tbl_citizen_mng as cm', 'cm.user_id = u.id');
        $this->db->join('tbl_voters as v', 'cm.citizen_id = v.id');
        $this->db->join('tbl_lookup as l', 'cm.user_role = l.id');
        $this->db->where('tp1.user_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('cm.user_role', 17);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function saveReminder($data) {
        $data_r = array(
            'citizen_id' => $data['citizen_id'],
            'status' => $data['v_status'],
            'created_by' => $data['user_id']
        );
        if($data['v_status'] == 12) {
            $data_r['no_of_voters'] = $data['voters'];
            $data_r['reminder_time'] = $data['expected_time'];
        }
        $result = $this->db->insert('tbl_poll_reminder', $data_r);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getReminderStatus($citizen_id, $user_id) {
        $this->db->select('r.id, r.citizen_id, r.poll_date, r.reminder_time, r.no_of_voters, r.status');
        $this->db->from('tbl_poll_reminder as r');
        $this->db->where('citizen_id', $citizen_id);
        $this->db->where('created_by', $user_id);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->row();
        }else {
            return false;
        }
    }
}