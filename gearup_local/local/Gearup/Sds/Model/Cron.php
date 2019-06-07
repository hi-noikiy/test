<?php

class Gearup_Sds_Model_Cron
{
    public function checkLowstock() {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('same_day_shipping', 1);
        $warnings = array();
        foreach ($collection as $alls) {
            $sds = Mage::getModel('catalog/product')->load($alls->getEntityId());
            if (!$sds->getData('low_stock')) {
                continue;
            }
            if (round($sds->getStockItem('qty')) < $sds->getData('low_stock')) {
                $warnings[] = $sds;
            }
        }
        if (count($warnings) > 0) {
            $this->sendMail($warnings);
        }
    }

    public function sendMail($products) {
        $message = '<h1>'.Mage::helper('gearup_sds')->__('SDS products low stock').'</h1><br/>';
        $message .= '<table cellspacing="0" border="1"><thead><tr><th>'.Mage::helper('gearup_sds')->__('Id').'</th>';
        $message .= '<th>'.Mage::helper('gearup_sds')->__('Name').'</th>';
        $message .= '<th width="10%">'.Mage::helper('gearup_sds')->__('Qty').'</th>';
        $message .= '<th>'.Mage::helper('gearup_sds')->__('Low Stock Threshold').'</th>';
        foreach ($products as $product) {
            $message .= '<tr><td>'.$product->getEntityId().'</td><td>'.$product->getName().'</td><td style="text-align:center;">'.round($product->getStockItem('qty')).'</td><td style="text-align:center;">'.$product->getLowStock().'</td></tr>';
        }
        $message .= '</tbody></table><br/><br/>';
        $message .= '<a href="'.Mage::getBaseUrl().'gearup_admin">Admin URL</a><br/>';
        try {
            $mailtosend = explode(';', Mage::getStoreConfig('gearup_sds/sdsmail/email'));
            foreach ($mailtosend as $mailsend) {
                $template_id = 'sds_email_template1';
                $email_to = $mailsend;
                $customer_name   = 'Admin';
                $email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
                $email_template_variables = array(
                    'message' => $message
                );
                $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
                $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
                $email_template->setSenderName($sender_name);
                $email_template->setSenderEmail($sender_email);
                $email_template->send($email_to, $customer_name, $email_template_variables);
            }
        } catch (Exception $e) {
            Mage::log($e, null, 'sdsmailerror.log');
        }
    }

    public function checkNotification() {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('same_day_shipping', 1);
        $warnings = array();
        $outOfStock = array();
        foreach ($collection as $alls) {
            $sds = Mage::getModel('catalog/product')->load($alls->getEntityId());
            if (!$sds->getData('low_stock')) {
                continue;
            }
            if (round($sds->getStockItem('qty')) <= $sds->getData('low_stock') && round($sds->getStockItem('qty')) != 0) {
                $warnings[] = $sds;
            } elseif (round($sds->getStockItem('qty')) == 0) {
                $outOfStock[] = $sds;
            }
        }
        if (count($warnings) > 0) {
            $this->sendNotification($warnings, 1);
        }
        if (count($outOfStock) > 0) {
            $this->sendNotification($outOfStock, 2);
        }
    }

    public function sendNotification($products, $type) {
        try {
            if ($type == 1) {
                $title = 'Waring: Products are Low on Stock reoder';
                $severity = 3;
                $message = '';
                foreach ($products as $product) {
                    $message .= '"'.$product->getName().'" + "'.$product->getSku().'" is Low on Stock reoder<br>';
                }
            } elseif ($type == 2) {
                $title = 'Products are Out of Stock reorder immediately';
                $severity = 1;
                $message = '';
                foreach ($products as $product) {
                    $message .= '"'.$product->getName().'" + "'.$product->getSku().'" is Out of Stock reorder immediately<br>';
                }
            }
            //var_dump(Mage::helper("adminhtml")->getUrl('adminhtml/notification/index', array('key'=>Mage::getSingleton('adminhtml/url')->getSecretKey("notification/index/","index"))));
                //die();
            $collection = Mage::getModel('adminnotification/inbox')->getCollection();
            $collection->addFieldToFilter('description', array('eq' => $message));
            /*var_dump($collection->getSize());
            die();*/
            $notification = Mage::getModel('adminnotification/inbox');
            $notification->setSeverity($severity);
            $notification->setDateAdded(Mage::getSingleton('core/date')->gmtDate());
            $notification->setTitle($title);
            $notification->setDescription($message);
            //$notification->setUrl(Mage::helper("adminhtml")->getUrl('adminhtml/notification/index'));
            $notification->setIsRead(0);
            $notification->setIsRemove(0);
            $notification->save();
        } catch (Exception $e) {
            Mage::log($e, null, 'sdsnotierror.log');
        }
    }

