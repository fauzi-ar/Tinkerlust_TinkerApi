<?php
$installer = $this;
$installer->startSetup();

//install oauth2 clients
$installer->run("
	DROP TABLE IF EXISTS tinkerlust_oauth2_clients;
	CREATE TABLE tinkerlust_oauth2_clients (client_id VARCHAR(80) NOT NULL, client_secret VARCHAR(80), redirect_uri VARCHAR(2000) NOT NULL, grant_types VARCHAR(80), scope VARCHAR(100), user_id VARCHAR(80));
");

$installer->endSetup();
?>