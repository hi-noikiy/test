<?php

/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CollinsHarper\Moneris\Block\Customer;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \CollinsHarper\Moneris\Helper\Data
     */
    private $chHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * Link constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \CollinsHarper\Moneris\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\Http\Context $httpContext,
        \CollinsHarper\Moneris\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->chHelper = $helper;
        $this->httpContext = $httpContext;
    }
    
    /**
     * Get href URL - force secure
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath(), ['_secure' => true]);
    }

    /**
     * Render block HTML - if and only if we have active tokenbase payment methods.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->chHelper->getVaultEnabled()) {
            return parent::_toHtml();
        }
        
        return '';
    }
}
