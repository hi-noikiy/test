<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Bundle option radiobox type renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Gearup_Bundleproduct_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Radio
    extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio
{
     /* * @return string

     */

    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection);

        $productName = $_selection->getName();

        $maxLengthProductName = Mage::getDesign()->getPackageName() == "aw_mobile3" ? 83 : 60;
        if (strlen($_selection->getName()) > $maxLengthProductName){
            $productName = mb_substr($_selection->getName(), 0, $maxLengthProductName, "UTF-8");
            $productName = $productName.".....";
        }

        $this->setFormatProduct($_selection);
        $priceTitle = '<span>[+'.$this->formatPriceString($price, $includeContainer).']</span>';

        /*$priceTitle .= ' '.$this->escapeHtml($productName);

        return $priceTitle; */
        $Title = $this->escapeHtml($productName). ' '.$priceTitle;

        return $Title;
    }
    
    /**
     * Format price string
     *
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPriceString($price, $includeContainer = true)
    {
        $taxHelper  = Mage::helper('tax');
        $coreHelper = $this->helper('core');
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

        $formated = $coreHelper->currencyByStore($priceTax, $product->getStore(), true, $includeContainer);
        if ($taxHelper->displayBothPrices() && $priceTax != $priceIncTax) {
            $formated .=
                    ' (+' .
                    $coreHelper->currencyByStore($priceIncTax, $product->getStore(), true, $includeContainer) .
                    ' ' . $this->__('Incl. Tax') . ')';
        }

        //return $formated;
        return $coreHelper->currencyByStore($priceIncTax, $product->getStore(), true, $includeContainer);
    }
    
}
