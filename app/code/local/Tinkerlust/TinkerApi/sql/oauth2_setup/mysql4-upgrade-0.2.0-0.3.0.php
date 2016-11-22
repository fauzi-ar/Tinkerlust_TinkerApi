<?php
$installer = $this;
$installer->startSetup();

//install oauth2 refersh token
$installer->run("
	DROP TABLE IF EXISTS tinkerlust_oauth2_refreshtokens;
	CREATE TABLE oauth_refresh_tokens (refresh_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL, scope VARCHAR(2000), CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token));
");

$installer->endSetup();
?>