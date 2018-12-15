<?php
require_once(Mage::getBaseDir('lib') . '/MPDF60/mpdf.php');
class EM_Onestepcheckout_Adminhtml_CimcreditorderController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/onestepcheckout')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'cimcreditorder.csv';
        $content = $this->getLayout()->createBlock('onestepcheckout/adminhtml_cimcreditorder_export')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'cimcreditorder.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_cimcreditorder_export')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function updateRowFieldsAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        if($this->getRequest()->getParam('dcp') == "") { 
            $dcp == NULL; 
        } else { $dcp = $this->getRequest()->getParam('dcp'); }
        
        if($this->getRequest()->getParam('deposit') == "") { 
            $deposit == NULL; 
        } else { $deposit = $this->getRequest()->getParam('deposit'); }
        
        if ($fieldId) {
            $model = Mage::getModel('onestepcheckout/salescimorder')->load($fieldId);
            $model->setDcp($dcp);
            $model->setInstallments($this->getRequest()->getParam('installments'));
            $model->setMonthly($this->getRequest()->getParam('monthly'));
            $model->setDeposit($deposit);
            $model->setCpp($this->getRequest()->getParam('cpp'));
            $model->setPayment($this->getRequest()->getParam('payment'));
            $model->setApp_number($this->getRequest()->getParam('appnumber'));
            $model->save();

            if($deposit != "" && $deposit > 0) {
                $delorder = Mage::getModel('onestepcheckout/salesdeliveryorder')->load($model->getOrderId(), 'order_id');
                //echo "<pre>"; print_r($delorder->getData()); 
                //if($delorder->getData()) {
                $delorder->setOrderId($model->getOrderId());
                $delorder->setDeposit($deposit);
                $delorder->save();
                //}
            }
        }
    }

    //Create shipment action 
    public function createshipmentAction() {
        $data = $this->getRequest()->getParams();
        
        $result = array();
        foreach($data['order_ids'] as $order_id) {
            $order = Mage::getModel('sales/order')->load($order_id);
            //$email = true; $addComment=false; $comment="";
            if ($order->canShip()) {
                $qty = array();
                foreach($order->getAllVisibleItems() as $item){
                    $qty[$item->getId()] = $item->getQtyToShip();
                }
                //echo "<pre>"; print_r($qty); die();
                //initialize shipment object
                $shipment = $order->prepareShipment($qty);
                if ($shipment) {
                    $shipment->register();
                    //$shipment->addComment($comment, $email && $addComment);
                    $shipment->sendEmail(false)
                        ->setEmailSent(false)
                        ->save();

                    $shipment->getOrder()->setIsInProcess(true);
                    try {
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($shipment)
                            ->addObject($shipment->getOrder())
                            ->save();
                        $shipment->sendEmail($email, ($addComment ? $comment : ''));
                    } catch (Mage_Core_Exception $e) {
                        //Mage::log("Errror While Creating Shipment...".$e->getMessage());
                        $result['error'] = true;
                        $result['error_message'] = $e->getMessage();
                    }
 
                }
            }
        }
        if($result['error']) {
            Mage::getSingleton('adminhtml/session')->addError($result['error_message']);
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('Shipment created successfully.'));
        }
        $this->_redirect('adminhtml/sales_order/index');
    }

    public function sendemailAction() {
        //echo "<per>"; print_r($this->getRequest()->getParams()); exit;
        $result = array();
        $cimemail = $this->getRequest()->getParam('cim-email-template');
        $template_id = "cimorder_email_".$cimemail;
        $order_id = $this->getRequest()->getParam('order_id');
        $real_id = $this->getRequest()->getParam('real_id');
        $order = Mage::getModel('onestepcheckout/salescimorder')->load($order_id, 'order_id');

        //$emailTemplate = Mage::getModel('core/email_template')->loadDefault($template_id);
        $sender = array('name' => 'Priceguru.mu', 'email' => 'credit@priceguru.mu');
        //recepient
        $email = $order->getEmail();
        $emailName = $order->getCustomerName();
        $vars = array();
        $vars = array('order' => $order);

        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        try {
            Mage::getModel('core/email_template')->setReplyTo('credit@priceguru.mu')->sendTransactional($template_id, $sender, $email, $emailName, $vars, $storeId);
            $translate->setTranslateInline(true);
            $result['ajaxExpired'] = 1;
            $result['ajaxRedirect'] = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('CIM order email send successfully.'));
        } catch(Exception $ex) {
            $result['error'] =  Mage::helper('onestepcheckout')->__('Unable to send email.');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('onestepcheckout')->__('Unable to send email.'));
        }

        
        /*$emailvars = array();
        $emailvars['order'] = $order;
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailvars);

		$mail = new Zend_Mail('UTF-8');       
        
        $mail->setFrom("info@priceguru.mu", "Priceguru.mu");
        $mail->setReplyTo('info@priceguru.mu', 'Priceguru.mu');
        $mail->addHeader('MIME-Version', '1.0');
        $mail->addHeader('Content-Transfer-Encoding', '8bit');
        $mail->addHeader('X-Mailer:', 'PHP/'.phpversion());
        $mail->addTo($order->getEmail(), $order->getCustomerName());
        $mail->setBodyHtml($processedTemplate);
        $mail->setSubject('Credit application order # '.$order_id);
		try {
			$mail->send();
			$result['ajaxExpired'] = 1;
			$result['ajaxRedirect'] = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('CIM order email send successfully.'));
			//echo "Email sent successfully";
		}
		catch(Exception $ex) {
			$result['error'] =  Mage::helper('onestepcheckout')->__('Unable to send email.');
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('onestepcheckout')->__('Unable to send email.'));
		}*/

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        //$this->_redirect('*/*/');
    }

    public function purchaseorderAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $pickup_id = $this->getRequest()->getParam('item_id');

        $result = array();
        $template_id = "purchase_order_email";
        //$order = Mage::getModel('onestepcheckout/salespickuporder')->load($order_id, 'real_order_id');
        $pickuporder = Mage::getModel('onestepcheckout/salespickuporder')->load($pickup_id);
        if($pickuporder->getData('po_created') == 0) { $pickuporder->setData('po_created', 1); $pickuporder->save(); }
        //$order->addFieldToFilter('pickup_id', array('eq' => $pickup_id));
        //$order->addFieldToFilter('real_order_id', array('eq' => $order_id));

        $itemoutput = "";
        $grandtotal = 0;
        
    	$increment_id = $pickuporder->getOrderId();
    	$wholesaler = Mage::getModel('onestepcheckout/wholesaler')->load($pickuporder->getWholesalerId());
    	if($wholesaler) {
    		$wholesaler_name = $wholesaler->getName();
    		$wholesaler_address = $wholesaler->getAddress();
    	} else {
    		$wholesaler_name =""; $wholesaler_address="";
    	}
    	$subtotal = $pickuporder->getWholesalePrice() * $pickuporder->getQty();
    	$grandtotal += $subtotal;

    	$itemoutput .= '<tr>
    		<td valign="top" width="300" height="700" style="border-right:1px solid #000000; padding:5px 10px; text-align: left;">';
    		if($pickuporder->getAttributes() != "") { 
            	$itemoutput .= 'Options: '. $pickuporder->getAttributes() .'<br>';
            }
            $itemoutput .= $pickuporder->getSku().'
          	</td>
          	<td valign="top" width="80" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
            	$pickuporder->getQty().'
         	</td>
          	<td valign="top" width="120" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
            	number_format($pickuporder->getWholesalePrice()).'
          	</td>
          	<td valign="top" width="120" style="padding:5px 10px; text-align: right;">'. 
            	number_format($subtotal).'
          	</td>
          </tr>';

        $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
        //recepient
        $email = "procurement@priceguru.mu";
        $emailName = "Purchase Order";
        
        //$email = "prashant.gohil@krishtechnolabs.com";
        //$emailName = "Prashant";

        //$subtotal = $order->getWholesalePrice() * $order->getQty();
        $vars = array();
        $vars = array(
        	'real_order_id' => $order_id, 
        	'increment_id' => $increment_id,
        	'itemoutput' => $itemoutput,
        	'grandtotal' => number_format($grandtotal),
        	'vendor' => $wholesaler_name.", ".$wholesaler_address, 
        	'todaydate' => date('Y-m-d')
        );
        //echo "<pre>"; print_r($vars); die();

        $emailTemplate = Mage::getModel('core/email_template')->loadDefault('purchase_order_pdf');
        $processedTemplate = $emailTemplate->getProcessedTemplate($vars);

        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');

        $transactionalEmail = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $transactionalEmail->setReplyTo('info@priceguru.mu');
        
        try {
        	$mpdf=new mPDF('c','A4'); 
			$mpdf->SetProtection(array('print'));
			$mpdf->SetDisplayMode('fullpage');
			$mpdf->WriteHTML($processedTemplate);
			$fn = Mage::getBaseDir('base').'/var/purchase_order/invoice_'.$pickup_id.'.pdf';
			$mpdf->Output($fn, 'F');

			//Send transaction email with pdf invoice
			if (!empty($fn) && file_exists($fn)) {
			    $transactionalEmail
			        ->getMail()
			        ->createAttachment(
			            file_get_contents($fn),
			            Zend_Mime::TYPE_OCTETSTREAM,
			            Zend_Mime::DISPOSITION_ATTACHMENT,
			            Zend_Mime::ENCODING_BASE64,
			            basename($fn)
			        );
			}
            $transactionalEmail->sendTransactional($template_id, $sender, $email, $emailName, $vars, $storeId);
            //print_r($response); exit;
            $translate->setTranslateInline(true);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('Purchase order created successfully.'));
        	//$url = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
        	$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
        } catch(Exception $ex) {
            $result['error'] =  Mage::helper('onestepcheckout')->__('Unable to send email.');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('onestepcheckout')->__('Unable to send email.'));
        }
        
    }

    public function purchaseorderprintAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $pickup_id = $this->getRequest()->getParam('item_id');

        $result = array();
        $template_id = "purchase_order_pdf";
        //$order = Mage::getModel('onestepcheckout/salespickuporder')->load($order_id, 'real_order_id');
        $order = Mage::getModel('onestepcheckout/salespickuporder')->getCollection();
        $order->addFieldToFilter('pickup_id', array('eq' => $pickup_id));
        $order->addFieldToFilter('real_order_id', array('eq' => $order_id));

        $itemoutput = "";
        $grandtotal = 0;
        foreach($order as $item) {

        	$increment_id = $item->getOrderId();
        	$wholesaler = Mage::getModel('onestepcheckout/wholesaler')->load($item->getWholesalerId());
        	if($wholesaler) {
        		$wholesaler_name = $wholesaler->getName();
        		$wholesaler_address = $wholesaler->getAddress();
        	} else {
        		$wholesaler_name =""; $wholesaler_address="";
        	}
        	$subtotal = $item->getWholesalePrice() * $item->getQty();
        	$grandtotal += $subtotal;

        	$itemoutput .= '<tr>
        		<td valign="top" width="300" height="700" style="border-right:1px solid #000000; padding:5px 10px; text-align: left;">';
        		if($item->getAttributes() != "") { 
                	$itemoutput .= 'Options: '. $item->getAttributes() .'<br>';
                }
                $itemoutput .= $item->getSku().'
              	</td>
              	<td valign="top" width="80" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
                	$item->getQty().'
             	</td>
              	<td valign="top" width="120" style="border-right:1px solid #000000; padding:5px 10px; text-align: right;">'. 
                	number_format($item->getWholesalePrice()).'
              	</td>
              	<td valign="top" width="120" style="padding:5px 10px; text-align: right;">'. 
                	number_format($subtotal).'
              	</td>
              </tr>';
        }

        $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
        //recepient
        //$email = "procurement@priceguru.mu";
        //$emailName = "Purchase Order";
        
        $email = "prashant.gohil@krishtechnolabs.com";
        $emailName = "Prashant";
        
        //$subtotal = $order->getWholesalePrice() * $order->getQty();

        $emailvars = array();
        $emailvars['real_order_id'] = $order_id;
        $emailvars['increment_id'] = $increment_id;
        $emailvars['itemoutput'] = $itemoutput;
        $emailvars['grandtotal'] = number_format($grandtotal);
        $emailvars['vendor'] = $wholesaler_name.", ".$wholesaler_address;
        $emailvars['todaydate'] = date('Y-m-d');

        $emailTemplate = Mage::getModel('core/email_template')->loadDefault($template_id);
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailvars);
        //echo $processedTemplate; exit;

        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        try {

			$mpdf=new mPDF('c','A4'); 
			$mpdf->SetProtection(array('print'));
			$mpdf->SetDisplayMode('fullpage');
			$mpdf->WriteHTML($processedTemplate);
			$fn = 'invoice'. $pickup_id .'.pdf';
			$mpdf->Output($fn, 'I');
			//$s = $mpdf->Output('','S'); 

            //Mage::getModel('core/email_template')->setReplyTo('info@priceguru.mu')->sendTransactional($template_id, $sender, $email, $emailName, $vars, $storeId);
            //$translate->setTranslateInline(true);
            //Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('Purchase order created successfully.'));
        	//$url = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
        	//$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
        } catch(Exception $ex) {
            $result['error'] =  Mage::helper('onestepcheckout')->__('Unable to create print copy.');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('onestepcheckout')->__('Unable to create print copy.'));
        }
        
    }

    public function salesinvoiceAction() {
    	$order_id = $this->getRequest()->getParam('order_id');
        //$pickup_id = $this->getRequest()->getParam('item_id');
        $data = $this->getRequest()->getPost();
        

        //if($data['invoice_id']!="") {
        $vatinvoice = Mage::getModel('onestepcheckout/salesinvoicevat')->load($order_id, 'order_id');
        $vatinvoice->setInvoiceId($data['invoice_id']);
        //$vatinvoice->setPickupId($pickup_id);
        $vatinvoice->setOrderId($order_id);
        //$vatinvoice->setProductName($data['product_name']);
        //$vatinvoice->setSku($data['sku']);
        //$vatinvoice->setQty($data['product_qty']);
        //$vatinvoice->setAttributes($data['attributes']);
        $vatinvoice->setVatregno($data['vatregno']);
        $vatinvoice->setBrn($data['brn']);
        $vatinvoice->save();
        //}

        $result = array();
        $template_id = "vat_invoice_email";
        
        $pickuporder = Mage::getModel('onestepcheckout/salespickuporder')->getCollection();
        $pickuporder->addFieldToFilter('real_order_id', array('eq' => $order_id));

        $order = Mage::getModel('sales/order')->load($order_id);
        $billingaddress = $order->getBillingAddress()->getData();
        
        $shipping_amount = $order->getShippingAmount();
        $reward_points = $order->getData('rewardpoints_discount');
        $grandtotal = 0; $vat = 0; $subtotal = 0;
        $itemout = "";
        $itemcount = $pickuporder->count(); 
        if($itemcount > 1) { $rawheight = 1000/$itemcount; } else { $rawheight = 400; }
        $i=0;
        foreach($pickuporder as $item) {
            $unit_price = $item->getRetailPrice() / 1.15; 
            $amount = $unit_price * $item->getQty();
            $subtotal += $amount;
            
            $itemout .= '<tr>';
            if($i == $itemcount - 1) {
            $itemout .='<td valign="top" height="'.round($rawheight).'" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">'
                .$item->getQty().'
                </td>';
            } else {
                $itemout .='<td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">'
                .$item->getQty().'
                </td>';
            }
            $itemout .= '<td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: left; width: 320px;">' 
                .$item->getSku().'
                </td>
                <td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">' 
                .number_format($unit_price).'  
                </td>
                <td valign="top" style="padding:5px 10px; text-align: right; width: 100px;">' 
                .number_format($amount).' 
                </td>
            </tr>'; $i++;
        }
        $vat = $subtotal*0.15;
        $grandtotal = $subtotal + $vat;
        
        if($shipping_amount > 0) {
            $grandtotal += $shipping_amount;
            $shipping_amount = number_format($shipping_amount,2);
        } else { $shipping_amount = ""; }

        if($reward_points > 0) {
            $grandtotal -= $reward_points;
            $reward_points = number_format($reward_points,2);
        } else { $reward_points = ""; }

        $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
        //recepient
        $email = "procurement@priceguru.mu";
        $emailName = "Priceguru.mu";
        
        //$email = "prashant.gohil@krishtechnolabs.com";
        //$emailName = "Prashant";

        //$subtotal = $order->getWholesalePrice() * $order->getQty();
        $vars = array();
        $vars = array(
            'invoice_id' => $data['invoice_id'], 
            'order_id' => $order->getIncrementId(),
            'billname' => $billingaddress['firstname']." ".$billingaddress['lastname'],
            'billto' => $billingaddress['street'],
            'telephone' => $billingaddress['telephone'],
            'vatregno' => $data['vatregno'],
            'brn' => $data['brn'],
            'shipping' => $shipping_amount,
            'itemout' => $itemout,
            //'sku' => $data['sku'],
            //'rate' => number_format($unit_price,2),
            //'qty' => $data['product_qty'],
            'reward_points' => $reward_points,
            'subtotal' => number_format($subtotal,2),
            'vat' => number_format($vat,2),
            'grandtotal' => number_format($grandtotal,2),
            'todaydate' => date('Y-m-d')
        );
        //echo "<pre>"; print_r($vars); die();

        $emailTemplate = Mage::getModel('core/email_template')->loadDefault('vat_invoice_email');
        $processedTemplate = $emailTemplate->getProcessedTemplate($vars);

        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');

        $transactionalEmail = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $transactionalEmail->setReplyTo('info@priceguru.mu');
        
        try {
            $mpdf=new mPDF('c','A4'); 
            $mpdf->SetProtection(array('print'));
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->WriteHTML($processedTemplate);
            $fn = Mage::getBaseDir('base').'/var/sale_invoice/invoice_vat'.$order_id.'.pdf';
            $mpdf->Output($fn, 'F');

            //Send transaction email with pdf invoice
            if (!empty($fn) && file_exists($fn)) {
                $transactionalEmail
                    ->getMail()
                    ->createAttachment(
                        file_get_contents($fn),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        basename($fn)
                    );
            }
            $transactionalEmail->sendTransactional($template_id, $sender, $email, $emailName, $vars, $storeId);
            $translate->setTranslateInline(true);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('Invoice created successfully.'));
            //$url = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
        } catch(Exception $ex) {
            $result['error'] =  Mage::helper('onestepcheckout')->__('Unable to send email.');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('onestepcheckout')->__('Unable to send email.'));
        }
        
    }

    public function deliverynoteAction() {
        $order_id = $this->getRequest()->getParam('order_id');
        //$pickup_id = $this->getRequest()->getParam('item_id');
        $data = $this->getRequest()->getPost();

        $result = array();
        $template_id = "delivery_note";
        $pickuporder = Mage::getModel('onestepcheckout/salespickuporder')->getCollection();
        $pickuporder->addFieldToFilter('real_order_id', array('eq' => $order_id));

        $order = Mage::getModel('sales/order')->load($order_id);
        $billingaddress = $order->getBillingAddress()->getData();

        //Start email processing and variable input
        $itemout = "";
        $deposit_amount = "";
        $payment_code = $order->getPayment()->getMethodInstance()->getCode();
        if($order->getIscimorder()) {
        	$salescimorder = Mage::getModel('onestepcheckout/salescimorder')->load($order->getIncrementId(), 'order_id');
        	$deposit_amount = $salescimorder->getDeposit();
        	$payment_method = "CIM";
        	if($deposit_amount == NULL) {
        		$payment_status = "No Deposit";	
        	} else { $payment_status = "Deposit"; }
        	
        } elseif($payment_code == "cashondelivery") {
	    	$payment_method = "Pay on Delivery";
	    	$payment_status = "Unpaid";
	    } elseif($payment_code == "banktransfer") {
	    	$payment_method = "Internet Banking";
	    	$payment_status = "Paid";
	    } else {
	    	$payment_method = "Credit Card";
	    	$payment_status = "Paid";
	    } 
        //$grandtotal = 0;
        //$unit_price = $pickuporder->getRetailPrice() / 1.15; 
        //$subtotal = $unit_price * $pickuporder->getQty();
        //$vat = $subtotal*0.15;
        //$grandtotal = $subtotal + $vat;
        $itemcount = $pickuporder->count(); 
        if($itemcount > 1) { $rawheight = 1000/$itemcount; } else { $rawheight = 400; }
        $i=0;
        foreach($pickuporder as $item) {
            $displayname = $item->getProductName().' - '.$item->getSku();
            $itemout .= '<tr>';
            if($i == $itemcount - 1) {
            $itemout .='<td valign="top" height="'.round($rawheight).'" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">'
                .$item->getQty().'
                </td>';
            } else {
                $itemout .='<td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: right; width: 100px;">'
                .$item->getQty().'
                </td>';
            }
            $itemout .= '<td valign="top" style="border-right:1px solid #000000; padding:5px 10px; text-align: left; width: 320px;">' 
                .$displayname.'</td>
            </tr>'; $i++;
        }

        $sender = array('name' => 'Priceguru.mu', 'email' => 'info@priceguru.mu');
        //recepient
        $email = "procurement@priceguru.mu";
        $emailName = "Purchase Order";
        
        //$email = "prashant.gohil@krishtechnolabs.com";
        //$emailName = "Prashant";

        //$subtotal = $order->getWholesalePrice() * $order->getQty();
        $vars = array();
        $vars = array(
            'increment_id' => $order->getIncrementId(), 
            'order_id' => $order_id,
            'billname' => $billingaddress['firstname']." ".$billingaddress['lastname'],
            'billto' => $billingaddress['street'],
            'telephone' => $billingaddress['telephone'],
            'sku' => $data['sku'],
            'rate' => number_format($unit_price),
            'qty' => $data['product_qty'],
            'itemout' => $itemout,
            'deposit' => $deposit_amount,
            'payment_method' => $payment_method,
            'payment_status' => $payment_status,
            //'subtotal' => number_format($subtotal),
            //'vat' => number_format($vat,2),	
            //'grandtotal' => number_format($grandtotal,2),
            'todaydate' => date('Y-m-d')
        );

        $emailTemplate = Mage::getModel('core/email_template')->loadDefault('delivery_note');
        $processedTemplate = $emailTemplate->getProcessedTemplate($vars);

        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');

        $transactionalEmail = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $transactionalEmail->setReplyTo('info@priceguru.mu');
        
        try {
            $mpdf=new mPDF('c','A4'); 
            $mpdf->SetProtection(array('print'));
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->WriteHTML($processedTemplate);
            $fn = Mage::getBaseDir('base').'/var/deliverynote/invoice_'.$pickup_id.'.pdf';
            $mpdf->Output($fn, 'F');

            //Send transaction email with pdf invoice
            if (!empty($fn) && file_exists($fn)) {
                $transactionalEmail
                    ->getMail()
                    ->createAttachment(
                        file_get_contents($fn),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        basename($fn)
                    );
            }
            $transactionalEmail->sendTransactional($template_id, $sender, $email, $emailName, $vars, $storeId);
            //print_r($response); exit;
            $translate->setTranslateInline(true);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('onestepcheckout')->__('Delivery note invoice created successfully.'));
            //$url = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
        } catch(Exception $ex) {
            $result['error'] =  Mage::helper('onestepcheckout')->__('Unable to send email.');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('onestepcheckout')->__('Unable to send email.'));
        }
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    //Added by quickfix script. Take note when upgrading this module! Powered by SupportDesk (www.supportdesk.nu)
    function _isAllowed()
    {
        return true;
    }
}