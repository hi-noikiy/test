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



require 'testsuite/Magento/Customer/_files/customer.php';
require 'testsuite/Magento/Store/_files/core_fixturestore.php';
require __DIR__ . '/order.php';
require 'testsuite/Magento/User/_files/dummy_user.php';

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\App\ResourceConnection $installer */
$installer = $objectManager->create('Magento\Framework\App\ResourceConnection');
$installer->getConnection()->delete('mst_rma_rma');
$installer->getConnection()->query(
    'ALTER TABLE '.$installer->getTableName('mst_rma_rma').' AUTO_INCREMENT = 1;'
);


$installer->getConnection()->delete('mst_rma_message');
$installer->getConnection()->query(
    'ALTER TABLE '.$installer->getTableName('mst_rma_message').' AUTO_INCREMENT = 1;'
);

/** @var Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->loadByIncrementId('100000001');

$currentStore = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

/* @var $rma \Mirasvit\Rma\Model\Rma */
$rma = $objectManager->create('Mirasvit\Rma\Model\Rma');
$rma
    ->setStoreId($currentStore)
    ->setIncrementId('100100001')
    ->setGuestId('87cb09bf721c860e591568ec93239497')
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setEmail('customer@example.com')
    ->setCustomerId(1)
    ->setUserId(1)
    ->setOrderId($order->getId())
    ->setCreatedAt('2015-07-03 00:00:00')
    ->setUpdatedAt('2015-07-05 00:00:00')
    ->setInTest(true)
    ->save();

/** @var \Mirasvit\Rma\Model\Message $message */
$message = $objectManager->create('Mirasvit\Rma\Model\Message');
$message->setRmaId($rma->getId())
    ->setCustomerId(1)
    ->setText('Some default message', 0)
    ->save();
