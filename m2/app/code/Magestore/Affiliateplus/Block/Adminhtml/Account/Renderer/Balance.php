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

namespace Magestore\Affiliateplus\Block\Adminhtml\Account\Renderer;


use  Magento\Framework\Pricing\PriceCurrencyInterface;
/**
 * Class Balance
 * @package Magestore\Affiliateplus\Block\Adminhtml\Account\Renderer
 */
class Balance extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Balance constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    )
    {
        $this->_priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @param $value
     * @param bool|true $format
     * @return mixed
     */
    public function convertPrice($value, $format = true)
    {
        return $this->_priceCurrency->convert($value, $format);
    }

    /**
     * @param $value
     * @return float
     */
    public function formatPrice($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }
    /**
     * show Affiliate Account with link in a row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        if($row->getBalance()>0){
            return sprintf('%s', $this->formatPrice($this->convertPrice($row->getBalance())) );
        } else{
            return sprintf('%s', $this->formatPrice($row->getBalance()));
        }
    }
}