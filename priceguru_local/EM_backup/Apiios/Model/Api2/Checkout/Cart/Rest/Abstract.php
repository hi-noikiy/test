<?php
class EM_Apiios_Model_Api2_Checkout_Cart_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
	/*protected function _getCart()
    {
        //return Mage::getModel('checkout/cart')->getQuote();
        return Mage::getSingleton('checkout/session')->getQuote();
    }*/
	
	protected function _getCart()
    {
        return Mage::getSingleton('apiios/api2_cart')->setStore($this->_getStore());
    }

    protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
		$result = array();
		$cart   = $this->_getCart();
		if($cart->getSummaryQty() > 0){
			foreach($cart->getItems() as $item){
				$product	=	$item->getData();
				$tmp	=	array();
				if($product['parent_item_id'] == "" ){
					$model	=	$this->getProductsbyId($item->getProductId());
					$tmp['item_id']			=	$product['item_id'];
					$tmp['quote_id']		=	$product['quote_id'];
					$tmp['product_id']		=	$product['product_id'];
					$tmp['product_type']		=	$product['product_type'];
					$tmp['name']			=	$product['name'];
					$tmp['sku']				=	$product['sku'];
					$tmp['small_image']	=	(string)Mage::helper('catalog/image')->init($model, 'small_image');
					$tmp['thumbnail']		=	(string)Mage::helper('catalog/image')->init($model, 'thumbnail');
					$tmp['free_shipping']	=	$product['free_shipping'];
					$tmp['no_discount']		=	$product['no_discount'];				
					$tmp['qty']				=	$product['qty'];					
					$tmp['gift_message_id']		=	$product['gift_message_id'];
					$tmp['is_recurring']		=	$product['is_recurring'];
					$tmp['prices']	=	array();

				//-------------------  get unit price and subtotal price .     ----------------//	
					$helper_tax	=	Mage::helper('tax');
					if ($helper_tax->displayCartPriceExclTax() || $helper_tax->displayCartBothPrices()){	// unit excl
						if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'sales') && $item->getWeeeTaxAppliedAmount())
							$unit_excl	=	$cart->getStore()->formatPrice($item->getCalculationPrice()+$item->getWeeeTaxAppliedAmount()+$item->getWeeeTaxDisposition(),false);
						else
							$unit_excl	=	$cart->getStore()->formatPrice($item->getCalculationPrice(),false);

						$unit_excls['label']	=	"Excl. Tax";
						$unit_excls['value']	=	$unit_excl;
						$tmp['prices']['unit'][]	=	$unit_excls;
					}

					if ($helper_tax->displayCartPriceInclTax() || $helper_tax->displayCartBothPrices()){	//	unit incl
						$_incl = Mage::helper('checkout')->getPriceInclTax($item);
						if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'sales') && $item->getWeeeTaxAppliedAmount())
							$unit_incl	=	$cart->getStore()->formatPrice($_incl+$item->getWeeeTaxAppliedAmount(),false);
						else
							$unit_incl	=	$cart->getStore()->formatPrice($_incl-$item->getWeeeTaxDisposition(),false);

						$unit_incls['label']	=	"Incl. Tax";
						$unit_incls['value']	=	$unit_incl;
						$tmp['prices']['unit'][]	=	$unit_incls;
					}

					if (($helper_tax->displayCartPriceExclTax() || $helper_tax->displayCartBothPrices()) && !$item->getNoSubtotal()){		// sub excl
						if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'sales') && $item->getWeeeTaxAppliedAmount())
							$sub_excl	=	$cart->getStore()->formatPrice($item->getRowTotal()+$item->getWeeeTaxAppliedRowAmount()+$item->getWeeeTaxRowDisposition(),false); 
						else
							$sub_excl	=	$cart->getStore()->formatPrice($item->getRowTotal(),false);

						$sub_excls['label']	=	"Excl. Tax";
						$sub_excls['value']	=	$sub_excl;
						$tmp['prices']['subtotal'][]	=	$sub_excls;
					}

					if (($helper_tax->displayCartPriceInclTax() || $helper_tax->displayCartBothPrices()) && !$item->getNoSubtotal()){		//sub incl
						$_incl = Mage::helper('checkout')->getSubtotalInclTax($item);
						if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'sales') && $item->getWeeeTaxAppliedAmount())
							$sub_incl	=	$cart->getStore()->formatPrice($_incl+$item->getWeeeTaxAppliedRowAmount(),false);
						else
							$sub_incl	=	$cart->getStore()->formatPrice($_incl-$item->getWeeeTaxRowDisposition(),false);

						$sub_incls['label']	=	"Incl. Tax";
						$sub_incls['value']	=	$sub_incl;
						$tmp['prices']['subtotal'][]	=	$sub_incls;
					}

				//-------------------  get children configurable options .     ----------------//
					$conf	=	array();
					$bund	=	array();
					if($product['product_type'] == 'configurable'){
						$helper = Mage::helper('catalog/product_configuration');
						$conf = $helper->getConfigurableOptions($item);

						$tmp['configurable']		=	$conf;
					}
					elseif($product['product_type'] == 'bundle'){
						$helper = Mage::helper('bundle/catalog_product_configuration');
						$bund = $helper->getBundleOptions($item);

						$tmp['bundle_options']		=	$bund;
					}

				//-------------------  get custom options .     ----------------//
					$_options = Mage::helper('catalog/product_configuration')->getCustomOptions($item); 
					$opt	=	array();
					foreach ($_options as $_option) {
						$opt[]	=	$_option;
					}
					$tmp['custom_options']	=	$opt;
					
					$rs[] = $tmp;
				}
			}
			$result['shopping_cart']['items'] = $rs;

		//-------------------  get crosssell product.     ----------------//
			$cross = Mage::getModel('apiios/api2_crosssell')->setStore($this->_getStore())->getItems();
			$result['shopping_cart']['crosssell'] = array();
			if(count($cross) > 0){
				$additional = array('thumbnail' => array(
					'width' =>  100,
					'height'=>  100
				));
				foreach($cross as $_cross){
					$this->_setProduct($_cross);
					$crs	=	$this->getProductsbyId($_cross->getId());
					$tmp_cross	=	$this->_prepareProductForResponse($crs,$additional);
					$crosssell[]	=	$tmp_cross;
				}
				$result['shopping_cart']['crosssell'] = $crosssell;
			}
			
		//-------------------  get totals shopping cart.     ----------------//
			$totals	=	$cart->totals();
			//echo '<pre>';print_r($totals);exit;
			$result['shopping_cart']['totals']	=	$totals;
		}else{
			$msg['result']['check']	=	"success";
			$msg['message']	=	Mage::helper('apiios')->__('You have no items in your shopping cart.'); ;
			$msg['code']	=	200;
			
			$result['success'][]	=	$msg;
		}
		//print_r($result);exit;
		return $result;
    }
	
	protected function getProductsbyId($id){
		$storeId 	= $this->_getStore()->getId();
		/*$products = Mage::getResourceModel('catalog/product_collection')->setStoreId($storeId)->addStoreFilter();
        $products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $products = $this->_addProductAttributesAndPrices($products)
			->addAttributeToFilter('entity_id',$id);*/
        return Mage::getModel('catalog/product')->setStoreId($storeId)->load($id);
	}


}
?>