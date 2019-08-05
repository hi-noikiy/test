<?php
/**
 * Copyright © 2018 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magmodules\AlternateHreflang\Helper\Homepage as HomepageHelper;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

/**
 * Class Homepage
 *
 * @package Magmodules\AlternateHreflang\Block
 */
class Homepage extends Template
{

    /**
     * @var HomepageHelper
     */
    private $homepageHelper;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Homepage constructor.
     *
     * @param Context        $context
     * @param HomepageHelper $homepageHelper
     * @param GeneralHelper  $generalHelper
     * @param array          $data
     */
    public function __construct(
        Context $context,
        HomepageHelper $homepageHelper,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        $this->homepageHelper = $homepageHelper;
        $this->generalHelper = $generalHelper;
        parent::__construct($context, $data);
    }

    /**
     * Gets alternate data from homepage helper
     *
     * @return array
     */
    public function getAlternateData()
    {
        return $this->homepageHelper->getAlternateData();
    }

    /**
     * Checks if debug message must be displayed
     *
     * @return bool
     */
    public function getAlternateDebug()
    {
        $showDebug = $this->request->getParam('show-alternate');
        if ($showDebug) {
            return $this->generalHelper->getAlternateDebug();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function showCommentTags()
    {
        return $this->generalHelper->getAlternateDebug();
    }
}
