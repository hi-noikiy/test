<?php


class Admitad_Tracking_Helper_Admitad extends Mage_Core_Helper_Abstract
{

    const TYPE_INACTIVE = 0;
    const TYPE_SALE = 1;

    public function getTariffData($product)
    {
        $configuration = json_decode(
            Mage::getStoreConfig(
                'admitadtracking/general/configuration',
                Mage::app()->getStore()
            ), true
        );
        $sections = $product->getCategoryIds();
        $defaultData = array();
        $configuration = isset($configuration[1]) ? $configuration[1] : array();
        foreach ($configuration as $actionCode => $actionData) {
            if (!$actionData['type']) {
                continue;
            }

            foreach ($actionData['tariffs'] as $tariffCode => $data) {
                if (empty($defaultData) && !empty($data['categories'])) {
                    $defaultData = array(
                        'action_code' => $actionCode,
                        'tariff_code' => $tariffCode,
                    );
                }

                $tariffSections = array_values($data['categories']);

                if (array_intersect($sections, $tariffSections)) {
                    return array(
                        'action_code' => $actionCode,
                        'tariff_code' => $tariffCode,
                    );
                }
            }
        }

        return $defaultData;
    }


    public static function admitadPostback($campaignCode, $postbackKey, $orderId, array $positions, array $parameters = array(), $uid = null)
    {
        $positions = array_values($positions);

        $defaults = array(
            'payment_type' => 'sale',
            'tariff_code' => 1,
        );

        $global = array_merge(
            array(
                'campaign_code' => $campaignCode,
                'postback' => true,
                'postback_key' => $postbackKey,
                'response_type' => 'img',
                'action_code' => 1,
                'adm_method' => 'plugin',
                'adm_method_name' => 'magento1',
                'action_useragent' => Mage::helper('core/http')->getHttpUserAgent(),
            ), $parameters
        );

        $admitadPositions = static::generateAdmitadPositions($uid, $orderId, $positions, array_merge($global, $defaults));

        foreach ($admitadPositions as $i => $position) {
            $parts = array();
            foreach ($position as $key => $value) {
                $parts[] = $key . '=' . urlencode($value);
            }

            $url = 'https://ad.admitad.com/r?' . implode('&', $parts);
            if (!function_exists('curl_init')) {
                file_get_contents($url);
                continue;
            }

            $cl = curl_init();

            curl_setopt($cl, CURLOPT_URL, $url);
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);

            curl_exec($cl);
        }
    }

    public static function generateAdmitadPositions($uid, $orderId, array $positions, array $parameters = array())
    {
        $config = array_merge(
            array(
                'uid' => $uid,
                'order_id' => $orderId,
                'position_count' => count($positions),
            ), $parameters
        );

        foreach ($positions as $index => &$position) {
            $position = array_merge(
                $config, array(
                'position_id' => $index + 1,
            ), $position
            );
        }

        return $positions;
    }
}
