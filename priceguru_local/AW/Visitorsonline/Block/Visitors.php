<?php

class AW_Visitorsonline_Block_Visitors extends Mage_Core_Block_Template
{
		public function getNumberOfVisitorsOnline ($url)
		{
			return Mage::helper('visitorsonline')->getNumberOfVisitorsOnline($url);
		}
	
}

?>
