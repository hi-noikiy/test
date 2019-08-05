<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Account\Withdrawal;

class Request extends \Amasty\Affiliate\Controller\Account\Withdrawal\AbstractWithdrawal
{
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $requestedAmount = $this->getRequest()->getParam('amount');
            if (!$this->validateWithdrawal($requestedAmount)) {
                return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
            }
            $this->withdrawal->create($requestedAmount);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
        }

        $this->messageManager->addSuccessMessage(__('Withdrawal was successfully created.'));

        return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
    }
}
