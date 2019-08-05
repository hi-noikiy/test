<?php

/**
 * Magestore.
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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Observer\AbtractObserver;

class AddFieldPaymentForm extends AbtractObserver implements ObserverInterface{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if (!$this->_helper->isAffiliateModuleEnabled()) {
            return $this;
        }
        $fieldset = $observer['fieldset'];
        $data = $observer->getEvent()->getForm()->getFormValues();
        if (isset($data['is_recurring']) && $data['is_recurring']) {
            $fieldset->addField('is_recurring', 'note', [
                'label' => __('Is Recurring Payment'),
                'text'  => '<strong>' . __('Yes') . '</strong>',
            ]);
        }
    }
}