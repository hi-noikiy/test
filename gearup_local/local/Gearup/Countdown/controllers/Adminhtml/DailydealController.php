<?php
class Gearup_Countdown_Adminhtml_DailydealController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    } 
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/gearup_countdown_dailydeal')
            ->_title($this->__('Sales'))->_title($this->__('Daily Deal'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Daily Deal'), $this->__('Daily Deal'));
         
        return $this;
    }
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/gearup_countdown_dailydeal');
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('gearup_countdown/adminhtml_dailydeal_grid')->toHtml()
        );
    }
    
    public function deleteAction()
    {
        if($this->getRequest()->getParam('id') > 0){
            try{
                $Model = Mage::getModel('countdown/countdown');
                $Model->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess('successfully deleted');
                $this->_redirect('*/*/');
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }
        }
        $this->_redirect('*/*/');
    }
}
