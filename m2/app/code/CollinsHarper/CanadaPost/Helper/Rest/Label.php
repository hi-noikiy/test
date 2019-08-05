<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper\Rest;

/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Label extends Request
{

    /**
     * 
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param boolean $return_transfer
     * @return boolean
     */
    public function getPdf($shipment, $return_transfer = 1)
    {

        $label_data = $this->getShipmentLinkModel()->create()->getLabelDataByOrderId($shipment->getOrderId());


        if (!empty($label_data)) {

            if ($return_transfer) {

                header('Content-type: ' . $label_data['media_type']);

                header('Content-Disposition: attachment; filename="label-' . date('Y-m-d--H-i-s') . '.pdf"');

                $this->send($label_data['url'], '', 1, $this->_header_pdf);

                return true;

            } else {

                return $this->send($label_data['url'], '', 1, $this->_header_pdf);

            }

        } else {

            return false;

        }

    }
}