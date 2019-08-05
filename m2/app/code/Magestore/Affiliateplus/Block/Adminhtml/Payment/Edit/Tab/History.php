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

namespace Magestore\Affiliateplus\Block\Adminhtml\Payment\Edit\Tab;
class History extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    protected $_collection = '';
    /**
     * History constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        return $this->setTemplate('Magestore_Affiliateplus::payment/history.phtml');
    }

    public function getFullHistory() {

        if (!$this->_collection) {
            $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Payment\History\Collection')
                ->addFieldToFilter('payment_id', $this->getPayment()->getId());
            $collection->getSelect()->order('created_time DESC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function getCollection() {
        return $this->getFullHistory();
    }

    public function getPayment() {
        if ($this->_coreRegistry->registry('payment_data')) {
            return $this->_coreRegistry->registry('payment_data');
        }
        return new \Magento\Framework\DataObject();
    }
}