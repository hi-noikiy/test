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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Test_Helper_RmaTest extends Mirasvit_Rma_Test_Helper_RmaPHPUnit
{
    /**
     * @test
     */
    public function createOrUpdateRmaFromPostTest()
    {
        $data = include $this->fixtureFolder.'admin/order_data_new.php';
        $data = $this->applyCustomerToData($data);

        $this->helper->setData($data);
        $this->helper->validate($data);
        $createdRma = Mage::helper('rma/rma_save_user')->createOrUpdateRmaUser($this->helper, $this->user);

        $rma = Mage::getModel('rma/rma')->load($createdRma->getId());

        $this->assertNotEquals(null, $rma->getId());
        $this->assertEquals($rma->getId(), $createdRma->getId());
        $this->assertEquals(2, $rma->getItemsCollection()->count());
    }

    /**
     * @test
     * @loadFixture data
     */
    public function createRmaFromPostTest()
    {
        $order = Mage::getModel('sales/order')->getCollection()->getFirstItem();
        $orderItem = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $order->getId())->getFirstItem();
        $data = $this->getOrderFixture($order->getId(), $orderItem->getId());

        $dataProcessor = Mage::helper('rma/rma_save_postDataProcessor');
        $dataProcessor->setData($data);
        $createdRma = Mage::helper('rma/rma_save_customer')->createOrUpdateRmaCustomer(
            $dataProcessor,
            $this->customer
        );

        $rma = Mage::getModel('rma/rma')->load($createdRma->getId());

        $this->assertNotEquals(null, $rma->getId());
        $this->assertEquals($rma->getId(), $createdRma->getId());
        $this->assertEquals(1, $rma->getItemsCollection()->count());
    }

    /**
     * @param int $orderId
     * @param int $itemId
     *
     * @return array
     */
    protected function getOrderFixture($orderId, $itemId)
    {
        return array(
            'form_key' => 'z2ADiowHt92YdmHn',
            'form_uid' => '93',
            'order_id' => array(
                0 => $orderId,
            ),
            'items' => array(
                $orderId => array(
                    $itemId => array(
                        'is_return' => '1',
                        'qty_requested' => '1',
                        'order_item_id' => $itemId,
                        'name' => '',
                        'reason_id' => '3',
                        'condition_id' => '3',
                    ),
                ),
            ),
            'offline_orders' => array(),
            'comment' => '',
        );
    }
}
