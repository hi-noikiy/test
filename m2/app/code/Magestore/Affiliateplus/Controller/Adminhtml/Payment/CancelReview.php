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
namespace Magestore\Affiliateplus\Controller\Adminhtml\Payment;

/**
 * Action Edit
 */
class CancelReview extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {

        if ($data = $this->getRequest()->getPostValue()) {
            $this->getBackendSession()->setFormData($data);
            return $this->_redirect('*/*/edit', [
                'payment_id' => $this->getRequest()->getParam('payment_id'),
                'store' => $this->getRequest()->getParam('store'),
                'account_id' => isset($data['account_id']) ? $data['account_id'] : null,
                ]
            );
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return \Magento\Backend\Model\Session
     */
    public function getBackendSession()
    {
        return $this->_objectManager->create('Magento\Backend\Model\Session');
    }

}