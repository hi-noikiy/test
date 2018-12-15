<?php
class EM_Apiios_Helper_Review extends Mage_Core_Helper_Abstract
{
    protected $_checkout = null;
    protected $_quote = null;
    protected $_store = null;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Get checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (null === $this->_checkout) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * Get active quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function getToTals(){
        return $this->getQuote()->getTotals();
    }

	public function renderTotals(){
		$result = array();
		foreach($this->getTotals() as $total){
			if($total->getCode() == 'subtotal'){
				$result[$total->getCode()] = $this->getSubTotal($total);
			} else if($total->getCode() == 'tax'){
				$result[$total->getCode()] = $this->getTax($total);
			} else if($total->getCode() == 'grand_total'){
				$result[$total->getCode()] = $this->getGrandTotal($total);
			}
		}
		return $result;
	}
	
	public function getTax($total){
		$_value = $total->getValue();
		$result = array();
		if (Mage::helper('tax')->displayFullSummary() && $_value!=0){
			foreach ($total->getFullInfo() as $info){
				if (isset($info['hidden']) && $info['hidden']) continue;
				$percent = $info['percent'];
				$amount = $info['amount'];
				$rates = $info['rates'];
				$isFirst = 1;
				foreach ($rates as $rate){
					$item = array();
					$item['label'] = $rate['label'];
					if (!is_null($rate['percent'])){
						$item['percent'] = "(".(float)$rate['percent'].")";
					}
					if($isFirst){
						$item['value'] = $amount;
					}
					$result[] = $item;
					$isFirst = 0;
				}
			}
		}
		$result[] = array(
			'label'	=>	$total->getTitle(),
			'value'	=>	$this->getStore()->formatPrice($_value,false)
		);
		return $result;
	}
	
    public function getSubtotal($total){
        $result = array();
        if(Mage::getSingleton('tax/config')->displayCartSubtotalBoth($this->getStore())){
            $result['exc'] = array(
                'code'      => 'subtotal_excl',
                'value'     => $this->getStore()->formatPrice($total->getValueExclTax(),false),
                'label'     => Mage::helper('tax')->__('Subtotal (Excl.Tax)')
            );
            $result['inc'] = array(
                'code'      => 'subtotal_excl',
                'value'     => $this->getStore()->formatPrice($total->getValueInclTax(),false),
                'label'     => Mage::helper('tax')->__('Subtotal (Excl.Tax)')
            );
        }
        else {
			$result['regular_price'] = array(
				'label'	=>	$total->getTitle(),
				'value'=>$this->getStore()->formatPrice($total->getValue(),false)
			);
        }
		return $result;
    }
	
	public function getGrandTotal($total){
		$result = array();
		if ($this->includeTax($total) && $this->getTotalExclTax($total)>=0){
			$result = array(
				'exc'	=>	array(
					'label'	=>	Mage::helper('tax')->__('Grand Total Excl. Tax'),
					'value'	=>	$this->getStore()->formatPrice($this->getTotalExclTax($total),false)
				),
				'inc'	=>	array(
					'label'	=>	Mage::helper('tax')->__('Grand Total Incl. Tax'),
					'value'	=>	$this->getStore()->formatPrice($total->getValue())
				)
			);
		} else {
			$resullt['regular_price'] = array(
				'label'	=>	$total->getTitle(),
				'value'	=>	$this->getStore()->formatPrice($total->getValue())
			);
		}
		return $result;
	}
	
	/**
     * Check if we have include tax amount between grandtotal incl/excl tax
     *
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