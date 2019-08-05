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
namespace Magestore\Affiliateplus\Block\Sales;
/**
 * Class Statistic
 * @package Magestore\Affiliateplus\Block\Sales
 */
class Statistic extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @var array
     */
    protected $_transactionBlock = [];
    /**
     * @var array
     */
    protected $_statisticInfo = [];
    /**
     * @var array
     */
    protected $_totalStatistic = [
        'number_commission' => 0,
        'commissions' => 0
    ];

    /**
     * @param $name
     * @param $label
     * @param $link
     * @param $type
     * @param null $template
     * @return $this
     */
    public function addTransactionBlock($name, $label, $link, $type, $template = null) {
//        && (!$this->_helperLevel->isPluginEnabled())
        if ($name == 'tier')
            return $this;
        $block = $this->getLayout()->createBlock($type, $name);
        if ($template)
            $block->setTemplate($template);
        $this->_transactionBlock[$name] = $block;
        $this->getParentBlock()->addTransactionBlock($name, $label, $link, $block);
        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getFormatedCurreny($value) {
        return $this->_currencyInterface->toCurrency($value, $options = []);
    }

    /**
     * @return array
     */
    public function getStatisticInfo() {
        return $this->_statisticInfo;
    }

    /**
     * @return array
     */
    public function getTotalStatistic() {
        return $this->_totalStatistic;
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        foreach ($this->_transactionBlock as $block)
            if (method_exists($block, 'getStatisticInfo')) {
                $staticInfo = $block->getStatisticInfo();
                $this->_statisticInfo[] = $staticInfo;
                $this->_totalStatistic['number_commission'] += $staticInfo['number_commission'];
                $this->_totalStatistic['commissions'] += $staticInfo['commissions'];
            }
        if (count($this->_statisticInfo) <= 1)
            return '';
        return parent::_toHtml();
    }
}