    public function checkSdsRed() {
        try {
            $products = Mage::getModel('catalog/product')->getCollection();
            $products->addFieldToFilter('dxbs', array('eq'=>1));
            if ($products->getSize()) {
                $last21 = Mage::getModel('core/date')->date('Y-m-d H:i:s', strtotime("-21 day"));
                foreach ($products as $product) {
                    $track = Mage::getModel('gearup_sds/tracking')->load($product->getId(), 'product_id');
                    $updated = $track->getUpdateLastAt();
                    if ($updated < $last21) {
                        $product->setSdsRed(1);
                        $product->save();
                    } else {
                        $product->setSdsRed(0);
                        $product->save();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'sdscheckred.log');
        }
    }

    public function sdsDayStockOverview() {
        try {
            $currentDate = Mage::getModel('core/date')->date('Y-m-d');
            $previousDate = date('Y-m-d', strtotime($currentDate .' -1 day'));

            $path = Mage::getBaseDir('var') . DS . 'export/sds-report';
            $name = $previousDate;
            $file = $path . DS . $name . '.txt';

            $reportGenerated = 'Report Generated: '.Mage::getModel('core/date')->date('d-m-Y H:i:s'); 
            file_put_contents($file, $reportGenerated.PHP_EOL);

            $blankLine = '';
            file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

            /* Write log for SDS sold items in a day */

            $sdsSoldHistory = Mage::getModel('gearup_sds/history')->getCollection()
                            ->addFieldToFilter('sds_status', 1)
                            ->addFieldToFilter('order_id', array('gteq' => 0))
                            ->addFieldToFilter('in_out', array('lt' => 0))
                            ->addFieldToFilter('create_date', array('gteq' => $previousDate.' 00:00:00'))
                            ->addFieldToFilter('create_date', array('lteq' => $previousDate.' 23:59:59'));
                                    
            if(count($sdsSoldHistory)){

                $totalSoldQty = 0;
                $totalSoldValue = 0;        
                
                $content = 'Products sold - SKU | PN | Qty sold | Sold value';
                file_put_contents($file, $content.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                foreach ($sdsSoldHistory as $_sdsSoldHistory) {

                    $soldRecord = '';                       
                    $soldRecord = $_sdsSoldHistory['sku'].' | '.$_sdsSoldHistory['part_number'].' | '.abs($_sdsSoldHistory['in_out']).' | '.number_format(($_sdsSoldHistory['cost'] * abs($_sdsSoldHistory['in_out'])), 2);
                    
                    file_put_contents($file, $soldRecord.PHP_EOL , FILE_APPEND | LOCK_EX); 

                    $totalSoldQty += abs($_sdsSoldHistory['in_out']);
                    $totalSoldValue = $totalSoldValue + ($_sdsSoldHistory['cost'] * abs($_sdsSoldHistory['in_out']));     
                }

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $soldSummery = 'Total Products(SKU): '.count($sdsSoldHistory).', Total Sold QTY: '.$totalSoldQty.', Total Sold Value: '.number_format($totalSoldValue, 2);
                file_put_contents($file, $soldSummery.PHP_EOL, FILE_APPEND | LOCK_EX); 

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);

            }else{

                $content = 'None sold for the day';
                file_put_contents($file, $content.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);  

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);
            }

            /* Write log for Inbound or cancelled SDS=YES orders in a day */

            $sdsCanceldOrderHistory = Mage::getModel('gearup_sds/history')->getCollection()
                                    ->addFieldToFilter('sds_status', 1)
                                    ->addFieldToFilter('order_id', array('gt' => 0))
                                    ->addFieldToFilter('in_out', array('gt' => 0))
                                    ->addFieldToFilter('create_date', array('gteq' => $previousDate.' 00:00:00'))
                                    ->addFieldToFilter('create_date', array('lteq' => $previousDate.' 23:59:59'));
                                    
            if(count($sdsCanceldOrderHistory)){

                $canceldOrderQty = 0;
                $canceldOrderValue = 0;

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);
                
                $content = 'Inbound or cancelled order - SKU | PN | Qty | Value';
                file_put_contents($file, $content.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                foreach ($sdsCanceldOrderHistory as $_sdsCanceldOrderHistory) {

                    $cancelledRecord = '';                       
                    $cancelledRecord = $_sdsCanceldOrderHistory['sku'].' | '.$_sdsCanceldOrderHistory['part_number'].' | '.abs($_sdsCanceldOrderHistory['in_out']).' | '.number_format(($_sdsCanceldOrderHistory['cost'] * abs($_sdsCanceldOrderHistory['in_out'])), 2);
                    
                    file_put_contents($file, $cancelledRecord.PHP_EOL , FILE_APPEND | LOCK_EX);

                    $canceldOrderQty += abs($_sdsCanceldOrderHistory['in_out']);
                    $canceldOrderValue = $canceldOrderValue + ($_sdsCanceldOrderHistory['cost'] * abs($_sdsCanceldOrderHistory['in_out']));
                         
                }

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $canceldOrderSummery = 'Total Products(SKU): '.count($sdsCanceldOrderHistory).', Total QTY: '.$canceldOrderQty.', Total Value: '.number_format($canceldOrderValue, 2);
                file_put_contents($file, $canceldOrderSummery.PHP_EOL, FILE_APPEND | LOCK_EX); 

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);

            }else{

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);
                
                $contents = 'No Inbound or Cancelled for the day';
                file_put_contents($file, $contents.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);
            }

            /* Write log for SDS product missing cost value */
            
            $sdsMissingCost = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSort('created_at', 'DESC')
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter('dxbs', 1)
                            ->addFieldToFilter('same_day_shipping', 1)
                            ->addAttributeToFilter(
                                array(
                                    array('attribute'=> 'cost','null' => true),
                                    array('attribute'=> 'cost','eq' => 0),
                                )
                            )
                            ->joinField(
                                'qty',
                                'cataloginventory/stock_item',
                                'qty',
                                'product_id=entity_id',
                                '{{table}}.stock_id=1',
                                'left'
                            );                
                                        
            if(count($sdsMissingCost)){    

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);     
                
                $content = 'Missing cost value - SKU | PN | Qty';
                file_put_contents($file, $content.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                foreach ($sdsMissingCost as $_sdsMissingCost) {

                    $sdsMissingCostRecord = '';                       
                    $sdsMissingCostRecord = $_sdsMissingCost['sku'].' | '.$_sdsMissingCost['part_nr'].' | '.abs($_sdsMissingCost['qty']);
                    
                    file_put_contents($file, $sdsMissingCostRecord.PHP_EOL , FILE_APPEND | LOCK_EX);                 
                }

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);

            }else{

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);
                
