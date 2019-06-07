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
    DROP TABLE IF EXISTS {$this->getTable('configurator/vectorgraphics_file')};
    CREATE TABLE {$this->getTable('configurator/vectorgraphics_file')} (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL DEFAULT 0,
            `template_id` int(11) NOT NULL DEFAULT 0,
            `js_template_id` varchar(64) NOT NULL,
            `option_id` int(11) NOT NULL DEFAULT 0,
            `width` int(11) NOT NULL DEFAULT 0,
			`height` int(11) NOT NULL DEFAULT 0,
			`content` text,
            `body` text,
            `session_id` varchar(8) NOT NULL,
            `quote_id` int(11),
            `quote_item_id` int(11),
            `order_id` int(11),
            `order_item_id` int(11),
            `status` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
    );
");
$installer->endSetup();