<?php 
	
	class Tinkerlust_TinkerApi_RestController extends Mage_Core_Controller_Front_Action
	{
		private $helper;
		protected function _construct()
	  	{
	  		$this->helper = Mage::helper('tinkerapi');
	  	}

	  	private function force_request_method($method){
			if ($method == 'GET'){
				if (!$this->getRequest()->isGet()){
					$this->helper->buildJson(null,false,'Access Denied. Please use GET method for your request.');
					die();
				}
			}
			else if ($method == 'POST'){
				if (!$this->getRequest()->isPost()){
					$this->helper->buildJson(null,false,'Access Denied. Please use POST method for your request.');
					die();
				}	
			}
		}
		
		public function productsAction()
		{
			$this->force_request_method('GET');
			$params = $this->getRequest()->getParams();
			$baseEndPoint = 'tinkerapi/processrest/products';
			$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'GET');
			$this->helper->returnJson($result);
		}

		public function categoryAction(){
			$this->force_request_method('GET');
			$params = $this->getRequest()->getParams();
			$baseEndPoint = 'tinkerapi/processrest/category';
			$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'GET');
			$this->helper->returnJson($result);
		}

		public function categoriesAction(){
			$this->force_request_method('GET');
			$params = $this->getRequest()->getParams();
			$baseEndPoint = 'tinkerapi/processrest/categories';
			$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'GET');
			$this->helper->returnJson($result);
		}

		public function featuredSellerAction()
		{
			//TODO: When the new vendor system is UP, change this.
			$featuredSellerHtml = $this->getLayout()->createBlock('cms/block')->setBlockId('featured_seller_slider')->toHtml(); 
			
			$result = null;			
			preg_match_all('/href="([\S]+?)"[\s\S]*?<h3>(.+?)<\/h3>\s*<p>(.+?)<\/p>[\s\S]*?<img.*src="(.+?)"/',$featuredSellerHtml,$matches);
			$size = sizeof($matches[0]);
			$featured_sellers = array();
			for ($i = 0;$i<$size;$i++){
				$featured_seller = array(
					'name' => $matches[2][$i],	
					'url' => $matches[1][$i],
					'job' => $matches[3][$i],
					'image' => $matches[4][$i],
				);

				$featured_sellers[$i] = $featured_seller;
			}

			$this->helper->buildJson($featured_sellers);
		}


		public function customerAction(){
			$this->force_request_method('GET');
			$params = $this->getRequest()->getParams();

			$baseEndPoint = 'tinkerapi/processrest/customer';
			$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->helper->returnJson($result);
		}

		public function cartAction(){
			if ($this->getRequest()->isGet()){
				$params = $this->getRequest()->getParams();
				$baseEndPoint = 'tinkerapi/processrest/cartGET';
				$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
				$this->helper->returnJson($result);
			}
			else if ($this->getRequest()->isPost()) {
				$params = $this->getRequest()->getParams();
				if (array_keys($params)[0] == 'add'){
					$baseEndPoint = 'tinkerapi/processrest/addtocart';
					$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
					$this->helper->returnJson($result);
				}
			}
		}

		public function forgotpasswordAction(){
			$this->force_request_method('POST');
			$params = $this->getRequest()->getParams();
			$baseEndPoint = 'tinkerapi/processrest/forgotpassword';
			$result = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->helper->returnJson($result);
		}
		
	}
?>