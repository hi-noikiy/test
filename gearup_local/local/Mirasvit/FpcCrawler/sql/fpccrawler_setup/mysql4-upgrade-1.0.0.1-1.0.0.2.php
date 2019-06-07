<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



$installer = $this;

$installer->startSetup();
$helper = Mage::helper('fpccrawler/migration');

$tableCrawler = $installer->getTable('fpccrawler/crawler_url');
$helper->addIndex($installer, $tableCrawler, 'crawler_cache_id', 'cache_id');
$helper->addIndex($installer, $tableCrawler, 'crawler_status', 'status');
$helper->addIndex($installer, $tableCrawler, 'crawler_sort_by_page_type', 'sort_by_page_type');
$helper->addIndex($installer, $tableCrawler, 'crawler_sort_by_page_type', 'sort_by_product_attribute');
$helper->addIndex($installer, $tableCrawler, 'crawler_customer_group_id', 'customer_group_id');

$tableCrawlerlogged = $installer->getTable('fpccrawler/crawlerlogged_url');
$helper->addIndex($installer, $tableCrawlerlogged, 'crawlerlogged_cache_id', 'cache_id');
$helper->addIndex($installer, $tableCrawlerlogged, 'crawlerlogged_status', 'status');
$helper->addIndex($installer, $tableCrawlerlogged, 'crawlerlogged_sort_by_page_type', 'sort_by_page_type');
$helper->addIndex($installer, $tableCrawlerlogged, 'crawlerlogged_sort_by_product_attribute', 'sort_by_product_attribute');

$installer->endSetup();
