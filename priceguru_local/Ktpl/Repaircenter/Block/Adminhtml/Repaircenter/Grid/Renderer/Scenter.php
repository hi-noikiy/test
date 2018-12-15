<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Scenter extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $url = Mage::helper("adminhtml")->getUrl("*/*/updateServiceaddress");
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="sc_id'.$row->getRepairId().'" name="'.$this->getColumn()->getId() . '" onchange="updateServiceaddress(this, '. $row->getRepairId() .', \''. $url .'\'); return false" style="width:100px;">';
        $serc = Mage::getSingleton('repaircenter/servicecenter')->getCollection()
                ->addFieldToFilter('service_status',1);

    	$html .= '<option value=""></option>';
        foreach ($serc as $ser) {
            $selval = "";
            if($ser->getServiceId() == $rowval) { $selval = 'selected="selected"'; }
            $html .= '<option value="'. $ser->getId() .'" '.$selval.'>'. $ser->getServiceName() .'</option>';
        }
        $html .= '</select>';
 
        return $html;
    }
    public function renderExport(Varien_Object $row) {
        $serc = Mage::getSingleton('repaircenter/servicecenter')->getCollection();
    	foreach ($serc as $ser) {
    	 	if($ser->getServiceId() == $row->getData($this->getColumn()->getIndex())){
    	 		return $ser->getServiceName();
    	 	}
    	}
       // return $row->getData($this->getColumn()->getIndex());
    }
}