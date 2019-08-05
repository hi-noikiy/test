<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Helper;

class Distance
{
    /**
     * @var \Magento\Framework\HTTP\ClientFactory
     */
    private $clientUrl;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var System
     */
    private $system;

    /**
     * Distance constructor.
     * @param \Magento\Framework\HTTP\ClientFactory $clientUrl
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param System $system
     */
    public function __construct(
        \Magento\Framework\HTTP\ClientFactory $clientUrl,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        System $system
    ) {
        $this->clientUrl = $clientUrl;
        $this->jsonDecoder = $jsonDecoder;
        $this->system = $system;
    }

    /**
     * Prepare urlencode string to send it as parameter to google
     *
     * @param array $data
     * @return string
     */
    public function prepareAddressForGoogle($data)
    {
        $address = "";
        $arrayCodes = ['country', 'country_id', 'state', 'region', 'city', 'address', 'street', 'zip'];
        foreach ($arrayCodes as $code) {
            if (isset($data[$code]) && !empty($data[$code])) {
                if ($code == 'region') {
                    /** If address was save, region will be an array on M2.2.* */
                    if (is_array($data[$code])) {
                        $data[$code] = (string)$data[$code][$code];
                    }
                    /** If address was saved, region will be an object on M2.1.* */
                    if (is_object($data[$code])) {
                        $data[$code] = (string)$data[$code]->getRegion();
                    }
                }
                if (strlen($address) > 0) {
                    $address .= " ";
                }
                $address .= $data[$code];
            }
        }

        return urlencode($address);
    }

    /**
     * Send request to google to get coordinates by address
     *
     * @param $address
     * @return bool|array
     */
    public function getCoordinatesByAddress($address)
    {
        if ($this->system->isMultiEnabled()
            && !$this->system->isUseGoogleForDistance()
            && $key = $this->system->getGoogleMapsKey()
        ) {
            $key = "&key=" . $this->system->getGoogleMapsKey();
            $clientUrl = $this->clientUrl->create();
            $url = $this->system->getGeocodeUrl() . 'address=' . $address . $key;
            $clientUrl->get($url);
            if ($clientUrl->getStatus() == \Amasty\MultiInventory\Model\Dispatch::STATUS_RESPONSE) {
                $response = $this->jsonDecoder->decode($clientUrl->getBody());
                if (!empty($response) && isset($response['results'][0]['geometry']['location'])
                    && $response['status'] == 'OK'
                ) {
                    return $response['results'][0]['geometry']['location'];
                }
            }
        }

        return false;
    }
}
