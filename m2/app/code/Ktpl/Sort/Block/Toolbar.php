<?php
namespace Ktpl\Sort\Block;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    public $_exceptions = array(
        'popularity',
        'value'
    );

    protected $_eavAttribute;
    
    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        array $data = []
    ) {
        
        parent::__construct($context,$catalogSession,$catalogConfig,$toolbarModel,$urlEncoder,$productListHelper,$postDataHelper, $data);
        $this->_eavAttribute = $eavAttribute;
    }
    

    public function getCurrentAttribute()
    {
        $order = $this->getCurrentOrder();
        $block_name='attribute-'.$order.'-detail';
        $availableOrders = $this->getAvailableOrders();
        return $this->getLayout()->createBlock('Magento\Cms\Block\Block')
          ->setBlockId($block_name)
          ->toHtml();
       
    }

    public function getCurrentAttributeCode(){
        return $this->getCurrentOrder();
    }
    
    public function getCurrentAttributeLabel(){
        $current='';
        foreach ($this->getAvailableOrders() as $_key => $_order):
            if ($this->isOrderCurrent($_key)): 
                $current=$this->escapeHtml(__($_order));
                break;
            endif;  
        endforeach;
        
        return $current;
    }
    
    public function getAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = $this->_catalogConfig->getAttributeUsedForSortByArray();
        }
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $availableOrder = $this->_availableOrder;
        $settings = $obj->get('Magento\Catalog\Model\Layer\Resolver')->get()->getCurrentCategory()->getData('mmsort_attributes');
        if (!$settings) {
            return $availableOrder;
        }
        
        $settings = explode(',', $settings);
        $settings = array_map('trim', $settings);

        $exceptions = $this->_exceptions;
        $orders = array();
        foreach ($settings as $order) {
            if (!array_key_exists($order, $availableOrder) && !in_array($order, $exceptions)) {
                continue;
            }
            if (isset($availableOrder[$order])) {
                $orders[$order] = $availableOrder[$order];
            } else {
                $attributeModel = $obj->create('Magento\Eav\Model\Entity\Attribute')->loadByCode('catalog_product', $order);
                $orders[$order] = $attributeModel->getStoreLabel();
            }
        }
        if(empty($orders)) {
            return $availableOrder; 
        }
        return $orders;
        
    }
    
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()=='popularity'){
          $attributeId = $this->_eavAttribute->getIdByCode('catalog_product', 'popularity_multiplication'); 
           $this->getCollection()->getSelect()
                    ->joinLeft('catalog_product_entity_varchar','catalog_product_entity_varchar.entity_id = e.entity_id and catalog_product_entity_varchar.attribute_id = "'.$attributeId.'"',
                ['catalog_product_entity_varchar.value as popularitycount',
                ])->joinLeft('report_event',
            "report_event.object_id = e.entity_id and logged_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)",
            [
            new \Zend_Db_Expr('IF(catalog_product_entity_varchar.value IS NOT NULL,( (catalog_product_entity_varchar.value) * (COUNT(report_event.event_id))),(COUNT(report_event.event_id)))  AS viewscount')
            ]
            )->group('e.entity_id')
            ->order('viewscount DESC');
             // echo "Query : ".$this->_collection->getSelect();  
              //   die;  



        } 
        if ($this->getCurrentOrder()) 
            $this->_collection->setOrder($this->getCurrentOrder(),$this->getCurrentOrder()=='price' ?'asc': $this->getCurrentDirection());        
        return $this;
    }


    
}