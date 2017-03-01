<?php
namespace Ktpl\Customreport\Block\Adminhtml;

class Pickuporder extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'pickuporder/pickuporder.phtml';
   
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add'); // Add this code to remove the button
    }
   
    protected function _prepareLayout()
    {

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid', 'pickuporder.grid')
        );
        
        return parent::_prepareLayout();
    }
   
    
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}