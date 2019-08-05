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
 * @package   mirasvit/module-helpdesk
 * @version   1.1.59
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Gateway;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Mirasvit\Helpdesk\Controller\Adminhtml\Gateway
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $gateway = $this->_initGateway();

        if ($gateway->getId()) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Gateway '%1'", $gateway->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(
                __('Gateways'),
                __('Gateways'),
                $this->getUrl('*/*/')
            );
            $this->_addBreadcrumb(
                __('Edit Gateway '),
                __('Edit Gateway ')
            );

            $resultPage->getLayout()
                ->getBlock('head')
                ;

            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('The Gateway does not exist.'));
            $this->_redirect('*/*/');
        }
    }
}
