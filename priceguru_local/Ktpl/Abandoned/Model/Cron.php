<?php

class Ktpl_Abandoned_Model_Cron {

    /**
     * Process transactions (holding, expire) by cron
     */
    public function sendabandoned() {
        try {
            Varien_Profiler::start('ABANDONED_CRON::sendrewards');
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $write = $resource->getConnection('core_write');
            $now = date('Y-m-d h:i',time());
            //$now = '2017-05-09 10:28:03';
            $start_date = new DateTime('2017-05-09 08:53:00');
           // $rewards = $read->query("select * from sales_flat_quote where is_active = '1' && items_count > 0 && customer_email != ''");
            $rewards = $read->query(" SELECT * from sales_flat_quote where DATE_FORMAT(updated_at,'%Y-%m-%d %h:%i') = DATE_FORMAT(DATE_SUB('$now', INTERVAL 1 HOUR),'%Y-%m-%d %h:%i') 
                                    OR
                DATE_FORMAT(updated_at,'%Y-%m-%d %h:%i') = DATE_FORMAT(DATE_SUB('$now', INTERVAL 24 HOUR),'%Y-%m-%d %h:%i') && is_active = '1' && items_count > 0 && customer_email != '' " );
            
           // $i = 1;
            
            foreach ($rewards as $r) {
                if ($r['customer_email'] && $r['customer_email'] != NULL) {
                   
                    $update = new DateTime($r['updated_at']);
                    $start_date = new DateTime('2017-05-09 08:53:00');
                    $since_start = $start_date->diff($update);
                     $store = Mage::app()->getStore()->getId();
                     /*echo $since_start->days.' days total<br>'; 
                          echo $since_start->y.' years<br>';
                          echo $since_start->m.' months<br>';
                          echo $since_start->d.' days<br>';
                          echo $since_start->h.' hours<br>';
                          echo $since_start->i.' minutes<br>';
                          echo $since_start->s.' seconds<br>';  */
                 /*   if (($since_start->h == 2 && $since_start->y < 1 && $since_start->m < 1 && $since_start->d < 1 && $since_start->i < 1) ||
                            ($since_start->d == 1 && $since_start->y < 1 && $since_start->m < 1 && $since_start->h < 1 && $since_start->i < 1)) {  */
                     //if ($since_start->i == 5 && $since_start->y < 1 && $since_start->m < 1 && $since_start->d < 1 && $since_start->h < 1) {
                        $point = 0;
                        $rs = 0;
                        if($r['customer_id']){
                            $rData = Mage::getModel('rewardpoints/customer')->load($r['customer_id'], 'customer_id');
                            $customer = Mage::getModel('customer/customer')->load($r['customer_id']);
                            $rate = Mage::getSingleton('rewardpoints/rate')->getRate(
                                Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY, $customer->getGroupId(), $customer->getWebsiteId()
                            );
                            if ($rData->getPointBalance() > 0 && $rData->getPointBalance() != '') {
                                $point = $rData->getPointBalance();
                                $rs=ceil($rData->getPointBalance()*$rate->getMoney()/$rate->getpoints());
                            }
                        }    
                        $templateId = 24; // Enter you new template ID
                        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');  //Get Sender Name from Store Email Addresses
                        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');  //Get Sender Email Id from Store Email Addresses
                        $sender = array('name' => $senderName,
                            'email' => $senderEmail);
                        $email_template = Mage::getModel('core/email_template')->loadDefault($templateId);
                        $email_template->setDesignConfig(array('area' => 'frontend'));
                        $itemoutput = '';
                        $grandtotal = 0;
                        // Set recepient information
                        $recepientEmail = $r['customer_email'];
                        $recepientName = 'Guest';
                        if ($r['customer_firstname']) {
                            $recepientName = $r['customer_firstname'] . ' ' . $r['customer_lastname'];
                        } 
                        $order = Mage::getModel('sales/quote')->loadByIdWithoutStore($r['entity_id']);
                        foreach ($order->getAllVisibleItems() as $k=> $item) {
                            if($item->getParentItemId()) continue; 
                            // if($item->getProductType() == 'configurable' || $item->getProductType() == 'bundle') :
                            $finalResult = array();
                            $result = array();
                            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                            // Check for options
                            if ($options) {
                                if (isset($options['options']))	{
                                    $result = array_merge($result, $options['options']);
                                }
                                if (isset($options['additional_options'])) {
                                    $result = array_merge($result, $options['additional_options']);
                                }
                                if (!empty($options['attributes_info'])) {
                                    $result = array_merge($options['attributes_info'], $result);
                                }
                                if(isset($options['bundle_options'])) {
                                    $bundled_product = new Mage_Catalog_Model_Product();
                                    $bundled_product->load($item->getProduct()->getId());
                                    $selectionCollection = $bundled_product->getTypeInstance(true)->getSelectionsCollection(
                                    $bundled_product->getTypeInstance(true)->getOptionsIds($bundled_product), $bundled_product);
                                    $bundled_items = array();
                                    $label = '';
                                    $qty = '';
                                    foreach($selectionCollection as $option)
                                    {
                                        foreach($options['bundle_options'] as $bundle){
                                            if($bundle['value'][0]['title'] == $option->getName()){
                                                $label = $bundle['label'];
                                                $qty = $bundle['value'][0]['qty'];
                                                $aux_options[] = array('label' => $label, 'value' => $qty .' x '. $option->getName() .' '. Mage::helper('checkout')->formatPrice($option->getPrice()), 'sku' => $option->getSku());
                                            }
                                        }
                                    }
                                    $result = array_merge($result, $aux_options);
                                }
                            }
                            $options = array_merge($finalResult, $result);
                        //endif; 
                            
                            $subtotal = $item->getPrice() * $item->getQty();
                            $grandtotal += $subtotal;
                            $itemoutput .= '<tr>
                                <td class="box sub" style="width:50.6%; margin:0px; padding:10px 0 10px 0px; border-right:2px solid #d5d5d5;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                                <tr>
                                <td style="margin:0px; padding:0px;vertical-align: top; width:100px; ">';
                                
                            $product = Mage::getModel('catalog/product')->load($item->getProductId());
                            $url[$k] = $product->getProductUrl();
                            $itemoutput .= '<img width="100px" src="'.Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail()).'" /> </td>';
                            $itemoutput .= '<td  class="product" valign="top" style="margin:0px; padding:0 0 0 10px; font-size:18px; font-weight:400; line-height:24px;">';
                            $itemoutput .= '<span class="desktop">Product Name : </span><b>'.$item->getName() . '</b><br>';
                            $itemoutput .= $item->getSku();
                            if(isset($options) && is_array($options)) : 
                                 foreach($options as $option) : 
                                    $itemoutput .= '<br /><strong>'.$option['label'].'</strong> '.$option['value']; 
                                    if(isset($option['sku'])) : $itemoutput .= ' ' . $option['sku']; endif; 
                                endforeach; 
                                $options = null; 
                            endif;
                            $itemoutput .= '</td></tr></table></td>
                                <td class="box price" align="left" valign="top" style="width:15%; font-size:18px;margin:0px; padding:10px; border-right:2px solid #d5d5d5;">'.
                                    '<span class="desktop"><b>Price : </b></span><span class="price">'.Mage::helper('checkout')->formatPrice($item->getPrice()).'</span> 
                                </td>
                                <td class="box quantity" align="left" valign="top" style="width:15%; font-size:18px;margin:0px; padding:10px 0 10px 10px; border-right:2px solid #d5d5d5;">'.
                                   '<span class="desktop"><b>Quantity :</b> </span>'. $item->getQty() . '
                                </td>
                                <td class="box subtotal" align="left" valign="top" style="width:14.62%; margin:0px;font-size:18px; padding:10px 0 10px 10px;text-align: right;">' .
                                    '<span class="desktop"><b>Subtotal : </b></span><span class="price">'.Mage::helper('checkout')->formatPrice($subtotal) . '</span> 
                                </td>
                                </tr>';
                        }
                        
                        // Set variables that can be used in email template
                        $vars = array('name' => $recepientName,
                            'quote' => $order,
                            'itemoutput' => $itemoutput,
                            'grandtotal' => Mage::helper('checkout')->formatPrice($grandtotal),
                            'point' => $point, 'rs' => $rs, 'url' => $url[0],
                        );
                        

                        // Send Transactional Email
                        if (!$email_template
                                        ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $store)) {
                            Mage::log($recepientEmail, null, 'ktpl_abandoned_fail-' . date("Y-m-d") . '.log');
                        } else {
                            Mage::log($recepientEmail, null, 'ktpl_abandoned_success-' . date("Y-m-d") . '.log');
                        }   
                   // }
                } 
            }
        } catch (Exception $e) {
            Mage::printException($e);
        }

        Varien_Profiler::stop('ABANDONED_CRON::sendrewards');
    }
}
