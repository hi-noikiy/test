<?php
class Mage_Adminhtml_Block_Sales_Order_Renderer_Ordernumber
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        if ($row->getData('status') == Gearup_Sds_Helper_Data::ORDER_STATUS_CANCEL || $row->getData('status') == Gearup_Sds_Helper_Data::ORDER_STATUS_CLOSE) {
            $sdsall = '';
        } else {
            $sdsall = Mage::helper('gearup_sds')->getSdsAll($row->getEntityId());
            $userComment = Mage::helper('gearup_sds')->getUserComment($row->getEntityId());
            if ($sdsall && $userComment) {
                $sdsall = 'sdsall-half';
            } else if (!$sdsall && $userComment) {
                $sdsall = 'yellowbar';
            }
        }
        $html   = '<span class="ordernumber'.$row->getEntityId().'">'.$value.'</span>';
        $html   .= "<script>$$('.ordernumber".$row->getEntityId()."').each(function(s) {
                    var parentid = $(s).up(0);
                    parentid.addClassName('".$sdsall."');
                });</script>";
        return $html;
    }

}
