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



abstract class Mirasvit_Rma_Helper_Rma_Create_AbstractNewControllerStrategy
{
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @param array $data
     * @return Mirasvit_Rma_Model_Rma
     */
    abstract public function createOrUpdateRma($data);

    /**
     * @param array $data
     * @return Mirasvit_Rma_Model_Rma
     */
    abstract public function initRma($data);

    /**
     * @param object $layout
     * @return void
     */
    abstract public function setLayout($layout);

    /**
     * @return Mage_Sales_Model_Order[]
     */
    abstract public function getAllowedOrders();

    /**
     * @return void
     */
    abstract public function preDispatch();
}