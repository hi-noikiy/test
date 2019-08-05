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
class Manifest extends Request
{

    /**
     * 
     * @param string $url
     * @param string $mediaType
     * @return string
     */
    public function getManifest($url, $mediaType = '')
    {

        if (!$mediaType) {
            $headers = $this->_header_transmit;
        } else {
            $headers = array(
                'Content-Type: ' . $mediaType,
                'Accept: ' . $mediaType
            );
        }

        return $this->send($url, '', false, $headers);

    }

    /**
     * 
     * @param string $url
     * @param int $return_transfer
     * @return boolean
     */
    public function getPdf($url, $return_transfer = 1)
    {

        if (!empty($url)) {

            if ($return_transfer) {

                header('Content-type: application/pdf');

                header('Content-Disposition: attachment; filename="manifest-' . date('Y-m-d--H-i-s') . '.pdf"');

                $this->send($url, '', 1, $this->_header_pdf);

                return true;

            } else {

                return $this->send($url, '', 1, $this->_header_pdf);

            }

        } else {

            return false;

        }

    }

}