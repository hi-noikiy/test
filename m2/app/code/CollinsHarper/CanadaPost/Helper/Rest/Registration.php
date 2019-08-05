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
class Registration extends Request
{


    // TODO we should not need this the config should allow for this to work
    /**
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        $url = $this->getConfigValue(self::XML_PATH_URL);

        if (!preg_match('/\/$/', $url)) {

            $url .= '/';

        }

        return $url;

    }

    /**
     * 
     * @return string
     */
    public function _getTokenData()
    {
        return $this->getConfigValue(self::XML_PATH_PLATFORM_LOGIN) . ':' . $this->getConfigValue(self::XML_PATH_PLATFORM_PASSWORD);
    }

    /**
     * 
     * @return string
     */
    public function getRegistrationToken() {

        // TODO the trailing slash may upset them?
        // $url = $this->forceLive()->getBaseUrl().'ot/token';
        $url = rtrim(sprintf(self::API_PATH_REGISTRATION, $this->forceLive()->getBaseUrl(), ''),'/');

        $headers = $this->_header_registration;

        $response = $this->send($url, '', false, $headers, 'POST');

        $xml = new \SimpleXMLElement($response);

        $token = false;

        if (!empty($xml->{'token-id'})) {

            $token = $xml->{'token-id'};

        } else if (!empty($xml->message->description)) {

            $this->error = $xml->message->description;

        }

        return $token;

    }

    /**
     * 
     * @param string $token
     * @return bool
     */
    public function getRegistrationData($token) {

        $url = sprintf(self::API_PATH_REGISTRATION, $this->forceLive()->getBaseUrl(), $token);

        $response = $this->send($url, '', false, $this->_header_registration);

        $xml = new \SimpleXMLElement($response);

        if (!empty($xml->message->description)) {

            $this->error = $xml->message->description;

            $xml = false;

        }

        return $xml;

    }

}