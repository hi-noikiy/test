<?php
class EM_Onestepcheckout_IndexController extends Mage_Core_Controller_Front_Action
{
	public function reviewAction() {
		$this->loadLayout();
		Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
		echo $this->getLayout()->getBlock('checkout.onepage.review.info')->toHtml();
	}

	public function cimcheckoutAction()
    {
        $data = $this->getRequest()->getParams();
        Mage::getSingleton('core/session')->setCheckoutcredit($data);
        echo $this->getRequest()->getParam('product');
        return;
        //$this->_redirect('ajaxcart/index/cimcheckoutform');
    }

    public function savecimorderAction()
    {
        $data = $this->getRequest()->getPost();
        $productdata = Mage::getSingleton('core/session')->getCheckoutcredit();

        $result = array();
        if(!isset($data['cimtermcondition'])) {
            $result['error'] = true;
            $result['error_messages'] = "Please read and accept Terms and condition";
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        $buyInfo = array(
            'product' => $productdata['product'],
            'options' => $productdata['options'],
            'qty' => 1
        );

        $cimmodel = Mage::getModel('onestepcheckout/cimorder');
        $Id = $cimmodel->createOrder($buyInfo, $data);
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

	public function creditformAction() {
        if(!Mage::getSingleton('core/session')->getCheckoutcredit()) {
            $this->_redirect('checkout/cart');
            return;
        }
		$this->loadLayout();
        $this->renderLayout();
	}

    public function cimordersuccessAction() {
        $this->loadLayout();
        Mage::getSingleton('core/session')->unsetData('checkoutcredit');
        $this->renderLayout();
    }
}