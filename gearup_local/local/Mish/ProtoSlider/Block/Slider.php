<?php
/**
 * ProtoSlider Block
 */
class Mish_ProtoSlider_Block_Slider extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    /**
     * Widget Parameters
     * 
     * @var array
     */
    protected $params;
    
    /**
     * Widget Config Object
     * 
     * @var Varien_Object
     */
    protected $widgetConfig;
    
    /**
     * Block Head
     * 
     * @var Mage_Page_Block_Html_Head
     */
    protected $head;
    
    /**
     * ResourceModel
     * 
     * @var Mage_Widget_Model_Widget_Instance
     */
    protected $resourceModel;
    
    /**
     * Initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('protoslider/slider.phtml');
    }
    
    /**
     * On prepare layout
     */
    protected function _prepareLayout() 
    {
        parent::_prepareLayout();
        $this->loadAssets();
    }
    
    /**
     * Gets widget instance from model
     * 
     * @return Mage_Widget_Model_Widget_Instance
     */
    protected function getResourceModel()
    {
        if (!isset($this->resourceModel))
        {
            $this->resourceModel = Mage::getResourceModel('widget/widget_instance_collection')->getItemById($this->getWidgetId());
        }
        
        return $this->resourceModel;
    }
    
    /**
     * Content of block
     * 
     * @param string $blockId
     * @return string
     */
    public function getBlockContent($blockId)
    {
        return $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }
    
    /**
     * Gets widget parametrs
     * 
     * @return array
     */
    public function getWidgetParams()
    {
        if (!isset($this->params) && is_object($this->getResourceModel()))
        {
            $this->params = $this->validateParams($this->getResourceModel()->getWidgetParameters());
        }
        
        return $this->params;
    }
    
    /**
     * Validation params
     * 
     * @param array $params
     * @return array
     */
    protected function validateParams($params)
    {
        return $params;
    }

    /**
     * Loads assets
     */
    public function loadAssets()
    {
        $this->addCss('css/protoslider/protoslider.css');
        $this->addJs('js/protoslider/protoslider.js');
    }
    
    /**
     * Gets head
     * 
     * @return Mage_Page_Block_Html_Head
     */
    protected function getHead()
    {
        if (!isset($this->head))
        {
            $this->head = Mage::getSingleton('core/layout')->getBlock('head');
            if (!($this->head instanceof Mage_Page_Block_Html_Head))
            {
                throw new Mage_Exception("Can not find 'head' block in layout singleton!");
            }
        }
        
        return $this->head;
    }
    
    /**
     * Adds javascript file to head
     * 
     * @param string $file
     */
    public function addJs($file)
    {
        $this->getHead()->addItem('skin_js', $file);
        
        return $this;
    }
    
    /**
     * Adds stylesheet file to head
     * 
     * @param string $file
     */
    public function addCss($file)
    {
        $this->getHead()->addCss($file);
        
        return $this;
    }
    
    /**
     * List of static blocks
     * 
     * @return array
     */
    public function getBlocks()
    {
        $params = $this->getWidgetParams();
        $blocks = array();
        
        $blocksModel = Mage::getResourceModel('cms/block_collection');
        
        if (!isset($params['blocks']))
        {
            $params['blocks'] = array();
        }
        
        foreach ($params['blocks'] as $blockId)
        {
            $block = $blocksModel->getItemById($blockId);
            $blocks[$block->getIdentifier()] = $block;
        }
        
        return $this->getOrderedBlocks($blocks);
    }
    
    /**
     * Gets ordered bloks
     * 
     * @param array $blocks 
     */
    protected function getOrderedBlocks(array $blocks)
    {
        $params = $this->getWidgetParams();
        
        $strBlocks = trim($params['order']);
        if ($strBlocks == '')
        {
            return $blocks;
        }
        
        $orderedBlocks = array();
        $ids = explode(',', $strBlocks);
        foreach ($ids as $id)
        {
            $id = trim($id);
            if (isset($blocks[$id]))
            {
                $orderedBlocks[$id] = $blocks[$id];
                unset($blocks[$id]);
            }
        }
        
        return $orderedBlocks + $blocks;
    }
    
    /**
     * Configuration object
     * 
     * @return Varien_Object
     */
    public function getWidgetConfig()
    {
        if (!isset($this->widgetConfig))
        {
            $this->widgetConfig = new Varien_Object();
            $this->widgetConfig->addData($this->getWidgetParams());
        }
        
        return $this->widgetConfig;
    }
}
