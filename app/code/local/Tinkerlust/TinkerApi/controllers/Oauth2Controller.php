<?php 
	class Tinkerlust_TinkerApi_Oauth2Controller extends Mage_Core_Controller_Front_Action
	{
		private $helper;

		protected function _construct()
	  	{
	  		$this->helper = Mage::helper('tinkerapi');
	  	}		

		private function force_request_method($method){
			if ($method == 'GET'){
				if (!$this->getRequest()->isGet()){
					$this->helper->buildJson('Access Denied. Please use GET method for your request.',false);
					die();
				}
			}
			else if ($method == 'POST'){
				if (!$this->getRequest()->isPost()){
					$this->helper->buildJson('Access Denied. Please use POST method for your request.',false);
					die();
				}	
			}
		}
	
		public function loginAction(){
			$this->force_request_method('POST');
			$params = $this->getRequest()->getParams();
			$params['grant_type'] = 'password';
			$baseEndPoint = 'tinkerapi/processoauth2/login';
			$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->helper->returnJson($restData);
		}

		//TODO: Make it POST only
		public function registerAction(){
			$this->force_request_method('POST');
			$params = $this->getRequest()->getParams();
			$params['grant_type'] = 'client_credentials';
			$baseEndPoint = 'tinkerapi/processoauth2/getaccesstokenforregistration';
			//process the oauth
			$tokenDataJSON = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$tokenData = JSON_decode($tokenDataJSON);
			
			//if authorization succeed (using client_credentials), create customer
			if (isset($tokenData->access_token)){
				//make sure that name, email, and password is filled
				if (isset($params['name']) && isset($params['email']) && isset($params['password'])){
					//fill up registration Data based on POST params;
					$registrationData = [];
					$nameArray = explode(" ", $params['name']);
					$registrationData['firstname'] = array_shift($nameArray);
					$registrationData['lastname'] = implode($nameArray);
					$registrationData['email'] = $params['email'];
					$registrationData['password'] = $params['password'];

					$customerCreate = $this->helper->createCustomer($registrationData);
					//if customer creation succeed
					if ($customerCreate['status'] == true){
						$params['grant_type'] = 'password';
						$params['username'] = $params['email'];
						$baseEndPoint = 'tinkerapi/processoauth2/login';
						$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
						$this->helper->returnJson($restData);
					}
					else {//if registration failed
						$this->helper->buildJson($customerCreate['message'],false);
					}	
				}
				else{//if Name, Email, or Password is missing
					$this->helper->buildJson("Registration Data is incomplete. Make sure [name], [email], [password] parameters are present.",false);
				}
			}
			else { //if client_id or client_secret is wrong
				$this->helper->returnJson($tokenDataJSON);
			}
			
			//$this->helper->returnJson($restData);
		}

		public function refreshAction(){
			$params = $this->getRequest()->getParams();
			$params['grant_type'] = 'refresh_token';
			$baseEndPoint = 'tinkerapi/processoauth2/refresh';
			$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->helper->returnJson($restData);
		}
	}
 ?>