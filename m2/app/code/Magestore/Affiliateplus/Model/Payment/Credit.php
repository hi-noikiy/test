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
namespace Magestore\Affiliateplus\Model\Payment;
use Magestore\Affiliateplus\Model\Payment;

/**
 * Class Credit
 * @package Magestore\Affiliateplus\Model\Payment
 */
class Credit extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{
    /**
     * @var string
     */
    protected $_code = 'credit';

    /**
     * @var string
     */
    protected $_eventPrefix = 'affiliateplus_credit';
    /**
     * @var string
     */
    protected $_eventObject = 'affiliateplus_credit';

    /**
     *
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\Credit');
    }

    /**
     * @return $this
     */
    public function savePaymentMethodInfo() {
        $payment = $this->getPayment();
        $this->setPaymentId($payment->getId())->save();
        return parent::savePaymentMethodInfo();
    }

    /**
     * @return $this
     */
    public function loadPaymentMethodInfo() {
        if ($this->getPayment()) {
            $this->load($this->getPayment()->getId(), 'payment_id');
        }
        return parent::loadPaymentMethodInfo();
    }

    /**
     * @return mixed
     */
    public function getInfoString() {
        return __('
                Method: %1 \n
                Pay for Order: %1 \n
            ', $this->getLabel()
            , $this->getOrderIncrementId()
        );
    }

    /**
     * @return string
     */
    public function getInfoHtml() {
        $html = __('Method: ');
        $html .= '<strong>'.$this->getLabel().'</strong><br />';
        $html .= __('Pay for Order: ');
        $html .= '<strong><a href="';

        if ($this->_helper->isAdmin()) {
            $html .= $this->getUrl('sales/order/view', ['order_id' => $this->getOrderId()]);
        } else {
            $html .= $this->getUrl('sales/order/view', array('order_id' => $this->getOrderId()));
        }
        $html .= '" title="'.__('View Order').'">#'.$this->getOrderIncrementId().'</a></strong><br />';
        if ($this->getBaseRefundAmount() > 0) {
            $html .= __('Refunded: ');
            $formatedAmount = $this->_helper->formatPrice($this->getBaseRefundAmount());
            $html .= '<strong>'.$formatedAmount.'</strong><br />';
        }
        return $html;
    }
}