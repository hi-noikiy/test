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

use Magento\Framework\Controller\ResultFactory;
/**
 * Action Edit
 */
class CancelPayment extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('payment_id');
        $payment = $this->_paymentFactory->create()->load($id);
        if (($payment->getStatus() <= 2))
            try {
                $payment->setStatus(4)->save();
                $this->messageManager->addSuccess(__('Withdrawal was successfully canceled!'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('index');
    }



}