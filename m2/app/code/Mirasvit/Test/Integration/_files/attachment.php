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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



require 'testsuite/Magento/Store/_files/core_fixturestore.php';

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\App\ResourceConnection $installer */
$installer = $objectManager->create('Magento\Framework\App\ResourceConnection');
$installer->getConnection()->query(
    'ALTER TABLE '.$installer->getTableName('mst_rma_attachment').' AUTO_INCREMENT = 1;'
);


/** @var \Mirasvit\Rma\Model\Attachment $attachment */
$attachment = $objectManager->create('Mirasvit\Rma\Model\Attachment');
$attachment->setName('file.txt')
    ->setUid('cccccccccccccccccccccccc')
    ->setType('APPLICATION/OCTET-STREAM')
    ->setBody('txt file body')
    ->setSize(strlen('txt file body'))
    ->save();
