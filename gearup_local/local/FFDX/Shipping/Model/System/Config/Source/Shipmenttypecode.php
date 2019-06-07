<?php

/**
 * Frontier Force
 */
class FFDX_Shipping_Model_System_Config_Source_Shipmenttypecode {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $ffdxHelper = Mage::helper('ffdxshipping');
        $shipmentTypeCode = $ffdxHelper->getShipmentTypeCode();
        foreach ($shipmentTypeCode as $k => $v):
            $return[] = ['value' => $k, 'label' => $v];
        endforeach;

        return $return;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        $ffdxHelper = Mage::helper('ffdxshipping');
        $shipmentTypeCode = $ffdxHelper->getShipmentTypeCode();
        foreach ($shipmentTypeCode as $k => $v):
            $return[$k] = $v;
        endforeach;

        return $return;
    }

}
