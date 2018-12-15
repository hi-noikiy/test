<?php
class EM_Getbestdealproduct_Model_Observer
{

			public function getbestdeal(Varien_Event_Observer $observer)
			{
				if(Mage::registry('current_category')!="")
				{
					$iCurrentCategory = Mage::registry('current_category')->getId();
					if($iCurrentCategory=="211")
					{
						// print_r('chay');
						// die;
						$collection = $observer->getEvent()->getCollection();
						$collection->addAttributeToFilter('new_best_seller', 0);
						return this;
					}
				}
			}
		
}
