<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper\Rest;


//use \Magento\Shipping\Model\Config;
//use \CollinsHarper\CanadaPost\Helper\Data as Config;

use \Magento\Store\Model\Information;
use \Magento\Sales\Model\Order\Shipment;

/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Request extends \CollinsHarper\CanadaPost\Helper\AbstractHelp
{

    /**
     *
     * @param string $service_url
     * @param xml string $xmlRequest
     * @param bool $return_file
     * @param array $headers
     * @param string $method
     * @return string
     */
    public function send($service_url, $xmlRequest = '', $return_file = false, $headers = array(), $method='') {

        $this->_chLogged->info(__METHOD__ . "canada post service url: " . $service_url);

        $curl = curl_init($service_url);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        //it seems to be working without certificate
        //curl_setopt($curl, CURLOPT_CAINFO, realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../../../third-party/cert/cacert.pem'); // Mozilla cacerts

        if (!empty($xmlRequest) || $method == self::API_METHOD_POST) {

            curl_setopt($curl, CURLOPT_POST, true);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);

            $this->_chLogged->info(__METHOD__ . "CP Request:\n" . $xmlRequest);

        }

        if (!empty($method) && $method == self::API_METHOD_DELETE) {

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, self::API_METHOD_DELETE);

            curl_setopt($curl, CURLOPT_HEADER, 1);

        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, !$return_file);

        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($curl, CURLOPT_USERPWD, $this->_getTokenData());

        if (!$return_file && empty($headers)) {

            if (preg_match(self::PREG_MANIFEST, $service_url)) {

                $headers = $this->_header_manifest;

            } else if (preg_match(self::PREG_POST_OFFICE, $service_url)) {

                $headers = $this->_header_post_office;

            } else {

                $headers = $this->_header_rate;

            }

        }

        if (!$this->isTestMode()) {

            $headers[] = $this->_header_platform_id . $this->getConfigValue(self::XML_PATH_PLATFORM_ID);

        }

        if (!empty($headers)) {

            $headers[] = $this->_header_language . (
                ($this->getConfigValue(\Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE) == self::LANG_MAGE_FR_CA) ?
                    self::LANG_CP_FR :
                    self::LANG_CP_EN );
            $this->_chLogged->info(__METHOD__ . __LINE__);
            $this->_chLogged->info(__METHOD__ . print_r($headers, 1));

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        }

        if (!$return_file) {

            try {

                $response = curl_exec($curl);

            } catch (Exception $e) {

                $this->_chLogged->critical($e->getMessage());

            }

            $error = curl_error($curl);

            if (!empty($error)) {

                $this->_chLogged->info(__METHOD__ . "request error: " . $error);

                $response = null;

                $this->error = self::API_METHOD_CONNECTION_ERROR;

            }

            $this->_chLogged->info(__METHOD__ . "CP Response:\n" . $response);

            if (!empty($method) && $method == self::API_METHOD_DELETE) {
                return (curl_getinfo($curl,CURLINFO_HTTP_CODE) == self::API_METHOD_DELETE_SUCCESS);
            }

            return $response;

        } else {

            curl_exec($curl);

        }

    }

}