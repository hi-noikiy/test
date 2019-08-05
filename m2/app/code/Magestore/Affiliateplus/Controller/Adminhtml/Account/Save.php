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
 * Action Save
 */
class Save extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $storeId = $this->getRequest()->getParam('store', 0);

        if ($data = $this->getRequest()->getPostValue()) {
            /** @var \Magestore\Affiliateplus\Model\Affiliateplus $model */
            $model = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
            $customer = $this->_customerFactory->create()->load($data['customer_id']);
            $email = isset($data['email']) ? $data['email'] : '';
            $id = $this->getRequest()->getParam('account_id');
            $beforeStatusIsDisabled = false;
            if ($id) {
                $address = $customer->getDefaultShippingAddress();

                if ($address && $address->getId())
                    $data['address_id'] = $address->getId();

                $beforeAccount = $this->_accountFactory->create()->load($id);
                $beforeStatusIsDisabled = ($beforeAccount->getStatus() == 2) ? true : false;
                $unapproved = ($beforeAccount->getApproved() == 2) ? true : false;


            }
            $model->setData($data)->setId($id);

            if (!$id && !$customer->getId()) {
                if (!$email || !strpos($email, '@')) {
                    $this->messageManager->addError(__('Invalid email address'));
                    $this->_getSession()->setFormData($data);
                    $resultRedirect->setPath('*/*/edit',
                        [
                            'id' => $id,
                            'store' => $storeId
                        ]
                    );
                    return;
                }

                $websiteId = null;
                if (isset($data['associate_website_id']) && $data['associate_website_id'])
                    $websiteId = $data['associate_website_id'];

                $customer = $this->_customerFactory->create()
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);

                if (!$customer || !$customer->getId()) {
                    try {
                        $websiteId = isset($data['associate_website_id']) ? $data['associate_website_id'] : null;
                        $customer->setEmail($email)
                            ->setWebsiteId($this->_storeManager->getWebsite($websiteId)->getId())
                            ->setGroupId($customer->getGroupId())
                            ->setFirstname($data['firstname'])
                            ->setLastname($data['lastname'])
                            ->setForceConfirmed(true);
                        $password = $data['password'];
                        if (!$password)
                            $password = '123456';
                        $customer->setPassword($password);
                        $customer->save();
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        $this->_getSession()->setFormData($data);
                        $this->_redirect('*/*/edit', ['id' => $id, 'store' => $storeId]);
                        return;
                    }
                } else {
                    $existedAccount = $this->_accountFactory->create()->loadByCustomerId($customer->getId());
                    if ($existedAccount->getId())
                        $id = $existedAccount->getId();
                    if ($data['password']) {
                        try {
                            $customer->setFirstname($data['firstname'])
                                ->setLastname($data['lastname']);
                            $customer->changePassword($data['password']);
                            $customer->sendPasswordReminderEmail();
                        } catch (\Exception $e) {

                        }
                    }
                }
            }

            try {
                $this->_eventManager->dispatch('affiliateplus_adminhtml_before_save_account', ['post_data' => $data, 'account' => $model]);
                $customer->setFirstname($data['firstname'])
                    ->setLastname($data['lastname']);
                if ($email && strpos($email, '@'))
                    $customer->setEmail($email);
                $customer->save();
//                $storeId = $storeId ? $storeId : $customer->getStoreId();

                $model->setId($id)
                    ->setStoreViewId($storeId)
                    ->setName($customer->getName())
                    ->setCustomerId($customer->getId())
                    ->setEmail($email);

                if (!$id) {
                    $now = new \DateTime();
                    $model->setIdentifyCode($model->generateIdentifyCode())
                        ->setCreatedTime($now)
                        ->setApproved(1)
                    ;
                }

                $model->save();

                if ($id) {
                    if ($model->isEnabled() && $beforeStatusIsDisabled && $unapproved) {
                        $model->sendMailToApprovedAccount();
                    }
                } else {
                    $model->sendMailToNewAccount($model->getIdentifyCode());
                }

                $this->_eventManager->dispatch('affiliateplus_adminhtml_after_save_account', ['post_data' => $data, 'account' => $model]);
                $this->messageManager->addSuccess(__('The record has been saved.'));
                $this->_getSession()->setFormData(false);

                if ($this->getRequest()->getParam('back') === 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'account_id' => $model->getId(),
                            '_current' => true,
                            'store' => $storeId
                        ]
                    );
                } elseif ($this->getRequest()->getParam('back') === 'new') {
                    return $resultRedirect->setPath(
                        '*/*/new',
                        ['_current' => true,'store' => $storeId]

                    );
                }

                return $resultRedirect->setPath('*/*/',['store' => $storeId]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the record.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath(
                '*/*/edit',
                ['account_id' => $this->getRequest()->getParam('account_id'),'store' => $storeId]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
