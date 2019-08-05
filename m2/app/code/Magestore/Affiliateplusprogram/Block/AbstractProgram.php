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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Block;

    /**
     * Class Edit
     * @package Magestore\Affiliateplusprogram\Block\AbstractProgram
     */
/**
 * Class AbstractProgram
 * @package Magestore\Affiliateplusprogram\Block
 */
class AbstractProgram extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\Affiliateplus\Helper\Account
     */
    protected $_accountHelper;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_dataStandardHelper;

    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\ProgramFactory
     */
    protected $_programFactory;

    /**
     * @var \Magestore\Affiliateplusprogram\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magestore\Affiliateplus\Helper\Url
     */
    protected $_urlHelper;

    /**
     * AbstractProgram constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Affiliateplus\Helper\Account $accountHelper
     * @param \Magestore\Affiliateplus\Helper\Config $configHelper
     * @param \Magestore\Affiliateplus\Helper\Data $dataStandardHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Affiliateplusprogram\Model\ProgramFactory $programFactory
     * @param \Magestore\Affiliateplusprogram\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Affiliateplus\Helper\Account $accountHelper,
        \Magestore\Affiliateplus\Helper\Config $configHelper,
        \Magestore\Affiliateplus\Helper\Data $dataStandardHelper,
        /* \Magento\Framework\Event\ManagerInterface $eventManager, */
        \Magestore\Affiliateplusprogram\Model\ProgramFactory $programFactory,
        \Magestore\Affiliateplusprogram\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Affiliateplus\Helper\Url $urlHelper,
        array $data = []
    )
    {
        $this->_programFactory = $programFactory;
        $this->_objectManager = $objectManager;
        $this->_accountHelper = $accountHelper;
        $this->_configHelper = $configHelper;
        $this->_dataStandardHelper = $dataStandardHelper;
        $this->_eventManager = $context->getEventManager();
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param $row
     * @return string
     */
    public function getNoNumber($row) {
        return sprintf('#%s', $row->getId());
    }

    /**
     * @param $row
     * @return string
     */
    public function getProgramName($row) {
        return sprintf('<a href="%s" title="%s">%s</a>'
            , $this->getUrl('affiliateplus/program/detail', array('id' => $row->getId()))
            , __('View Program Description')
            , $row->getName()
        );
    }

    /**
     * @param $row
     * @return string
     */
    public function getProgramDetails($row) {
        $html = '';
        // Program Discount
        $discount = floatval($row->getDiscount());
        $secDiscount = floatval($row->getSecondaryDiscount());
        $store = $this->_storeManager->getStore();
        if ($row->getDiscountType() == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_FIXED_AMOUNT_PER_ITEM) {
            $discountText = $this->_dataStandardHelper->formatPrice($discount);
        } else if ($row->getDiscountType() == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_PERCENTAGE) {
            $discountText = rtrim(sprintf("%s", $discount), '.') . '%';
        } else {
            $discountText = $this->_dataStandardHelper->formatPrice($discount);
            $discountText .= ' ' . __('for whole cart');
        }

        if ($row->getSecDiscountType() == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_FIXED_AMOUNT_PER_ITEM) {
            $secText = $this->_dataStandardHelper->formatPrice($secDiscount);
        } else if ($row->getSecDiscountType() == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_PERCENTAGE) {
            $secText = rtrim(sprintf("%s", $secDiscount), '.') . '%';
        } else {
            $secText = $this->_dataStandardHelper->formatPrice($secDiscount);
            $secText .= ' ' . __('for whole cart');
        }

        if (!$this->hasSecondaryDiscount($row)) {
            $html .= __('Discount: ') . '<strong>' . $discountText . '</strong><br />';
        } else {
            $html .= __('First Order Discount: ') . '<strong>' . $discountText . '</strong><br />';
            $html .= __('Discount: ') . '<strong>' . $secText . '</strong><br />';
        }
        // Program Commission
        $commission = floatval($row->getCommission());
        $secCommission = floatval($row->getSecondaryCommission());
        if ($row->getCommissionType() == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_ITEM){
            $commissionText = $this->_dataStandardHelper->formatPrice($commission);
        } elseif ($row->getCommissionType() == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_CART){
            $commissionText = $this->_dataStandardHelper->formatPrice($commission) . ' ' . __('for whole cart');
        } else {
            $commissionText = rtrim(sprintf("%s", $commission), '.') . '%';
        }
        if ($row->getSecCommissionType() == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_ITEM){
            $secText = $this->_dataStandardHelper->formatPrice($secCommission);
        } elseif ($row->getSecCommissionType() == \Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage::COMMISSION_FIXED_AMOUNT_PER_CART){
            $secText = $this->_dataStandardHelper->formatPrice($secCommission) . ' ' . __('for whole cart');
        } else {
            $secText = rtrim(sprintf("%s", $secCommission), '.') . '%';
        }
        $typeIsProfit = $this->_dataStandardHelper->affiliateTypeIsProfit();
        if ($row->getAffiliateType()) {
            $typeIsProfit = (bool) ($row->getAffiliateType() == \Magestore\Affiliateplus\Model\System\Config\Source\Type::XML_PATH_COMMISSION_TYPE_PROFIT);
        }
        if ($typeIsProfit) {
            $label = __('Pay-per-profit');
        } else {
            $label = __('Pay-per-sales');
        }
        if (!$this->hasSecondaryCommission($row)) {
            $html .= $label . ': <strong>' . $commissionText . '</strong>';
        } else {
            $html .= $label . ' (' . __('first order') . ')' . ': <strong>' . $commissionText . '</strong><br />';
            $html .= $label . ': <strong>' . $secText . '</strong>';
        }

        /** edited by blanka 18-10-2012 * */
        $obj = new \Magento\Framework\DataObject(array('html_view' => $html));
        $this->_eventManager->dispatch('affiliateplus_prepare_program', ['info' => $row, 'obj' => $obj]);
        $html = $obj->getHtmlView();
        /** end edit by blanka* */
        if ($row->getLevelCount()) {
            $popHtml = '<table class="data-table"><tr>';

            if ($row->getSecLevelCount())
                $popHtml .= '<td rowspan="' . ($row->getLevelCount() + 1) . '">' . __('for the first order of a customer') . '</td>';

            $popHtml .= '<td><strong>' . __('Level %1', 1) . '</strong></td><td>';
            if ($row->getCommissionType() == 'fixed')
                $popHtml .= __('%1 per sale', $commissionText);
            else
                $popHtml .= __('%1 of sales amount', $commissionText);
            $popHtml .= '</td></tr>';
            foreach ($row->getTierCommission() as $tierCommission) {
                $popHtml .= '<tr><td><strong>' . $tierCommission['level'] . '</strong></td><td>';
                $popHtml .= $tierCommission['commission'] . '</td></tr>';
            }
            if ($row->getSecLevelCount()) {
                $popHtml .= '<td rowspan="' . ($row->getSecLevelCount() + 1) . '">' . __('for next orders') . '</td>';
                $popHtml .= '<td><strong>' . __('Level %1', 1) . '</strong></td><td>';
                if ($this->hasSecondaryCommission($row))
                    $commissionText = $secText;
                if ($row->getSecCommissionType() == 'fixed')
                    $popHtml .= __('%1 per sale', $commissionText);
                else
                    $popHtml .= __('%1 of sales amount', $commissionText);
                $popHtml .= '</td></tr>';
                foreach ($row->getSecTierCommission() as $tierCommission) {
                    $popHtml .= '<tr><td><strong>' . $tierCommission['level'] . '</strong></td><td>';
                    $popHtml .= $tierCommission['commission'] . '</td></tr>';
                }
            }
            $popHtml .= '</table>';

            $html .= '<script type="text/javascript">var popHtml' . $row->getId() . '= \'' . $this->jsQuoteEscape($popHtml) . '\';</script>';
            // Changed By Billy Trinh to responsive
//            $html .= '<br /><span class="affiliateplus-anchor" title="' . $this->__('View tier level commission amounts') . '" onclick="TINY.box.show(popHtml' . $row->getId() . ',0,0,0,0);return false;">' . $this->__('View Tier Commission') . '</span>';
            $html .= '<br /><span class="affiliateplus-anchor" title="' . __('View tier level commission amounts') . '" onclick="ajaxPopup(null,popHtml' . $row->getId() . ',this);">' . __('View Tier Commission') . '</span>';
        }

        if ($row->getValidFrom())
            $html .= '<br />' . __('From: ') . '<strong>' . $this->formatDate($row->getValidFrom(), \IntlDateFormatter::MEDIUM, false) . '</strong>';
        if ($row->getValidTo())
            $html .= '<br />' . __('To: ') . '<strong>' . $this->formatDate($row->getValidTo(), \IntlDateFormatter::MEDIUM, false) . '</strong>';

        return $html;
    }

    /**
     * @return string
     */
    public function getPagerHtml() {
        return $this->getChildHtml('programs_pager');
    }

    /**
     * @return string
     */
    public function getGridHtml() {
        return $this->getChildHtml('programs_grid');
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        if(count($this->getCollection())){
            $this->getChildBlock('programs_grid')
                ->setCollection($this->getCollection());
        }
        return parent::_toHtml();
    }

    /**
     * @param $program
     * @return bool
     */
    public function hasSecondaryCommission($program) {
        return ($program->getData('sec_commission') && ($program->getData('sec_commission_type') != $program->getData('commission_type') || $program->getData('secondary_commission') != $program->getData('commission')
            ));
    }

    /**
     * @param $program
     * @return bool
     */
    public function hasSecondaryDiscount($program) {
        return ($program->getData('sec_discount') && ($program->getData('sec_discount_type') != $program->getData('discount_type') || $program->getData('secondary_discount') != $program->getData('discount')
            ));
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getStyleConfig($storeId){
        return $this->_configHelper->getStyleConfig('responsive_enable', $storeId);
    }

    /**
     * @return int
     */
    public function getStoreId() {
        if(!$this->hasData('store_id')){
            $storeId = $this->_storeManager->getStore()->getId();
            $this->setData('store_id', $storeId);
        }
        return $this->getData('store_id');
    }
}
