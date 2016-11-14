<?php 
	
	class Tinkerlust_TinkerApi_RestController extends Mage_Core_Controller_Front_Action
	{
		protected function _construct()
	  	{
	  	}
	  	public function indexAction($param1 = null,$param2=null)
	  	{
	  		echo 'kok bisa';
	  		var_dump();
	  		echo '<br/>';
	  		echo $param2;
	  	}
		
		public function productsAction()
		{
			$baseEndPoint = 'api/rest/products/';
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

			//with options
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
			}
			header('Content-type: application/json');
			echo $restData; 
/*
			$clients = array('TestClient' => array('client_secret' => 'TestSecret'));
			$storage = new OAuth2_Storage_Memory(array('client_credentials' => $clients));
			$storage = Mage::getModel('tinkerapi/client');
			$server = new OAuth2_Server($storage);

			$grantType = new OAuth2_GrantType_ClientCredentials($storage);
			$server->addGrantType($grantType);

			$server->handleTokenRequest(OAuth2_Request::createFromGlobals())->send();*/
		}

		private function curl($path) {
		    $ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,$path);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			curl_setopt($ch, CURLOPT_FAILONERROR,1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$retValue = curl_exec($ch);          
			curl_close($ch);
			return $retValue;
		}
		
	}
?>