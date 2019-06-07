<?php

/**
 * Helper Data
 */
class FFDX_Shipping_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getCodeService() {
        return array('SRV1' => 'DOMESTIC EXPRESS', 'SRV2' => 'PICKUP SERVICE', 'SRV3' => 'INTL OUTBOUND',
            'SRV5' => 'TIME DEFINITE', 'SRV6' => 'DOMESTIC STANDARD',
            'SRV8' => 'ROUND TRIP (SIGN. BACK)', 'SRV12' => 'CLEARANCE', 'SRV13' => 'AIR FREIGHT',
            'SRV14' => 'LAND FREIGHT', 'SRV15' => 'SEA FREIGHT', 'SRV16' => 'MONTHLY CHARGE',
            'SRV17' => 'REMAILING', 'SRV19' => 'BULK MAIL', 'SRV21' => 'INVOICE DISTRIBUTION', 'SRV24' => 'CO CHECK OR CASH COLLECTION',
            'SRV26' => 'MAIL BOX SERVICES', 'SRV27' => 'MAIL MGMT SERVICE', 'SRV28' => 'MY BOX', 'SRV30' => 'INTL INBOUND', 'SRV33' => 'RANDOM DISTRIBUTION',
            'SRV34' => 'PASSPORT SERVICE', 'SRV35' => 'SE - UMRAH', 'SRV36' => 'SE - TRANSIT', 'SRV37' => 'SE - ONE VISIT',
            'SRV38' => 'SE - MULTI VISIT', 'SRV39' => 'SE - PP AMERICAN', 'SRV40' => 'DHL PICKUP',
        );
    }

    public function getShipmentTypeCode() {
        return array(
            'SHPT2' => 'NON DOC',            
            'SHPT1' => 'DOC',            
        );
    }

}
