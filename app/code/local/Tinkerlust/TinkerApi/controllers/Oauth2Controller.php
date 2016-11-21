<?php 
	class Tinkerlust_TinkerApi_Oauth2Controller extends Mage_Core_Controller_Front_Action
	{
		private $helper;

		protected function _construct()
	  	{
	  		$this->helper = Mage::helper('tinkerapi');
	  	}		
	
		public function loginAction(){
			$params = $this->getRequest()->getParams();
			$params['grant_type'] = 'password';
			$baseEndPoint = 'tinkerapi/processoauth2/login';
			$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->helper->returnJson($restData);
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