<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Method_Available extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract
{
    protected $_rates;
    protected $_address;

    public function getShippingRates()
    {

        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();
            /*
            if (!empty($groups)) {
                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter(Mage::app()->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true, false);
    }

    public function  toArrayFields() {
        $_shippingRateGroups = $this->getShippingRates();
        $helper = Mage::helper('checkout');
        if (!$_shippingRateGroups)
            return array('message'=>$helper->__('Sorry, no quotes are available for this order at this time.'));
        $_sole = count($_shippingRateGroups) == 1;
        $result = array();
        foreach ($_shippingRateGroups as $code => $_rates){
            $shipping = array();
            $shipping['carrier'] = $this->getCarrierName($code);
            $_sole = $_sole && count($_rates) == 1;
            $rateData = array();
            foreach ($_rates as $_rate){
                $label = array(
                    'title' =>  $_rate->getMethodTitle()
                );
                $_excl = $this->getShippingPrice($_rate->getPrice(), Mage::helper('tax')->displayShippingPriceIncludingTax());
                $_incl = $this->getShippingPrice($_rate->getPrice(), true);
                $price = $_excl;
                if (Mage::helper('tax')->displayShippingBothPrices() && $_incl != $_excl){
                    $price .= ' '.$helper->__('Incl. Tax').' '.$_incl;
                }
                $label['price'] = $price;
                $rateData[] = array(
                    'value' =>  $_rate->getCode(),
                    'label' =>  $label
                );
            }
            $shipping['rates'] = $rateData;
            $result[] = $shipping;
        }

        $final = array(
            'title' =>  $helper->__('Shipping Method'),
            'prefix'=>  'shipping_method',
            'fields'=>  array(
                array(
                    'name'      =>  'shipping_method',
                    'type'      =>  'radio_group',
                    'methods'   =>  $result
                )
            )
        );
        return $final;
    }
}
?>
