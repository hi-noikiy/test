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
 * @package     Magestore_Megamenu
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab;

class Categories extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var int[]
     */
    protected $_selectedIds = [];

    /**
     * @var array
     */
    protected $_expandedPath = [];

    /**
     * Categories constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param array $data
     */
    public function __construct(
        \Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        /* \Magento\Framework\Registry $registry, */
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        array $data = []
    ) {
        /* parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data
        ); */
        parent::__construct(
            $context,
            $categoryTree,
            $context->getRegistry(),
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data
        );
        $this->_objectManager = $context->getObjectManager();
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $context->getRegistry();
    }
    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Magestore_Affiliateplusprogram::category/categories.phtml');
    }

    /**
     * @return mixed
     */
    public function getCategoryCollection(){
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if (is_null($collection)){
            $collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);
            $this->setData('category_collection',$collection);
        }
        return $collection;
    }

    /**
     * @return bool
     */
    public function isReadonly(){
        return false;
    }

    /**
     * @return array|mixed
     */
    protected function getCategoryIds(){
        if (!$this->_coreRegistry->registry('program_categories'))
            return array();
        return $this->_coreRegistry->registry('program_categories');
    }

    /**
     * @return string
     */
    public function getIdsString(){
        $categoryIds = $this->getCategoryIds();
        if (is_array($categoryIds))
            return implode(',',$categoryIds);
        return parent::getIdsString();
    }

    /**
     * @return mixed
     */
    public function getProgram(){
        $id = $this->getRequest()->getParam('program_id');
        return $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\Program')
            ->load($id);
    }

    /**
     * @return mixed
     */
    public function getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }
    /**
     * @return array
     */
    protected function getExpandedPath()
    {
        return $this->_expandedPath;
    }

    /**
     * @param string $path
     * @return $this
     */
    protected function setExpandedPath($path)
    {
        $this->_expandedPath = array_merge($this->_expandedPath, explode('/', $path));
        return $this;
    }

    /**
     * @param array|Node $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = [];
        $item['text'] = $this->escapeHtml($node->getName());
        if ($this->_withProductCount) {
            $item['text'] .= ' (' . $node->getProductCount() . ')';
        }
        $item['id'] = $node->getId();
        $item['path'] = $node->getData('path');
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;
        if (in_array($node->getId(), $this->getCategoryIds())) {
            $this->setExpandedPath($node->getData('path'));
            $item['checked'] = true;
        }
        if ($node->getLevel() < 2) {
            $this->setExpandedPath($node->getData('path'));
        }
        if ($node->hasChildren()) {
            $item['children'] = [];
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }
        if (empty($item['children']) && (int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }
        $item['expanded'] = in_array($node->getId(), $this->getExpandedPath());
        return $item;
    }
}