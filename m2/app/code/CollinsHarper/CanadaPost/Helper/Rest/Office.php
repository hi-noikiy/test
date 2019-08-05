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
class Office extends Request
{

    /**
     * 
     * @param string $postal_code
     * @return string
     */
    public function getNearest($postal_code)
    {

        $url = sprintf(self::API_PATH_SERVICE, $this->getBaseUrl(),
            urlencode($this->formatPostalCode($postal_code)),
            $this->getConfigValue(self::XML_PATH_PO_LIST_SIZE));


        return $this->send($url, '', false, $this->_header_post_office);

    }

    /**
     * 
     * @param string $url
     * @return string
     */
    public function getDetails($url)
    {
        return $this->send($url, '', false, $this->_header_post_office);

    }

}