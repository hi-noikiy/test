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

namespace Magestore\Affiliateplus\Model\Banner;

class Value extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * Value constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Affiliateplus\Model\ResourceModel\Banner\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Banner\Value');
    }

    public function loadAttributeValue($bannerId, $storeId, $attributeCode){
        $attributeValue = $this->_valueCollectionFactory->create()
            ->addFieldToFilter('banner_id',$bannerId)
            ->addFieldToFilter('store_id',$storeId)
            ->addFieldToFilter('attribute_code',$attributeCode)
            ->getFirstItem()
        ;

        $this->setData('banner_id',$bannerId)
            ->setData('store_id',$storeId)
            ->setData('attribute_code',$attributeCode);

        if ($attributeValue && $attributeValue->getId()) {
            $this->addData($attributeValue->getData())
                ->setId($attributeValue->getId());
        }

        return $this;
    }
}