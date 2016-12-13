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
		
		public function productsAction()
		{

			$params = $this->getRequest()->getParams();

			$limit = (isset($params['limit'])) ? $params['limit'] : 10; 
			$products = Mage::getModel('catalog/product')->getCollection()->setPageSize($limit)->setCurPage(1);

			if (sizeof($params) == 1 && is_int(array_keys($params)[0])){
				$products->addFieldToFilter('entity_id',array_keys($params)[0]);
			}

			if (isset($params['category_id']))
			{
				$products->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left');
				$products->addFieldToFilter('category_id',$params['category_id']);
			}
			
			//TODO: [Tech Debt] Make it parameterized
			$products->addAttributeToSelect('*');
/*			$products->addAttributeToSelect('name');
			$products->addAttributeToSelect('short_description');						
			$products->addAttributeToSelect('description');
			$products->addAttributeToSelect('certificate');
			$products->addAttributeToSelect('condition');
			$products->addAttributeToSelect('regular_price_with_tax');
			$products->addAttributeToSelect('dustbag');
			$products->addAttributeToSelect('color');
			$products->addAttributeToSelect('brand');
			$products->addAttributeToSelect('material');
			$products->addAttributeToSelect('is_salable');
			$products->addAttributeToSelect('price');
			$products->addAttributeToSelect('size');
			$products->addAttributeToSelect('qty');
			$products->addAttributeToSelect('image');
			$products->addAttributeToSelect('small_image');
			$products->addAttributeToSelect('thumbnail');*/

			$data = [];

			foreach ($products as $product){
				$data[] = $product->getData();
			}
			$this->helper->buildJson($data);
		}


		public function customerAction(){
			$this->force_request_method('GET');
			$params = $this->getRequest()->getParams();

			$baseEndPoint = 'tinkerapi/processoauth2/customer';
			$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->helper->returnJson($restData);
		}

		
	}
?>