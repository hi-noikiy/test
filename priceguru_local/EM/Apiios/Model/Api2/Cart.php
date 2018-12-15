<?php
class EM_Apiios_Model_Api2_Cart extends Mage_Checkout_Model_Cart
{
	protected $store;
	
	public function setStore($store){
		$this->store = $store;
		return $this;
	}
	
	public function getStore(){
		return $this->store;
	}

   /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('apiios/api2_checkout_session')->setStore($this->getStore());
    }

    /**
     * Get product object based on requested product information
     *
     * @param   mixed $productInfo
     * @return  Mage_Catalog_Model_Product
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Mage_Catalog_Model_Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getStore()->getId())
                ->load($productInfo);
        }
        $currentWebsiteId = $this->getStore()->getWebsiteId();
        if (!$product
            || !$product->getId()
            || !is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            Mage::throwException(Mage::helper('checkout')->__('The product could not be found.'));
        }
        return $product;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $this->setData('quote', $this->getCheckoutSession()->getQuote()->setStoreId($this->getStore()->getId()));
        }
        return $this->_getData('quote');
    }

    /**
     * Set quote object associated with the cart
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Cart
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->setData('quote', $quote->setStoreId($this->getStore()->getId()));
        return $this;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = $this->getCheckoutSession()->getQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && Mage::getSingleton('customer/session')->isLoggedIn()) {
            $quote = $this->getCheckoutSession()->getQuote();
            $quoteId = $this->getCheckoutSession()->getQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            if (Mage::getStoreConfig('checkout/cart_link/use_qty')) {
                $this->_summaryQty = $this->getItemsQty();
            } else {
                $this->_summaryQty = $this->getItemsCount();
            }
        }
        return $this->_summaryQty;
    }

	/**
     * @param  $quoteId
     * @param  $store
     * @return void
     */
    public function totals()
    {
        $quote = $this->getQuote();

        $totals = $quote->getTotals();

        $totalsResult = array();
        foreach ($totals as $total) {
            $data = array();
            if($total->getCode() == 'subtotal'){
                if(Mage::getSingleton('tax/config')->displayCartSubtotalBoth($this->getStore())){
                    $data['exc'] = array(
                        'code'      => 'subtotal_excl',
                        'label' =>  Mage::helper('tax')->__('Subtotal (Excl. Tax)'),
                        'value' =>  $this->getQuote()->getStore()->formatPrice($total->getValueExclTax(),false)
                    );
                    $data['inc'] = array(
                        'code'      => 'subtotal_incl',
                        'label' =>  Mage::helper('tax')->__('Subtotal (Incl. Tax)'),
                        'value' =>  $this->getQuote()->getStore()->formatPrice($total->getValueInclTax(),false)
                    );
                } else{
                    $data['label'] = $total->getTitle();
                    $data['value'] = $this->getQuote()->getStore()->formatPrice($total->getValue(),false);
                }
            } else if($total->getCode() == 'grand_total'){
                if ($this->includeTax($total) && $this->getTotalExclTax($total)>=0){
                    $data['exc'] = array(
                        'code'      => 'grand_total_excl',
                        'strong'    => true,
                        'label' => Mage::helper('tax')->__('Grand Total Excl. Tax'),
                        'value' => $this->getQuote()->getStore()->formatPrice($this->getTotalExclTax($total),false)
                    );

                    $data['inc'] = array(
                        'code'      => 'grand_total_incl',
                        'strong'    => true,
                        'label' => Mage::helper('tax')->__('Grand Total Incl. Tax'),
                        'value' => $this->getQuote()->getStore()->formatPrice($total->getValue(),false)
                    );
                } else {
                    $data['label'] = $total->getTitle();
                    $data['strong'] = true;
                    $data['value'] = $this->getQuote()->getStore()->formatPrice($total->getValue(),false);
                }
            } else {
                $data = array(
                    "label" => $total->getTitle(),
                    "value" => $this->getQuote()->getStore()->formatPrice($total->getValue(),false)
                );
            }

            $totalsResult[] = $data;
        }
        return $totalsResult;
    }

    /**
     * Check if we have include tax amount between grandtotal incl/excl tax
     *
     * param : Mage_Sales_Model_Quote_Address_Total $total
     * @return bool
     */
    public function includeTax($total)
    {
        if ($total->getAddress()->getGrandTotal()) {
            return Mage::getSingleton('tax/config')->displayCartTaxWithGrandTotal($this->getStore());
        }
        return false;
    }

    /**
     * Get grandtotal exclude tax
     *
     * param : Mage_Sales_Model_Quote_Address_Total $total
     * @return float
     */
    public function getTotalExclTax($total)
    {
        $excl = $total->getAddress()->getGrandTotal()-$total->getAddress()->getTaxAmount();
        $excl = max($excl, 0);
        return $excl;
    }

}
?>