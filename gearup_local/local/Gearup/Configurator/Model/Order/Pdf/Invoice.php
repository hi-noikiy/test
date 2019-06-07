<?php
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

class Gearup_Configurator_Model_Order_Pdf_Invoice extends Gearup_Configurator_Model_Order_Pdf_Invoice_Amasty_Pure
{  
    protected $_invoice;
    protected $_store;
    protected $_order;
    protected $_accounts;
    
    public $x;
    public $y;
    
    const NOVAT_BASE_OFFSET = 35;
   
    public function getPdf($invoices = array())
    {       
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        /** @var Mage_Core_Helper_String $stringHelper */
        $stringHelper = Mage::helper('core/string');

        foreach ($invoices as $invoice) {
            $this->_invoice = $invoice;
            $this->_store   = $invoice->getStore();
           
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
            }
            
            $this->_order = $order = $invoice->getOrder();
            
            $page = $this->newPage( array(), $this->_store );
            $width  = $page->getWidth();  
            $height = $page->getHeight();               
                        
            /* Add info regions */
            $x = round($width/2) + 25; 
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->setLineWidth(0.5);  

            // dodavatel
            $page->drawRectangle(25, 800, $x, 690);

            // hlavni odberatel
            $page->setLineWidth(0.5);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            
            $font = $this->_setFontBold($page, 7);
            $page->drawText(Mage::helper('sales')->__('SUPPLIER'), 35, 785, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('CUSTOMER'), $x+10, 785, 'UTF-8');

            // IČ dodavatele
            $ic = Mage::getStoreConfig('sales_pdf/invoice/ic', $this->_store);
            if ($ic != '') {
               $fontSize = 8;
               $font = $this->_setFontRegular($page, $fontSize);
               $label = Mage::helper('sales')->__('Customer (I No.)') . ': ' . $ic;
               $page->drawText($label, $x - 25 - $this->widthForStringUsingFontSize($label, $font, $fontSize), 785, 'UTF-8');
            }
            // DIČ dodavatele            
            $dic = Mage::getStoreConfig('sales_pdf/invoice/taxvat', $this->_store);
            if ($dic != '') {
               $fontSize = 8;
               $font = $this->_setFontRegular($page, $fontSize);
               $label = Mage::helper('sales')->__('Tax I No.') . ': ' . $dic;
               $page->drawText($label, $x - 25 - $this->widthForStringUsingFontSize($label, $font, $fontSize), 775, 'UTF-8');
            }            

            $this->insertTaxInvoiceText($page, $order); // the custom text is added here
            /* Add logo */
            $this->y = 775;
            
            $is_logo = $this->insertLogo($page, $this->_store);            
            
            /* Add address */
            $this->insertAddress($page, $this->_store, $is_logo);
            
            /* Add bank account */
            //$this->y = 650;
            $this->insertBankAccount($page, $this->_store);

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));
                       
            /* Add table */            
            $this->y -= 20;
            
            /* Add table head */
            $this->insertTableHead($page, $this->_store);
            
            $this->_setFontRegular($page, 8);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));            
            
            /* Add body */
            $prevOptionId = '';
            foreach ($invoice->getAllItems() as $item){
                $attributes = $this->getSelectionAttributes($item);
                if (is_array($attributes)) {
                    $optionId = $attributes['option_id'];
                }
                else {
                    $optionId = 0;
                }

                if (!isset($drawItems[$optionId])) {
                    $drawItems[$optionId] = array(
                        'lines'  => array(),
                        'height' => 15
                    );
                }
                if ($item->getOrderItem()->getParentItem()) {
                    if ($prevOptionId != $attributes['option_id']) {
                        $line[0] = array(
                            'font' => 'italic',
                            'text' => $stringHelper->str_split($attributes['option_label'], 45, true, true),
                            'feed' => 35
                        );

                        $drawItems[$optionId] = array(
                            'lines'  => array($line),
                            'height' => 15
                        );

                        $line = array();

                        $prevOptionId = $attributes['option_id'];
                    }
                }

                /* in case Product name is longer than 80 chars - it is written in a few lines */
                if ($item->getOrderItem()->getParentItem()) {
                    $feed = 40;
                    $name = $this->getValueHtml($item);
                } else {
                    $feed = 35;
                    $name = $item->getName();
                }
                $line[] = array(
                    'text'  => $stringHelper->str_split($name, 35, true, true),
                    'feed'  => $feed
                );

                if ($this->y < 40) {
                    $page = $this->newPage(array('table_header' => true));
                }               
                 $configuratorRenderer = new Gearup_Configurator_Block_Adminhtml_Sales_Order_View_Items_Renderer();
                 $items = $configuratorRenderer->getChilds($item->getOrderItem());
                 if(count($items)>0){
                     $item->setIsConfigurator(true);
                        $itemQty = $item->getOrderItem()->getData('qty_ordered');
                       // $item->setQty(1);
                        //$getPrices = $configuratorRenderer->resetMainItemPrice($item->getOrderItem());
                        //$item->setRowTotalInclTax($getPrices[1]*$itemQty);
                        //$item->setPrice($getPrices[1]*$itemQty);
                     $i = 0;
                     $itemArray = array_merge( array($item), $items);
                    foreach($itemArray as $_item){
                        if($i>0){                            
                            $_item->setPrice(0);
                            //$_item->setRowTotalInclTax($_item->getFinalPrice()*1);  
                            $_item->setQty($itemQty);
                        }
                        $i++;
                       $page = $this->_drawItem($_item, $page, $order);                        
                    } 
                 }else{
                     $item->setIsConfigurator(false);                     
                    /* Draw item */
                    $page = $this->_drawItem($item, $page, $order);
                 }
            }
            
            $page->drawLine(25, $this->y, 570, $this->y);

            if ($this->y < 240) {
               $page = $this->newPage(array('table_header' => false));
            }
            
            /* Add totals */
            $page = $this->insertTotals($page, $invoice);
            /* Add stamp */
            $this->insertStamp($page, $this->_store);
            
            /* Add paging */
            $this->insertPaging();

            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }
        
        $this->_afterGetPdf();

        return $pdf;
    }

    /**
     * Retrieve Selection attributes
     *
     * @param Varien_Object $item
     * @return mixed
     */
    public function getSelectionAttributes($item)
    {
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    public function insertTaxInvoiceText($page, $order)
    {
        $page->drawLine(25, $this->y, 570, $this->y); 
        $this->y += 10;
        $fontSize = 14;
        $this->_setFontBold($page, $fontSize);
        
        $orderId = $order->getId();         
        $taxPercent = Mage::getModel('sales/order_tax')->getCollection()
                    ->addFieldToSelect('percent')
                    ->addFieldToFilter('order_id',$orderId)
                    ->getFirstItem();
        
        $rate = (float)$taxPercent['percent'];
        if($rate > 0){
            $page->drawText(Mage::helper('sales')->__('TAX INVOICE'), 26, $this->y, 'UTF-8');
        }
    }
}
