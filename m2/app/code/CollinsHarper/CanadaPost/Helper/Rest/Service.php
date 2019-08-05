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
class Service extends Request
{
    /**
     *
     * @param string $service_code
     * @param string $country_code
     * @return SimpleXMLElement
     */
    public function getInfo($service_code, $country_code)
    {

        $url = sprintf(self::API_PATH_SERVICE, $this->getBaseUrl(), $service_code, $country_code);

        // TODO note this did not have a content type
        $response = $this->send($url, '', false, $this->_header_rate);

        $xml = new \SimpleXMLElement($response);

        if (!empty($xml->message->description)) {

            $this->error = $xml->message->description;

        }

        return $xml;

    }
}