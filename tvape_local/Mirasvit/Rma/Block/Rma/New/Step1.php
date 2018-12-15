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



class Mirasvit_Rma_Block_Rma_New_Step1 extends Mirasvit_Rma_Block_Rma_New
{
    /**
     * Returns next step post URL
     *
     * @return string
     */
    public function getStep1PostUrl()
    {
        return Mage::getUrl('rma/rma_new/step2');
    }

    /**
     * Returns available order list
     *
     * @return  Mage_Sales_Model_Order[]
     */
    public function getAllowedOrders()
    {
        return $this->getStrategy()->getAllowedOrders();
    }

    /**
     * Returns period, during which returns are available
     *
     * @return int
     */
    public function getReturnPeriod()
    {
        return $this->getConfig()->getPolicyReturnPeriod();
    }

    /**
     * Returns number of days, available for return
     *
     * @param int $orderId
     * @return int
     */
    public function getOrderAvailableDays($orderId)
    {
        return Mage::helper('rma/order')->getOrderAvailableDays($orderId);
    }
}
