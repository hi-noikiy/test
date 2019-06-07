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

class Easy_Invoice_Model_Sales_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
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
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y < 40) {
                    $page = $this->newPage(array('table_header' => true));
                }               
                 $configuratorRenderer = new Gearup_Configurator_Block_Adminhtml_Sales_Order_View_Items_Renderer();
                 $items = $configuratorRenderer->getChilds($item->getOrderItem());
                 if(count($items)>0){
                     $itemArray = array_merge( array($item), $items);
                    foreach($itemArray as $_item){
                       $page = $this->_drawItem($_item, $page, $order);                        
                    } 
                 }else{
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
     * Draw Item process
     *
     * @param Varien_Object $item
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     * @return Zend_Pdf_Page
     */
    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
    {
        $type = $item->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setStore($this->_store);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }    
    
    protected function insertPaging() {
      $fontSize = 7;

      $pages = sizeof($this->_getPdf()->pages);
      foreach ($this->_getPdf()->pages as $i => $page) {
         $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
         $font = $this->_setFontRegular($page, $fontSize);
         
         $width = $page->getWidth();
         $paging =  Mage::helper('sales')->__('Page %d / %d', $i+1, $pages);
         $page->drawText($paging, $width - 30 - $this->widthForStringUsingFontSize($paging, $font, $fontSize), 25, 'UTF-8');                        
      }
    }
    
    protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales_pdf/invoice/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (strpos($image, "{{root_dir}}") !== false) {
                $image = str_replace("{{root_dir}}", Mage::getBaseDir(), $image);
            }
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 35, $this->y - 75, 135, $this->y);
                $this->y -= 75;
                return true;
            }
        }
        
        return false;
    }
    
    protected function insertStamp(&$page, $store = null)
    {       
        $image = Mage::getStoreConfig('sales_pdf/invoice/stamp', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/stamp/' . $image;
            if (strpos($image, "{{root_dir}}") !== false) {
                $image = str_replace("{{root_dir}}", Mage::getBaseDir(), $image);
            }
            if (is_file($image)) {
                $this->y -= 10;
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 395, $this->y - 120, 555, $this->y);
                $this->y -= 120;
                return true;
            }
        }
        
        return false;
    }      

    protected function insertAddress(&$page, $store = null, $is_logo = false)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0);        

        $address = Mage::getStoreConfig('sales_pdf/invoice/address', $store);
        if (empty($address)) {           
           $address = Mage::getStoreConfig('sales/identity/address', $store);
        }           
        if (empty($address)) {           
           $address .= Mage::helper('sales')->__('Supplier data not filled.');
        }
        
        $address = array_slice(explode("\n", $address), 0, 9);        
        
        $this->x = 145;
        $this->y = 770;    
        $this->_setFontBold($page, 10);

        // nazev dodavatele
        $value = array_shift($address);  
        $page->drawText(trim(strip_tags($value)), $this->x, $this->y, 'UTF-8');
                      
        if ($is_logo) {
           $this->x = 145;
        }
        $this->y = 760;
        $this->_setFontRegular($page, 9);
        
        foreach ($address as $value){
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), $this->x, $this->y, 'UTF-8');
                $this->y -=10;
            }
        }
    }

    protected function getBankPayment()
    {
        if (!$this->_accounts) {
            $accounts = unserialize(Mage::getStoreConfig('payment/bankpayment/bank_accounts', $this->_store->getStoreId()));

            $this->_accounts = array();
            $fields = is_array($accounts) ? array_keys($accounts) : null;
            if (!empty($fields)) {
                foreach ($accounts[$fields[0]] as $i => $k) {
                    if ($k) {
                        $account = new Varien_Object();
                        foreach ($fields as $field) {
                            $account->setData($field,$accounts[$field][$i]);
                        }
                        $this->_accounts[] = $account;
                    }
                }
            }
        }
        return $this->_accounts;
    }

    protected function insertBankAccount(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 9);
                      
        $page->setLineWidth(0);
        $this->x -= 110;
        $x2 = $this->x + 120;
        $this->y -= 10;

        // Native Magento bank account:
        $account_no = Mage::getStoreConfig('sales_pdf/invoice/bank_account_no', $store);
        if (empty($account_no)) {
            return;
        }

        $page->drawText(Mage::helper('sales')->__('Account Number: '), $this->x, $this->y, 'UTF-8');
        $page->drawText(trim(strip_tags($account_no)), $x2, $this->y, 'UTF-8');

        $sort_code = Mage::getStoreConfig('sales_pdf/invoice/bank_sort_code', $store);
        if (!empty($sort_code)) {
           $this->y -= 10;
           $page->drawText(Mage::helper('sales')->__('Bank code: '), $this->x, $this->y, 'UTF-8');
           $page->drawText(trim(strip_tags($sort_code)), $x2, $this->y, 'UTF-8');
        }

        $iban = Mage::getStoreConfig('sales_pdf/invoice/bank_iban', $store);
        if (!empty($iban)) {
           $this->y -= 10;
           $page->drawText(Mage::helper('sales')->__('IBAN: '), $this->x, $this->y, 'UTF-8');
           $page->drawText(trim(strip_tags(str_replace(' ', '', $iban))), $x2, $this->y, 'UTF-8');
        }

        $swift = Mage::getStoreConfig('sales_pdf/invoice/bank_swift', $store);
        if (!empty($swift)) {
           $this->y -= 10;
           $page->drawText(Mage::helper('sales')->__('SWIFT: '), $this->x, $this->y, 'UTF-8');
           $page->drawText(trim(strip_tags($swift)), $x2, $this->y, 'UTF-8');
        }

    }
        
    protected function insertTableHead(&$page, $store = null)
    {
        $this->y -=10;

        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);

        $page->drawLine(25, $this->y-5, 570, $this->y-5);

        $this->_setFontBold($page, 8);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->drawText(Mage::helper('sales')->__('Product'), 35, $this->y, 'UTF-8');

        if (Mage::getStoreConfig('sales_pdf/invoice/show_sku', $store)) {
         $page->drawText(Mage::helper('sales')->__('SKU'), 300, $this->y, 'UTF-8');
        }

        $show_vat = Mage::getStoreConfig('sales_pdf/invoice/show_vat', $store);
        $novat_offset = $show_vat ? 0 : self::NOVAT_BASE_OFFSET;

        $page->drawText(Mage::helper('sales')->__('Price'), 370+$novat_offset, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('QTY'), 430+($novat_offset*2), $this->y, 'UTF-8');
        if ($show_vat) {
         $page->drawText(Mage::helper('sales')->__('VAT %'), 475, $this->y, 'UTF-8');
         //$page->drawText(Mage::helper('sales')->__('Vat'), 465, $this->y, 'UTF-8');
        }
        $page->drawText(Mage::helper('sales')->__('Subtotal'), 520, $this->y, 'UTF-8');

        $this->y -=15;
    }    
   
    /**
     * @parent class  Mage_Sales_Model_Order_Pdf_Abstract
     * 
     * @param $page
     * @param Mage_Sales_Model_Order $order 
     * @param boolean $putOrderId
     */
    protected function insertOrder(&$page, $order, $putOrderId = true)
    {
        $width  = $page->getWidth();  
        $height = $page->getHeight();               
       
        /* Easy_IcDic */
        $ic  = $order->getData('customer_ic');
        $dic = $order->getData('customer_taxvat');
        $fontSize = 8;
        $font = $this->_setFontBold($page, $fontSize);
        
        if (!empty($ic)) {
            $label = Mage::helper('sales')->__('Customer (I No.)') . ': ' . $ic;
            $page->drawText($label, $width - 40 - $this->widthForStringUsingFontSize($label, $font, $fontSize), 785, 'UTF-8');
        }
        
        if (!empty($dic)) {            
            $label = Mage::helper('sales')->__('Tax I No.') . ': ' . $dic;
            $x = round($width/2); 
            $page->drawText($label, $width - 40 - $this->widthForStringUsingFontSize($label, $font, $fontSize), 775, 'UTF-8');
        }
        /* Easy_IcDic */
        
        $fontSize = 10;
                           
        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
                         
        $x = round($width/2) + 35;
        $this->y = 770;
        
        $this->_setFontBold($page, 10);
        $value = array_shift($billingAddress);
        if ($value!=='') {
           $page->drawText(strip_tags(ltrim($value)), $x, $this->y, 'UTF-8');
           $this->y -= 15;
        }

        $this->_setFontRegular($page, 9);
        foreach ($billingAddress as $value){
            if ($value!=='') {//Mage::log($value,null,"inv.log",true);
                $page->drawText(strip_tags(ltrim($value)), $x, $this->y, 'UTF-8');
                $this->y -=10;
            }
        }

        $page->setLineWidth(0.5);
        $page->drawLine(round($width/2)+25, $this->y, $width-25, $this->y);

        // Draw rectangle by using lines:
        $page->setLineWidth(1.5);
        $page->drawLine(round($width/2)+25, 800, $width-25, 800);
        $page->drawLine(round($width/2)+25, $this->y, $width-25, $this->y);
        $page->drawLine(round($width/2)+25, 800, round($width/2)+25, $this->y);
        $page->drawLine($width-25, 800, $width-25, $this->y);

        $page->setLineWidth(0.5);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
            
            $this->y = 655;
            $font = $this->_setFontBold($page, 7);
            $page->drawText(Mage::helper('sales')->__('BENEFICIARY'), $x, $this->y, 'UTF-8');            
                        
            $this->_setFontRegular($page, 8);            
            $this->y -= 15;
            
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $page->drawText(strip_tags(ltrim($value)), $x, $this->y, 'UTF-8');
                    $this->y -=10;
                }
            }

            $page->drawLine(round($width/2)+25, $this->y, $width - 25, $this->y);
        }      
        
        $fontSize = 8;
        $this->_setFontRegular($page, $fontSize); 
        
        $x2 = $x + 120;

        $this->y-=10;

        if ($putOrderId) {
            $this->y -= 10;
            $page->drawText(Mage::helper('sales')->__('Order #'), $x, $this->y, 'UTF-8');
            $page->drawText($order->getRealOrderId(), $x2, $this->y, 'UTF-8');
        }
        
        $this->y -= 10;
        $page->drawText(Mage::helper('sales')->__('Order Date: '), $x, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), $x2, $this->y, 'UTF-8');
        
        $issue_date = $this->_invoice->getCreatedAtStoreDate();
        $this->y -= 10;
        $page->drawText(Mage::helper('sales')->__('Date of Issue: '), $x, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('core')->formatDate($issue_date, 'medium', false), $x2, $this->y, 'UTF-8');
        
        $orderId = $order->getId();         
        $taxPercent = Mage::getModel('sales/order_tax')->getCollection()
                    ->addFieldToSelect('percent')
                    ->addFieldToFilter('order_id',$orderId)
                    ->getFirstItem();
        
        $rate = (float)$taxPercent['percent'];
        if($rate > 0){
            $this->y -= 10;
            $page->drawText(Mage::helper('sales')->__('TRN Number: '), $x, $this->y, 'UTF-8');
            $page->drawText('100046263800003', $x2, $this->y, 'UTF-8');
            $this->_setFontRegular($page, $fontSize);
        }
        
        /*$due = Mage::getStoreConfig('sales_pdf/invoice/due_date', $this->_invoice->getStore());
        if ($due !== '') {  
           $days = (int) $due;
                    
           if (empty($days)) {
              $days = 14;
           }
           $due_date = strtotime($issue_date);       
           if ($days > 0) {       
              $due_date = strtotime("+{$days} days", $due_date);
           }        
           $due_date = Mage::app()->getLocale()->date($due_date, null, null, true);
           $this->_setFontBold($page, $fontSize);
           
           $this->y -= 10;
           $page->drawText(Mage::helper('sales')->__('Due date: '), $x, $this->y, 'UTF-8');
           $page->drawText(Mage::helper('core')->formatDate($due_date, 'medium', false), $x2, $this->y, 'UTF-8');
           $this->_setFontRegular($page, $fontSize);
        }*/           
                
        $duzp = Mage::getStoreConfig('sales_pdf/invoice/duzp', $this->_invoice->getStore());
        if ($duzp !== '') {
           $days = (int) $duzp;
           
           if (empty($days)) {
              $days = 0;
           }
           
           $duzp_date = strtotime($issue_date);
           if ($days > 0) {       
              $duzp_date = strtotime("+{$days} days", $duzp_date);
           }
           $duzp_date = Mage::app()->getLocale()->date($duzp_date, null, null, true);
           $this->y -= 10;
           $page->drawText(Mage::helper('sales')->__('Date of taxable transactions: '), $x, $this->y, 'UTF-8');
           $page->drawText(Mage::helper('core')->formatDate($duzp_date, 'medium', false), $x2, $this->y, 'UTF-8');           
        }
        
        $this->y -= 10;

        /* Payment */
        $orderPayment = $order->getPayment();

        /* Fix - 5217 - Fooman - Xero connector - Invoice PDF does not display payment method */ 
        // $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
        //     ->setIsSecureMode(true)
        //     ->toPdf();
        // $payment = explode('{{pdf_row_separator}}', $paymentInfo);        
        // foreach ($payment as $key=>$value){
        //     if (strip_tags(trim($value))==''){
        //         unset($payment[$key]);
        //     }
        // }
        // reset($payment);
        
        $x  = 35;
        $x2 = $x + 100;
        $y = 670;

        if (!$order->getIsVirtual()) {
            $this->_setFontRegular($page, $fontSize);
            $page->drawText(Mage::helper('sales')->__('Shipping Method: '), $x, $y, 'UTF-8');
            $shipping = $order->getShippingDescription();
            $shipping = wordwrap($shipping, 50, "\n");

            foreach(explode("\n", $shipping) as $textLine){
                if ($textLine!=='') {
                    $page->drawText(strip_tags(ltrim($textLine)), $x2, $y, 'UTF-8');
                    $y -=10;
                }
            }
        }

        $this->_setFontRegular($page, $fontSize);
        $page->drawText(Mage::helper('sales')->__('Payment method: '), $x, $y, 'UTF-8');
        
        $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
        $page->drawText(strip_tags(ltrim($paymentMethod)), $x2, $y, 'UTF-8');

        /* Fix - 5217 - Fooman - Xero connector - Invoice PDF does not display payment method */ 
        // $value = strip_tags(trim(array_shift($payment)));
        // if ($value!=='') {           
        //    $value = wordwrap($value, 50, "\n");
           
        //    foreach(explode("\n", $value) as $textLine){
        //       if ($textLine!=='') {
        //         $page->drawText(strip_tags(ltrim($textLine)), $x2, $y, 'UTF-8');
        //         $y -=10;
        //       }
        //    }
        // }

        $y -= 10;

        if ($orderPayment->getMethod() === 'bankpayment') {
            // Phoenix BankPayment active:
            $accounts = $this->getBankPayment();
            $account = array_shift($accounts);
            $fields = array(
                'account_holder'            => 'Beneficiary Name',
                'account_number'            => 'Beneficiary Account Nr',
                'iban'                      => 'Beneficiary IBAN',
                'account_holder_address'    => 'Beneficiary Address',
                'bank_name'                 => 'Beneficiary Bank Name',
                'bank_address'              => 'Beneficiary Bank Address',
                'swift'                     => 'SWIFT Code'
            );

            foreach ($fields as $field => $label) {
                $value = $account->getData($field);
                if (!empty($value)) {
                    $page->drawText(Mage::helper('sales')->__($label . ': '), $x, $y, 'UTF-8');
                    $page->drawText(trim(strip_tags($value)), $x2, $y, 'UTF-8');
                    $y -= 10;
                }
            }

            $this->_setFontBold($page, $fontSize);
            $page->drawText(Mage::helper('sales')->__('Reference: '), $x, $y, 'UTF-8');
            $vs_invoice_id = Mage::getStoreConfig('sales_pdf/invoice/vs_invoice_id', $this->_invoice->getStore());
            $vs = ($vs_invoice_id ? $this->_invoice->getIncrementId() : $order->getRealOrderId());
            $page->drawText($vs, $x2, $y, 'UTF-8');
            $y -= 10;
        }

        $this->y = min($this->y, $y);
        
        $x = round($width/2) + 25;
        $page->drawLine($x, 800, $x, $this->y);
        $page->drawLine(25, $this->y, $width - 25, $this->y);
    }
   
    
    protected function _getTotalsList($source)
    {
        
        $totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();   

        foreach (array_keys($totals) as $totalCode) {
           $sort_order = Mage::getStoreConfig('sales/totals_sort/'.$totalCode);
           if ($sort_order) {
              $totals[$totalCode]['sort_order'] = $sort_order;
           }
        }
        
        usort($totals, array($this, '_sortTotalsList'));

        return $totals;
    }    
    
    protected function insertTotals($page, $source){
        /** @var Mage_Sales_Model_Order $order */
        $order = $source->getOrder();

        $totals = $this->_getTotalsList($source);
        
        $this->y -= 20;
        
        $start_y = $this->y+10;     
        
        $lineBlock = array(
            'lines'  => array(),
            'height' => 15
        );
                
        $orderId = $order->getId();         
        $taxPercent = Mage::getModel('sales/order_tax')->getCollection()
                    ->addFieldToSelect('percent')
                    ->addFieldToFilter('order_id',$orderId)
                    ->getFirstItem();
        
        $rate = (float)$taxPercent['percent'];
        
        $before_last = null;
        foreach ($totals as $total) {
            $amount = $source->getDataUsingMethod($total['source_field']);
            $displayZero = (isset($total['display_zero']) ? $total['display_zero'] : 0);

            if ($amount != 0 || $displayZero) {
                $amount = $order->formatPriceTxt($amount);

                if (isset($total['amount_prefix']) && $total['amount_prefix']) {
                    $amount = "{$total['amount_prefix']}{$amount}";
                }

                //$fontSize = (isset($total['font_size']) ? $total['font_size'] : 8);
                $fontSize = 9;
                if(isset($total['source_field']) && $total['source_field'] == 'tax_amount' ){
                    $tlabel = Mage::helper('sales')->__('VAT amount ('. $rate .'%)') . ':';
                    $am = $amount; continue;
                }else{
                    $label = Mage::helper('sales')->__($total['title']) . ':';
                }

                if(isset($total['source_field']) && $total['source_field'] == 'grand_total' ){
                    $inclLabel = ((float)$order->getTaxAmount()) ? ' (VAT inclusive)' : '';
                    $label = Mage::helper('sales')->__('Grand Total' . $inclLabel) . ':';
                }

                if ($total['source_field'] != 'grand_total') {
                   $before_last = sizeof($lineBlock['lines']);
                }

                $is_last = ($total['source_field'] == 'grand_total');                
                
                $lineBlock['lines'][] = array(
                    array(
                        'text'      => $label,
                        'feed'      => 495,
                        'align'     => 'right',
                        'font_size' => $fontSize + ($is_last ? 1 : 0),
                        'font'      => ($is_last ? 'bold' : null),
                    ),
                    array(
                        'text'      => $amount,
                        'feed'      => 565,
                        'align'     => 'right',
                        'font_size' => $fontSize + ($is_last ? 1 : 0),
                        'font'      => ($is_last ? 'bold' : null),
                    ),
                );

                if($total['source_field'] == 'grand_total' ){
                    //$before_last = sizeof($lineBlock['lines']);
                    $lineBlock['lines'][] = array(
                        array(
                            'text'      => $tlabel,
                            'feed'      => 495,
                            'align'     => 'right',
                            'font_size' => $fontSize-1,
                            'font'      => null,
                        ),
                        array(
                            'text'      => $am,
                            'feed'      => 565,
                            'align'     => 'right',
                            'font_size' => $fontSize-1,
                            'font'      => null,
                        ),
                    );
                }
            }
        }
        if ($before_last !== null) {
           $lineBlock['lines'][$before_last][0]['height'] = 20;
        }        

        $page = $this->drawLineBlocks($page, array($lineBlock));
        
        $x1 = 378;
        $x2 = $page->getWidth() - 25;
        $this->y += 8;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawLine($x1, $start_y, $x2, $start_y);
        $page->drawLine($x1, $start_y, $x1, $this->y);
        $page->drawLine($x1, $this->y, $x2, $this->y);
        
        $y2 = $this->y+20;
        $page->setLineWidth(2);
        $page->drawLine($x1, $y2-20, $x2, $y2-20);
        /*$page->drawLine($x1, $y2, $x1, $this->y);
        $page->drawLine($x2, $y2, $x2, $this->y);
        $page->drawLine($x1, $this->y, $x2, $this->y);*/
        $page->drawLine($x1, $y2-20, $x1, $this->y+35);
        $page->drawLine($x2, $y2-20, $x2, $this->y+35);
        $page->drawLine($x1, $this->y+35, $x2, $this->y+35);
        
        return $page;
    }
    
    
    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array(), $store = null)
    {
        /* Add new page */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;       
       
        $width = $page->getWidth();
        
        /* Add invoice number */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));            
        $fontSize = 14;
        $font = $this->_setFontBold($page, $fontSize);
        
        $replace_invoice_id = Mage::getStoreConfig('sales_pdf/invoice/replace_invoice_id', $this->_invoice->getStore());
        $invoice_id = ($replace_invoice_id ? $this->_order->getRealOrderId() : $this->_invoice->getIncrementId());
        
        $show_vat = Mage::getStoreConfig('sales_pdf/invoice/show_vat', $store);
        if ($show_vat) {
           $label = Mage::helper('sales')->__('Invoice #') . $invoice_id;
        } else {
           $label = Mage::helper('sales')->__('Invoice #') . $invoice_id;
        }
        $page->drawText($label, $width - 25 - $this->widthForStringUsingFontSize($label, $font, $fontSize), 810, 'UTF-8');       

        /* Add page border */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineWidth(0.5);  
        // ohraniceni stranky
        $page->drawRectangle(25, 800, $width - 25, 35);
        
        /* Add footer */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 7);
        $issuer = Mage::getStoreConfig('sales_pdf/invoice/issuer', $this->_store);
        $created_by = Mage::helper('sales')->__('Gear-up.me is a registered trademark of Orynx General Tarding LLC. Thank you for your business.');
        if (!empty($issuer)) {      
           $created_by = Mage::helper('sales')->__('Issued by: ') . $issuer . '   |   '. $created_by;
        }
        $page->drawText($created_by, 30, 25, 'UTF-8');        
        
        /* Add new table head */        
        if (!empty($settings['table_header'])) {
            $this->insertTableHead($page, $store);            
        }

        $this->_setFontRegular($page, 8);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));          
        
        return $page;
    }    
    
    protected function _setFontRegular($object, $size = 7)
    {
        //$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/Cantarell-Regular.ttf');
       // $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/LiberationSans-Regular.ttf');
         $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/BahijASVCodar-Bold.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        //$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/Cantarell-Bold.ttf');
        //$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/LiberationSans-Bold.ttf');
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/BahijASVCodar-Bold.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 7)
    {       
        //$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/Cantarell-Oblique.ttf');
        //$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/LiberationSans-Italic.ttf');
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/FreeSansFonts/BahijASVCodar-Bold.ttf');
        $object->setFont($font, $size);
        return $font;
    }    
}
