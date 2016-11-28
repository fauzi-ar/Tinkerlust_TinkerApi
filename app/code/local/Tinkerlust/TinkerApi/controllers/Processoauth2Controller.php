<?php 
	class Tinkerlust_TinkerApi_Processoauth2Controller extends Mage_Core_Controller_Front_Action
	{

		private $_server;
		private $_storage;

		public function _construct(){
			$this->_storage = Mage::getModel('tinkerapi/client');
			$this->_server = new OAuth2_Server($this->_storage);

		}
		public function tokenIsValid(){
			if (!$this->_server->verifyResourceRequest(OAuth2_Request::createFromGlobals())) return false;
			else return true;
		}
		public function loginAction(){
			$this->_server->addGrantType(new OAuth2_GrantType_UserCredentials($this->_storage));
			$this->_server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();
		}
		public function getaccesstokenforregistrationAction(){
			$this->_server->addGrantType(new OAuth2_GrantType_ClientCredentials($this->_storage,['access_lifetime' => 60,'id_lifetime' => 60]));
			$this->_server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();			
		}
		public function refreshAction(){
			$this->_server->addGrantType(new OAuth2_GrantType_RefreshToken($this->_storage,['always_issue_new_refresh_token' => true]));
			$this->_server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();
		}

		public function customerAction(){
			//return false if token is invalid
			if (!$this->tokenIsValid()) {
				$this->_server->getResponse()->send();
			}
			//otherwise, return requested data
			else {
				$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
				$user_id = $token['user_id'];
				$customer_data = Mage::helper('tinkerapi')->getCustomerData($user_id);
				echo json_encode($customer_data);	
			}
		}
	}
 ?>