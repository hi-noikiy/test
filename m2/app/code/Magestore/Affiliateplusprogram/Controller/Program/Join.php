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

class Join extends \Magestore\Affiliateplusprogram\Controller\AbstractAction
{
    /**
     * Join to a program
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute(){
        if (!$this->_helper->isPluginEnabled()) {
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->_accountHelper->accountNotLogin()){
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($id = $this->getRequest()->getParam('id')) {
            $programAccount = $this->_programAccountFactory->create()
                ->getCollection()
                ->addFieldToFilter('program_id', $id)
                ->addFieldToFilter('account_id', $this->_accountHelper->getAccount()->getId())
                ->getFirstItem();
            if ($programAccount && $programAccount->getId()) {
                $this->messageManager->addError(__('Program joined already!'));
            }else {
                try {
                    $now = new \DateTime();
                    $programAccount = $this->_programAccountFactory->create()
                        ->setAccountId($this->_accountHelper->getAccount()->getId())
                        ->setProgramId($id)
                        ->setJoined($now)
                        ->save();
                    $program = $this->_programFactory->create()
                        ->load($id);
                    $program->setNumAccount($program->getNumAccount() + 1)->save();
                    // Update joined program
                    $this->_programJoinedFactory->create()
                        ->insertJoined($program->getId(), $programAccount->getAccountId());
                    $this->messageManager->addSuccess(__('You have joined program "%1" successfully!', $program->getName()));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        } else{
            $this->messageManager->addError(__('Program not found!'));
        }
        return $this->_redirect('*/*/index');
    }
}