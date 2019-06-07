<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

$installer = $this;

if (!$installer->getConnection()->tableColumnExists($installer->getTable('cms_page'), 'mageworx_hreflang_identifier')) {
    $installer->getConnection()->addColumn($installer->getTable('cms_page'), 'mageworx_hreflang_identifier', "varchar(255) NOT NULL DEFAULT ''");
}

$installer->endSetup();

?>