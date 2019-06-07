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
class Gearup_Competera_Block_Adminhtml_Competera_Priceload extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_competera_priceload';
        $this->_blockGroup = 'competera';
        $this->_headerText = 'Products Price Comparison';
        parent::__construct();
        $this->_removeButton('add');
        $today = date('Y-m-d H:i:s');

        $this->addButton('lastupdated', array(
            'label'     => Mage::helper('competera')->__('Last Refresh: ').Mage::helper('core')->formatDate($today, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true),
            'class'     => 'justlabel'
        ));

        $this->addButton('refresh', array(
            'label'     => Mage::helper('competera')->__('Refresh'),
            'onclick'   => "setLocation('{$this->getUrl('*/*')}')"
        ));
        $this->addButton('review_history', array(
            'label'     => Mage::helper('competera')->__('Review History'),
            'onclick'   => "setLocation('{$this->getUrl('*/competera_history')}')",
            'class'     => 'go'
        ));
    }
}