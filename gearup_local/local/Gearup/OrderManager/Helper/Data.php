<?php

/**
 * Helper
 */

class Gearup_OrderManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function exportperiodCSV($periodId) {
        $period = Mage::getModel('hordermanager/period')->load($periodId);
        $path = Mage::getBaseDir() . '/media/dxbs/period/' . $periodId . '/';
        $file = 'orderperiod-' . $period->getData('custom_period_id').'.csv';
        $csv = new Varien_File_Csv();
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $i = 0;
        $ordersCollection = Mage::getResourceModel('hordermanager/sales_order_collection');
        $ordersCollection->setPeriodFilter($period);
        $ordersCollection->filterVisible();
        $ordersCollection->filterStatus();
        $ordersCollection->setOrder('order_id', 'ASC');
        foreach ($ordersCollection as $orderModel) {
            //$order = Mage::getModel('sales/order')->load(1069);
            $address = Mage::getModel('sales/order_address')->load($orderModel->getShippingAddressId());
            $payment = $orderModel->getPayment()->getMethodInstance()->getCode();
            //$allActivePaymentMethods = Mage::getModel('payment/config')->getActiveMethods();
            $cod = '';
            $ck = '';
            $ppl = '';
            $bank = '';
            switch ($payment) {
                case 'cashondelivery':
                    $cod = true;
                    break;
                case 'creditcard':
                    $ck = true;
                    break;
                case 'paypal_standard':
                    $ppl = true;
                    break;
                case 'bankpayment':
                    $bank = true;
                    break;
                default:
                    break;
            }

            $items = $orderModel->getAllItems();
            foreach ($items as $item) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if (file_exists($path.$file)) {
                    $oldContent = Mage::getModel('gearup_sds/tracking')->getCsvData($path.$file);
                } else {
                    $oldContent = '';
                }
                $head = array();
                $head['0'] = 'Comment';
                $head['1'] = 'Order number';
                $head['2'] = 'Item';
                $head['3'] = 'Pcs';
                $head['4'] = 'Part number';
                $head['5'] = 'Invoiced';
                $head['6'] = 'Customer';
                $head['7'] = 'COD';
                $head['8'] = 'CK';
                $head['9'] = 'PPL';
                $head['10'] = 'Bank';
                $head['11'] = 'Shipping';
                $head['12'] = 'Invoice USD';
                $head['13'] = 'Gross M';
                $head['14'] = 'M';
                $head['15'] = 'Discount';

                if ($item->getDiscountAmount() > 1) {
                    $discount = $item->getDiscountAmount();
                } else {
                    $discount = '';
                }

                if ($previousOrderId && $orderModel->getIncrementId() == $previousOrderId) {
                    $orderIncrement = '';
                    $customer = '';
                    $shipping = '';
                } else {
                    $previousOrderId = $orderModel->getIncrementId();
                    $orderIncrement = $orderModel->getIncrementId();
                    $customer = $address->getLastname();
                    $shipping = $orderModel->getShippingAmount();
                }
                $completeData = array();
                $completeData['0'] = '';
                $completeData['1'] = $orderIncrement;
                $completeData['2'] = htmlspecialchars_decode($product->getName(),ENT_COMPAT);
                $completeData['3'] = $item->getQtyOrdered();
                $completeData['4'] = $product->getPartNr();
                $completeData['5'] = $item->getRowTotal();
                $completeData['6'] = $customer;
                $completeData['7'] = $cod ? $item->getRowTotal() : '';
                $completeData['8'] = $ck ? $item->getRowTotal() : '';
                $completeData['9'] = $ppl ? $item->getRowTotal() : '';
                $completeData['10'] = $bank ? $item->getRowTotal() : '';
                $completeData['11'] = $shipping;
                $completeData['12'] = '';
                $completeData['13'] = '';
                $completeData['14'] = '';
                $completeData['15'] = $discount;
                if ($oldContent) {
                    $csvdata = $oldContent;
                } else {
                    $csvdata = array();
                    $csvdata[] = $head;
                }
                $csvdata[] = $completeData;
                $csv->saveData($path.$file, $csvdata);
                $i++;
            }
        }
    }

    public function exportperiodXLS($periodId) {
        require_once Mage::getBaseDir('lib') . DS .'PHPExcel.php';
        require_once Mage::getBaseDir('lib') . DS .'PHPExcel/IOFactory.php';
        $period = Mage::getModel('hordermanager/period')->load($periodId);
        $path = Mage::getBaseDir() . '/media/dxbs/period/' . $period->getData('custom_period_id') . '/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $objPHPExcel = new PHPExcel();
        //$shareFont = new PHPExcel_Shared_Font();
        $objPHPExcel->getProperties()
            ->setCreator("Gearupme")
            ->setLastModifiedBy("Gearupme")
            ->setTitle('orderperiod-' . $period->getData('custom_period_id'))
            ->setSubject('orderperiod-' . $period->getData('custom_period_id'))
            ->setDescription('orderperiod-' . $period->getData('custom_period_id'))
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Orderperiod");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Comment')
            ->setCellValue('B1', 'Order nr.:')
            ->setCellValue('C1', 'Item')
			->setCellValue('D1', 'PCS')
			->setCellValue('E1', 'Part Number')
            ->setCellValue('F1', 'Invoiced')
            ->setCellValue('G1', 'Customer')
            ->setCellValue('H1', 'Country')
            ->setCellValue('I1', 'COD')
            ->setCellValue('J1', 'Checkout')
            ->setCellValue('K1', 'Paypal')
            ->setCellValue('L1', 'Payfort')
            ->setCellValue('M1', 'Bank')
            ->setCellValue('N1', 'Invoice USD')
            ->setCellValue('O1', 'Gross Margin AED')
            ->setCellValue('P1', 'Margin %')
            ->setCellValue('Q1', 'Discount')
            ->setCellValue('R1', 'Checkout')
            ->setCellValue('S1', 'Paypal')
            ->setCellValue('T1', 'Payfort')
            ->setCellValue('U1', 'Shipping AED')
            ->setCellValue('V1', 'NetMargin AED');
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(17);

        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
            'color' => array('rgb'=>'000000')
        );
        $none_border = array(
            'style' => PHPExcel_Style_Border::BORDER_NONE
        );
        $style_border = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            )
        );
        $style_borderR = array(
            'borders' => array(
                'right' => $default_border,
            )
        );
        $style_borderB = array(
            'borders' => array(
                'bottom' => $default_border,
            )
        );
        $style_borderNone = array(
            'borders' => array(
                'top' => $none_border,
                'bottom' => $none_border,
                'right' => $none_border,
                'left' => $none_border,
            )
        );
        $style_borderNoneT = array(
            'borders' => array(
                'top' => $none_border,
            )
        );
        $style_borderT = array(
            'borders' => array(
                'top' => $default_border,
            )
        );

        $objConditional1 = new PHPExcel_Style_Conditional();
        $objConditional1->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
        $objConditional1->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_LESSTHAN);
        $objConditional1->addCondition('0');
        $objConditional1->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKRED);
        $objConditional1->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        //----------------------- get DXBS inbound --------------------------//
        $sdsCollection = Mage::getModel('catalog/product')->getCollection();
        $sdsCollection->getSelect()->joinLeft(array("last" => 'gearup_sds_tracking'), "e.entity_id = last.product_id", array("update_last_at" => "last.update_last_at", "order_id" => "last.order_id", "inbound" => "last.inbound"));
        $sdsCollection->getSelect()->where("last.inbound != ''");


        $i = 2;
        //$itemsall = $this->countItem($period, 1) + 10 + count($sdsCollection) + 5;
        $itemsall = $this->countItem($period, 1) + count($sdsCollection) + 3;
        $itemsOnly = $this->countItem($period);
        $usdratio = $itemsall + 3;
        $ordersCollection = Mage::getResourceModel('hordermanager/sales_order_collection');
        $ordersCollection->setPeriodFilter($period);
        $ordersCollection->filterVisible();
        $ordersCollection->filterStatus();
        $ordersCollection->setOrder('order_id', 'ASC');
        foreach ($ordersCollection as $orderModel) {
            if ($orderModel->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CANCEL || $orderModel->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CLOSE) {
                continue;
            }
            $sdsall = Mage::helper('gearup_sds')->getSdsAll($orderModel->getId());
            $sdsall = ($sdsall == 'sdsall-full-blue')?'':$sdsall;
            $address = Mage::getModel('sales/order_address')->load($orderModel->getShippingAddressId());
            $country = Mage::getModel('directory/country')->loadByCode($address->getCountryId());
            $payment = $orderModel->getPayment()->getMethodInstance()->getCode();
            //$allActivePaymentMethods = Mage::getModel('payment/config')->getActiveMethods();
            $cod = '';
            $ck = '';
            $ppl = '';
            $bank = '';
            $cashu = '';
            switch ($payment) {
                case 'cashondelivery':
                    $cod = true;
                    break;
                case 'creditcard':
                    $ck = true;
                    break;
                case 'checkoutapijs':
                    $ck = true;
                    break;
                case 'paypal_standard':
                    $ppl = true;
                    break;
                case 'bankpayment':
                    $bank = true;
                    break;
                case 'payfortinstallments':
                    $cashu = true;
                    break;
                default:
                    break;
            }

            $items = $orderModel->getAllItems();
            
            
            $previousOrderId = '';
            $count_itemstart = 1;
            $count_items = count($items);
            $gearupConfiguratorRendere = new Gearup_Configurator_Block_Adminhtml_Sales_Order_View_Items_Renderer();
            foreach ($items as $item2) {

                if($gearupConfiguratorRendere->isConfigurator($item2)){    
                    $item2->setBaseRowTotal($item2->getProduct()->getFinalPrice()*$item2->getQtyOrdered());                               
                    $items2 = array_merge([$item2],$gearupConfiguratorRendere->getChilds($item2));
                    $count_items += count($items2)-1;
                    $itemsOnly += $count_items; 
                    $itemsall += $itemsOnly;
                    $usdratio += $itemsOnly;
                }
                else
                    $items2 = [$item2];
                foreach($items2 as $item){

                $product = Mage::getModel('catalog/product')->load($item->getProductId());

                if ($item->getDiscountAmount() > 1) {
                    $discount = $this->switchCurrency($item->getBaseDiscountAmount());
                } else {
                    $discount = '';
                }
                if ($previousOrderId && $orderModel->getIncrementId() == $previousOrderId) {
                    $orderIncrement = '';
                    $customer = '';
                    $countryName = '';
                    if ($sdsall) { $objPHPExcel->getActiveSheet()->mergeCells('A' . $multistart . ':A' . $i);  }
                    $objPHPExcel->getActiveSheet()->mergeCells('B' . $multistart . ':B' . $i);
                    $objPHPExcel->getActiveSheet()->mergeCells('G' . $multistart . ':G' . $i);
                    $objPHPExcel->getActiveSheet()->mergeCells('H' . $multistart . ':H' . $i);
                    $objPHPExcel->getActiveSheet()->mergeCells('U' . $multistart . ':U' . $i);
                } else {
                    $previousOrderId = $orderModel->getIncrementId();
                    $orderIncrement = $orderModel->getIncrementId();
                    $customer = $address->getLastname();
                    $countryName = $country->getName();
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray( $style_borderT );
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->applyFromArray( $style_borderT );
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$i)->applyFromArray( $style_borderT );
                    if ($count_items > 1) {
                        $multistart = $i;
                    }                    
                }
                $uae = 3.4;
                if($country->getName() == 'United Arab Emirates'){
                    $uae = 2.6;
                }
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderModel->getIncrementId());
                $_incl = Mage::helper('core')->currency(Mage::helper('rounding')->getRoundedTaxShipment($orderModel->getBaseShippingAmount() + $order->getBaseShippingTaxAmount())/Mage::app()->getStore()->getCurrentCurrencyRate(),false,false);
                $_incl2 = Mage::helper('core')->currency($orderModel->getBaseShippingAmount(),false,false);
                $taxRate = $item->getTaxPercent();

                $currentRate = $this->switchCurrency($item->getBaseRowTotal());
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $sdsall ? 'SDS' : '');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $orderIncrement);

                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, htmlspecialchars_decode($product->getName(),ENT_COMPAT));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $item->getQtyOrdered());
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $product->getPartNr());
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, number_format((float)$currentRate, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $customer);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $countryName);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $cod ? $currentRate : '');
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $ck ? $currentRate : '');
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $ppl ? $currentRate : '');
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $cashu ? $currentRate : '');
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, $bank ? $currentRate : '');
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, $sdsall ? 0 : '');
                $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, '=F'.$i.'-(N'.$i.'*$G$'.$usdratio.')');
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, $sdsall ? '' : '=O'.$i.'/(F'.$i.'/100)');
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, $discount);
                //$objPHPExcel->getActiveSheet()->setCellValue('R' . $i, $ck ? '=(J'.$i.'/100)*2.89' : '');
                $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, $ck ? '=(J'.$i.'/100)*'.$uae : '');
                $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, $ppl ? '=F'.$i.'-(((K'.$i.'/3.67)-(((K'.$i.'/3.67)*0.039)+0.3))*3.653)' : '');
                //$objPHPExcel->getActiveSheet()->setCellValue('T' . $i, $cashu ? '=(L'.$i.'/100)*3' : '');
                $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, $cashu ? '=(L'.$i.'/100)*2.55' : '');
                //$objPHPExcel->getActiveSheet()->setCellValue('U' . $i, $this->switchCurrency($orderModel->getBaseShippingAmount()));
                $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, $taxRate > 0 ? $_incl  : $_incl2 );
                if ($ppl) {
                    $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, '=O'.$i.'-S'.$i);
                } else if ($cashu) {
                    $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, '=O'.$i.'-T'.$i);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, '=O'.$i.'-R'.$i);
                }

                // set style SDS all

                if ($sdsall) {
                    $style_sdsall = array('font' => array('color' => array('rgb'=>'00AD4B'), 'bold' => true));
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->applyFromArray( $style_sdsall );
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray( $style_sdsall );
                }

                // set style
                $sds = Mage::helper('gearup_sds')->getSdsHorder($product, $periodId, $orderModel->getId());
                if ($sds) {
                    $style_sds = array('font' => array('color' => array('rgb'=>'027AC9')));
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray( $style_sds );
                }
                $objPHPExcel->getActiveSheet()->getStyle("C".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle("F".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("H".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("I".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("J".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("K".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("L".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("M".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("N".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("O".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("Q".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("R".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("S".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("T".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("U".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle("V".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                if ($count_itemstart == $count_items) {
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$i)->applyFromArray( $style_borderB );
                }

                // set round
                $objPHPExcel->getActiveSheet()->getStyle("F".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("I".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("J".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("K".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("L".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("M".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("N".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("O".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("P".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("Q".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("R".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("S".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("T".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("U".$i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle("V".$i)->getNumberFormat()->setFormatCode('#,##0.00');

                if ($sdsall) {
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }
                $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('T'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(17);

                $i++;
                $count_itemstart++;
            }
            }
        }

        $objPHPExcel->getActiveSheet()->duplicateConditionalStyle(
            array($objConditional1),
            'O2:P' .($itemsall)
        );
        /*$objPHPExcel->getActiveSheet()->insertNewRowBefore($itemsOnly + 2, 10);
        for ($r=2;$r<=11;$r++) {
            //$objPHPExcel->getActiveSheet()->setCellValue('R' . ($itemsOnly+$r), '=(J'.($itemsOnly+$r).'/100)*2.89');
            if ($r!=11){
                $objPHPExcel->getActiveSheet()->getStyle('A'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('B'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('C'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('D'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('E'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('F'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('G'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
                $objPHPExcel->getActiveSheet()->getStyle('H'.($itemsOnly+$r))->applyFromArray( $style_borderNone );
            }else {
                $objPHPExcel->getActiveSheet()->getStyle('B'.($itemsOnly+$r))->applyFromArray( $style_borderNoneT );
                $objPHPExcel->getActiveSheet()->getStyle('G'.($itemsOnly+$r))->applyFromArray( $style_borderNoneT );
                $objPHPExcel->getActiveSheet()->getStyle('H'.($itemsOnly+$r))->applyFromArray( $style_borderNoneT );
            }
        }*/

        if ($sdsCollection->getSize() > 0) {
            $itemslistOnly = $itemsOnly + 1;
            $inboundcount = $itemslistOnly + 2;
            $lineEndSds = $itemslistOnly + 1 + count($sdsCollection);
            foreach ($sdsCollection as $inbound) {
                $objPHPExcel->getActiveSheet()->mergeCells('B' . ($itemslistOnly + 2) . ':B' . $inboundcount);
                $objPHPExcel->getActiveSheet()->mergeCells('G' . ($itemslistOnly + 2) . ':G' . $inboundcount);

                $product = Mage::getModel('catalog/product')->load($inbound->getId());
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $inboundcount, 'Sklad');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $inboundcount, htmlspecialchars_decode($product->getName(),ENT_COMPAT));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $inboundcount, $inbound->getInbound());
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $inboundcount, $product->getPartNr());
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $inboundcount, 'Sklad');

                $objPHPExcel->getActiveSheet()->getStyle("C".$inboundcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$inboundcount)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$inboundcount)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getRowDimension($inboundcount)->setRowHeight(17);
                $inboundcount++;
            }

            $objPHPExcel->getActiveSheet()->getStyle('A'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('B'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('C'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('D'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('E'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('F'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('G'.$lineEndSds)->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle('H'.$lineEndSds)->applyFromArray( $style_borderB );
        }

        //$objPHPExcel->getActiveSheet()->insertNewRowBefore($itemsall + 1, 5);

        // set footer XLS
        $itemsall = $inboundcount;
        $sumline = $itemsall + 1;
        $objPHPExcel->getActiveSheet()->setCellValue("D".$sumline, '=SUM(D2:D'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("F".$sumline, '=SUM(F2:F'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("I".$sumline, '=SUM(I2:I'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("J".$sumline, '=SUM(J2:J'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("K".$sumline, '=SUM(K2:K'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("L".$sumline, '=SUM(L2:L'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("M".$sumline, '=SUM(M2:M'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("N".$sumline, '=SUM(N2:N'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("O".$sumline, '=SUM(O2:O'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("P".$sumline, '=AVERAGE(P2:P'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("Q".$sumline, '=SUM(Q2:Q'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("R".$sumline, '=SUM(R2:R'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("S".$sumline, '=SUM(S2:S'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("T".$sumline, '=SUM(T2:T'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("U".$sumline, '=SUM(U2:U'.$itemsall.')');
        $objPHPExcel->getActiveSheet()->setCellValue("V".$sumline, '=SUM(V2:V'.$itemsall.')');
//        $objPHPExcel->getActiveSheet()->setCellValue("U".$sumline, '=(U'.($itemsall+3).'/G'.($itemsall+2).')*G'.($itemsall+3));
//        $objPHPExcel->getActiveSheet()->setCellValue("V".$sumline, '=N'.$sumline.'-P'.$sumline.'-Q'.$sumline.'-R'.$sumline.'-S'.$sumline.'-T'.$sumline.'-U'.$sumline);

        $objPHPExcel->getActiveSheet()->setCellValue("F".($itemsall+2), 'USD invoice');
        $objPHPExcel->getActiveSheet()->setCellValue("F".($itemsall+3), 'USD/AED');
        $objPHPExcel->getActiveSheet()->setCellValue("F".($itemsall+4), 'USD to AED');

        $objPHPExcel->getActiveSheet()->setCellValue("G".($itemsall+2), '23.798');
        $objPHPExcel->getActiveSheet()->setCellValue("G".($itemsall+3), '3.6861');
        $objPHPExcel->getActiveSheet()->setCellValue("G".($itemsall+4), '3.693');

        $objPHPExcel->getActiveSheet()->setCellValue("J".($itemsall+2), '=J'.$sumline.'/100*2.89');
        $objPHPExcel->getActiveSheet()->setCellValue("K".($itemsall+2), '=(K'.$sumline.'/100)*6.65');
        $objPHPExcel->getActiveSheet()->setCellValue("O".($itemsall+2), '=O'.$sumline.'/(F'.$sumline.'/100)');
//        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+2), '=U'.$sumline.'/(N'.$sumline.'/100)');
//        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+2), '=V'.$sumline.'/(F'.$sumline.'/100)');

        $objPHPExcel->getActiveSheet()->setCellValue("T".($itemsall+4), 'Customs');
        $objPHPExcel->getActiveSheet()->setCellValue("T".($itemsall+5), 'DHL');
        $objPHPExcel->getActiveSheet()->setCellValue("T".($itemsall+6), 'Ptofit NET');

        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+4), '655');
        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+5), '=(U'.($itemsall+9).'/G'.($itemsall+2).')*G'.($itemsall+3));
        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+6), '=O'.$sumline.'-Q'.$sumline.'-R'.$sumline.'-S'.$sumline.'-T'.$sumline.'-U'.($itemsall+4).'-U'.($itemsall+5));

        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+4), '=U'.($itemsall+4).'/((N'.$sumline.'*G'.($itemsall+3).')/100)');
        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+5), '=U'.($itemsall+5).'/(O'.$sumline.'/100)');
        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+6), '=U'.($itemsall+6).'/(F'.$sumline.'/100)');

        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+9), '11666');
        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+10), '138');
        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+11), '=U'.($itemsall+9).'/U'.($itemsall+10));
        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+12), '=U'.($itemsall+11).'/G'.($itemsall+3));
        $objPHPExcel->getActiveSheet()->setCellValue("U".($itemsall+13), '=U'.($itemsall+12).'/3.67');

        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+10), 'Kg');
        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+11), 'CZ/Kg');
        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+12), 'Aed/Kg');
        $objPHPExcel->getActiveSheet()->setCellValue("V".($itemsall+13), 'USD/Kg');
        $objPHPExcel->getActiveSheet()->getStyle("V".($itemsall+10))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle("V".($itemsall+11))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle("V".($itemsall+12))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle("V".($itemsall+13))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle("D".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle("F".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline+3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("G".($sumline+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("G".($sumline+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle("G".($sumline+3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle("H".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("I".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("J".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("J".($sumline+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("K".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("K".($sumline+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("L".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("M".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("N".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("O".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("O".($sumline+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("P".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("Q".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("R".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("S".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("T".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("U".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        for($t=1;$t<14;$t++) {
            $objPHPExcel->getActiveSheet()->getStyle("U".($sumline+$t))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }
        $objPHPExcel->getActiveSheet()->getStyle("V".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle("V".($sumline+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $objPHPExcel->getActiveSheet()->getStyle("F".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("I".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("J".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("J".($sumline+1))->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("K".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("K".($sumline+1))->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("L".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("M".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("N".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("O".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("O".($sumline+1))->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("P".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("Q".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("R".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("S".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("T".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("U".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        for($u=1;$u<14;$u++) {
            $objPHPExcel->getActiveSheet()->getStyle("U".($sumline+$u))->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $objPHPExcel->getActiveSheet()->getStyle("V".$sumline)->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("V".($sumline+1))->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("V".($sumline+3))->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("V".($sumline+4))->getNumberFormat()->setFormatCode('#,##0.00');
        $objPHPExcel->getActiveSheet()->getStyle("V".($sumline+5))->getNumberFormat()->setFormatCode('#,##0.00');

        // set width
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30.3);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16.6);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(78.1);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(7.5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(31.7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16.6);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18.3);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(13.3);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(18.6);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(11.1);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10.4);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(11.9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(11.9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(11.9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(16.4);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(16.4);

        // set height footer
        $objPHPExcel->getActiveSheet()->getRowDimension($sumline)->setRowHeight(17);
        for($x=1;$x<14;$x++) {
            $objPHPExcel->getActiveSheet()->getRowDimension(($sumline+$x))->setRowHeight(17);
        }

        // set border
        $apball = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V');
        $apbs = array('A','B','C','D','E','F','G','H');
        $apbR = array('A','B','C','D','E','F','G','H','M','P');
        foreach ($apbs as $apb) {
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$apb.'1')->applyFromArray( $style_border );
        }
        foreach ($apball as $apbB) {
            $objPHPExcel->getActiveSheet()->getStyle('A1:'.$apbB.'1')->applyFromArray( $style_borderB );
        }
        foreach ($apbR as $apbr) {
            $objPHPExcel->getActiveSheet()->getStyle($apbr.'1:'.$apbr.$itemsall)->applyFromArray( $style_borderR );
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1:V'.$itemsall)->applyFromArray( $style_border );


        $style_header = array('font' => array('bold' => true));
        $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle('F2:M'.$i)->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline).':V'.($sumline))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline+1).':V'.($sumline+1))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline+2))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("F".($sumline+3))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("T".($sumline+3))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("T".($sumline+4))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("U".($sumline+3))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("U".($sumline+4))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("U".($sumline+5))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("V".($sumline+1))->applyFromArray( $style_header );

        $style_ptofit = array('font' => array('color' => array('rgb'=>'f29626'), 'bold' => true));
        $objPHPExcel->getActiveSheet()->getStyle("T".($sumline+5))->applyFromArray( $style_ptofit );
        $objPHPExcel->getActiveSheet()->getStyle("U".($sumline+5))->applyFromArray( $style_ptofit );

        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $strFileName = 'orderperiod-' . $period->getData('custom_period_id') . '.xls';
        $objWriter->setPreCalculateFormulas(FALSE);
        $objWriter->save($path . $strFileName);
        //Mage::log('memory use after = '.memory_get_usage(), null, 'exportperiod.log');
    }

    public function countItem($period, $head=NULL) {
        $ordersCollection = Mage::getResourceModel('hordermanager/sales_order_collection');
        $ordersCollection->setPeriodFilter($period);
        $ordersCollection->filterVisible();
        $ordersCollection->filterStatus();
        $ordersCollection->setOrder('order_id', 'ASC');
        $count = 0;
        foreach ($ordersCollection as $orderModel) {
            if ($orderModel->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CANCEL || $orderModel->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CLOSE) {
                continue;
            }
            $count = $count + count($orderModel->getAllItems());
        }
        if ($head) {
            return $count + 1;
        } else {
            return $count;
        }
    }

    public function exportperiodCSVsecond($periodId) {
        $period = Mage::getModel('hordermanager/period')->load($periodId);
        $path = Mage::getBaseDir() . '/media/dxbs/period/' . $period->getData('custom_period_id') . '/';
        $file = 'Period SDS report - ' . $period->getData('custom_period_id').'.csv';
        $csv = new Varien_File_Csv();
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $i = 0;
        $ordersCollection = Mage::getResourceModel('hordermanager/sales_order_collection');
        $ordersCollection->setPeriodFilter($period);
        $ordersCollection->filterVisible();
        $ordersCollection->filterStatus();
        $ordersCollection->setOrder('order_id', 'ASC');
        $previousOrderId = '';
        foreach ($ordersCollection as $orderModel) {
            if ($orderModel->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE || $orderModel->getStatus() == 'processing_shipped') {
                continue;
            }
            //$order = Mage::getModel('sales/order')->load(1069);
            $items = $orderModel->getAllItems();
            foreach ($items as $item) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $sds = Mage::helper('gearup_sds')->getSdsHorder($product, $periodId, $orderModel->getId());
                if (!$sds) {
                    continue;
                }
                if (file_exists($path.$file)) {
                    $oldContent = Mage::getModel('gearup_sds/tracking')->getCsvData($path.$file);
                } else {
                    $oldContent = '';
                }
                $head = array();
                $head['0'] = 'Product name';
                $head['1'] = 'Qty';
                $head['2'] = 'Part number';
                $head['3'] = 'Order number';

                if ($item->getDiscountAmount() > 1) {
                    $discount = $item->getDiscountAmount();
                } else {
                    $discount = '';
                }

                if ($previousOrderId && $orderModel->getIncrementId() == $previousOrderId) {
                    $orderIncrement = '';
                } else {
                    $previousOrderId = $orderModel->getIncrementId();
                    $orderIncrement = $orderModel->getIncrementId();
                }
                $completeData = array();
                $completeData['0'] = htmlspecialchars_decode($product->getName(),ENT_COMPAT);
                $completeData['1'] = $item->getQtyOrdered();
                $completeData['2'] = $product->getPartNr();
                $completeData['3'] = $orderIncrement;
                if ($oldContent) {
                    $csvdata = $oldContent;
                } else {
                    $csvdata = array();
                    $csvdata[] = $head;
                }
                $csvdata[] = $completeData;
                $csv->saveData($path.$file, $csvdata);
                $i++;
            }
        }
    }

    public function switchCurrency($convert) {
        return Mage::helper('core')->currency($convert,false,false);
    }

    public function exportSDSperiodXls($periodId) {
        require_once Mage::getBaseDir('lib') . DS .'PHPExcel.php';
        require_once Mage::getBaseDir('lib') . DS .'PHPExcel/IOFactory.php';
        $period = Mage::getModel('hordermanager/period')->load($periodId);
        $path = Mage::getBaseDir() . '/media/dxbs/period/' . $period->getData('custom_period_id') . '/';
        $periodIds = explode('-', $period->getData('custom_period_id'));
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $objPHPExcel = new PHPExcel();
        //$shareFont = new PHPExcel_Shared_Font();
        $objPHPExcel->getProperties()
            ->setCreator("Gearupme")
            ->setLastModifiedBy("Gearupme")
            ->setTitle('Stock_Outbound_' . Mage::getModel('core/date')->date('dmY'))
            ->setSubject('Stock_Outbound_' . Mage::getModel('core/date')->date('dmY'))
            ->setDescription('Stock_Outbound_' . Mage::getModel('core/date')->date('dmY'))
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Orderperiod");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'OutBound ' . $periodIds[1]);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', 'Name');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', 'QTY');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', 'Part number');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', 'Order');
        $objPHPExcel->getActiveSheet()->getStyle("A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(17);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(17);

        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
            'color' => array('rgb'=>'000000')
        );
        $style_border = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            )
        );
        $style_borderR = array(
            'borders' => array(
                'right' => $default_border,
            )
        );
        $style_borderB = array(
            'borders' => array(
                'bottom' => $default_border,
            )
        );
        $style_borderT = array(
            'borders' => array(
                'top' => $default_border,
            )
        );

        $i = 3;
        $ordersCollection = Mage::getResourceModel('hordermanager/sales_order_collection');
        $ordersCollection->setPeriodFilter($period);
        $ordersCollection->filterVisible();
        $ordersCollection->filterStatus();
        $ordersCollection->setOrder('order_id', 'ASC');
        foreach ($ordersCollection as $orderModel) {
            if ($orderModel->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CANCEL || $orderModel->getStatus() == Gearup_Sds_Helper_Data::ORDER_STATUS_CLOSE) {
                continue;
            }
            $sdsall = Mage::helper('gearup_sds')->getSdsAll($orderModel->getId());
            if ( $sdsall && ($orderModel->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE ||
                $orderModel->getStatus() == Gearup_Shippingffdx_Model_Tracktype::STATE_PROCESS_SHIPPED ||
                $orderModel->getStatus() == Gearup_Shippingffdx_Model_Tracktype::STATE_NEW_SHIPPED ||
                $orderModel->getStatus() == Gearup_Shippingffdx_Model_Tracktype::STATE_PROCESS_DELIVERED ||
                $orderModel->getStatus() == Gearup_Shippingffdx_Model_Tracktype::STATE_COMPLETE_DELIVERED ) )
            {
                continue;
            }
            $items = $orderModel->getAllItems();
            $previousOrderId = '';
            $count_itemstart = 1;
            $count_items = count($items);
            $gearupConfiguratorRendere = new Gearup_Configurator_Block_Adminhtml_Sales_Order_View_Items_Renderer();
            
            foreach ($items as $item2) {
                 if($gearupConfiguratorRendere->isConfigurator($item2)){                    
                    $items2 = array_merge([$item2],$gearupConfiguratorRendere->getChilds($item2));
                    $count_items += count($items2)-1;
                    $itemsOnly += $count_items;                   
                }
                else
                    $items2 = [$item2];
                foreach($items2 as $item){
                
                
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $sds = Mage::helper('gearup_sds')->getSdsHorder($product, $periodId, $orderModel->getId());
                if (!$sds) {
                    $count_items = $count_items - 1;
                    continue;
                }

                if ($previousOrderId && $orderModel->getIncrementId() == $previousOrderId) {
                    $orderIncrement = '';
                    $customer = '';
                    $objPHPExcel->getActiveSheet()->mergeCells('D' . $multistart . ':D' . $i);
                } else {
                    $previousOrderId = $orderModel->getIncrementId();
                    $orderIncrement = $orderModel->getIncrementId();
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->applyFromArray( $style_borderT );
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray( $style_borderT );
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray( $style_borderT );
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->applyFromArray( $style_borderT );
                    if ($count_items > 1) {
                        $multistart = $i;
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, htmlspecialchars_decode($product->getName(),ENT_COMPAT));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $item->getQtyOrdered());
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $product->getPartNr());
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $orderIncrement);

                // set style
                $objPHPExcel->getActiveSheet()->getStyle("A".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                if ($count_itemstart == $count_items) {
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$i)->applyFromArray( $style_borderB );
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->applyFromArray( $style_borderB );
                }

                $objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(17);

                $i++;
                $count_itemstart++;
            }
            }
        }

        // set footer XLS
        $sumline = $i;
        $objPHPExcel->getActiveSheet()->setCellValue("A".$sumline, Mage::helper('gearup_sds')->__('Total Pcs:'));
        $objPHPExcel->getActiveSheet()->setCellValue("B".$sumline, '=SUM(B3:B'.($i-1).')');
        $objPHPExcel->getActiveSheet()->getStyle("A".$sumline)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // set width
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(78.1);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(7.5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25.7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18.3);

        $objPHPExcel->getActiveSheet()->getRowDimension($sumline)->setRowHeight(17);

        // set border
        $apball = array('A','B','C','D');
        foreach ($apball as $apbB) {
            $objPHPExcel->getActiveSheet()->getStyle('A2:'.$apbB.'2')->applyFromArray( $style_borderB );
            $objPHPExcel->getActiveSheet()->getStyle($apbB.'2:'.$apbB.($i-1))->applyFromArray( $style_borderR );
        }
        $objPHPExcel->getActiveSheet()->getStyle('A2:D'.($i-1))->applyFromArray( $style_border );


        $style_header = array('font' => array('bold' => true));
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("A".($sumline))->applyFromArray( $style_header );
        $objPHPExcel->getActiveSheet()->getStyle("B".($sumline))->applyFromArray( $style_header );

        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $strFileName = 'Stock_Outbound_' . Mage::getModel('core/date')->date('dmY') . '.xls';
        $objWriter->setPreCalculateFormulas(FALSE);
        $objWriter->save($path . $strFileName);
        //Mage::log('memory use after = '.memory_get_usage(), null, 'exportperiod.log');
    }
}