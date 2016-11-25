<?php 
	
	class Tinkerlust_TinkerApi_RestController extends Mage_Core_Controller_Front_Action
	{
		private $helper;
		protected function _construct()
	  	{
	  		$this->helper = Mage::helper('tinkerapi');
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
			$products->addAttributeToSelect('name');
			$products->addAttributeToSelect('description');
			$products->addAttributeToSelect('certificate');
			$products->addAttributeToSelect('condition');
			$products->addAttributeToSelect('regular_price_with_tax');
			$products->addAttributeToSelect('dustbag');
			$products->addAttributeToSelect('color');
			$products->addAttributeToSelect('brand');
			$products->addAttributeToSelect('material');
			$products->addAttributeToSelect('is_saleable');
			$products->addAttributeToSelect('price');
			$products->addAttributeToSelect('image');
			$products->addAttributeToSelect('small_image');
			$products->addAttributeToSelect('thumbnail');

			$data = [];

			foreach ($products as $product){
				$data[] = $product->getData();
			}
			$this->helper->buildJson($data);
		}

		public function customerAction(){
			
			$params = $this->getRequest()->getParams();
			
			if ($this->getRequest()->isGet()){
				$baseEndPoint = 'tinkerapi/processoauth2/customer';
				$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
				$this->helper->returnJson($restData);
			}

			else if($this->getRequest()->isPost() ){
				$baseEndPoint = 'tinkerapi/processoauth2/createcustomer';
				$restData = $this->helper->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			}
		}

		
	}
?>