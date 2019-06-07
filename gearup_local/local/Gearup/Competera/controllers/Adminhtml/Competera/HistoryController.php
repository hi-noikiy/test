<?php
/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gearup_Competera_Adminhtml_Competera_HistoryController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('competera/history');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $historyId = $this->getRequest()->getParam('id');
        try {
            // Delete histyory record
            $historyModel = Mage::getModel('competera/competerahistory');
            $historyModel->load($historyId)->delete();

            //Delete changelog history
            $collection = Mage::getModel('competera/pricechangelog')->getCollection()
                            ->addFieldToFilter('history_id', $historyId);
            $collection->walk('delete');
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('competera')->__('Record deleted successfully.'));
            $this->_redirect('*/*/index');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }    

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('competera/history');
    }
}