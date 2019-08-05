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
namespace Magestore\Affiliateplus\Block\ReferFriend\Email;

/**
 * Class Product
 * @package Magestore\Affiliateplus\Block\ReferFriend
 */
class Form extends \Magestore\Affiliateplus\Block\ReferFriend\Refer
{
    /**
     * @return mixed
     */
    public function getDefaultEmailContent() {
        $content = $this->_configHelper->getReferConfig('email_content');
        $url = $this->getRequest()->getParam('url');
        $url = $url ? $url : $this->getPersonalUrl();
        return str_replace(
            array(
                '{{store_name}}',
                '{{personal_url}}',
                '{{account_name}}'
            ), array(
            $this->_storeManager->getStore()->getFrontendName(),
            $url,
            $this->getAccount()->getName()
        ), $content
        );
    }
}