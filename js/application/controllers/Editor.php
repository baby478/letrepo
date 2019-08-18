<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Editor extends CI_Controller {
    private $_httpStatus;

    public function __construct() {
        parent::__construct();
        $this->load->model('EditorModel');
    }

    public function __sanitize_data(array $data) {
        $sanitized_data = array();
        // echo json_encode($data);
        // exit;
        foreach($data as $k => $dt) {
            if(is_array($dt)) {
                // echo json_encode($dt);
                // exit;
                foreach($dt as $v) {
                    $sanitized_data[$k][] = trim($v);
                }
            }else {
                $sanitized_data[trim($k)] = trim($dt);
            }
            
        }
        return $sanitized_data;
    }

    private function __json_output($response, array $data,  $headers = array()) {
        header('Content-Type: application/json');
        if(count($headers) > 0) {
            foreach($headers as $k => $v) {
                header($k.': '.$v);
            }
        }
        $resp = array(
            'Status' => $this->_httpStatus,
            'Response' => $response,
            'AddData' => $data
        );
        return json_encode($resp);
    }

    public function getMandals($id) {
        $mandals = $this->EditorModel->getMandalsByConstituence($id);
        $this->_httpStatus = http_response_code(200);
        $data = array(
            'success' => 'Success.',
            'status' => 1,
            'data' => array(
                'mandals' => $mandals
            )     
        );
        echo $this->__json_output('Success', $data);
    }

    public function getpollingstation($id) {
        $ps = $this->EditorModel->getPollingStationByMandal($id);

        $this->_httpStatus = http_response_code(200);
        $data = array(
            'success' => 'Success.',
            'status' => 1,
            'data' => array(
                'ps' => $ps
            )     
        );
        echo $this->__json_output('Success', $data);
    }

    public function adduser() {
        if($this->input->post()) {
            $data = $this->__sanitize_data($this->input->post());
            
            $error_count = 0;
            $error = array();
            $fields = array('firstname', 'gender', 'mobile', 'psid');
            foreach($fields as $fd) {
                if(!$this->input->post($fd)) {
                    $error_count += 1;
                    $error['fields'][] = $fd;
                }
            }
            if($error_count > 0) {
                $this->_httpStatus = http_response_code(400);
                $data = array(
                    'fail' => 'Bad Request - Mandatory fields are missing.',
                    'status' => 0,
                    'fields' => $error['fields']     
                );
                echo $this->__json_output('Failure', $data); 
            }else {
                $mobile = $this->EditorModel->mobileVerify($data['mobile']);
                if($this->input->post('email')) {
                    $email = $this->EditorModel->emailVerify($data['email']);
                }else {
                    $email = false;
                }
                if(!$mobile && !$email) {
                    // echo 'ok'; exit;
                    if(isset($_FILES['photo']) && $_FILES['photo']['name'] !== '') {
                        //upload photo
                        $config['upload_path']   = $this->config->item('assets_users');
                        $config['allowed_types'] = 'png|jpg|jpeg';
                        // $config['max_size']  = 2048;
                        $config['file_name'] = time().$data['mobile'];
                        $this->load->library('upload', $config);

                        if($this->upload->do_upload('photo')) {
                            $uploadData = $this->upload->data();
                            $uploadedFile = $uploadData['file_name'];
                            $data['photo'] = $uploadedFile;
                            //image resize
                            $this->resizeImage($uploadedFile);
                        }else {
                            $this->_httpStatus = http_response_code(409);
                            $data = array(
                                'fail' => 'Could not save file to server.',
                                'status' => 0,
                                'error' => $this->upload->display_errors('<p>', '</p>')    
                            );
                            echo $this->__json_output('Failure', $data);
                        }
                    }else {
                        $data['photo'] = null;
                    }
                    $add_u = $this->EditorModel->addUser($data);
                    if($add_u) {
                        $this->_httpStatus = http_response_code(201);
                        $data = array(
                            'success' => 'Created - User information is successfully saved.',
                            'status' => 1,
                            'data' => array(
                                'user_id' => $add_u
                            )     
                        );
                        echo $this->__json_output('Success', $data);
                    }else {
                        $this->_httpStatus = http_response_code(500);
                        $data = array(
                            'fail' => 'Internal Server Error - We could not complete your request.',
                            'status' => 0     
                        );
                        echo $this->__json_output('Failure', $data);
                    }
                }else {
                    $this->_httpStatus = http_response_code(406);
                    $data = array(
                        'fail' => 'Not Accepted - Mobile already exists.',
                        'status' => 0     
                    );
                    echo $this->__json_output('Failure', $data);
                }
            }
        }
    }

    public function resizeImage($filename) {
        $source_path = $this->config->item('assets_users') . $filename;
        $config_manip = array(
            'image_library' => 'gd2',
            'source_image' => $source_path,
            // 'new_image' => $target_path,
            'maintain_ratio' => TRUE,
            // 'create_thumb' => TRUE,
            // 'thumb_marker' => '_thumb',
            'width' => 250,
            'height' => 250
        );
        $this->load->library('image_lib');
        // Set your config up
        $this->image_lib->initialize($config_manip);
        if (!$this->image_lib->resize()) {
          echo $this->image_lib->display_errors();
        }
  
  
        $this->image_lib->clear();
    }
}