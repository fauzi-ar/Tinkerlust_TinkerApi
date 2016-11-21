<?php 
	
	class Tinkerlust_TinkerApi_RestController extends Mage_Core_Controller_Front_Action
	{
		protected function _construct()
	  	{

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
	
			/*$baseEndPoint = 'api/rest/products/';
			$params = $this->getRequest()->getParams();
			
			//endpoints: tinkerapi/rest/products
			if ($params == null){
				$restData = $this->curl(Mage::getBaseUrl() . $baseEndPoint);
			}

			//endpoints: tinkerapi/rest/products/:id
			if (sizeof($params) == 1 && is_int(array_keys($params)[0])){	
				$product_id = array_keys($params)[0];
				$restData = $this->curl(Mage::getBaseUrl() . $baseEndPoint . $product_id);
			}

			//enpoints: tinkerapi/rest/products (with options)
			else {
				$restUrl = Mage::getBaseUrl() . $baseEndPoint;

				if (sizeof($params) > 0) {
					$restUrl .= '?';
					$first = true; 
					foreach($params as $key => $value){
						if ($first) $first = false;
						else $restUrl .= '&';
						$restUrl .= "$key=$value";
					}
				}

				$restData = $this->curl($restUrl);
			}*/
			$this->returnJson($data,true,'Success');
		}

		/* WE DON'T CLIENT CREDENTIALS ANYMORE BECAUSE WE HAVE TO LOGIN. SO 'USER_CREDENTIAL' IS USED INSTEAD */
		/*public function requesttokenAction(){
			$params = $this->getRequest()->getParams();
			$params['grant_type'] = 'client_credentials';
			$baseEndPoint = 'tinkerapi/oauth2/requesttoken';
			$restData = $this->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->returnJson($restData);
		}*/

		public function loginAction(){
			$params = $this->getRequest()->getParams();
			$params['grant_type'] = 'password';
			$baseEndPoint = 'tinkerapi/oauth2/login';
			$restData = $this->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->returnJson($restData);
		}

		public function customerAction(){
			$params = $this->getRequest()->getParams();
			$baseEndPoint = 'tinkerapi/oauth2/customer';
			$restData = $this->curl(Mage::getBaseUrl() . $baseEndPoint,$params,'POST');
			$this->returnJson($restData);
		}


		private function curl($path,$params = null,$method = 'GET') {

		    $ch = curl_init();

		    if ($method == 'POST'){
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

			}
			else if ($method == 'GET' && $params != null){
				$path .= '?' . http_build_query($params);	
			}

			curl_setopt($ch, CURLOPT_URL,$path);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json','Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_FAILONERROR,1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);			
			$returnValue = curl_exec($ch);          
			curl_close($ch);
			return $returnValue;
		}

		private function returnJson($data,$status = true,$message = null){
			header('Content-type: application/json');
			echo json_encode(array('data'=>$data,'status'=>$status,'message'=>$message));
		}
		
	}
?>