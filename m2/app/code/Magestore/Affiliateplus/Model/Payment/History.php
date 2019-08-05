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

class History extends \Magestore\Affiliateplus\Model\AbtractModel
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\History');
    }

    /**
     * @return string
     */
    public function getStatusLabel() {
        $statuses = array(
            Payment::PAYMENT_PENDING =>  __('Pending'),
            Payment::PAYMENT_PROCESSING =>  __('Processing'),
            Payment::PAYMENT_COMPLETE =>  __('Complete'),
            Payment::PAYMENT_CANCELED =>  __('Canceled')
        );
        if (isset($statuses[$this->getStatus()])){
            return $statuses[$this->getStatus()];
        }
        return '';
    }
}