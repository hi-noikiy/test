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
namespace Magestore\Affiliateplus\Block\Adminhtml\Payment\Renderer;

class Account extends \Magestore\Affiliateplus\Block\Adminhtml\AbstractRenderer
{


    /**
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if($row->getAccountId())
            return sprintf('
				<a href="%s" title="%s">%s</a>',
                $this->getUrl('affiliateplusadmin/account/edit', ['_current'=>true, 'id' => $row->getAccountId()]),
                __('View Affiliate Account Details'),
                $row->getAccountEmail()
            );
        return $row->getAccountEmail();
    }
}
