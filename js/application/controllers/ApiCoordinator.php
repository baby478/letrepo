<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ApiCoordinator extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('ApiCoordModel');
    }

    public function addVoter() {
        $miss_count = 0;
        
        if($this->input->post()) {
            $data = $this->input->post();
            $fields = array('firstname', 'lastname','f_name', 'dob', 'mobile',   
                             'voter_id',  'user_id');
            
            $miss_field = array();
            foreach($fields as $field) {
                if(!$this->input->post($field)) {
                    $miss_field[] = $field;
                    $miss_count += 1;
                }
            }
            // $photo = $_FILES['photo']['name'];
            // if(isset($_FILES['photo']['name']) && !empty($photo)) {
            //     $config['upload_path']   = $this->config->item('assets_users');
            //     $config['allowed_types'] = 'jpeg|jpg|png';
            //     $config['max_size']  = 1024;
            //     $config['file_name'] = time().$data['mobile'];
            //     $this->load->library('upload', $config);
            //     if($this->upload->do_upload('photo')){
            //         $uploadData = $this->upload->data();
            //         $uploadedFile = $uploadData['file_name'];
            //         $data['photo'] = $uploadedFile;
            //     }else {
            //         echo json_encode(array('error' => 'Photo could not be saved', 'status' => 0));
            //         exit;
            //     }
            // }
            if($miss_count > 0) {
                echo json_encode($miss_field);
                echo json_encode(array('error' => 'Fields are missing '.$miss_count, 'status' => 0));
                exit;
            }else {
                $voter_exists = $this->ApiCoordModel->voterExists();
                if($voter_exists) {
                    echo json_encode(array('error' => 'Voter already exists', 'status' => 0));
                    exit;
                }
                
                // if($this->input->post('photo') !== '') {
                //     $img_name = time().$data['voter_id'].'.png';
                //     $img_path = $this->config->item('assets_voters').$img_name;

                //     $img = fopen($img_path, 'wb');
                //     if(fwrite($img, base64_decode($data['photo'])) === FALSE) {
                //         echo json_encode(array('error' => 'Photo could not be saved', 'status' => 0));
                //         exit;
                //     }
                //     fclose($img);
                //     $data['photo'] = $img_name;
                // }
                
                $upload_data = $this->ApiCoordModel->addVoterDetails($data);
                if($upload_data) {
                    echo json_encode(array('success' => 'Record inserted successfully', 'status' => 1));
                    exit;
                }else {
                    echo json_encode(array('error' => 'Sorry! Record is not inserted', 'status' => 0));
                    exit; 
                }
            }
        }else {
            echo json_encode(array('error' => 'Not post', 'status' => 0));
        }
    }

    public function addEvent() {
        $miss_count = 0;
        if($this->input->post()) {
            $data = $this->input->post();
            $fields = array('event_type', 'event_name', 'event_img', 'user_id');
            
            foreach($fields as $field) {
                if(!$this->input->post($field)) {
                    $miss_count += 1;
                }
            }
            
            if($miss_count > 0) {
                echo json_encode(array('error' => 'Fields are missing', 'status' => 0));
                exit;
            }else {
                
                $evnt_name = time().$data['event_name'].'.png';
                $image_path = $this->config->item('assets_events').$evnt_name;
                
                // if(is_writable($image_path)) {
                    $evnt = fopen($image_path, 'wb');
                    if(fwrite($evnt, base64_decode($data['event_img'])) === FALSE) {
                        echo json_encode(array('error' => 'Photo could not be saved', 'status' => 0));
                        exit;
                    }
                    fclose($evnt);
                    $data['event_img'] = $evnt_name;
                // }else {
                //     echo json_encode(array('error' => 'Some error occurred could not write', 'status' => 0));
                //     exit;
                // }
                $upload_data = $this->ApiCoordModel->addEvent($data);
                if($upload_data) {
                    echo json_encode(array('success' => 'Event saved successfully', 'status' => 1));
                    exit;
                }else {
                    echo json_encode(array('error' => 'Error! Event is not saved', 'status' => 0));
                    exit; 
                }
            }
        }else {
            echo json_encode(array('error' => 'Not post', 'status' => 0));
        }
    }
}