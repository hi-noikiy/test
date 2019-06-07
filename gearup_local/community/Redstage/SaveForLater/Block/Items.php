<?php

class Redstage_SaveForLater_Block_Items extends Mage_Core_Block_Template {

	public function _toHtml(){
		if( !Mage::getStoreConfig('saveforlater/settings/active') ){
				return '';
		}
		return parent::_toHtml();
	}

	public function getItems(){

		$items = Mage::getResourceModel('saveforlater/item_collection');

		if( Mage::getSingleton('customer/session')->getCustomer() && Mage::getSingleton('checkout/session')->getQuote()->getId() ){
			$items->getSelect()
				->where( "
					( quote_id = ". Mage::getSingleton('checkout/session')->getQuote()->getId() ." )
					". ( Mage::getSingleton('customer/session')->getCustomer() ? 'OR ( customer_id = \''. Mage::getSingleton('customer/session')->getCustomer()->getId() .'\')' : '' ) ."
				" );
		} else {
			$items->getSelect()
				->where( "1 = 2" );
		}

		return $items;

	}

	public function getItemHtml( $saveforlater_item ){
        $_item = self::collectData($saveforlater_item);

		$renderer = $this->getLayout()->createBlock( 'saveforlater/checkout_cart_item_renderer' )
			->setTemplate( 'redstage_saveforlater/cart/item/default.phtml' )
			->setSaveForLaterItem( $saveforlater_item )
			->setItem( $_item );

		return $renderer->toHtml();

	}


    public function getPopupItemHtml( $saveforlater_item, $index ){
	    $_item = self::collectData($saveforlater_item);

        $renderer = $this->getLayout()->createBlock( 'saveforlater/checkout_cart_item_renderer' )
            ->setTemplate( 'redstage_saveforlater/cart/item/popup.phtml' )
            ->setSaveForLaterItem( $saveforlater_item )
            ->setItem( $_item )
            ->setIndex( $index );

        return $renderer->toHtml();

    }

    protected function collectData($saveforlater_item) {
        $product = Mage::getModel('catalog/product')->load( $saveforlater_item->getProductId() );

        $_item = Mage::getModel('sales/quote_item')
            ->setQuote( Mage::getSingleton('checkout/cart')->getQuote() )
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setProduct( $product );

        $_item->addOption( new Varien_Object( array(
            'product' => $product,
            'product_id' => $product->getId(),
            'code' => 'info_buyRequest',
            'value' => $saveforlater_item->getBuyRequest()
        ) ) );

        /* FOR CUSTOM OPTIONS */
        $buyRequest = unserialize( $saveforlater_item->getBuyRequest() );
        if(isset($buyRequest['options'])) {
            $_item->addOption( new Varien_Object( array(
                'product' => $product,
                'product_id' => $product->getId(),
                'code' => 'option_ids',
                'value' => implode( ',', array_keys( $buyRequest['options'] ) )
            ) ) );
            foreach( $buyRequest['options'] as $option_id => $option_value ){
                $_item->addOption( new Varien_Object( array(
                    'product' => $product,
                    'product_id' => $product->getId(),
                    'code' => 'option_'. $option_id,
                    'value' => $option_value
                ) ) );
            }
        }
        /* END FOR CUSTOM OPTIONS */

        /* FOR CONFIGURABLE PRODUCTS */
        if(isset($buyRequest['super_attribute'])) {
            $_item->addOption( new Varien_Object( array(
                'product' => $product,
                'product_id' => $product->getId(),
                'code' => 'attributes',
                'value' => serialize( $buyRequest['super_attribute'] )
            ) ) );
        }
        /* END FOR CONFIGURABLE PRODUCTS */

        /* FOR BUNDLED PRODUCTS */
        $options = $qtys = $selectionIds = array();
        if(isset($buyRequest['bundle_option'])) {
            $options = $buyRequest['bundle_option'];
        } if(isset($buyRequest['bundle_option'])) {
            $qtys = $buyRequest['bundle_option_qty'];
        }
        if (is_array($options)) {
            /*$options = array_filter($options, 'intval');
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }*/
            $optionIds = array_keys($options);
        //}
            foreach ($options as $optionId => $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = (int)$selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = (int)$id;
                        }
                    }
                }
            }
            $_item->addOption( new Varien_Object( array(
                'product' => $product,
                'product_id' => $product->getId(),
                'code' => 'bundle_option_ids',
                'value' => serialize( array_map('intval', $optionIds) )
            ) ) );
            $_item->addOption( new Varien_Object( array(
                'product' => $product,
                'product_id' => $product->getId(),
                'code' => 'bundle_selection_ids',
                'value' => serialize($selectionIds)
            ) ) );

        /*$stream = fopen( Mage::getBaseDir() .'/tmp/tmp.txt', 'a+' );
        fwrite( $stream, "selectionIds: ". print_r( $selectionIds, 1 ) ."\n" );
        fwrite( $stream, "qtys: ". print_r( $qtys, 1 ) ."\n" );
        fclose( $stream );*/

            $i = 1;
            foreach( $selectionIds as $selection_id ){
                if(isset($qtys[ $i ])) {
                    $_item->addOption( new Varien_Object( array(
                        'product' => $product,
                        'product_id' => $product->getId(),
                        'code' => 'selection_qty_'. $selection_id,
                        'value' => $qtys[ $i ]
                    ) ) );
                } else {
                    $_item->addOption( new Varien_Object( array(
                        'product' => $product,
                        'product_id' => $product->getId(),
                        'code' => 'selection_qty_'. $selection_id,
                        'value' => ''
                    ) ) );
                }
                $i++;
            }
        }
        /* END FOR BUNDLED PRODUCTS */

        return $_item;

    }


    public function getMergeFormActionUrl()
    {
        return $this->getUrl('cartmerge', array('_secure' => $this->_isSecure()));
    }

    public function getSaveForLaterFormActionUrl()
    {
        return $this->getUrl('cartmerge/index/saveForLater', array('_secure' => $this->_isSecure()));
    }



}