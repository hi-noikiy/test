<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Gearup_Bannerwidget_Model_System_Config_Source_Widgetlist
{
    public function toOptionArray()
    {
        $option = array();
        $option[] = array(
                    'label' => "Select Top Banner Widget",
                    'value' => ''
                );
        $widgetList = Mage::getResourceModel('widget/widget_instance_collection');
        if($widgetList->count() > 0){
            foreach($widgetList as $item) {                
                $option[] = array(
                    'label' => $item->getTitle(),
                    'value' => $item->getInstanceId()
                );
            }    
        }
        return $option;
    }

}
