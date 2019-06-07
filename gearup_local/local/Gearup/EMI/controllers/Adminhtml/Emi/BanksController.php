<?php
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Bank admin controller
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Adminhtml_Emi_BanksController extends Gearup_EMI_Controller_Adminhtml_EMI
{
    /**
     * init the bank
     *
     * @access protected
     * @return Gearup_EMI_Model_Banks
     */
    protected function _initBanks()
    {
        $banksId  = (int) $this->getRequest()->getParam('id');
        $banks    = Mage::getModel('gearup_emi/banks');
        if ($banksId) {
            $banks->load($banksId);
        }
        Mage::register('current_banks', $banks);
        return $banks;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('gearup_emi')->__('EMI Manager'))
             ->_title(Mage::helper('gearup_emi')->__('Banks Manager '));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit bank - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $banksId    = $this->getRequest()->getParam('id');
        $banks      = $this->_initBanks();
        if ($banksId && !$banks->getId()) {
            $this->_getSession()->addError(
                Mage::helper('gearup_emi')->__('This bank no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getBanksData(true);
        if (!empty($data)) {
            $banks->setData($data);
        }
        Mage::register('banks_data', $banks);
        $this->loadLayout();
        $this->_title(Mage::helper('gearup_emi')->__('EMI Manager'))
             ->_title(Mage::helper('gearup_emi')->__('Banks Manager '));
        if ($banks->getId()) {
            $this->_title($banks->getTitle());
        } else {
            $this->_title(Mage::helper('gearup_emi')->__('Add bank'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new bank action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save bank - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('banks')) {
            try {
                $banks = $this->_initBanks();
                $banks->addData($data);
                $imageName = $this->_uploadAndGetName(
                    'image',
                    Mage::helper('gearup_emi/banks_image')->getImageBaseDir(),
                    $data
                );
                $banks->setData('options', serialize($data['options']));
                $banks->setData('image', $imageName);
                $banks->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('gearup_emi')->__('Bank was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $banks->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                if (isset($data['image']['value'])) {
                    $data['image'] = $data['image']['value'];
                }
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setBanksData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                if (isset($data['image']['value'])) {
                    $data['image'] = $data['image']['value'];
                }
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('gearup_emi')->__('There was a problem saving the bank.')
                );
                Mage::getSingleton('adminhtml/session')->setBanksData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('gearup_emi')->__('Unable to find bank to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete bank - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $banks = Mage::getModel('gearup_emi/banks');
                $banks->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('gearup_emi')->__('Bank was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('gearup_emi')->__('There was an error deleting bank.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('gearup_emi')->__('Could not find bank to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete bank - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $banksIds = $this->getRequest()->getParam('banks');
        if (!is_array($banksIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_emi')->__('Please select banks manager  to delete.')
            );
        } else {
            try {
                foreach ($banksIds as $banksId) {
                    $banks = Mage::getModel('gearup_emi/banks');
                    $banks->setId($banksId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('gearup_emi')->__('Total of %d banks manager  were successfully deleted.', count($banksIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('gearup_emi')->__('There was an error deleting banks manager .')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massStatusAction()
    {
        $banksIds = $this->getRequest()->getParam('banks');
        if (!is_array($banksIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('gearup_emi')->__('Please select banks manager .')
            );
        } else {
            try {
                foreach ($banksIds as $banksId) {
                $banks = Mage::getSingleton('gearup_emi/banks')->load($banksId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d banks manager  were successfully updated.', count($banksIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('gearup_emi')->__('There was an error updating banks manager .')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportCsvAction()
    {
        $fileName   = 'banks.csv';
        $content    = $this->getLayout()->createBlock('gearup_emi/adminhtml_banks_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportExcelAction()
    {
        $fileName   = 'banks.xls';
        $content    = $this->getLayout()->createBlock('gearup_emi/adminhtml_banks_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportXmlAction()
    {
        $fileName   = 'banks.xml';
        $content    = $this->getLayout()->createBlock('gearup_emi/adminhtml_banks_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Ultimate Module Creator
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('gearup_emi/banks');
    }
}
