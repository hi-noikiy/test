<?php
class EM_Apiios_Model_Api2_Productsinfo_Bundle_Option extends Mage_Core_Model_Abstract
{
    protected $_product = null;

    public function setProduct($_product){
        $this->_product = $_product;
        return $this;
    }

    /**
     * Retrieve product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }


    /**
     * Get title price for selection product
     *
     * @param Mage_Catalog_Model_Product $_selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionTitlePrice($_selection, $includeContainer = false)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);
        $this->setFormatProduct($_selection);
        //$priceTitle = $this->escapeHtml($_selection->getName());
        //$priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '')
          //  . '+' . $this->formatPriceString($price, $includeContainer)
            //. ($includeContainer ? '</span>' : '');
        return $this->formatPriceString($price, $includeContainer);
        //return $priceTitle;
    }

    /**
     * Format price string
     *
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPriceString($price, $includeContainer = false)
    {
        $taxHelper  = Mage::helper('tax');
        $coreHelper = Mage::helper('core');
        $currentProduct = $this->getProduct();
        if ($currentProduct->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC
            && $this->getFormatProduct()
        ) {
            $product = $this->getFormatProduct();
        } else {
            $product = $currentProduct;
        }

        $priceTax    = $taxHelper->getPrice($product, $price);
        $priceIncTax = $taxHelper->getPrice($product, $price, true);

        $result = array();
        $result['exc'] = array(
            'value'     =>  $coreHelper->currencyByStore($priceTax, $product->getStore(), true, $includeContainer),
            'value_org' =>  $coreHelper->currencyByStore($priceTax, $product->getStore(), false, $includeContainer),
            'label'     =>  $taxHelper->__('Excl. Tax:')
        );
        if ($taxHelper->displayBothPrices() && $priceTax != $priceIncTax) {
            $result['inc'] = array(
                'value'     =>  $coreHelper->currencyByStore($priceIncTax, $product->getStore(), true, $includeContainer),
                'value_org' =>  $coreHelper->currencyByStore($priceIncTax, $product->getStore(), false, $includeContainer),
                'label'     =>  $taxHelper->__('Incl. Tax:')
            );
        }
        return $result;
    }
}