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
class Gearup_Competera_Block_Adminhtml_Competera_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_competera_history';
        $this->_blockGroup = 'competera';
        $this->_headerText = 'Competera Price Changelog History';
        parent::__construct();
        $this->_removeButton('add');
        $this->addButton('priceload', array(
            'label'     => Mage::helper('competera')->__('Priceload'),
            'onclick'   => "setLocation('{$this->getUrl('*/competera_priceload')}')",
            'class'     => 'back'
        ));
    }
}