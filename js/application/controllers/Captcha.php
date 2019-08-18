<?php
defined('BASEPATH') OR exit('your exit message');
class Captcha extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('captcha');
    }
     
    public function index() {
	   $string = $this->generateRandomString(7);
       $vals = array(   'word' => $string,
	                    'img_path' => './assets/images/captcha/', 
                        'img_url' => base_url() . 'assets/images/captcha/',
                        'font_path' => base_url() . 'assets/fonts/summernote.ttf',
                        'img_width' => '150', 'img_height' => 50,
                        'expiration' => 7200, 'word_length' => 8, 
                        'font_size' => 18, 'img_id' => 'Imageid',
                        'pool' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                        // White background and border, black text and red grid 
                        'colors' => array( 'background' => array(223,249,252), 
                            'border' => array(123,167,168), 
                            'text' => array(0, 0, 0),
                            'grid' => array(215,245,201) 
                                    ) 
                    ); 
	   $cap = create_captcha($vals); 
	   echo $cap['image'];
                
    }
    
     
    public function refresh() {
        // Captcha configuration
        $config = array(
            'img_path'      => 'captcha_images/',
            'img_url'       => base_url().'captcha_images/',
            'font_path'     => 'system/fonts/texb.ttf',
            'img_width'     => '160',
            'img_height'    => 50,
            'word_length'   => 8,
            'font_size'     => 18
        );
        $captcha = create_captcha($config);
        
        // Unset previous captcha and set new captcha word
        $this->session->unset_userdata('captchaCode');
        $this->session->set_userdata('captchaCode',$captcha['word']);
        
        // Display captcha image
        echo $captcha['image'];
    }
    
    public function generateRandomString($length = 7) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}