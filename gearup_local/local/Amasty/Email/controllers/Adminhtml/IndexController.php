<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */    
class Amasty_Email_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function customerAction()
    {
        Mage::register('current_customer_id', (int) $this->getRequest()->getParam('customer_id'));
        $this->getResponse()->setBody($this->getLayout()->createBlock('amemail/adminhtml_customer_edit_tab')->toHtml());
    }
    
    public function orderAction()
    {
        Mage::register('current_order_id', (int) $this->getRequest()->getParam('order_id'));
        $this->getResponse()->setBody($this->getLayout()->createBlock('amemail/adminhtml_sales_order_view_tab')->toHtml());
    }
    
    public function indexAction() 
    {
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);

		$orders = $this->getRequest()->getParam('order_ids');
		$orderId = $this->getRequest()->getParam('order_id');
		if ($orderId){
            $orders = array($orderId);
		}
		
		$customers = $this->getRequest()->getParam('customer');
		$customerId = $this->getRequest()->getParam('customer_id');
		if ($customerId){
            $customers = array($customerId);
		}
		
		
		if (!$orders && !$customers && !$data){
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amemail')->__('Please select customers'));
		    return $this->_redirect('customer/index');
		}
		
		$template  = new Varien_Object();
		if (!empty($data)) {
			$template->setData($data);
		}
		else {
            $template->setTxt(Mage::getStoreConfig('amemail/general/txt'));
            $template->setSubj(Mage::getStoreConfig('amemail/general/subj'));
            
    		if ($orders){
                $template->setOrders(implode(',', $orders));
    		}
    		elseif ($customers){
                $template->setCustomers(implode(',', $customers));
    		}
		}
		
		Mage::register('amemail_template', $template);

		$this->loadLayout();
		
        $this->_addContent($this->getLayout()->createBlock('amemail/adminhtml_template_edit'));
        
		$this->renderLayout();
		
		return $this;
	}
	
	public function sendAction() 
	{
	    // todo check validity;
	    $template = new Varien_Object();
	    $template->setData($this->getRequest()->getParams());

	    $customersIds = $this->getRequest()->getParam('customers');
	    $ordersIds = $this->getRequest()->getParam('orders');
	    
	    if ($customersIds){
            $collection = Mage::getResourceModel('customer/customer_collection')
                ->addNameToSelect()
                ->addAttributeToSelect('email')
                ->addFieldToFilter('entity_id', array('in' => explode(',', $customersIds)));
                
            $cnt = 0;   
            foreach ($collection as $customer){
                $vars = array(
                    'customer'   => $customer,
                    'store'      => Mage::app()->getStore($customer->getStoreId()),
                    'order'      => new Varien_Object(),
                );
                $res = $this->send($customer->getEmail(), $customer->getName(), $template, $vars);
                if (true === $res){
                    $cnt++;
                }
                else {
                    Mage::getSingleton('adminhtml/session')->addError($res);
                }
            }//foreach       
                
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amemail')->__('%s email(s) have been sent.', $cnt));
            // show customer page if we have only one order
            if (1 == count($customersIds)){
                return $this->_redirect('customer/edit', array('id'=>$customersIds[0]));    
            }
            return $this->_redirect('customer/index');
            
	    }//if customers
	    elseif ($ordersIds){


	        $ordersIds = explode(',', $ordersIds);
	        $collection = Mage::getResourceModel('sales/order_collection')
	           ->addFieldToFilter('entity_id', array('in' => $ordersIds));

	        if (version_compare(Mage::getVersion(), '1.6.2') < 0){
    	        $collection 
                    ->addAttributeToSelect('*')
                    ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                    ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ;
	        }

            $cnt = 0;   
            foreach ($collection as $order){

	            $customer = new Varien_Object();
	            
	            if ($order->getCustomerFirstname() || $order->getCustomerLastname()){
	                $customer->setName($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
	            }
	            else {
	                $customer->setName($order->getShippingFirstname() . ' ' . $order->getShippingLastname());
	            }
	            
	            $customer->setId($order->getCustomerId());
                
                $vars = array(
                    'order'    => $order, 
                    'customer' => $customer,
                    'store'    => Mage::app()->getStore($order->getStoreId()),
                );


                
                $res = $this->send($order->getCustomerEmail(), $customer->getName(), $template, $vars);
                if (true === $res){
                    $cnt++;
                }
                else {
                    Mage::getSingleton('adminhtml/session')->addError($res);
                }
            }//foreach       
                
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amemail')->__('%s email(s) have been sent.', $cnt));
            
            // show order page if we have only one order
            if (1 == count($ordersIds)){
                return $this->_redirect('sales_order/view', array('order_id'=>$ordersIds[0]));    
            }
            return $this->_redirect('sales_order/index');
	    }
	    
	    // no ids
            return $this->_redirect('dashboard/index');
	}
 
	protected function send($email, $name, $template, $vars)
	{

	    $result = true;

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        
        $vars['email_text'] = nl2br($this->parse($template->getTxt(), $vars));
        $vars['email_subject'] = $this->parse($template->getSubj(), $vars);
               
        try {
            $tpl = Mage::getModel('core/email_template');
            $tpl->setDesignConfig(array('area' => 'frontend', 'store' => $vars['store']->getId()));
            
            $tpl->sendTransactional(
                    //$template->getCode(),
                    Mage::getStoreConfig('amemail/general/template', $vars['store']),
                    Mage::getStoreConfig('amemail/general/identity', $vars['store']),
                    $email,
                    $name,
                    $vars
                );
            //$vars[] = Mage::getStoreConfig('amemail/general/template', $vars['store']);     
            //mail($email, $name, print_r($vars,1));
            
        }
        catch (Exception $e) {
            $result =  $e->getMessage();
        }
        $translate->setTranslateInline(true); 
        
        // log 
        if (true === $result){
            $log = Mage::getModel('amemail/log');
            $log->setCustomerId($vars['customer']->getId());
            $log->setOrderId($vars['order']->getId());
            $log->setSentDate(date('Y-m-d H:i:s'));
            //$log->setCode($template->getCode());
            $log->setCode(substr(trim(strip_tags($vars['email_text'])), 0, 255));
            $log->setTxt($vars['email_text']); // todo add parser or return from sendTransactional?
            $log->save();
        }
        
        return $result;
	}
	
	protected function parse($str, $vars)
	{
        $processor = new Varien_Filter_Template();
        $processor->setVariables($vars);
        return $processor->filter($str);
	} 
	
	protected function _redirect($path, $params=array())
    {
        $url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/' . $path, $params);
        $this->getResponse()->setRedirect($url);
        return $this;
    } 
    
}