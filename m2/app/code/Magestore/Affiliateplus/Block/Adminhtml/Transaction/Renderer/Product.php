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

class Product extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magestore\Affiliateplus\Helper\Data;
     */
    protected $_helper;

    /**
     * Product constructor.
     * @param \Magestore\Affiliateplus\Helper\Data $helper
     */
    public function __construct(\Magestore\Affiliateplus\Helper\Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if($row->getOrderItemIds()){
            $html = $this->_helper->getBackendProductHtml($row->getOrderItemIds());
            return sprintf('%s', $html);
        }  else {

            return sprintf('%s', 'N/A');
        }
    }
}