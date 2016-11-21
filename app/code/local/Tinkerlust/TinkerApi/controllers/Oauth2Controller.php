<?php 
	class Tinkerlust_TinkerApi_Oauth2Controller extends Mage_Core_Controller_Front_Action
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

		public function customerAction(){
			//return false if token is invalid
			if (!$this->tokenIsValid()) {
				$this->_server->getResponse()->send();
			}
			//otherwise, return requested data
			else {
				$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
				$user_id = $token['user_id'];
				$customer = Mage::getModel('customer/customer');
				$customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
				$customer->load($user_id);

				$customer_data = $customer->getOrigData();
				unset($customer_data['password_hash']);
				echo json_encode($customer_data);	
			}
		}
	}
 ?>