<?php
class AppCoordinatorModel extends CI_Model {

    private $_sdb;

    public function __construct() {
        parent::__construct();
        $this->_sdb = $this->load->database('security', TRUE);
    }

    public function getotp($user_id, $mobile) {
        //get user by mobile
        $this->db->select('u.id, u.mobile, u.user_role');
        $this->db->from('tbl_users as u');
        $this->db->where('u.mobile', $mobile);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            $muser = $result->row();
            //Division Coordinator
            if($muser->user_role == 137 || $muser->user_role == 2) {
                $this->db->select('tm2.user_id');
                $this->db->from('tbl_team_mng as tm');
                $this->db->join('tbl_team_mng as tm2', 'tm.location = tm2.location');
                $this->db->where('tm.user_id', $user_id);
                $this->db->where('tm2.user_id', $muser->id);
                $mresult = $this->db->get();
                if($mresult->num_rows() > 0) {
                    $this->_sdb->select('o.user_id, from_base64(o.otp_code) as otp, o.created_at, o.expired_at');
                    $this->_sdb->from('tbl_otp as o');   
                    $this->_sdb->where('o.created_at >= DATE_ADD(CURDATE(), INTERVAL - 3 DAY)', NULL, FALSE);
                    $this->_sdb->where('o.user_id', $muser->id);
                    $this->_sdb->order_by('o.created_at', 'desc');
                    $res = $this->_sdb->get();
                    if($res->num_rows() > 0) {
                        $o_data = $res->result();
                        foreach($o_data as $u) {
                            $this->db->select('u.first_name, u.last_name, u.mobile, lu.value as user_role');
                            $this->db->from('tbl_users as u');
                            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                            $this->db->where('u.id', $u->user_id);
                            $res_u = $this->db->get()->row();
                            $u->name = $res_u->first_name . ' ' .$res_u->last_name;
                            $u->mobile = $res_u->mobile;
                            $u->role = $res_u->user_role;
                        }
                        return $o_data;
                    }else {
                        return $o_data = 'No content';
                    }
                    
                }else {
                    return false;
                }
            }elseif($muser->user_role == 55 || $muser->user_role == 18 || $muser->user_role == 3) {
                $this->db->select('tm2.user_id');
                $this->db->from('tbl_team_mng as tm');
                $this->db->join('tbl_locations as l', 'l.parent_id = tm.location');
                $this->db->join('tbl_team_mng as tm2', 'tm2.location = l.id');
                $this->db->where('tm.user_id', $user_id);
                $this->db->where('tm2.user_id', $muser->id);
                $mresult = $this->db->get();
                if($mresult->num_rows() > 0) {
                    $this->_sdb->select('o.user_id, from_base64(o.otp_code) as otp, o.created_at, o.expired_at');
                    $this->_sdb->from('tbl_otp as o');   
                    $this->_sdb->where('o.created_at >= DATE_ADD(CURDATE(), INTERVAL - 3 DAY)', NULL, FALSE);
                    $this->_sdb->where('o.user_id', $muser->id);
                    $this->_sdb->order_by('o.created_at', 'desc');
                    $res = $this->_sdb->get();
                    if($res->num_rows() > 0) {
                        $o_data = $res->result();
                        foreach($o_data as $u) {
                            $this->db->select('u.first_name, u.last_name, u.mobile, lu.value as user_role');
                            $this->db->from('tbl_users as u');
                            $this->db->join('tbl_lookup as lu', 'u.user_role = lu.id');
                            $this->db->where('u.id', $u->user_id);
                            $res_u = $this->db->get()->row();
                            $u->name = $res_u->first_name . ' ' .$res_u->last_name;
                            $u->mobile = $res_u->mobile;
                            $u->role = $res_u->user_role;
                        }
                        return $o_data;
                    }else {
                        return $o_data = 'No content';
                    }
                }else {
                    return false;
                }
            }
            

        }else {
            return false;
        }
    }

    public function getDivisionPresident($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm.location = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('u.user_role', 137);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $muser = $result->row();
            
            $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
            $this->_sdb->from('tbl_download as d');
            $this->_sdb->where('d.user_id', $muser->id);
            $this->_sdb->where('d.app_id', 86);
            $this->_sdb->where('d.status', 1);
            $dresult = $this->_sdb->get();
            if($dresult->num_rows() > 0) {
                $this->_sdb->select();
                $this->_sdb->from('tbl_activity as ua');
                $this->_sdb->where('ua.user_id', $muser->id);
                $this->_sdb->where('ua.request', 'DivisionHead/dashboard');
                $this->_sdb->where('ua.http_request', 200);
                $this->_sdb->where('ua.status', 1);
                $uaresult = $this->_sdb->get();
                if($uaresult->num_rows() > 0) {
                    $muser->downloadstatus = 1;
                    $muser->dmessage = 'Downloaded and using.';
                }else {
                    $muser->downloadstatus = 2;
                    $muser->dmessage = 'Downloaded. But not using.';
                    $muser->ddate = $dresult->row()->download_at;
                }
            }else {
                $muser->downloadstatus = 3;
                $muser->dmessage = 'Not downloaded yet.';
            }
            return $muser;
        }else {
            return false;
        }
    }

    public function getDivisionCoordinator($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm.location = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('u.user_role', 2);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where('d.app_id', 84);
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                if($dresult->num_rows() > 0) {
                    $this->_sdb->select();
                    $this->_sdb->from('tbl_activity as ua');
                    $this->_sdb->where('ua.user_id', $mu->id);
                    $this->_sdb->where('ua.request', 'DManager/dashboard');
                    $this->_sdb->where('ua.http_request', 200);
                    $this->_sdb->where('ua.status', 1);
                    $uaresult = $this->_sdb->get();
                    if($uaresult->num_rows() > 0) {
                        $mu->downloadstatus = 1;
                        $mu->dmessage = 'Downloaded and using.';
                    }else {
                        $mu->downloadstatus = 2;
                        $mu->dmessage = 'Downloaded. But not using.';
                        $mu->ddate = $dresult->row()->download_at;
                    }
                }else {
                    $mu->downloadstatus = 3;
                    $mu->dmessage = 'Not downloaded yet.';
                }
            }
            
            return $muser;
        }else {
            return false;
        }
    }

    public function getBoothCoordinator($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as lc', 'lc.parent_id = tm.location');
        $this->db->join('tbl_team_mng as tm2', 'lc.id = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('u.user_role', 55);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where('d.app_id', 81);
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                if($dresult->num_rows() > 0) {
                    $this->_sdb->select();
                    $this->_sdb->from('tbl_activity as ua');
                    $this->_sdb->where('ua.user_id', $mu->id);
                    $this->_sdb->where('ua.request', 'MobileTeam/dashboard');
                    $this->_sdb->where('ua.http_request', 200);
                    $this->_sdb->where('ua.status', 1);
                    $uaresult = $this->_sdb->get();
                    if($uaresult->num_rows() > 0) {
                        $mu->downloadstatus = 1;
                        $mu->dmessage = 'Downloaded and using.';
                    }else {
                        $mu->downloadstatus = 2;
                        $mu->dmessage = 'Downloaded. But not using.';
                        $mu->ddate = $dresult->row()->download_at;
                    }
                }else {
                    $mu->downloadstatus = 3;
                    $mu->dmessage = 'Not downloaded yet.';
                }
            }
            
            return $muser;
        }else {
            return false;
        }
    }

    public function getBoothPresident($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as lc', 'lc.parent_id = tm.location');
        $this->db->join('tbl_team_mng as tm2', 'lc.id = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('u.user_role', 18);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where('d.app_id', 79);
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                if($dresult->num_rows() > 0) {
                    $this->_sdb->select();
                    $this->_sdb->from('tbl_activity as ua');
                    $this->_sdb->where('ua.user_id', $mu->id);
                    $this->_sdb->where('ua.request', 'TeamLeader/dashboard');
                    $this->_sdb->where('ua.http_request', 200);
                    $this->_sdb->where('ua.status', 1);
                    $uaresult = $this->_sdb->get();
                    if($uaresult->num_rows() > 0) {
                        $mu->downloadstatus = 1;
                        $mu->dmessage = 'Downloaded and using.';
                    }else {
                        $mu->downloadstatus = 2;
                        $mu->dmessage = 'Downloaded. But not using.';
                        $mu->ddate = $dresult->row()->download_at;
                    }
                }else {
                    $mu->downloadstatus = 3;
                    $mu->dmessage = 'Not downloaded yet.';
                }
            }
            
            return $muser;
        }else {
            return false;
        }
    }

    public function getStreetPresident($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as lc', 'lc.parent_id = tm.location');
        $this->db->join('tbl_team_mng as tm2', 'lc.id = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('u.user_role', 3);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where('d.app_id', 78);
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                if($dresult->num_rows() > 0) {
                    $this->_sdb->select();
                    $this->_sdb->from('tbl_activity as ua');
                    $this->_sdb->where('ua.user_id', $mu->id);
                    $this->_sdb->where('ua.request', 'Coordinator/dashboard');
                    $this->_sdb->where('ua.http_request', 200);
                    $this->_sdb->where('ua.status', 1);
                    $uaresult = $this->_sdb->get();
                    if($uaresult->num_rows() > 0) {
                        $mu->downloadstatus = 1;
                        $mu->dmessage = 'Downloaded and using.';
                    }else {
                        $mu->downloadstatus = 2;
                        $mu->dmessage = 'Downloaded. But not using.';
                        $mu->ddate = $dresult->row()->download_at;
                    }
                }else {
                    $mu->downloadstatus = 3;
                    $mu->dmessage = 'Not downloaded yet.';
                }
            }
            
            return $muser;
        }else {
            return false;
        }
    }

    public function getTelecaller($id) {
        $this->db->select('u.id, u.first_name, u.last_name, u.mobile, u.photo, u.gender as gid, u.user_role as role, l.value as gender, rl.value as user_role');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as lc', 'lc.parent_id = tm.location');
        $this->db->join('tbl_team_mng as tm2', 'lc.id = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->join('tbl_lookup as l', 'u.gender = l.id');
        $this->db->join('tbl_lookup as rl', 'u.user_role = rl.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where('u.user_role', 138);
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        
        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where('d.app_id', 85);
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                if($dresult->num_rows() > 0) {
                    $this->_sdb->select();
                    $this->_sdb->from('tbl_activity as ua');
                    $this->_sdb->where('ua.user_id', $mu->id);
                    $this->_sdb->where('ua.request', 'Telecaller/dashboard');
                    $this->_sdb->where('ua.http_request', 200);
                    $this->_sdb->where('ua.status', 1);
                    $uaresult = $this->_sdb->get();
                    if($uaresult->num_rows() > 0) {
                        $mu->downloadstatus = 1;
                        $mu->dmessage = 'Downloaded and using.';
                    }else {
                        $mu->downloadstatus = 2;
                        $mu->dmessage = 'Downloaded. But not using.';
                        $mu->ddate = $dresult->row()->download_at;
                    }
                }else {
                    $mu->downloadstatus = 3;
                    $mu->dmessage = 'Not downloaded yet.';
                }
            }
            
            return $muser;
        }else {
            return false;
        }
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

    public function getRegisteredCount($id) {
        $rcount = 0;

        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm.location = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where_in('u.user_role', array(137, 2));
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        $rcount = $rcount + $result->num_rows();

        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as lc', 'lc.parent_id = tm.location');
        $this->db->join('tbl_team_mng as tm2', 'lc.id = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where_in('u.user_role', array(55, 18, 138, 3));
        $this->db->where('u.status', 1);
        $result = $this->db->get();
        $rcount = $rcount + $result->num_rows();
        return $rcount;
    }

    public function getDownloadCount($id) {
        $dcount = 0;
        
        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_team_mng as tm2', 'tm.location = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where_in('u.user_role', array(137, 2));
        $this->db->where('u.status', 1);
        $result = $this->db->get();

        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where_in('d.app_id', array(84, 86));
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                $dcount = $dcount + $dresult->num_rows();
            }    
        }

        $this->db->select('u.id');
        $this->db->from('tbl_team_mng as tm');
        $this->db->join('tbl_locations as lc', 'lc.parent_id = tm.location');
        $this->db->join('tbl_team_mng as tm2', 'lc.id = tm2.location');
        $this->db->join('tbl_users as u', 'tm2.user_id = u.id');
        $this->db->where('tm.user_id', $id);
        $this->db->where_in('u.user_role', array(55, 18, 138, 3));
        $this->db->where('u.status', 1);
        $result = $this->db->get();

        if($result->num_rows() > 0) {
            $muser = $result->result();
            foreach($muser as $mu) {
                $this->_sdb->select('d.id, d.user_id, d.status, d.download_at');
                $this->_sdb->from('tbl_download as d');
                $this->_sdb->where('d.user_id', $mu->id);
                $this->_sdb->where_in('d.app_id', array(78, 79, 81, 85));
                $this->_sdb->where('d.status', 1);
                $dresult = $this->_sdb->get();
                $dcount = $dcount + $dresult->num_rows();
            }    
        }

        return $dcount;
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
}