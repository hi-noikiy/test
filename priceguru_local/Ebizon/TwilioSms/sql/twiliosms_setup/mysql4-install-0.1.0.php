<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `twilio_sms` (
`id` int(11) NOT NULL,
  `accounts_id` varchar(50) NOT NULL DEFAULT 'ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
  `auth_token` varchar(50) NOT NULL DEFAULT 'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',
  `from_number` varchar(25) NOT NULL DEFAULT '+xxxxxxxxxxx',
  `order_sms` varchar(500) NOT NULL DEFAULT '',
  `cim_credit_sms` varchar(500) NOT NULL DEFAULT '',
  `cim_process_sms` varchar(500) NOT NULL DEFAULT '',
  `order_complete_sms` varchar(500) NOT NULL DEFAULT '',
  `cim_complete_sms` varchar(500) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `twilio_sms`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `twilio_sms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 
