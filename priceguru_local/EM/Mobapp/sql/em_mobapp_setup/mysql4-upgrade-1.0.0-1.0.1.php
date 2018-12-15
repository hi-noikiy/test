<?php
/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer  = $this;

	$pathFile = Mage::getBaseDir('var').DS.'[EM_Mobapp]update_1-0-1.txt';
	if(file_exists($pathFile)){
		echo 'Updating EM Mobapp version 1.0.1 , please come back in some minutes ...';
		exit;
	}
	file_put_contents($pathFile,'Updating EM Mega Menu version 1.0.1');

	Mage::getModel("mobapp/update")->version("1.0.1");
	unlink($pathFile);

$installer->endSetup(); 