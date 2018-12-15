<?php

$query = "

ALTER TABLE {$this->getTable('belitsoft_survey/answer')}
	ADD `order_id` int(10) unsigned NOT NULL AFTER `answer_id`;
";

$installer = $this;
$installer->startSetup();
$installer->run($query);
$installer->endSetup();