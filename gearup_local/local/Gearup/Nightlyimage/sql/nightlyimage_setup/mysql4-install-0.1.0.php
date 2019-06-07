<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table nightly(id int not null auto_increment, sku varchar(100), image varchar(255),primary key(id));

		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 