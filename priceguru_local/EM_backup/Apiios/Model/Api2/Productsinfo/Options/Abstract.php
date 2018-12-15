<?php
class EM_Apiios_Model_Api2_Productsinfo_Options_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Product object
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Product option object
     *
     * @var Mage_Catalog_Model_Product_Option
     */
    protected $_option;

    /**
     * Set Product object
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Block_Product_View_Options_Abstract
     */
    public function setProduct(Mage_Catalog_Model_Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Retrieve Product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Set option
     *
     * @param Mage_Catalog_Model_Product_Option $option
     * @return Mage_Catalog_Block_Product_View_Options_Abstract
     */
    public function setOption(Mage_Catalog_Model_Product_Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * Get option
     *
     * @return Mage_Catalog_Model_Product_Option
     */
    public function getOption()
    {
        return $this->_option;
    }

    public function getFormatedPrice()
    {
        if ($option = $this->getOption()) {
            return $this->_formatPrice(array(
                'is_percent'    => ($option->getPriceType() == 'percent'),
                'pricing_value' => $option->getPrice($option->getPriceType() == 'percent')
            ));
        }
        return '';
    }

    /**
     * Return formated price
     *
     * @param array $value
     * @return array
     */
    protected function _formatPrice($value, $flag=false)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }
        $result = array();
        $taxHelper = Mage::helper('tax');
        $store = $this->getProduct()->getStore();
        $sign = 1;
        if ($value['pricing_value'] < 0) {
            $sign = -1;
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;
        $_priceInclTax = $this->getPrice($value['pricing_value'], true)*$sign;
        $_priceExclTax = $this->getPrice($value['pricing_value'])*$sign;
        if ($taxHelper->displayPriceIncludingTax()) {
            $result['inc'] = array(
                'value'     =>  $this->helper('core')->currencyByStore($_priceInclTax, $store, true, $flag),
                'value_org' =>  $this->helper('core')->currencyByStore($_priceInclTax, $store, false, $flag),
                'label'     =>  Mage::helper('tax')->__('Incl. Tax:')
            );
            //$priceStr .= $this->helper('core')->currencyByStore($_priceInclTax, $store, true, $flag);
        } elseif ($taxHelper->displayPriceExcludingTax()) {
            $result['exc'] = array(
                'value' =>  $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag),
                'value_org' =>  $this->helper('core')->currencyByStore($_priceExclTax, $store, false, $flag),
                'label'     =>  Mage::helper('tax')->__('Excl. Tax:')
            );
            //$priceStr .= $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag);
        } elseif ($taxHelper->displayBothPrices()) {
            $result['exc'] = array(
                'value' =>  $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag),
                'value_org' =>  $this->helper('core')->currencyByStore($_priceExclTax, $store, false, $flag),
                'label'     =>  Mage::helper('tax')->__('Excl. Tax:')
            );
            //$priceStr .= $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag);
            if ($_priceInclTax != $_priceExclTax) {
                $result['inc'] = array(
                    'value' =>  $this->helper('core')->currencyByStore($_priceInclTax, $store, true, $flag),
                    'value_org' =>  $this->helper('core')->currencyByStore($_priceInclTax, $store, false, $flag),
                    'label'     =>  Mage::helper('tax')->__('Incl. Tax:')
                );
                //$priceStr .= ' ('.$sign.$this->helper('core')
                  //  ->currencyByStore($_priceInclTax, $store, true, $flag).' '.$this->__('Incl. Tax').')';
            }
        }

        /*if ($flag) {
            $priceStr = '<span class="price-notice">'.$priceStr.'</span>';
        }*/

        return $result;
    }

    /**
     * Get price with including/excluding tax
     *
     * @param decimal $price
     * @param bool $includingTax
     * @return decimal
     */
    public function getPrice($price, $includingTax = null)
    {
        if (!is_null($includingTax)) {
            $price = Mage::helper('tax')->getPrice($this->getProduct(), $price, true);
        } else {
            $price = Mage::helper('tax')->getPrice($this->getProduct(), $price);
        }
        return $price;
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->getProduct()->getStore();
        return $this->helper('core')->currencyByStore($price, $store, false);
    }

    public function helper($name){
        return Mage::helper($name);
    }

    public function getOptionArray(){
        $result = $this->getOption()->getData();
        $result['prices'] = $this->getFormatedPrice();
        return $result;
    }
}