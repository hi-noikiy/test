<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table nightly_remove(id int not null auto_increment, sku varchar(100), image longtext,primary key(id));

		
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 