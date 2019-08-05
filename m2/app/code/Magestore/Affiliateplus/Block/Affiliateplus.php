<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block;

/**
 * Block BlockTest
 */
class Affiliateplus extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
    * @var \Magestore\Affiliateplus\Helper\Config
    */
    protected $_helperConfig;

    /**
     * Affiliateplus constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magestore\Affiliateplus\Helper\Config $helperConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magestore\Affiliateplus\Helper\Config $helperConfig,
        array $data = array()
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_helperConfig = $helperConfig;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_helperConfig->getGeneralConfig('show_affiliate_link_on_frontend') && $this->_helperConfig->getGeneralConfig('enable')) {
            return parent::_toHtml();
        }
        return '';
    }
}
