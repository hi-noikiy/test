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

namespace Magestore\Affiliateplus\Controller\Checkout;
/**
 * Affiliateplus Checkout ReloadData Action
 *
 * @category Magestore
 * @package  Magestore_Affiliateplus
 * @module   Affiliateplus
 * @author   Magestore Developer
 */
class ReloadData extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $result = $this->_objectManager->create('Magestore\Affiliateplus\Block\Credit\Form')->getAffiliateCreditInfo();
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
