<?php 
	class Tinkerlust_TinkerApi_ProcessRestController extends Mage_Core_Controller_Front_Action
	{

		private $_server;
		private $_storage;

		public function _construct(){
			$this->_storage = Mage::getModel('tinkerapi/client');
			$this->_server = new OAuth2_Server($this->_storage,['access_lifetime' => 3600,'id_lifetime' => 3600 , 'allow_public_clients' => false]);
			$this->helper = Mage::helper('tinkerapi');
		}
		public function check_access_token(){
			if (!$this->_server->verifyResourceRequest(OAuth2_Request::createFromGlobals())) {
				$this->helper->buildJson(null,false,"Access denied: access_token is invalid or not found in the request");die();
			}
		}

		/* all REST related functions */
		public function productsAction(){
			$params = $this->getRequest()->getParams();
			$limit = (isset($params['limit'])) ? $params['limit'] : 10; 
			$products = Mage::getModel('catalog/product')->getCollection()->setPageSize($limit)->setCurPage(1);

			if (sizeof($params) == 1 && is_int(array_keys($params)[0]))
			{
				$products->addFieldToFilter('entity_id',array_keys($params)[0]);
			}

			if (isset($params['category_id']))
			{
				$products->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left');
				$products->addFieldToFilter('category_id',$params['category_id']);
			}
			else if (isset($params['category_url']))
			{
				$category_id = Mage::getModel('catalog/category')->getCollection()
							->addAttributeToFilter('url_key', $params['category_url'])->addAttributeToSelect('id')
							->getFirstItem()->getId();
				
				if ($category_id != null){
					$products->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left');
					$products->addFieldToFilter('category_id',$category_id);
				}
				else {
					$this->helper->buildJson(array(),true);						
				}
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
			$this->helper->buildJson($data,true);
		}

		/* all REST related functions */
		public function categoriesAction(){
			$categories = array();
			$rootId = 3;
			$children = Mage::getModel('catalog/category')->getCategories($rootId);
			foreach ($children as $child)
			{
				$subCategories = array();
				$grandChildren = Mage::getModel('catalog/category')->getCategories($child->getId());
				foreach ($grandChildren as $grandChild){
					$grandChildObj = array(
						'id' => $grandChild->getId(),
						'name' => $grandChild->getName(),
						'url' => $grandChild->getUrlKey(),
					);
					array_push($subCategories,$grandChildObj);
				}

				if($child->getIsActive() && $child->getIncludeInMenu()){
					$childObj = array(
						'id' => $child->getId(),
						'name' => $child->getName(),
						'url' => $child->getUrlKey(),
						'children' => $subCategories
					);
					array_push($categories,$childObj);		
				} 
			}
			$this->helper->buildJson($categories,true);
		}

		public function categoryAction(){
			$params = $this->getRequest()->getParams();
			$paramSize = sizeof($params);
			if ($paramSize == 1){
				$cat =  Mage::getModel('catalog/category')->load(array_keys($params)[0]);
				$obj = array(
					'id' => $cat->getId(),
					'name' => $cat->getName(),
					'url' => $cat->getUrlKey()
				);
				$this->helper->buildJson($obj,true);
			}
			else {
				$this->helper->buildJson(null,false,"Error: Please include category ID in your request.");
			}
		}

		public function customerAction(){
			//return false if token is invalid
			$this->check_access_token();
			$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
			$user_id = $token['user_id'];
			
			$customer = Mage::getModel('customer/customer');
			$customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
			$customer->load($user_id);
			$customer_data = $customer->getOrigData();
			unset($customer_data['password_hash']);
			$this->helper->buildJson($customer_data,true);
		}

	}
 ?>