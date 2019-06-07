<?php
/**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gearup_Competera_Block_Adminhtml_Competera_Pricechange extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_competera_pricechange';
        $this->_blockGroup = 'competera';
        $this->_headerText = 'Products Price Changelog';
        parent::__construct();
        $this->_removeButton('add');
        $this->addButton('back', array(
            'label'   => $this->__('Back'),
            'onclick' => "setLocation('{$this->getUrl('*/competera_history')}')",
            'class'   => 'back'
        ));
    }
}