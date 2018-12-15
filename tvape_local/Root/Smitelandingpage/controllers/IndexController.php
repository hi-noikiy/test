<?php
class Root_Smitelandingpage_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction() 
	{ 
		$this->loadLayout();
		$this->getLayout()->getBlock("head")->setTitle($this->__("Smite landingpage"));
		$this->renderLayout();
    }
	
	public function AddtocartAction() 
	{
		$product_id = $this->getRequest()->getParam('product_id');
		$main_opt_id = $this->getRequest()->getParam('main_opt_id');
		$free_pro_id = $this->getRequest()->getParam('free_pro_id');
		
		$params = array(
			'product' => $product_id,
			'related_product' => null,
			'bundle_option' => array($main_opt_id => $free_pro_id),
			'qty' => 1,
		);
		 
		$cart = Mage::getSingleton('checkout/cart');
		 
		$product = new Mage_Catalog_Model_Product();
		$product->load($product_id);
		 
		$cart->addProduct($product, $params);
		$cart->save();
		 
		Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
		 
		$message = $this->__('%s was added to your shopping cart.', $product->getName());
		Mage::getSingleton('checkout/session')->addSuccess($message);
    }
}