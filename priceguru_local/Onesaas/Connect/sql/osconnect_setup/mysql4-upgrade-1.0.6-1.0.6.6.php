<?php
$installer = $this;
$installer->startSetup();
$installer->run("
INSERT INTO {$this->getTable('osconnectkeys')} VALUES (null,'https://secure.onesaas.com/signin/start', NOW());
");
$installer->endSetup();
?>
