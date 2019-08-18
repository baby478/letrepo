<?php
class CommunicationModel extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function getTemplateMessageByID($id) {
        $this->db->where('id', $id);
        $result = $this->db->get('tbl_msg_template');
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }
    }

    public function storeSMS($data) {
        $user_id = $this->session->userdata('user')->id;
        $sms_data = array(
            'user_id' => $user_id,
            'mobile' => $data['mobile'],
            'text_message' => $data['message']
        );
        $result = $this->db->insert('tbl_sms_details', $sms_data);
        if($result) {
            return true;
        }else {
            return false;
        }
    }

    public function getConfig() {
        $this->db->select('id, name, value');
        $this->db->from('tbl_config');
        $this->db->where('gen_id', 70);
        $result = $this->db->get();
        if($result->num_rows() > 0) {
            return $result->result();
        }else {
            return false;
        }

    }
}