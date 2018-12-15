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



class Mirasvit_Rma_Block_Rma_New_Step2 extends Mirasvit_Rma_Block_Rma_New
{
    /**
     * @return string
     */
    public function getStep2PostUrl()
    {
        return Mage::getUrl('rma/rma_new/submit');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string                 $id
     *
     * @return Mage_Sales_Model_Order_Item|false
     */
    public function getOfflineOrderItemById($order, $id)
    {
        foreach ($order->getItems() as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }

        return false;
    }

    /**
     * @return Mirasvit_Rma_Model_Reason[]|Mirasvit_Rma_Model_Resource_Reason_Collection
     */
    public function getReasonCollection()
    {
        return Mage::getModel('rma/reason')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setStoreId($this->getStoreId())
            ->setOrder('sort_order', 'asc');
    }

    /**
     * @return Mirasvit_Rma_Model_Resolution[]|Mirasvit_Rma_Model_Resource_Resolution_Collection
     */
    public function getResolutionCollection()
    {
        return Mage::getModel('rma/resolution')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    /**
     * @return Mirasvit_Rma_Model_Condition[]|Mirasvit_Rma_Model_Resource_Condition_Collection
     */
    public function getConditionCollection()
    {
        return Mage::getModel('rma/condition')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    /**
     * @return Mirasvit_Rma_Model_Field[]|Mirasvit_Rma_Model_Resource_Field_Collection
     */
    public function getCustomFields()
    {
        $collection = Mage::helper('rma/field')->getVisibleCustomerCollection('initial', true);

        return $collection;
    }

    /**
     * @return string
     */
    public function getSubmitButtonName()
    {
        if ($this->getConfig()->getGeneralIsAdditionalStepAllowed()) {
            return $this->__('Next Step');
        } else {
            return $this->__('Submit Request');
        }
    }

    /**
     * @return bool
     */
    public function getPolicyIsActive()
    {
        return $this->getConfig()->getPolicyIsActive();
    }

    /**
     * @var Mage_Core_Model_Abstract
     */
    protected $_pblock;

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getPolicyBlock()
    {
        if (!$this->_pblock) {
            $this->_pblock = Mage::getModel('cms/block')->load($this->getConfig()->getPolicyPolicyBlock());
        }

        return $this->_pblock;
    }

    /**
     * @return string
     */
    public function getPolicyTitle()
    {
        return $this->getPolicyBlock()->getTitle();
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function getPolicyContent()
    {
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();

        return $processor->filter($this->getPolicyBlock()->getContent());
    }

    /**
     * @return int
     */
    public function getReturnPeriod()
    {
        return $this->getConfig()->getPolicyReturnPeriod();
    }

    /**
     * @return bool
     */
    public function getAllowGift()
    {
        return !$this->getCustomer()->getId() && $this->getConfig()->getGeneralIsGiftActive();
    }
}
