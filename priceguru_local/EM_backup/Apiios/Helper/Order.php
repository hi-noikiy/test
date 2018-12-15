<?php
class EM_Apiios_Helper_Order extends Mage_Tax_Helper_Data
{
    protected $_store = null;
    protected $_source = null;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    public function getPriceOrderItem($item,$key = 'price'){
        $result = array();
        $helperSale = Mage::helper('sales');
        $flat = 0;
        $value = 0;
        $order = $item->getOrder();
        if ($this->displaySalesBothPrices() || $this->displaySalesPriceExclTax()){
            if($key == 'price')
                $value = $order->formatPriceTxt($item->getPrice());
            else
                $value = $order->formatPriceTxt($item->getRowTotal());
            $result['exc'] = array(
                'label' =>  $helperSale->__('Excl. Tax'),
                'value' =>  $value
            );
            $flat++;
        }
        if($this->displaySalesBothPrices() || $this->displaySalesPriceInclTax()){
            if($key == 'price')
                $_incl = Mage::helper('checkout')->getPriceInclTax($item);
            else {
                $_incl = Mage::helper('checkout')->getSubtotalInclTax($item);
            }
            if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'sales') && (float)$item->getWeeeTaxAppliedAmount()){
                $value = $order->formatPriceTxt($_incl+$item->getWeeeTaxAppliedAmount());
            } else {
                $value = $order->formatPriceTxt($_incl-$item->getWeeeTaxDisposition());
            }
            $result['inc'] = array(
                'label' =>  $helperSale->__('Incl. Tax'),
                'value' =>  $value
            );
            $flat++;
        }

        if($flat < 2){
            $result = array('regular_price' => $value);
        }
        return $result;
    }

    public function _initSubtotal($order)
    {
        $store  = $this->getStore();
        $this->_source = $order;
        if ($this->_config->displaySalesSubtotalBoth($store)) {
            $subtotal       = (float) $this->_source->getSubtotal();
            $baseSubtotal   = (float) $this->_source->getBaseSubtotal();
            $subtotalIncl   = (float) $this->_source->getSubtotalInclTax();
            $baseSubtotalIncl= (float) $this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $subtotal+ $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $baseSubtotal + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount();
            }
            $subtotalIncl = max(0, $subtotalIncl);
            $baseSubtotalIncl = max(0, $baseSubtotalIncl);
            $totalExcl = array(
                'code'      => 'subtotal_excl',
                'value'     => $order->formatPriceTxt($subtotal),
                'base_value'=> $order->formatPriceTxt($baseSubtotal),
                'label'     => $this->__('Subtotal (Excl.Tax)')
            );
            $totalIncl = array(
                'code'      => 'subtotal_incl',
                'value'     => $order->formatPriceTxt($subtotalIncl),
                'base_value'=> $order->formatPriceTxt($baseSubtotalIncl),
                'label'     => $this->__('Subtotal (Incl.Tax)')
            );
            return array('exc' => $totalExcl,'inc'=>$totalIncl);
        } elseif ($this->_config->displaySalesSubtotalInclTax($store)) {
            $subtotalIncl   = (float) $this->_source->getSubtotalInclTax();
            $baseSubtotalIncl= (float) $this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $this->_source->getSubtotal()
                    + $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $this->_source->getBaseSubtotal()
                    + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount();
            }

            return array('regular_price' => $order->formatPriceTxt(max(0, $subtotalIncl)));
            //    $total->setBaseValue(max(0, $baseSubtotalIncl));
        }
    }

    public function _initShipping($order)
    {
        $store  = $this->getStore();
        $this->_source = $order;
        if ($this->_config->displaySalesShippingBoth($store)) {
            $shipping           = (float) $this->_source->getShippingAmount();
            $baseShipping       = (float) $this->_source->getBaseShippingAmount();
            $shippingIncl       = (float) $this->_source->getShippingInclTax();
            if (!$shippingIncl) {
                $shippingIncl   = $shipping + (float) $this->_source->getShippingTaxAmount();
            }
            $baseShippingIncl   = (float) $this->_source->getBaseShippingInclTax();
            if (!$baseShippingIncl) {
                $baseShippingIncl = $baseShipping + (float) $this->_source->getBaseShippingTaxAmount();
            }

            $totalExcl = array(
                'code'      => 'shipping_excl',
                'value'     => $order->formatPriceTxt($shipping),
                'base_value'=> $order->formatPriceTxt($baseShipping),
                'label'     => $this->__('Shipping & Handling (Excl.Tax)')
            );
            $totalIncl = array(
                'code'      => 'shipping_incl',
                'value'     => $order->formatPriceTxt($shippingIncl),
                'base_value'=> $order->formatPriceTxt($baseShippingIncl),
                'label'     => $this->__('Shipping & Handling (Incl.Tax)')
            );
            return array('exc' => $totalExcl,'inc' => $totalIncl);
        } elseif ($this->_config->displaySalesShippingInclTax($store)) {
            $shippingIncl       = $this->_source->getShippingInclTax();
            if (!$shippingIncl) {
                $shippingIncl = $this->_source->getShippingAmount()
                    + $this->_source->getShippingTaxAmount();
            }
            $baseShippingIncl   = $this->_source->getBaseShippingInclTax();
            if (!$baseShippingIncl) {
                $baseShippingIncl = $this->_source->getBaseShippingAmount()
                    + $this->_source->getBaseShippingTaxAmount();
            }
            return array('regular_price' => $order->formatPriceTxt($shippingIncl));
        }
    }

    protected function _initDiscount()
    {
//        $store  = $this->getStore();
//        $parent = $this->getParentBlock();
//        if ($this->_config->displaySales) {
//
//        } elseif ($this->_config->displaySales) {
//
//        }
    }

    public function _initGrandTotal($order)
    {
        $store  = $this->getStore();
        $this->_source = $order;
        if ($this->_config->displaySalesTaxWithGrandTotal($store)) {
            $grandtotal         = $this->_source->getGrandTotal();
            $baseGrandtotal     = $this->_source->getBaseGrandTotal();
            $grandtotalExcl     = $grandtotal - $this->_source->getTaxAmount();
            $baseGrandtotalExcl = $baseGrandtotal - $this->_source->getBaseTaxAmount();
            $grandtotalExcl     = max($grandtotalExcl, 0);
            $baseGrandtotalExcl = max($baseGrandtotalExcl, 0);
            $totalExcl = array(
                'code'      => 'grand_total_excl',
                'strong'    => true,
                'value'     => $order->formatPriceTxt($grandtotalExcl),
                'base_value'=> $order->formatPriceTxt($baseGrandtotalExcl),
                'label'     => $this->__('Grand Total (Excl.Tax)')
            );
            $totalIncl = array(
                'code'      => 'grand_total_incl',
                'strong'    => true,
                'value'     => $order->formatPriceTxt($grandtotal),
                'base_value'=> $order->formatPriceTxt($baseGrandtotal),
                'label'     => $this->__('Grand Total (Incl.Tax)')
            );
            return array('exc' => $totalExcl,'inc' => $totalIncl);
        } else {
            return array('regular_price' => $order->formatPriceTxt($this->_source->getGrandTotal()));
        }
    }
}
?>
