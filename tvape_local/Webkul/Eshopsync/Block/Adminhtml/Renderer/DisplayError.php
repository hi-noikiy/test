<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
class Webkul_Eshopsync_Block_Adminhtml_Renderer_DisplayError extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$magento_id = $row->getData('magento_id');
        $error_hints = $row->getData('error_hints');
        if($error_hints && $magento_id){
          $error = $error_hints;
          $url = $this->getSkinUrl('images/rule_component_remove.gif');
          $link = "<div style='cursor:pointer;' title='$error'><img src='$url'></div>";
        }
        elseif(!$error_hints && $magento_id){
          $url = $this->getSkinUrl('images/rule_component_apply.gif');
          $link = "<div style='cursor:pointer;' title='Synchronisation Successfully'><img src='$url'></div>";
        }
        else{
          $link = "";
        }

        return $link;
    }
}
