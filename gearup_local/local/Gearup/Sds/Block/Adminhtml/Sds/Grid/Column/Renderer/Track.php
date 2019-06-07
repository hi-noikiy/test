<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Track
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $track = Mage::getModel('gearup_sds/tracking')->load($row->getId(), 'product_id');
        $updated = $track->getUpdateLastAt();
        $last21 = Mage::getModel('core/date')->date('Y-m-d H:i:s', strtotime("-21 day"));
        if ($updated < $last21) {
            $lowflag = $updated ? 'lowflag' : '';
            Mage::getModel('gearup_sds/tracking')->changeRedTrack($row->getId(),1);
        } else {
            $lowflag = '';
            Mage::getModel('gearup_sds/tracking')->changeRedTrack($row->getId(),2);
        }
        $orders = Mage::getModel('sales/order_item')->getCollection();
        $orders->addFieldToFilter('product_id', array('eq' => $row->getId()));
        //$link = '';
        //if (count($orders) > 0) {
            $link = "'".$this->getUrl('*/sales_order/index', array('product_id' => $row->getId()))."'";
        //}
        if ($row->getUpdateLastAt()) {
            $html = '<a target="_blank" class="lowlink linkorder" onclick="window.open('.$link.'); return false;"><span class="'.$lowflag.' '.$row->getId().'" id="track'.$row->getId().'">' . Mage::helper('core')->formatDate($row->getUpdateLastAt(), 'medium', false) . '</span></a>';
        } else {
            $html = '<a target="_blank" class="lowlink linkorder" onclick="window.open('.$link.'); return false;"><span class="'.$lowflag.' '.$row->getId().'" id="track'.$row->getId().'">&nbsp;</span></a>';
        }
        $html .= "<script>$$('.lowflag').each(function(s) {
                    var parentid = $(s).up(1);
                    parentid.addClassName('low');
                });</script>";
        return $html;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {

        return Mage::helper('core')->formatDate($row->getUpdateLastAt(), 'medium', false);
    }

}
