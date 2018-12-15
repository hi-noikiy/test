<?php

class Ktpl_Customreport_Model_Cimorder extends Mage_Core_Model_Abstract
{
    const CUSTOMER_RANDOM = null;
    protected $_shippingMethod = 'freeshipping_freeshipping';
    protected $_paymentMethod = 'cashondelivery';
    protected $_subTotal = 0;
    protected $_order;
    protected $_storeId = '0';

    public function _construct()
    {
        parent::_construct();
        $this->_init('customreport/cimorder');
    }

    public function setShippingMethod($methodName)
    {
        $this->_shippingMethod = $methodName;
    }
    public function setPaymentMethod($methodName)
    {
        $this->_paymentMethod = $methodName;
    }
    
    public function setCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer){
            $this->_customer = $customer;
        }
        if (is_numeric($customer)){
            $this->_customer = Mage::getModel('customer/customer')->load($customer);
        }
        else if ($customer === self::CUSTOMER_RANDOM){
            $customers = Mage::getResourceModel('customer/customer_collection');

            $customers
                ->getSelect()
                ->limit(1)
                ->order('RAND()');

            $id = $customers->getFirstItem()->getId();
            
            $this->_customer = Mage::getModel('customer/customer')->load($id);
        }
    }

    public function createOrder($products, $customerdata)
    {
        /*if (!($this->_customer instanceof Mage_Customer_Model_Customer)){
            $this->setCustomer(self::CUSTOMER_RANDOM);
        }*/

        $transaction = Mage::getModel('core/resource_transaction');
        //$this->_storeId = $this->_customer->getStoreId();
        $this->_storeId = Mage::app()->getStore()->getId();
        $reservedOrderId = Mage::getSingleton('eav/config')
            ->getEntityType('order')
            ->fetchNewIncrementId($this->_storeId);

        $currencyCode  = Mage::app()->getBaseCurrencyCode();
        $this->_order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($this->_storeId)
            ->setQuoteId(0)
            ->setGlobalCurrencyCode($currencyCode)
            ->setBaseCurrencyCode($currencyCode)
            ->setStoreCurrencyCode($currencyCode)
            ->setOrderCurrencyCode($currencyCode);


        $this->_order->setCustomerEmail($customerdata['email'])
            ->setCustomerFirstname($customerdata['firstname'])
            ->setCustomerLastname($customerdata['lastname'])
            ->setCustomerIsGuest(1);

        $street = array(
            '0' => $customerdata['street1'],
            '1' => $customerdata['street2'],
        );

        //$billing = $this->_customer->getDefaultBillingAddress();
        $billingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($this->_storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
            ->setFirstname($customerdata['firstname'])
            ->setMiddlename('')
            ->setLastname($customerdata['lastname'])
            ->setStreet($street)
            ->setCity($customerdata['city'])
            ->setCountryId($customerdata['country_id'])
            ->setRegion('')
            ->setRegionId('')
            ->setPostcode('')
            ->setTelephone($customerdata['telephone'])
            ->setFax('')
            ->setVatId('');

        
        $this->_order->setBillingAddress($billingAddress);

        $shippingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($this->_storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
            ->setFirstname($customerdata['firstname'])
            ->setMiddlename('')
            ->setLastname($customerdata['lastname'])
            ->setStreet($street)
            ->setCity($customerdata['city'])
            ->setCountryId($customerdata['country_id'])
            ->setRegion('')
            ->setRegionId('')
            ->setPostcode('')
            ->setTelephone($customerdata['telephone'])
            ->setFax('')
            ->setVatId('');

        $this->_order->setShippingAddress($shippingAddress)->setShippingMethod($this->_shippingMethod);
        
        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($this->_storeId)
            ->setCustomerPaymentId(0)
            ->setMethod($this->_paymentMethod);

        $this->_order->setPayment($orderPayment);
        
        $itempro = $this->_addProduct($products);

        $this->_order->setSubtotal($this->_subTotal)
            ->setBaseSubtotal($this->_subTotal)
            ->setGrandTotal($this->_subTotal)
            ->setBaseGrandTotal($this->_subTotal);
        $this->_order->setIscimorder(1);
        $transaction->addObject($this->_order);
        $transaction->addCommitCallback(array($this->_order, 'place'));
        $transaction->addCommitCallback(array($this->_order, 'save'));
        $transaction->save();  

        //$this->_order->getSendConfirmation(null);
        //$this->_order->sendNewOrderEmail(); 
        
        
        //$this->_order->addStatusHistoryComment($customerdata['cimcomment']);
        $this->_order->save();
        Mage::getSingleton('core/session')->setLastcimOrderId($this->_order->getIncrementId());

        $template_id = "cimorder_email_guest";
        /*$emailTemplate = Mage::getModel('core/email_template')->loadDefault($template_id);
        $emailvars = array();
        $emailvars['order'] = $this->_order;
        $emailvars['billing'] = $this->_order->getBillingAddress();
        $emailvars['payment_html'] = "<b>Credit Application</b>";
        $emailvars['productname'] = $itempro['product_name'];
        $emailvars['productsku'] = $itempro['sku'];
        $emailvars['installments'] = $customerdata['installments'];
        $emailvars['monthlyprice'] = $customerdata['monthlyprice'];*/


        //$emailTemplate = Mage::getModel('core/email_template')->loadDefault($template_id);
        $sender = array('name' => 'Priceguru.mu', 'email' => 'credit@priceguru.mu');
        //recepient
        $email = $this->_order->getCustomerEmail();
        $emailName = $this->_order->getCustomerName();
        $vars = array();
        $vars = array(
            'order' => $this->_order, 
            'billing' => $this->_order->getBillingAddress(),
            'payment_html' => "<b>Credit Application</b>",
            'productname' => $itempro['product_name'],
            'productsku' => $itempro['sku'],
            'installments' => $customerdata['installments'], 
            'monthlyprice' => $customerdata['monthlyprice']
        );

        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        try {
            Mage::getModel('core/email_template')->setReplyTo('credit@priceguru.mu')->sendTransactional($template_id, $sender, $email, $emailName, $vars, $storeId);
            $translate->setTranslateInline(true);
            
        } catch(Exception $ex) {
        }

        /*$processedTemplate = $emailTemplate->getProcessedTemplate($emailvars);
        $mail = new Zend_Mail('UTF-8');     
        
        $mail->setFrom('info@priceguru.mu', "Priceguru.mu");
        $mail->setReplyTo('info@priceguru.mu', 'Priceguru.mu');
        $mail->addHeader('MIME-Version', '1.0');
        $mail->addHeader('Content-Transfer-Encoding', '8bit');
        $mail->addHeader('X-Mailer:', 'PHP/'.phpversion());
        $mail->setBodyHtml($processedTemplate);
        //$mail->addBcc('priceguru.sales@gmail.com');
        $mail->addTo($this->_order->getCustomerEmail(), $this->_order->getCustomerName());
        $mail->setSubject('Credit application order');

        try {
            $mail->send();
        }
        catch(Exception $ex) {
            //echo $ex->getMessage(); exit;
        }*/

        /* Subscribe to newsletter */
        $emailExist = Mage::getModel('newsletter/subscriber')->load($customerdata['email'], 'subscriber_email');
        if (!$emailExist->getId()) {
            Mage::getModel('newsletter/subscriber')->subscribe($customerdata['email']);
        }

        /* Start log entry in CIM order grid for backend */
        $productdata = Mage::getSingleton('core/session')->getCheckoutcredit();
        $value_title = "";
        foreach($productdata['optselects'] as $opt) {
            $value_title = $opt['value_title'];
        }

        $salescimorder = Mage::getModel('customreport/salescimorder');
        $salescimorder->setOrderId($this->_order->getIncrementId());
        $salescimorder->setCustomerName($this->_order->getCustomerFirstname()." ".$this->_order->getCustomerLastname());
        $salescimorder->setTelephone($customerdata['telephone']);
        $salescimorder->setEmail($customerdata['email']);
        $salescimorder->setIscimcustomer($customerdata['iscimcustomer']);
        $salescimorder->setProductName($itempro['product_name']);
        $salescimorder->setSku($itempro['sku']);
        $salescimorder->setAttributes($value_title);
        $salescimorder->setInstallments($customerdata['installments']);
        $salescimorder->setMonthly($customerdata['monthlyprice']);
        $salescimorder->setCimcomment($customerdata['cimcomment']);
        //$salescimorder->setStatus($this->_order->getStatus());
        $salescimorder->save();

    }

    protected function _addProduct($requestData)
    {
        $request = new Varien_Object();
        $request->setData($requestData);

        $product = Mage::getModel('catalog/product')->load($request['product']);

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product);

        if (is_string($cartCandidates)) {
            throw new Exception($cartCandidates);
        }

        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($cartCandidates as $candidate) {
            $item = $this->_productToOrderItem($candidate, $candidate->getCartQty());

            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }
            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->setQty($item->getQty() + $candidate->getCartQty());

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) { // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

        foreach ($items as $item){
            $this->_order->addItem($item);
        }

        return array(
            'product_name' => $product->getName(),
            'sku' => $product->getSku()
        );
    }

    function _productToOrderItem(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $rowTotal = $product->getFinalPrice() * $qty;

        $options = $product->getCustomOptions();

        $optionsByCode = array();

        foreach ($options as $option)
        {
            $quoteOption = Mage::getModel('sales/quote_item_option')->setData($option->getData())
                ->setProduct($option->getProduct());

            $optionsByCode[$quoteOption->getCode()] = $quoteOption;
        }

        $product->setCustomOptions($optionsByCode);

        $options = $product->getTypeInstance(true)->getOrderOptions($product);

        $orderItem = Mage::getModel('sales/order_item')
            ->setStoreId($this->_storeId)
            ->setQuoteItemId(0)
            ->setQuoteParentItemId(NULL)
            ->setProductId($product->getId())
            ->setProductType($product->getTypeId())
            ->setQtyBackordered(NULL)
            ->setTotalQtyOrdered($product['rqty'])
            ->setQtyOrdered($product['qty'])
            ->setName($product->getName())
            ->setSku($product->getSku())
            ->setPrice($product->getFinalPrice())
            ->setBasePrice($product->getFinalPrice())
            ->setOriginalPrice($product->getFinalPrice())
            ->setRowTotal($rowTotal)
            ->setBaseRowTotal($rowTotal)

            ->setWeeeTaxApplied(serialize(array()))
            ->setBaseWeeeTaxDisposition(0)
            ->setWeeeTaxDisposition(0)
            ->setBaseWeeeTaxRowDisposition(0)
            ->setWeeeTaxRowDisposition(0)
            ->setBaseWeeeTaxAppliedAmount(0)
            ->setBaseWeeeTaxAppliedRowAmount(0)
            ->setWeeeTaxAppliedAmount(0)
            ->setWeeeTaxAppliedRowAmount(0)

            ->setProductOptions($options);

        $this->_subTotal += $rowTotal;

        return $orderItem;
    }
}
