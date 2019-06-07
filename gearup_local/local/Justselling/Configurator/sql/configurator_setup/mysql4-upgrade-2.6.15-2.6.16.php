<?php
/**
 * justselling Germany Ltd. EULA
* http://www.justselling.de/
* Read the license at http://www.justselling.de/lizenz
*
* Do not edit or add to this file, please refer to http://www.justselling.de for more information.
*
* @category    justselling
* @package     justselling_configurator
* @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
* @license     http://www.justselling.de/lizenz
*/

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('configurator/upload')} ADD INDEX `IDX_DELETEOLDENTRIES` USING BTREE (order_id, created_at),
		ADD INDEX `IDX_UPLOAD_SESSIONID_QUOTEID` USING BTREE (quote_id, session_id),
		ADD INDEX `IDX_UPLOAD_QUOTEID` USING BTREE (quote_id),
		ADD INDEX `IDX_UPLOAD_OPTIONID_ORDERID_JSTEMPLATEID` USING BTREE (order_id, option_id, js_template_id);


");
$installer->endSetup();