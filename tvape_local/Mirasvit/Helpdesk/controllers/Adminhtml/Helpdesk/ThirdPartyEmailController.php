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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Adminhtml_Helpdesk_ThirdPartyEmailController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('helpdesk');

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Third party Email'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('helpdesk/adminhtml_thirdPartyEmail'));
        $this->renderLayout();
    }

    public function addAction()
    {
        $this->_title($this->__('New Third party Email'));

        $this->_initEmail();

        $this->_initAction();
        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Third party Email Manager'),
            Mage::helper('adminhtml')->__('Third party Email Manager'), $this->getUrl('*/*/')
        );
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Third party Email '), Mage::helper('adminhtml')
            ->__('Add Third party Email'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('helpdesk/adminhtml_thirdPartyEmail_edit'));
        $this->renderLayout();
    }

    public function editAction()
    {
        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $email */
        $email = $this->_initEmail();

        if ($email->getId()) {
            $this->_title($this->__("Edit Third party Email '%s'", $email->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Third party Emails'),
                    Mage::helper('adminhtml')->__('Third party Emails'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Third party Email '),
                    Mage::helper('adminhtml')->__('Edit Third party Email '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('helpdesk/adminhtml_thirdPartyEmail_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')
                ->__('The Third party Email does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $email */
            $email = $this->_initEmail();
            $email->addData($data);

            try {
                $email->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')
                    ->__('Third party Email was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $email->getId(), 'store' => $email->getStoreId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')
            ->__('Unable to find Third party Email to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('helpdesk/thirdPartyEmail')->load($id)->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Third party Email was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('third_party_email_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')
                ->__('Please select Third party Email(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $email */
                    $email = Mage::getModel('helpdesk/thirdPartyEmail')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $email->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function _initEmail()
    {
        $email = Mage::getModel('helpdesk/thirdPartyEmail');
        if ($this->getRequest()->getParam('id')) {
            $email->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $email->setStoreId($storeId);
            }
        }

        Mage::register('current_third_party_email', $email);

        return $email;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdesk/dictionary/third_party_email');
    }

    /************************/
}
