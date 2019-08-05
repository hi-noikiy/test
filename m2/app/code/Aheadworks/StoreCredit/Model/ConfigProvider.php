<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Class ConfigProvider
 *
 * @package Aheadworks\StoreCredit\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'awStoreCredit' => [
                    'removeUrl' => $this->getRemoveUrl(),
                ],
            ]
        ];
        return $config;
    }

    /**
     * Retrieve URL to remove store credit balance
     *
     * @return string
     */
    protected function getRemoveUrl()
    {
        return $this->urlBuilder->getUrl('aw_store_credit/cart/remove');
    }
}
