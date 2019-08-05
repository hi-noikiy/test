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
class Tracking extends Request
{
    
    /**
     * 
     * @param int $pin_number
     * @return string
     */
    public function getDetails($pin_number)
    {

        $url = sprintf(self::API_PATH_TRACKING, $this->getBaseUrl(), $pin_number);

        return $this->send($url, null, false, $this->_header_tracking);

    }

}