                $contents = 'No cost value missing';
                file_put_contents($file, $contents.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);
            }

            /* Write log for SDS Products */

            $sdsProducts = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSort('created_at', 'DESC')
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter('dxbs', 1)
                            ->addFieldToFilter('same_day_shipping', 1)
                            ->joinField(
                                'qty',
                                'cataloginventory/stock_item',
                                'qty',
                                'product_id=entity_id',
                                '{{table}}.stock_id=1',
                                'left'
                            );           

            if(count($sdsProducts)){

                $totalQty = 0;
                $totalValue = 0;  

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);          
                
                $content = 'SDS Report - Products SKU | PN | Qty | Value';
                file_put_contents($file, $content.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                foreach ($sdsProducts as $_sdsProducts) {

                    $record = '';                       
                    $record = $_sdsProducts['sku'].' | '.$_sdsProducts['part_nr'].' | '.abs($_sdsProducts['qty']).' | '.number_format(($_sdsProducts['cost'] * abs($_sdsProducts['qty'])), 2);
                    
                    file_put_contents($file, $record.PHP_EOL , FILE_APPEND | LOCK_EX); 

                    $totalQty += abs($_sdsProducts['qty']);
                    $totalValue = $totalValue + ($_sdsProducts['cost'] * $_sdsProducts['qty']);
                }

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $summery = 'Total Products(SKU): '.count($sdsProducts).', Total QTY: '.$totalQty.', Total Value: '.number_format($totalValue, 2);
                file_put_contents($file, $summery.PHP_EOL, FILE_APPEND | LOCK_EX); 

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);
                
            }else{

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);
                
                $contents = 'None of SDS products';
                file_put_contents($file, $contents.PHP_EOL, FILE_APPEND | LOCK_EX);

                $blankLine = '';
                file_put_contents($file, $blankLine.PHP_EOL, FILE_APPEND | LOCK_EX);

                $dotedLine = '=====================================================================';
                file_put_contents($file, $dotedLine.PHP_EOL, FILE_APPEND | LOCK_EX);
            }   
            Mage::helper('missingattribute')->removefile($path.DS);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'sdsdaystockoverview.log');
        }
    }
}
