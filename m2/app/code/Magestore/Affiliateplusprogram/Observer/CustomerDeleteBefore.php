<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerDeleteBefore extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $customer = $observer->getEvent()->getCustomer();
        $affiliateAccount = $this->_accountFactory->create()
            ->loadByCustomer($customer);
        $collection = $this->_programAccountCollectionFactory->create()
            ->addFieldToFilter('account_id', $affiliateAccount->getId());
        foreach ($collection as $value) {
            $program = $this->_programFactory->create()
                ->load($value->getProgramId());
            $numAccount = $program->getNumAccount();
            try {
                $program->setNumAccount($numAccount - 1);
                $program->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return $this;
    }
}