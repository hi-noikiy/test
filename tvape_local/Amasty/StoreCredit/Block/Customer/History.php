<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Customer_History extends Mage_Core_Block_Template
{
    protected $_transactions;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }

        $pager = $this->getLayout()->createBlock('page/html_pager', 'amstcred.customer.history.pager')
            ->setCollection($this->getTransactions());
        $this->setChild('pager', $pager);
        $this->getTransactions()->load();
        return $this;

    }

    /**
     * @return Amasty_StoreCredit_Model_Resource_BalanceHistory_Collection
     */
    public function getTransactions()
    {
        if (!$this->_transactions) {
            $website_id = Mage::app()->getStore()->getWebsiteId();
            $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();

            $this->_transactions =
                Mage::getModel('amstcred/balanceHistory')
                    ->getCollection()
                    ->addFieldToFilter('customer_id', $customer_id)
                    ->addFieldToFilter('website_id', $website_id)
                    ->addOrder('updated_at', 'DESC');
        }
        return $this->_transactions;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
