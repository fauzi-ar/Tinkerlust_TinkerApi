<?php
class Tinkerlust_TinkerApi_Model_Resource_Client extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('tinkerapi/client','id');
	}	
	
}
?>