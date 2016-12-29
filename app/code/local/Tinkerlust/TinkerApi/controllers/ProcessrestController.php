<?php 
	class Tinkerlust_TinkerApi_ProcessrestController extends Mage_Core_Controller_Front_Action
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
			if (sizeof($params) == 1 && is_int(array_keys($params)[0])) {
				$product = Mage::getModel('catalog/product')->load(array_keys($params)[0]);
				if ($product->getId()){
					$data = $product->getData();
					$data['image_location'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
					$this->helper->buildJson($data,true);
				}
				else {
					$this->helper->buildJson(null,false,"Product ID does not exist.");
				}
			}
			else {
				$limit = (isset($params['limit'])) ? $params['limit'] : 10;
				$products = Mage::getModel('catalog/product')->getCollection()->setPageSize($limit)->setCurPage(1);

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
						$this->helper->buildJson(null,false,"Category_url does not exist.");						
					}
				}

				//TODO: [Tech Debt] Make it parameterized
				$products->addAttributeToSelect('*');

				$data = [];

				foreach ($products as $product){
					$thisProduct = $product->getData();
					$thisProduct['image_location'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
					$data[] = $thisProduct;
				}
				$this->helper->buildJson($data,true);
			}
		}

		/*item for sale*/
		//TODO: THIS IS DUMMY METHOD. PLEASE FIX THIS AFTER THE NEW VENDOR SYSTEM IS UP
		public function itemforsaleAction(){
			$products = Mage::getModel('catalog/product')->getCollection()->setPageSize(30)->setCurPage(1);

			$products->addAttributeToSelect('*');

			$data = [];

			foreach ($products as $product){
				$thisProduct = $product->getData();
				$thisProduct['image_location'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
				$data[] = $thisProduct;
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

		public function subscribedbyAction(){
			//return false if token is invalid
			$this->check_access_token();
			
			$customers = Mage::getModel('customer/customer')->getCollection();
			$customers->addAttributeToSelect('firstname')->addAttributeToSelect('lastname')->setPageSize(46)->setCurPage(1);;

			$data = [];
			foreach($customers as $customer){
				$customer_data['name'] = $customer->getData('firstname') . ' ' . $customer->getData('lastname');
				$customer_data['profile_img'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'seller/profile/placeholder.jpg';
				$data[] = $customer_data;
			}
			$this->helper->buildJson($data,true);
		}

		public function subscribeAction(){
			$this->check_access_token();

			$params = $this->getRequest()->getParams();
			if (is_int(array_keys($params)[0])){
				$this->helper->buildJson(null,true,"Successfully subscribed!");
			}
			else {
				$this->helper->buildJson(null,false,"User ID is not found or user ID is invalid.");	
			}
		}

		public function cartGETAction(){
			$this->check_access_token();
			$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
			
			$user_id = $token['user_id'];

			$quote = Mage::getModel('sales/quote')->loadByCustomer($user_id);

			if ($quote) {
			    $cartProducts = array();
			    foreach ($quote->getAllItems() as $item) { 
			        $product = $item->getProduct(); 
			        $cartProducts[] = array(
			            'product_id' => $product->getId(),
			            'sku' => $product->getSku(),
			            'name' => $product->getName(),
			            'category_ids' => $product->getCategoryIds(),
			            'qty'	=> $item->getQty()
			        );
			    }
			}
			$this->helper->buildJson($cartProducts,true);	
		}

		public function addtocartAction(){
			$this->check_access_token();
			$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
			$user_id = $token['user_id'];
			$params = $this->getRequest()->getParams();

			$product_id = (is_numeric($params['add']))?$params['add']:null;
			if ($product_id){
				$_product = Mage::getModel('catalog/product')->load($product_id);
				if ($_product->getId() != null){
					try {
						$quote = Mage::getModel('sales/quote')->loadByCustomer($user_id);
						$quote->addProduct($_product);
						$quote->save();
						$this->helper->buildJson(null,true);
					} catch(Mage_Core_Exception $e){
						$this->helper->buildJson(null,false,"Cannot add product to cart, probably product is not available anymore.");
					}
				}
				else {
					$this->helper->buildJson(null,false,"Product with ID in the request doesn't exist.");
				}
			}
			else {
				$this->helper->buildJson(null,false,"Product ID is invalid or is not found");
			}
		}

		public function wishlistGETAction(){
			$this->check_access_token();
			$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
			$user_id = $token['user_id'];
			$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($user_id, true);
			$wishlistProducts = [];
			if ($wishlist) {
			    foreach ($wishlist->getItemCollection() as $item) { 
			        $product = $item->getProduct(); 
			        $wishlistProducts[] = array(
			            'product_id' => $product->getId(),
			            'sku' => $product->getSku(),
			            'name' => $product->getName()
			        );
			    }
			}
			$this->helper->buildJson($wishlistProducts,true);	
		}

		public function wishlistPOSTAction(){
			$this->check_access_token();
			$token = $this->_server->getAccessTokenData(OAuth2_Request::createFromGlobals());
			$user_id = $token['user_id'];
			$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($user_id, true);
			
			$params = $this->getRequest()->getParams();
			$product_id = (is_int(array_keys($params)[0]))?array_keys($params)[0]:null;
			if ($wishlist) {
				if ($product_id && $product = Mage::getModel('catalog/product')->load($product_id)){
					$buyRequest = new Varien_Object(array()); 
					$result = $wishlist->addNewItem($product, $buyRequest);
					$wishlist->save();
					$this->helper->buildJson(null,true,$product->getName() . " has been added to wishlist.");
				}
				else {
					$this->helper->buildJson(null,false,"Invalid Product ID.");
				}
			}
			else {
				$this->helper->buildJson(null,false,"Something's wrong. Can't get wishlist data.");
			}
		}

		public function searchAction(){
			$product_per_page = 10;
			$params = $this->getRequest()->getParams();

			$term = $params['q'];
		    $query = Mage::getModel('catalogsearch/query')->setQueryText($term)->prepare();
		    $fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
		        Mage::getModel('catalogsearch/fulltext'),
		        $term,
		        $query
		    );

		    $collection = Mage::getResourceModel('catalog/product_collection')->setPageSize($product_per_page);
		    $collection->addAttributeToSelect('*');
		    $collection->getSelect()->joinInner(
		        array('search_result' => $collection->getTable('catalogsearch/result')),
		        $collection->getConnection()->quoteInto(
		            'search_result.product_id=e.entity_id AND search_result.query_id=?',
		            $query->getId()
		        ),
		        array('relevance' => 'relevance')
		    );

		    $data = [];
		    $data['num_of_result'] = $collection->getSize();
		    $data['current_page'] = $params['p']?:1;

		    if ($data['num_of_result'] > ( ($data['current_page']-1) * $product_per_page ) ){
		    	$data['products'] = [];

		    	$collection->setCurPage($data['current_page']);

		    	foreach($collection as $product){
		    		$thisProduct = $product->getData();
		    		$thisProduct['image_location'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
		    		$data['products'][] = $thisProduct;
		    	}
		    }
		    else {
		    	$data['products'] = array();
		    }
		    
			$this->helper->buildJson($data,true);
		}
	}
 ?>