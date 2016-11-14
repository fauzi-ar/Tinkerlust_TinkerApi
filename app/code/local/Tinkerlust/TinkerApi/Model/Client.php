<?php 
	class Tinkerlust_TinkerApi_Model_Client 
		extends Mage_Core_Model_Abstract 
		implements 
			OAuth2_Storage_ClientCredentialsInterface,
			OAuth2_Storage_AccessTokenInterface {

		public $authorizationCodes;
		public $userCredentials;
		public $clientCredentials;
		public $refreshTokens;
		public $accessTokens;
		public $jwt;
		public $jti;
		public $supportedScopes;
		public $defaultScope;
		public $keys;
		
		public function checkClientCredentials($client_id, $client_secret = null){
			return true;
		}

		public function isPublicClient($client_id){
			return true;
		}

		public function getClientDetails($client_id){
			return 'lorem ipsum';
		}

		public function getClientScope($client_id){
			return 'lorem scope';
		}

		public function checkRestrictedGrantType($client_id, $grant_type){
			return true;
		}

		//Methods from Access Token Interface
		public function getAccessToken($access_token)
		{
		    return isset($this->accessTokens[$access_token]) ? $this->accessTokens[$access_token] : false;
		}

		public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null, $id_token = null)
		{
		    $this->accessTokens[$access_token] = compact('access_token', 'client_id', 'user_id', 'expires', 'scope', 'id_token');

		    return true;
		}


	}
?>