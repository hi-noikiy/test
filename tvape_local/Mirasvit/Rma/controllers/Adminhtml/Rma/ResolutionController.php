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



class Mirasvit_Rma_Adminhtml_Rma_ResolutionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return Mirasvit_Rma_Adminhtml_Rma_ResolutionController
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
        $this->_title($this->__('Resolution'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('rma/adminhtml_resolution'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function addAction()
    {
        $this->_title($this->__('New Resolution'));

        $this->_initResolution();

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Resolution  Manager'),
                Mage::helper('adminhtml')->__('Resolution Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Resolution '),
            Mage::helper('adminhtml')->__('Add Resolution'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_resolution_edit'));
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $resolution = $this->_initResolution();

        if ($resolution->getId()) {
            $this->_title($this->__("Edit Resolution '%s'", $resolution->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Resolution'),
                    Mage::helper('adminhtml')->__('Resolution'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Resolution '),
                    Mage::helper('adminhtml')->__('Edit Resolution '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_resolution_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('The Resolution does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $resolution = $this->_initResolution();
            $resolution->addData($data);
            //format date to standart
            // $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            // Mage::helper('mstcore/date')->formatDateForSave($resolution, 'active_from', $format);
            // Mage::helper('mstcore/date')->formatDateForSave($resolution, 'active_to', $format);

            try {
                $resolution->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Resolution was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $resolution->getId(),
                        'store' => $resolution->getStoreId()));

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
            Mage::helper('adminhtml')->__('Unable to find Resolution to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $resolution = Mage::getModel('rma/resolution');

                $resolution->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Resolution was successfully deleted'));
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
        $ids = $this->getRequest()->getParam('resolution_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Resolution(s)'));
        } else {
            try {
                $isActive = $this->getRequest()->getParam('is_active');
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Resolution $resolution */
                    $resolution = Mage::getModel('rma/resolution')->load($id);
                    $resolution->setIsActive($isActive);
                    $resolution->save();
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
        $ids = $this->getRequest()->getParam('resolution_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select Resolution(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var Mirasvit_Rma_Model_Resolution $resolution */
                    $resolution = Mage::getModel('rma/resolution')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $resolution->delete();
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
     * @return Mirasvit_Rma_Model_Resolution
     */
    public function _initResolution()
    {
        $resolution = Mage::getModel('rma/resolution');
        if ($this->getRequest()->getParam('id')) {
            $resolution->load($this->getRequest()->getParam('id'));
            if ($storeId = (int) $this->getRequest()->getParam('store')) {
                $resolution->setStoreId($storeId);
            }
        }

        Mage::register('current_resolution', $resolution);

        return $resolution;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rma/dictionary/resolution');
    }

    /************************/
}
