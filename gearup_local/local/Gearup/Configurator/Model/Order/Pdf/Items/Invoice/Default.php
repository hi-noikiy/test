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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento Module developed by EasyCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@easycommerce.cz so we can send you a copy immediately.
 * 
 * @copyright  Copyright (c) 2010 EasyCommerce (http://easycommerce.cz)
 * @category   EasyCommerce
 * @package    Easy_Invoice
 * @author     EasyCommerce <info@easycommerce.cz>
 */
class Gearup_Configurator_Model_Order_Pdf_Items_Invoice_Default extends Gearup_Configurator_Model_Order_Pdf_Items_Invoice_Default_Amasty_Pure {
    /**
     * Store model
     *
     * @var Varien_Object
     */

    /**
     * Draw item line
     *
     */
    public function draw() {
        $store = Mage::app()->getStore(); /* $this->getStore(); */
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = array();

        $fontSize = $page->getFontSize();
        $optionFontSize = $fontSize - 1;

        $show_sku = Mage::getStoreConfig('sales_pdf/invoice/show_sku', $store);
        $show_vat = Mage::getStoreConfig('sales_pdf/invoice/show_vat', $store);

        $novat_offset = $show_vat ? 0 : Easy_Invoice_Model_Sales_Order_Pdf_Invoice::NOVAT_BASE_OFFSET;

        // draw Product name
        $prname = Mage::helper('core/string')->str_split($item->getName(), $show_sku ? 70 : 65, true, true);
        $modname = html_entity_decode($prname[0]);
        $lines[0] = array(array(
                'text' => $modname,
                'feed' => 35,
                'font_size' => $fontSize
        ));

        if ($show_sku) {
            // draw SKU
            $lines[0][] = array(
                'text' => Mage::helper('core/string')->str_split($this->getSku($item), 25),
                'feed' => 300,
                'font_size' => $fontSize
            );
        }

        // draw Price
        $lines[0][] = array(
            'text' => ($item->getPrice())?$order->formatPriceTxt($item->getPriceInclTax()):'',
            'feed' => 405 + $novat_offset,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        );

        // draw QTY
        $lines[0][] = array(
            'text' => $item->getQty() * 1,
            'feed' => 435 + ($novat_offset * 2),
            'font_size' => $fontSize
        );

        if ($show_vat) {
            // draw DPH %
            if ($item instanceof Mage_Sales_Model_Order_Invoice_Item) {
                $percent = (float) $item->getOrderItem()->getTaxPercent();
                $lines[0][] = array(
                    'text' => (ceil($percent) != floor($percent) ? number_format($percent, 2) : round($percent)) . '%',
                    'feed' => 490,
                    'align' => 'right',
                    'font_size' => $fontSize
                );
            }
            // draw Tax
            /*$lines[0][] = array(
                'text' => $item->getTaxAmount()?$order->formatPriceTxt($item->getTaxAmount()):'',
                'feed' => 490,
                'font' => 'bold',
                'align' => 'right',
                'font_size' => $fontSize
            );*/
        }

        // draw Subtotal ($item->getRowTotalInclTax())?$order->formatPriceTxt($item->getRowTotalInclTax()):'',
        $lines[0][] = array(
            'text' => ($item->getRowTotal())?$order->formatPriceTxt($item->getRowTotalInclTax()):'',
            'feed' => 555,
            'font' => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                    'font_size' => $optionFontSize
                );

                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
                            'feed' => 40,
                            'font_size' => $optionFontSize
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines' => $lines,
            'height' => 12
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }

    /**
     * Return item Sku
     *
     * @param  $item
     * @return mixed
     */
    public function getSku($item) {

            if ($item instanceof Mage_Sales_Model_Order_Invoice_Item && $item->getOrderItem()->getProductOptionByCode('simple_sku'))
                return $item->getOrderItem()->getProductOptionByCode('simple_sku');
            else
                return $item->getSku();

    }

    /**
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions() {
        $result = array();
        // echo get_class($this->getItem());exit;  
        if ($this->getItem() instanceof Mage_Sales_Model_Order_Invoice_Item && $this->getItem()->getIsConfigurator() == false) {
            if ($options = $this->getItem()->getOrderItem()->getProductOptions()) {
                if (isset($options['options'])) {
                    $result = array_merge($result, $options['options']);
                }
                if (isset($options['additional_options'])) {
                    $result = array_merge($result, $options['additional_options']);
                }
                if (isset($options['attributes_info'])) {
                    $result = array_merge($result, $options['attributes_info']);
                }
            }
        }
        return $result;
    }

}
