<?php

class Gearup_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getProductStatus($_product)
    {
        $result = '';
        $qty = (int)$_product->getStockItem()->getQty();
        if ($_product->isSaleable()) {
            $result .= '<span class="cart-stock">';
            if ($qty > 5 && $qty <= 10) {
                $result .= "<span>" . $this->__('In Stock &gt; 5 pcs') . "</span>";
            } elseif ($qty > 3 && $qty <= 5) {
                $result .= "<span>" . $this->__('In Stock 3-5 pcs') . "</span>";
            } elseif ($qty >= 1 && $qty <= 3) {
                $result .= "<span>" . $this->__('In Stock 1-3 pcs') . "</span>";
            } else {
                $result .= "<span>" . $this->__('In Stock') . "</span>";
            }

            $result .= '</span>';
        } else {
            $date = $_product->getAvailabilityEstimationDate() ? $_product->getAvailabilityEstimationDate()->format('d-m-Y') : '';
            $result .= '<p class="availability out-of-stock">';
            if ($days = $_product->getDateDiff()) {
                if ($days == 1) {
                    $result .= '<span title="' . $date . '">' . $_product->__('Expecting tomorrow') . '</span>';
                } elseif ($days > 1 && $days <= 14) {
                    $result .= '<span title="' . $date . '">' . $_product->__('Expecting in %d days',
                            $days) . '</span>';
                } else {
                    $result .= '<span>On Request</span>';
                }
            } else {
                $result .= '<span>On Request</span>';
            }
            $result .= '</p>';
        }


        return $result;
    }
}
	 