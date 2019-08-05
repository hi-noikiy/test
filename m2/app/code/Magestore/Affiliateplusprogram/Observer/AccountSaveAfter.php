<?php
/**
 * Magestore
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

namespace Magestore\Affiliateplusprogram\Observer;

use Magento\Framework\Event\ObserverInterface;

class AccountSaveAfter extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $affiliateplusAccount = $observer->getEvent()->getAffiliateplusAccount();
        if ($affiliateplusAccount && $affiliateplusAccount->hasData('account_program')) {
            $this->_UpdateAccountToProgram($affiliateplusAccount);
        } elseif ($affiliateplusAccount ) {
            $this->_AssignNewAccountToProgram($affiliateplusAccount);
        }
        return $this;
    }

    /**
     * @param $affiliateplusAccount
     * @return $this
     */
    protected function _AssignNewAccountToProgram($affiliateplusAccount){

        $oldProgramCollection = $this->_programAccountCollectionFactory->create()
            ->addFieldToFilter('account_id', $affiliateplusAccount->getId());
        if ($oldProgramCollection->getSize()){
            return $this;
        }
        $now = new \DateTime();
        $newProgram = $this->_programAccountFactory->create()
            ->setAccountId($affiliateplusAccount->getId())
            ->setJoined($now);
        $autoJoinPrograms = $this->_programCollectionFactory->create()
            ->addFieldToFilter('autojoin', 1);
        $group = $this->_customerFactory->create()
            ->load($affiliateplusAccount->getCustomerId())
            ->getGroupId();
        $autoJoinPrograms->getSelect()
            ->where("scope = 0 OR (scope = 1 AND FIND_IN_SET($group,customer_groups) )");
        foreach ($autoJoinPrograms as $autoJoinProgram) {
            $check = 2;
            $autoJoinProgram->setNumAccount($autoJoinProgram->getNumAccount() + 1)->orgSave();
            $newProgram->setProgramId($autoJoinProgram->getId())
                ->setId(null)
                ->save();
        }
        $this->_programJoinedFactory->create()
            ->updateJoined(null, $affiliateplusAccount->getId());
    }

    /**
     * @param $affiliateplusAccount
     */
    protected function _UpdateAccountToProgram($affiliateplusAccount){
        $joinPrograms = array();
        $joinPrograms = $this->_uri->setQuery($affiliateplusAccount->getAccountProgram());
        $joinPrograms= $joinPrograms->getQueryAsArray();
//        parse_str($affiliateplusAccount->getAccountProgram(), $joinPrograms);
        $joinPrograms = array_keys($joinPrograms);
        $joinedProgram = array();
        $oldProgramCollection = $this->_programAccountCollectionFactory->create()
            ->addFieldToFilter('account_id', $affiliateplusAccount->getId());
        $program = $this->_programFactory->create();
        foreach ($oldProgramCollection as $oldProgram) {
            $joinedProgram[] = $oldProgram->getProgramId();
            if (in_array($oldProgram->getProgramId(), $joinPrograms)){
                continue;
            }
            $check = 2;
            $program->load($oldProgram->getProgramId())
                ->setNumAccount($program->getNumAccount() - 1)
                ->setId($oldProgram->getProgramId())
                ->orgSave();
            $oldProgram->delete();
        }
        $addPrograms = array_diff($joinPrograms, $joinedProgram);
        $now = new \DateTime();
        $newProgram = $this->_programAccountFactory->create()
            ->setAccountId($affiliateplusAccount->getId())
            ->setJoined($now);
        foreach ($addPrograms as $programId) {
            $check = 2;
            $program->load($programId)
                ->setNumAccount($program->getNumAccount() + 1)
                ->setId($programId)
                ->orgSave();
            $newProgram->setProgramId($programId)->setId(null)->save();
        }
        $this->_programJoinedFactory->create()
            ->updateJoined(null, $affiliateplusAccount->getId());
    }
}