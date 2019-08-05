<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Signup;

use CollinsHarper\CanadaPost\Helper\Data as cpConfig;

class Back extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        // TODO verify this acl
        $this->chLogger->info(__METHOD__ . __LINE__);
        return $this->_authorization->isAllowed('CollinsHarper_CanadaPost::config');
    }

    /**
     * manifet grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->chLogger->info(__METHOD__ . __LINE__);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultRedirect = $this->resultRedirectFactory->create();

        $helper = $this->objectFactory->create('\CollinsHarper\CanadaPost\Helper\Data');
        $registration = $this->objectFactory->create('\CollinsHarper\CanadaPost\Helper\Rest\Registration');
        $token = (string) $this->getRequest()->getParam('token-id');

        $status = (string) $this->getRequest()->getParam('registration-status');

        if ($status == 'SUCCESS' && $token ==  $this->_objectManager->get('Magento\Backend\Model\Session')->getCanadapostRegistrationToken()) {

            $customerData = $registration->getRegistrationData($token);

            //$cfg->saveConfig('carriers/chcanpost2module/api_customer_number', (string) $customerData->{'customer-number'});

            $helper->saveConfigData(cpConfig::XML_PATH_CUSTOMER_NUMBER, (string) $customerData->{'customer-number'});

            $helper->saveConfigData(cpConfig::XML_PATH_CONTRACT_ID, (string) $customerData->{'contract-number'});
            $helper->saveConfigData(cpConfig::XML_PATH_API_LOGIN, (string) $customerData->{'merchant-username'});
            $helper->saveConfigData(cpConfig::XML_PATH_API_PASSWORD, (string) $customerData->{'merchant-password'});
            $helper->saveConfigData(cpConfig::XML_PATH_HAS_DEFAULT_CC, (string) $customerData->{'has-default-credit-card'});

            $this->messageManager->addSuccess(__('Your Canada Post Account Information has been updated.'));

        } else {

            $this->messageManager->addError(__('Your Canada Post Account signup was canceled or did not finish. Please contact them by phone to finish setup.'));

        }

        return $resultRedirect->setPath('adminhtml/system_config/edit', ['section' => 'carriers']);

    }


}
