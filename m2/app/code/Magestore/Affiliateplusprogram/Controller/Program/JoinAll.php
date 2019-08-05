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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Controller\Program;

class JoinAll extends \Magestore\Affiliateplusprogram\Controller\AbstractAction
{
    /**
     * Join to all program
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute(){
        if (!$this->_helper->isPluginEnabled()) {
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->_accountHelper->accountNotLogin()){
            return $this->_redirect('affiliateplus/account/login');
        }
        $programIds = $this->getRequest()->getParam('program_ids');
        if (is_array($programIds) && count($programIds)) {
            $now = new \DateTime();
            $programModel = $this->_programAccountFactory->create()
                ->setAccountId($this->_accountHelper->getAccount()->getId())
                ->setJoined($now);
            foreach ($programIds as $id) {
                $programAccount = $this->_programAccountFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('program_id', $id)
                    ->addFieldToFilter('account_id', $this->_accountHelper->getAccount()->getId())
                    ->getFirstItem();
                if ($programAccount && $programAccount->getId()) {
                    continue;
                } else {
                    try {
                        $programModel->setProgramId($id)
                            ->setId(null)
                            ->save();
                        $program = $this->_programFactory->create()
                            ->load($id);
                        $program->setNumAccount($program->getNumAccount() + 1)->save();
                        $this->messageManager->addSuccess(__('You have joined program "%1" successfully!', $program->getName()));
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        return $this->_redirect('*/*/all');
                    }
                }
            }
            // Update joined program for current account
            try {
                $this->_programJoinedFactory->create()
                    ->updateJoined(null, $programModel->getAccountId());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->_redirect('*/*/all');
            }
        }else {
            $this->messageManager->addError(__('Please select a program to join!'));
            return $this->_redirect('*/*/all');
        }
        return $this->_redirect('*/*/index');
    }
}