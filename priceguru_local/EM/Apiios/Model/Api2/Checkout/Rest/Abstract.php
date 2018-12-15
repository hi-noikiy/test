<?php
class EM_Apiios_Model_Api2_Checkout_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
	const API_ADD_SUCCESS = 'API add to cart';

	protected function _getCart()
    {
		return Mage::getSingleton('apiios/api2_cart')->setStore($this->_getStore());
    }

	protected function _getSession()
    {
		return Mage::getSingleton('apiios/api2_checkout_session')->setStore($this->_getStore());
    }

	protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

	protected function _initProduct($productId)
    {
        //$productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->_getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /*protected function _retrieveCollection(){
		return $this->delete();
    }*/
	
	protected function _addToCart(array $data){
		$result = array();

		$cart   = $this->_getCart();
        $params = $data;
        $message = '';
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct($params['product']);
            //$related = $params['related_product'];

            /**
             * Check product availability
             */
            if (!$product) {
                $check = 0;
				$message =  Mage::helper('apiios')->__('The product is not exist'); 
            }else{
				$cart->addProduct($product, $params);
				if (isset($params['related_product']) && !empty($related)) {
					$cart->addProductsByIds(explode(',', $params['related_product']));
				}
				
				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);

				/**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
					array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);

				if (!$this->_getSession()->getNoCartRedirect(true)) {
					if (!$cart->getQuote()->getHasError()){
						$message =  Mage::helper('apiios')->__('%s was added to your shopping cart.', Mage::helper('apiios')->escapeHtml($product->getName()));
						$check = 1;
					}
				}
			}
        } catch (Mage_Core_Exception $e) {
			$check = 0;
           if ($this->_getSession()->getUseNotice(true)) {
				$message	=	Mage::helper('apiios')->escapeHtml($e->getMessage());
            } else {
                $message	=	Mage::helper('apiios')->escapeHtml($e->getMessage());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('apiios')->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
        }

		$arr['success'] 		= 	$check;
		//$arr['message']			= 	$message;
		$arr['total_items']		= 	$cart->getSummaryQty();
		if($arr['success'] == 1)	$arr['redirect']		=	0;
		else	$arr['redirect']		=	1;
		$result['result']	=	$arr;
        //print_r($result);exit;
		$this->_successMessage(
			$message,
			Mage_Api2_Model_Server::HTTP_OK,
			$result
		);
	}

    /**
     * Add to cart (for ios)
     *
     * @param array $data
     * @return string|void
     */
    protected function _create($data){
		Mage::app()->setCurrentStore($this->_getStore()->getId());
        $this->_addToCart($data);
        $this->_render($this->getResponse()->getMessages());
    }

    /**
     * Add to cart (for android)
     *
     * @param array $data
     */
    protected function _multiCreate($data){
		Mage::app()->setCurrentStore($this->_getStore()->getId());
        $this->_addToCart($data[0]);
    }

    /**
     * Update cart
     *
     * @param array $data
     */
    protected function _update(array $data){
		Mage::app()->setCurrentStore($this->_getStore()->getId());
		$updateAction = (string)$data['update_cart_action'];	
        switch ($updateAction) {
            case 'empty_cart':
                $tmp = $this->_emptyShoppingCart($data);
                break;
            case 'update_qty':
                $tmp = $this->_updateShoppingCart($data);
                break;
            default:
                $tmp = $this->_updateShoppingCart($data);
        }
		$cart   = $this->_getCart();
		if($tmp)	$result['cart_info']	=	$tmp;
		$result['cart_info']['total_items']		= 	$cart->getSummaryQty();

		$this->_successMessage(
			EM_Apiios_Model_Api2_Checkout_Rest_Guest_V1::API_ADD_SUCCESS,
			Mage_Api2_Model_Server::HTTP_OK,
			$result
		);
		$this->_render($this->getResponse()->getMessages());
        $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
	}

    protected function _delete(){
		$result	=	array();
		Mage::app()->setCurrentStore($this->_getStore()->getId());
		$cart   = Mage::getSingleton('checkout/cart');
		//print_r($cart->getItems()->getData());exit; 
		$id = $this->getRequest()->getParam('item_id');
        if ($id) {
            try {
				$cart->removeItem($id)
                 ->save();
            } catch (Exception $e) {
				throw new Mage_Api2_Exception(Mage::helper('apiios')->__('Cannot remove item'),300);
            }
        }
		$result['total_items']		= 	$cart->getSummaryQty();
		$this->_successMessage(
			Mage::helper('apiios')->__('Cart item has been deleted'),
			Mage_Api2_Model_Server::HTTP_OK,
			$result
		);
		$this->_render($this->getResponse()->getMessages());
	}

	 protected function _emptyShoppingCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }
	
	protected function _updateShoppingCart($data)
    {
        try {
            $cartData = $data['cart'];
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
			$success	=	1;
        } catch (Mage_Core_Exception $e) {
			$success	=	0;
			$msg	=	Mage::helper('core')->escapeHtml($e->getMessage());
        } catch (Exception $e) {
			$success	=	0;		
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
		$result['success']	=	$success;
		if(isset($msg))	$result['message']	=	$msg;
		
		return $result;
    }
}
?>