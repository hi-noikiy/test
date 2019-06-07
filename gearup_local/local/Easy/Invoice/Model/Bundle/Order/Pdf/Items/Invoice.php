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
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Easy_Invoice_Model_Bundle_Order_Pdf_Items_Invoice extends Mage_Bundle_Model_Sales_Order_Pdf_Items_Abstract
{
    /**
     * Store model
     *
     * @var Varien_Object
     */
    protected $_store;     
   
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $store  = $this->getStore();
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $this->_setFontRegular();
        $items = $this->getChilds($item);

        $_prevOptionId = '';
        $drawItems = array();
        
        $show_sku = Mage::getStoreConfig('sales_pdf/invoice/show_sku', $store);
        $show_vat = Mage::getStoreConfig('sales_pdf/invoice/show_vat', $store);
        
        $novat_offset = $show_vat ? 0 : Easy_Invoice_Model_Sales_Order_Pdf_Invoice::NOVAT_BASE_OFFSET;
        
        $fontSize = $page->getFontSize();
        $optionFontSize = $fontSize-1;

        foreach ($items as $_item) {
            $line   = array();

            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            }
            else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => 12
                );
            }

            if ($_item->getOrderItem()->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => Mage::helper('core/string')->str_split($attributes['option_label'], $show_sku ? 40 : 65, true, true),
                        'feed'  => 35
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => 12
                    );

                    $line = array();

                    $_prevOptionId = $attributes['option_id'];
                }
            }

            /* in case Product name is longer than 80 chars - it is written in a few lines */
            if ($_item->getOrderItem()->getParentItem()) {
                $feed = 40;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = 35;
                $name = $_item->getName();
            }
            $line[] = array(
                'text'  => Mage::helper('core/string')->str_split($name, $show_sku ? 40 : 65, true, true),
                'feed'  => $feed,
                'font_size' => $fontSize
            );

            // draw SKUs
            if ($show_sku && !$_item->getOrderItem()->getParentItem()) {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($item->getSku(), 30) as $part) {
                    $text[] = $part;
                }
                $line[] = array(
                    'text'  => $text,
                    'feed'  => 200,
                    'font_size' => $fontSize
                );
            }

            // draw prices
            if ($this->canShowPriceInfo($_item)) {
                $price = $order->formatPriceTxt($_item->getPriceInclTax());
                $line[] = array(
                    'text'  => $price,
                    'feed'  => 345+$novat_offset,
                    'font'  => 'bold',
                    'align' => 'right',
                    'font_size' => $fontSize
                );
                $line[] = array(
                    'text'  => $_item->getQty()*1,
                    'feed'  => 380+($novat_offset*2),
                    'font'  => 'bold',
                    'font_size' => $fontSize
                );

                if ($show_vat) {                    
                    // draw DPH %
                    $percent = (float) $_item->getOrderItem()->getTaxPercent();
                    $line[] = array(
                        'text'  => (ceil($percent) != floor($percent) ? number_format($percent, 2) : round($percent)) . '%',
                        'feed'  => 435,
                        'align' => 'right',
                        'font'  => 'bold',
                        'font_size' => $fontSize
                    );
                   
                   // draw Tax                
                   $tax = $order->formatPriceTxt($_item->getTaxAmount());
                   $line[] = array(
                       'text'  => $tax,
                       'feed'  => 490,
                       'font'  => 'bold',
                       'align' => 'right',
                       'font_size' => $fontSize
                   );
                }
                
                $row_total = $order->formatPriceTxt($_item->getRowTotal());
                $line[] = array(
                    'text'  => $row_total,
                    'feed'  => 565,
                    'font'  => 'bold',
                    'align' => 'right',
                    'font_size' => $fontSize
                );
            }

            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $lines[][] = array(
                        'text'  => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                        'font'  => 'italic',
                        'feed'  => 35,
                        'font_size' => $optionFontSize
                    );

                    if ($option['value']) {
                        $text = array();
                        $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach (Mage::helper('core/string')->str_split($value, 50, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => 40,
                            'font_size' => $optionFontSize
                        );
                    }

                    $drawItems[] = array(
                        'lines'  => $lines,
                        'height' => 12
                    );
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));

        $this->setPage($page);
    }
    
    /**
     * Set store model
     *
     * @param Mage_Core_Model_Store $invoice
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->_store = $store;
        return $this;
    }
    
    /**
     * Retrieve store object
     *
     * @throws Mage_Core_Exception
     * @return Varien_Object
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            Mage::throwException(Mage::helper('sales')->__('Store object is not specified.'));
        }
        return $this->_store;
    }    
}