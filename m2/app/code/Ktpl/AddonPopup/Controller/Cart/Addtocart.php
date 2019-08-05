<?php
namespace Ktpl\AddonPopup\Controller\Cart;


class Addtocart extends \Magento\Framework\App\Action\Action 
{

	protected $formKey;   
	protected $cart;
	protected $product;

	public function __construct(
	\Magento\Framework\App\Action\Context $context,
	\Magento\Framework\Data\Form\FormKey $formKey,
	\Magento\Checkout\Model\Cart $cart,
	\Magento\Catalog\Model\Product $product,
	array $data = []) {
	    $this->formKey = $formKey;
	    $this->cart = $cart;
	    $this->product = $product;      
	    parent::__construct($context);
	}

public function execute()
 { 
  $productId = $_POST['product_id'];
  $params = array(
                'form_key' => $this->formKey->getFormKey(),
                'product' => $productId, //product Id
                'qty'   =>1 //quantity of product                
            );              
    //Load the product based on productID   
  	$_product = $this->product->load($productId); 
  	$result['status'] = 'error';
    $result['message'] = 'An error was occurred adding '.$_product->getName().' to shopping cart';
       
    if($_product){   
	    $this->cart->addProduct($_product, $params);
	    $this->cart->save();
	    $result['status'] = 'success';
        $result['message'] = $_product->getName().' was added to your cart';
	}    
	echo json_encode($result);
 }

}