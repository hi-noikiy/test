<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Featuredproducts
 * @copyright  Copyright (c) 2008-2009 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */
class AW_Visitorsonline_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getNumberOfVisitorsOnline($curUrl)
	{
		
      $collection = Mage::getModel('log/visitor_online')
            ->prepare()
            ->getCollection();
	  $collection->addCustomerData();
	  $collection->addFieldToFilter('last_url',array('eq'=>$curUrl));
	  return count($collection)+ 15; 
	}
}
