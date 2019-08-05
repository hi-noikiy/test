<?php
/**
 * Copyright © 2018 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magmodules\AlternateHreflang\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Registry;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

/**
 * Class Category
 *
 * @package Magmodules\AlternateHreflang\Helper
 */
class Category extends AbstractHelper
{

    /**
     * @var General
     */
    private $generalHelper;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Http
     */
    private $request;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Category constructor.
     *
     * @param Context            $context
     * @param CategoryRepository $categoryRepository
     * @param General            $generalHelper
     * @param Registry           $registry
     * @param Http               $request
     */
    public function __construct(
        Context $context,
        CategoryRepository $categoryRepository,
        GeneralHelper $generalHelper,
        Registry $registry,
        Http $request
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
        $this->generalHelper = $generalHelper;
        $this->registry = $registry;
        $this->request = $request;
        $this->logger = $context->getLogger();
    }

    /**
     * Returns all alternate category url's in array
     *
     * @return array|bool
     */
    public function getAlternateData()
    {
        if ($this->generalHelper->getEnabled('category')) {
            $alternateData = [];
            $storeId = $this->generalHelper->getCurrentStore();
            $targetData = $this->generalHelper->getTargetData($storeId);

            if (empty($targetData['group_id'])) {
                return false;
            }

            $groupId = $targetData['group_id'];
            $categoryId = $this->getCurrentCategory()->getId();
            $canonicalCheck = $this->getCononicalCheck();

            if ($canonicalCheck > 0) {
                $alternateData['error'] = __('It seems that the current Category URLS has filters, 
                the Alternate Hreflang Tags can not be placed on filtered URLS.');

                return $alternateData;
            }

            foreach ($targetData[$groupId] as $row) {
                if ($storeId != $row['store_id']) {
                    $url = $this->getCategoryUrlByStore($categoryId, $row['store_id'], $storeId);
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                } else {
                    $url = $this->generalHelper->getCurrentUrl(true);
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                }
            }

            if (empty($alternateData['urls'])) {
                $alternateData['error'] = __('No Alternate URLs found.');
                return $alternateData;
            }

            if (count($alternateData['urls']) == 1) {
                $alternateData['error'] = __('Only one Alternate URL Found (%1). Needs at least two.', implode('', $alternateData['urls']));
                return $alternateData;
            }

            $canonical = $this->generalHelper->getCanonicalEnabled();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            if (isset($currentAlternate) && $canonical && $currentAlternate != $currentUrl) {
                $alternateData['error'] = __('Current URL %1 not canonical. Canonical: %2.', $currentUrl, $currentAlternate);
                return $alternateData;
            }

            return $alternateData;
        }

        if ($this->generalHelper->getAlternateDebug()) {
            $alternateData['error'] = __('Category Alternate Data not enabled.');
            return $alternateData;
        }

        return false;
    }

    /**
     * @return \Magento\Catalog\Model\Category Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Checks if current category url has filters
     *
     * @return int
     */
    public function getCononicalCheck()
    {
        $params = $this->request->getParams();
        if (isset($params['id'])) {
            unset($params['id']);
        }
        if (isset($params['show-alternate'])) {
            unset($params['show-alternate']);
        }

        return count($params);
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @param $currentStoreId
     *
     * @return bool|mixed|string
     */
    public function getCategoryUrlByStore($categoryId, $storeId, $currentStoreId)
    {
        try {
            if ($this->categoryRepository->get($categoryId, $storeId)->getIsActive()) {
                $categoryUrl = $this->categoryRepository->get($categoryId, $storeId)->getUrl();
                $baseUrlCurrent = $this->generalHelper->getBaseUrlStore($currentStoreId);
                $baseUrlTarget = $this->generalHelper->getBaseUrlStore($storeId);
                $url = str_replace($baseUrlCurrent, $baseUrlTarget, $categoryUrl);
                $url = strtok($url, '?');
                return $url;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }
}
