<?php
/**
 * Anowave Google Tag Manager Enhanced Ecommerce (UA) Tracking (User-defined)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ecu
 * @copyright 	Copyright (c) 2017 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */
 
class Anowave_Ecu_Model_Observer extends Anowave_Ec_Model_Observer
{
	/**
	 * Append 
	 * 
	 * {@inheritDoc}
	 * @see Anowave_Ec_Model_Observer::append()
	 */
	protected function append(Mage_Core_Block_Abstract $block)
	{
		$content = parent::append($block);
		
		if (is_null($content))
		{
			switch ($block->getNameInLayout())
			{
				case 'custom.checkout.onepage':	return $this->getCheckout();
			}
		}
		
		return $content;
	}
	
	/**
	 * Alter
	 * 
	 * {@inheritDoc}
	 * @see Anowave_Ec_Model_Observer::alter()
	 */
	protected function alter(Mage_Core_Block_Abstract $block, $content)
	{
		$content = parent::alter($block, $content);
		
		switch ($block->getNameInLayout())
		{
			case 'special-offer': return $this->getSpecialOffer($block, $content);
		}
		
		return $content;
	}
	
	/**
	 * Get checkout content
	 *
	 * @return string
	 */
	protected function getCheckout()
	{
		return Mage::helper('ec')->filter
		(
			Mage::app()->getLayout()->createBlock('ec/track')->setTemplate('ecu/checkout.phtml')->toHtml()
		);
	}
	
	/**
	 * Bind special offer 
	 * 
	 * @param Belvg_Countdown_Block_Products $block
	 * @param string $content
	 * @return string
	 */
	protected function getSpecialOffer(Belvg_Countdown_Block_Products $block, $content)
	{
		$products = [];
		
		foreach ($block->getProductEndedCollection() as $product)
		{
			$products[] = $product;	
		}

		$doc = new DOMDocument('1.0','utf-8');
		$dom = new DOMDocument('1.0','utf-8');
		
		@$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		
		$query = new DOMXPath($dom);
		
		/**
		 * Default item position
		 *
		 * @var int
		 */
		$position = 1;
		
		$category = Mage::helper('ec')->__('Special offer');
		
		foreach ($query->query('//div[contains(@class,"special-offer-right")]', $dom) as $key => $element)
		{
			if (isset($products[$key]))
			{
				/**
				 * Product click tracking
				 */
				foreach ($query->query('div/div[contains(@class,"product-name")]/a|div/div/div/a[contains(@class,"product-image")]', $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$a->setAttribute('data-id', 		$products[$key]->getSku());
					$a->setAttribute('data-name', 		Mage::helper('ec')->jsQuoteEscapeDataAttribute($products[$key]->getName()));
					$a->setAttribute('data-price', 		Mage::helper('ec/price')->getPrice($products[$key]));
					$a->setAttribute('data-category', 	$category);
					
					$a->setAttribute('data-brand',		Mage::helper('ec')->jsQuoteEscapeDataAttribute
					(
						Mage::helper('ec')->getBrand($products[$key])
					));
					$a->setAttribute('data-quantity', 	1);
					$a->setAttribute('data-click',		$click);
					$a->setAttribute('data-position',	$position);
					
					if (Mage::helper('ec')->useClickHandler())
					{
						$a->setAttribute('onclick', 'return AEC.click(this,dataLayer)');
					}
					
					$a->setAttribute('data-event', 'productClick');
					
					$a->setAttribute('data-list', $category);
					
					$object = new Varien_Object
					(
						array
						(
							'attributes' => Mage::helper('ec/attributes')->getAttributes()
						)
					);
					
					Mage::dispatchEvent('ec_get_click_attributes', array
					(
						'object'  => $object,
						'product' => $products[$key]
					));
					
					$a->setAttribute('data-attributes', Mage::helper('ec/json')->encode($object->getAttributes()));
				}
				
				/**
				 * @property Direct "Add to cart" tracking from categories
				 */
				foreach ($query->query('div/div/a[contains(@class,"btn-add-to-cart")]', $element) as $a)
				{
					$click = $a->getAttribute('onclick');
					
					$a->setAttribute('data-id', 		$products[$key]->getSku());
					$a->setAttribute('data-name', 		Mage::helper('ec')->jsQuoteEscapeDataAttribute($products[$key]->getName()));
					$a->setAttribute('data-price', 		Mage::helper('ec/price')->getPrice($products[$key]));
					$a->setAttribute('data-category', 	$category);
					$a->setAttribute('data-list', 		$category);
					$a->setAttribute('data-brand',		Mage::helper('ec')->jsQuoteEscapeDataAttribute
					(
						Mage::helper('ec')->getBrand($products[$key])
					));
					$a->setAttribute('data-quantity', 	1);
					$a->setAttribute('data-click',		$click);
					$a->setAttribute('data-position',	$position);
					
					if (Mage::helper('ec')->useClickHandler())
					{
						$a->setAttribute('onclick',	'return AEC.ajaxList(this,dataLayer)');
					}
					
					$a->setAttribute('data-event','addToCartList');
					
					$object = new Varien_Object
					(
						array
						(
							'attributes' => Mage::helper('ec/attributes')->getAttributes()
						)
					);
					
					Mage::dispatchEvent('ec_get_click_attributes', array
					(
						'object'  => $object,
						'product' => $products[$key]
					));
					
					$a->setAttribute('data-attributes', Mage::helper('ec/json')->encode($object->getAttributes()));
				}
				
				/**
				 * Increment position
				 */
				$position++;
			}
		}
		
		$content = $this->getDOMContent($dom, $doc);
		
		return $content;
	}
	
	/**
	 * Modify checkout products collection
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function getCheckoutProductsAfter(Varien_Event_Observer $observer)
	{
		/**
		 * @todo Your implementation here
		 */
	}
	
	/**
	 * Modify order products collection
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function getOrderProductsAfter(Varien_Event_Observer $observer) 
	{
		/**
		 * @todo Your implementation here
		 */
	}
	
	/**
	 * Modify impression data 
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function getImpressionDataAfter(Varien_Event_Observer $observer)
	{
		/**
		 * @todo Your implementation here
		 */
	}
}