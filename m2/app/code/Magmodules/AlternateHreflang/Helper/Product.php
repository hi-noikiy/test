<?php
/**
 * Copyright © 2018 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Registry;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

class Product extends AbstractHelper
{

    /**
     * @var General
     */
    private $generalHelper;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ProductHelper
     */
    private $productHelper;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Product constructor.
     *
     * @param Context           $context
     * @param ProductRepository $productRepository
     * @param ProductHelper     $productHelper
     * @param General           $generalHelper
     * @param Registry          $registry
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        ProductHelper $productHelper,
        GeneralHelper $generalHelper,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->generalHelper = $generalHelper;
        $this->registry = $registry;
        $this->productHelper = $productHelper;
        $this->logger = $context->getLogger();
    }

    /**
     * Returns all alternate Product url's in array
     *
     * @return array|bool
     */
    public function getAlternateData()
    {
        if ($this->generalHelper->getEnabled('product')) {
            $alternateData = [];
            $storeId = $this->generalHelper->getCurrentStore();
            $targetData = $this->generalHelper->getTargetData($storeId);

            if (empty($targetData['group_id'])) {
                return false;
            }

            $groupId = $targetData['group_id'];
            $product = $this->getCurrentProduct();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            $canonical = $this->generalHelper->getCanonicalEnabled();

            if ($canonical) {
                $canonicalCheck = $this->getCanonicalCheck($product, $currentUrl);
                if (empty($canonicalCheck)) {
                    $alternateData['error'] = __('Current product URL is not the canonical URL.');
                    return $alternateData;
                }
            }

            foreach ($targetData[$groupId] as $row) {
                if ($storeId != $row['store_id']) {
                    $url = $this->getProductUrlByStore($product, $row['store_id'], $canonical);
                    if ($url) {
                        $languageCode = $row['language_code'];
                        $alternateData['urls'][$languageCode] = $url;
                    }
                } else {
                    $url = $this->getProductUrlByStore($product, $row['store_id'], $canonical);
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                }
            }

            if (empty($alternateData['urls'])) {
                $alternateData['error'] = __('No Alternate URLs found.');
                return $alternateData;
            }

            if (count($alternateData['urls']) == 1) {
                $alternateData['error'] = __('Only one Alternate URL Found (%1). Needs at least two.',
                    implode('', $alternateData['urls']));
                return $alternateData;
            }

            $canonical = $this->generalHelper->getCanonicalEnabled();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            if (isset($currentAlternate) && $canonical && $currentAlternate != $currentUrl) {
                $alternateData['error'] = __('Current URL %1 not canonical. Canonical: %2.', $currentUrl,
                    $currentAlternate);
                return $alternateData;
            }

            return $alternateData;
        }

        if ($this->generalHelper->getAlternateDebug()) {
            $alternateData['error'] = __('Product Alternate Data not enabled.');
            return $alternateData;
        }

        return false;
    }

    /**
     * Load current product from registry
     *
     * @return object
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Checks if current Product is Canonical Url (= witouth categoies)
     *
     * @param $product
     * @param $currentUrl
     *
     * @return bool
     */
    public function getCanonicalCheck($product, $currentUrl)
    {
        $canonical = $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
        if ($canonical != $currentUrl) {
            return false;
        }

        return true;
    }

    /**
     * @param $product
     * @param $storeId
     * @param $canonical
     *
     * @return bool|string
     */
    public function getProductUrlByStore($product, $storeId, $canonical)
    {
        try {
            $productStore = $this->productRepository->getById($product->getId(), true, $storeId);

            if ($productStore->getStatus() == 2 || $productStore->getVisibility() == 1) {
                return false;
            }

            if ($canonical) {
                $url = $productStore->getUrlModel()->getUrl($productStore, ['_ignore_category' => true, '_scope' => $storeId]);
            } else {
                $url = $productStore->getUrlModel()->getUrl($productStore, ['_scope' => $storeId]);
            }

            $url = strtok($url, '?');

            return $url;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}