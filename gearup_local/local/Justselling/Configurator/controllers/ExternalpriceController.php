<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_ExternalpriceController extends Mage_Core_Controller_Front_Action
{
    public function priceAction()
    {
        $params = $this->getRequest()->getParams();
        $value = "0";

        $option_id = $params['option_id'];
        if( isset($option_id) ) {
            unset($params['option_id']);

            $tempalate = Mage::getModel("configurator/option")->load($option_id);
            $url = $tempalate->getUrl();
            $decimalPlace = $tempalate->getDecimalPlace();

            if(isset($url)) {
                $get_params = "";
                foreach ($params as $key => $value) {
                    $option = Mage::getModel("configurator/option")->load($key);
                    if ($option->getId()) {
                        if (strlen($get_params) > 0) {
                            $get_params .= "&";
                        }
                        $get_params .= urlencode($option->getAltTitle())."=".urlencode($value);
                    }
                }
                if (strlen($get_params) > 0) {
                    $url .= "?".$get_params;
                }

                $option = Mage::getModel("configurator/option")->load($option_id);

                $http = curl_init($url);
                curl_setopt($http, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($http);
                $statusCode = curl_getinfo($http, CURLINFO_HTTP_CODE);

                if ($statusCode == 200) {
                    $body = html_entity_decode($response);
                    if (!empty($body)) {
                        // check content type
                        if (false !== ($contentType = curl_getinfo($http, CURLINFO_CONTENT_TYPE))) {
                            if (strstr($contentType, 'application/text')){
                                $value = $body;
                            } elseif (strstr($contentType, 'application/json')) {
                                $decodedResponse = Zend_Json::decode($body);
                                if(is_array($decodedResponse) && count($decodedResponse) > 0){
                                    if ($option->getDefaultValue() && isset($decodedResponse[$option->getDefaultValue()])) {
                                        $value = $decodedResponse[$option->getDefaultValue()];
                                    } else {
                                        $value = reset($decodedResponse);
                                    }
                                }
                            } elseif (strstr($contentType, 'text/xml')) {
                                if ($option->getId() && $option->getDefaultValue()) {
                                    // Delete namespaces from document
                                    $simplexml= new SimpleXMLElement($body);
                                    $nspaces = $simplexml->getDocNamespaces();
                                    foreach ($nspaces as $nspace) {
                                        $str = "xmlns=\"".$nspace."\"";
                                        $body= str_replace($str,"",$body);
                                    }

                                    $simplexml= new SimpleXMLElement($body);
                                    $path = $option->getDefaultValue();
                                    $nodes =  $simplexml->xpath($path);
                                    $value = (string)$nodes[0];
                                }
                            }
                        }
                    }

                } else {
                    Mage::Log("can't reach external url to get value information: " .$url);
                }
            }
        }

        // round value by decimalPlace value set at backend
        if(isset($decimalPlace)){
            $value = (string) round ($value,$decimalPlace);
        }
        $this->getResponse()->setBody($value);
    }
}