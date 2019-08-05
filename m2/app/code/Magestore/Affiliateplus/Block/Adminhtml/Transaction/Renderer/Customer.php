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

class Customer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * show customer email with link in a row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if($row->getCustomerId())
            return sprintf('
				<a href="%s" title="%s">%s</a>',
                $this->getUrl('customer/index/edit/', ['_current'=>true, 'id' => $row->getCustomerId()]),
                __('View Customer Details'),
                $row->getCustomerEmail()
            );
        else if($row->getCustomerEmail())
            return sprintf('%s', $row->getCustomerEmail());
        else
            return sprintf('%s', 'N/A');
    }
}