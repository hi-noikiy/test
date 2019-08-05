<?php
/**
 *  
 *   
`*
 */
namespace Ktpl\CustomizeConfigurable\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    public $_storeManager;

    protected $_eavAttribute;

    protected $_groupCollection; 

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $eavAttribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $_groupCollection,
        array $data = []
    ) {
        $this->_storeManager=$storeManager;
        $this->_eavAttribute = $eavAttribute;
        $this->_groupCollection = $_groupCollection;
        parent::__construct($context,$registry,$priceCurrency,$data);
    }
  
    public function getAttributeGroupId($attributeSetId,$groupId)
    {
         $groupCollection = $this->_groupCollection->create();
         $groupCollection->addFieldToFilter('attribute_set_id',$attributeSetId);
         $groupCollection->addFieldToFilter('attribute_group_name',$groupId); 
         return $groupCollection->getFirstItem(); 

    }
 
}    