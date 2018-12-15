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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_Rma_Save_User extends Mirasvit_Rma_Helper_Rma_Save_AbstractSave
{
    /**
     * @var Mage_Admin_Model_User
     */
    protected $user;

    /**
     * @var Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * Save function for backend.
     *
     * For arrays description check parent class.
     *
     * @param Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor
     * @param Mage_Admin_Model_User $user
     *
     * @return Mirasvit_Rma_Model_Rma
     *
     * @throws Exception
     */
    public function createOrUpdateRmaUser(Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor, $user)
    {
        $this->user = $user;
        $this->dataProcessor = $dataProcessor;
        return $this->createOrUpdateRma($dataProcessor);
    }

    /**
     * {@inheritdoc}
     */
    protected function setCustomerData($rma) 
    {
        $data = $this->dataProcessor->getRmaData();
        if (!$data['customer_id'] && $this->dataProcessor->getNewCustomerData()) {
            $customer = Mage::helper('rma/rma_save_customerFactory')->loadOrCreate(
                $this->dataProcessor->getNewCustomerData()
            );
            $rma->addData($this->dataProcessor->getNewCustomerData());
            $rma->setCustomerId($customer->getId());
        }
    }


    /**
     * {@inheritdoc}
     */
    protected function setUserData($rma) 
    {
        if (!$rma->getUserId()) {
            $rma->setUserId($this->user->getId());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function addComment($data, $rma)
    {
        if ((isset($data['reply']) && trim($data['reply']) != '')
            || Mage::helper('mstcore/attachment')->hasAttachments()) {
            $isNotify = $isVisible = true;
            if ($data['reply_type'] == 'internal') {
                $isNotify = $isVisible = false;
            }
            $user = Mage::getSingleton('admin/session')->getUser();
            $rma->addComment(trim($data['reply']), true, false, $user, $isNotify, $isVisible);
        }
    }
}