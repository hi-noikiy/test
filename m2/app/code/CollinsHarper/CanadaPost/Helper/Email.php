<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper;


/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{

    // TODO identify datatype of shipment
    /**
     * Send email about return
     *
     * @param string $email
     * @param string $name
     * @param string $return_label_url
     * @param Magento\Sales\Model\Order\Shipment $shipment
     * @param array $customer_info
     * @return array
     */
    public function sendReturn($email, $name='', $return_label_url, $shipment, $customer_info = array()) {

        // TODO this does not work
        throw new \Exception(" broke");

    }

}
