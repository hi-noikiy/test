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



class Mirasvit_Rma_Adminhtml_Rma_Return_AddressController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return Mirasvit_Rma_Adminhtml_Rma_Return_AddressController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('rma');

        return $this;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Return Address'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('rma/adminhtml_return_address'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function addAction()
    {
        $this->_title($this->__('New Return Address'));

        $this->_initAddress();

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Return Address Manager'),
            Mage::helper('adminhtml')->__('Return Address'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Return Address'),
            Mage::helper('adminhtml')->__('Add Return Address'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_return_address_edit'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $address = $this->_initAddress();

        if ($address->getId()) {
            $this->_title($this->__("Edit Return Address '%s'", $address->getTitle()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Return Address'),
                Mage::helper('adminhtml')->__('Return Address'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Return Address'),
                Mage::helper('adminhtml')->__('Edit Return Address'));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_return_address_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('The Return Address does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $address = $this->_initAddress();
            $address->addData($data);

            try {
                $address->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Return Address was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $address->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                if ($id = $this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/add');
                }

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('adminhtml')->__('Unable to find Return Address to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $address = Mage::getModel('rma/return_address');

                $address->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Return Address was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id'), ));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function massChangeAction()
    {
        $ids = $this->getRequest()->getParam('address_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Return Address(es)'));
        } else {
            try {
                $isActive = $this->getRequest()->getParam('is_active');
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Return_Address $address */
                    $address = Mage::getModel('rma/return_address')->load($id);
                    $address->setIsActive($isActive);
                    $address->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @return void
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('address_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Return Address(es)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Return_Address $address */
                    $address = Mage::getModel('rma/return_address')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $address->delete();
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

    /**
     * @return Mirasvit_Rma_Model_Return_Address
     */
    public function _initAddress()
    {
        $address = Mage::getModel('rma/return_address');
        if ($this->getRequest()->getParam('id')) {
            $address->load($this->getRequest()->getParam('id'));
        }

        Mage::register('current_return_address', $address);

        return $address;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rma/dictionary/return_address');
    }

    /************************/
}
