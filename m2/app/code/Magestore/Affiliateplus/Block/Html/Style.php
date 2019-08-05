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

namespace Magestore\Affiliateplus\Block\Html;
/**
 * Class Standard
 * @package Magestore\Affiliateplus\Block\Sales
 */
class Style extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    /**
     * @param $value
     * @param $store_id
     * @return mixed
     */
    public function getStoreConfig($value, $store_id)
    {
        return $this->_dataHelper->getConfig($value, $store_id);
    }
}