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

namespace Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer;

class Account extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * show Affiliate Account with link in a row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if($row->getAccountId())
            return sprintf('
				<a href="%s" title="%s">%s</a>',
                $this->getUrl('affiliateplusadmin/account/edit/', ['_current'=>true, 'account_id' => $row->getAccountId()]),
                __('View Affiliate Account Details'),
                $row->getAccountEmail()
            );
        else
            return sprintf('%s', $row->getAccountEmail());
    }
}