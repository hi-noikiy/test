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
 * Class CheckIframe
 * @package Magestore\Affiliateplus\Block
 */
class CheckIframe extends AbstractTemplate
{
    /**
     * @return $this
     */
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /**
     * get Action Id
     *
     * @return mixed
     */
    public function getActionId() {

        $actionId = $this->_session->getData('transaction_checkiframe__action_id');
        $this->setActionId(NULL);
        return $actionId;
    }

    /**
     * set Action Id
     *
     * @param $actionId
     */
    public function setActionId($actionId) {

        $this->_session->setData('transaction_checkiframe__action_id', $actionId);
    }

    /**
     * get Hash Code
     *
     * @return mixed
     */
    public function getHashCode() {

        $hashCode = $this->_session->getData('transaction_checkiframe_hash_code');
        $this->setHashCode(NULL);
        return $hashCode;
    }

    /**
     * Set Hash Code
     *
     * @param $hashCode
     */
    public function setHashCode($hashCode) {
        $this->_session->setData('transaction_checkiframe_hash_code', $hashCode);
    }
}
