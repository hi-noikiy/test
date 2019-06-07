<?php
class Justselling_Configurator_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        Mage::register('current_customer_id', (int) $this->getRequest()->getParam('customer_id'));
        $this->getResponse()->setBody($this->getLayout()->createBlock('configurator/adminhtml_customer_edit_tab')->toHtml());
    }
}