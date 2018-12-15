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



class Mirasvit_Rma_Rma_NewController extends Mage_Core_Controller_Front_Action
{

    /**
     * @var Mirasvit_Rma_Helper_Rma_Create_AbstractNewControllerStrategy
     */
    protected $strategy;

    /**
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if ($this->_getSession()->isLoggedIn()) {
            $this->strategy = Mage::helper('rma/rma_create_customer_newControllerStrategy');
        } elseif ($this->_getSession()->getRmaGuestOrderId() || $this->_getSession()->getRmaGuestEmail()) {
            $this->strategy = Mage::helper('rma/rma_create_guest_newControllerStrategy');
        } else {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
        $this->strategy->preDispatch();
        Mage::register('newControllerStrategy', $this->strategy);
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Step 1.
     * @return void
     */
    public function step1Action()
    {
        $this->render();
    }

    /**
     * Step 2.
     * @return void
     */
    public function step2Action()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_redirect('*/*/step1');
            return;
        }

        $data = $this->getRequest()->getPost();
        if (isset($data['firstname'], $data['lastname'], $data['offline_orders'])) {
            $this->_getSession()->setRmaGuestOfflineData(array(
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $this->_getSession()->getRmaGuestEmail(),
            ));
        }

        $this->render();
    }

    /**
     * Submit.
     * @return void
     */
    public function submitAction()
    {
        $session = $this->_getSession();

        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();
            return;
        }

        $formUid = $this->getRequest()->getParam('form_uid');
        if ($formUid == $session->getLastFormUid()) { //simple protection from double posting. #RMA-90
            $this->_redirectReferer();
            return;
        }
        $session->setLastFormUid($formUid);

        $data = $this->getRequest()->getParams();
        try {
            $rma = $this->strategy->createOrUpdateRma($data);

            if (Mage::getSingleton('rma/config')->getGeneralIsAdditionalStepAllowed()) {
                $this->_redirect('*/*/step3', array('id' => $rma->getId()));
            } else {
                $this->_redirect('*/*/success', array('id' => $rma->getId()));
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $session->setFormData($data);
            if ($this->getRequest()->getParam('id')) {
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } else {
                $this->_redirect('*/*/add', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            $session->addException($e, Mage::helper('rma')->__('An error occurred while saving RMA.'));
            $this->_redirect('/');
        }
    }

    /**
     * Step 3.
     * @return void
     */
    public function step3Action()
    {
        try {
            if (!$this->_initRma()) {
                $this->_redirect('*/*/step1');

                return;
            }
            $this->render();
        } catch (Mage_Core_Exception $e) {
            $session = $this->_getSession();
            $session->addError($e->getMessage());
            $this->_redirect('*/*/step1');
        }
    }

    /**
     * Success.
     * @return void
     */
    public function successAction()
    {
        try {
            if (!$this->_initRma()) {
                $this->_redirect('*/*/step1');
                return;
            }
            $this->render();
        } catch (Mage_Core_Exception $e) {
            $session = $this->_getSession();
            $session->addError($e->getMessage());
            $this->_redirect('*/*/step1');
        }
    }

    /**
     * @throws \Mage_Core_Exception
     * @return Mirasvit_Rma_Model_Rma
     */
    protected function _initRma()
    {
        return $this->strategy->initRma($this->getRequest()->getParams());
    }

    /**
     * Mage_Cms_Helper_Page->_renderPage().
     *
     * @return bool
     */
    private function render()
    {
        $this->strategy->setLayout($this->getLayout());

        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();
        $layoutUpdate = 'page_two_columns_right';
        $this->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        $this->generateLayoutXml()->generateLayoutBlocks();

        $messageBlock = $this->getLayout()->getMessagesBlock();

        $storageType = 'customer/session';
        $storage = Mage::getSingleton($storageType);
        if ($storage) {
            $messageBlock->addStorageType($storageType);
            $messageBlock->addMessages($storage->getMessages(true));
        }

        $this->renderLayout();

        return true;
    }
}
