<?php
/**
* @package     Server
* @author      xiaocao
* @link        http://homeway.me/
* @copyright   Copyright(c) 2015
* @version     15.6.24
**/
date_default_timezone_set('Asia/Kolkata');

class Server {
    public function __construct($config=array()) {
        require_once(__DIR__.'/../third_party/Oauth2/src/OAuth2/Autoloader.php');	//oauth library
        require_once( BASEPATH .'database/DB.php' );
        $db =& DB();
        $config = array(
            'dsn' => 'mysql:dbname='.$db->database.';host=127.0.0.1',
            'username' => $db->username,
            'password' => $db->password
        );
        
		OAuth2\Autoloader::register();
		$this->storage = new OAuth2\Storage\Pdo(array('dsn' => $config["dsn"], 'username' => $config["username"], 'password' => $config["password"]));
		$this->server = new OAuth2\Server($this->storage, array('allow_implicit' => true));
		$this->request = OAuth2\Request::createFromGlobals();
		$this->response = new OAuth2\Response();
    }
    
    public function client_credentials() {
		$this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($this->storage, array(
    		"allow_credentials_in_request_body" => true
		)));
		$this->server->handleTokenRequest($this->request)->send();
	}
 

    
}