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



class Mirasvit_Rma_Block_Rma_Guest_New extends Mirasvit_Rma_Block_Rma_Guest_Abstract
{
    /**
     * @return string
     */
    public function getStep1PostUrl()
    {
        return Mage::helper('rma/url')->getGuestRmaUrl();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * @return string
     */
    public function getOrderIncrementId()
    {
        return Mage::app()->getRequest()->getParam('order_increment_id');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return Mage::app()->getRequest()->getParam('email');
    }
}
