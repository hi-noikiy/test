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
namespace Magestore\Affiliateplus\Controller\Adminhtml\Account;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action Edit
 */
class Edit extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('account_id');

        $storeViewId = $this->getRequest()->getParam('store');
        $model = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');

        if ($id) {
            $model->setStoreViewId($storeViewId)->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/');
            }
            $customer = $this->_customerFactory->create()->load($model->getCustomerId());
            $model->setFirstname($customer->getFirstname())
                ->setLastname($customer->getLastname())
                ->setEmail($customer->getEmail());

        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_objectManager->get('Magento\Framework\Registry')->register('affiliate_account', $model);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if($id){
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Account "%1"', $model->getName()));
        }else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add New Account'));
        }
        $resultPage->setActiveMenu('Magestore_Affiliateplus::magestoreaffiliateplus');
        return $resultPage;
    }
}
