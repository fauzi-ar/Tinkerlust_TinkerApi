<?php 
	class Tinkerlust_TinkerApi_Processoauth2Controller extends Mage_Core_Controller_Front_Action
	{
		private $_server;
		private $_storage;

		public function _construct(){
			$this->_storage = Mage::getModel('tinkerapi/client');
			$this->_server = new OAuth2_Server($this->_storage,['access_lifetime' => 3600,'id_lifetime' => 3600 , 'allow_public_clients' => false]);
			$this->helper = Mage::helper('tinkerapi');
		}
		public function loginAction(){
			$this->_server->addGrantType(new OAuth2_GrantType_UserCredentials($this->_storage));
			$this->_server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();
		}
		public function getaccesstokenforregistrationAction(){
			$this->_server->addGrantType(new OAuth2_GrantType_ClientCredentials($this->_storage));
			$this->_server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();			
		}
		public function refreshAction(){
			$this->_server->addGrantType(new OAuth2_GrantType_RefreshToken($this->_storage,['always_issue_new_refresh_token' => true]));
			$this->_server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();
		}
	}
 ?>