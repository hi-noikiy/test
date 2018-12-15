<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $link = "";
        $customer_id = $row->getData('magento_id');
        if($customer_id){
          $collection = Mage::getModel('eshopsync/contact')->getCollection()->addFieldToFilter('customer_id',$customer_id);
          if(count($collection)){
            $url = Mage::helper("adminhtml")->getUrl("eshopsync/adminhtml_contact/index/id/".$customer_id);
            $link = "<a href='$url'>View Contacts</a>";
          }
          else{
            $link = "No Contacts";
          }
        }
        return $link;
    }
}
