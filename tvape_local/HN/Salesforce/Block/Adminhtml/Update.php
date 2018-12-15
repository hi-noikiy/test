<?php
class HN_Salesforce_Block_Adminhtml_Update extends Mage_Adminhtml_Block_Template
{
    protected function getLoadUrl()
    {
        $url =  Mage::helper("adminhtml")->getUrl("adminhtml/salesforce_retrieve/load");
        
        return $url;
    }

    protected function getUpdateUrl()
    {
        $url =  Mage::helper("adminhtml")->getUrl("adminhtml/salesforce_retrieve/index");
        
        return $url;
    }

    protected function getSyncUrl()
    {
        $url =  Mage::helper("adminhtml")->getUrl("adminhtml/salesforce_sync/sync");
        
        return $url;
    }

    protected function getTable()
    {
        $model = Mage::getSingleton('salesforce/field');
        $data = $model->getAllTable();
        foreach ($data as $key => $value) {
            $length = strlen($key);
            $subkey = substr($key, $length - 3 , $length);
            if($subkey == '__c')
                $data[$key] = substr($key, 0, $length - 3);
            elseif($key == 'Product2')
                $data[$key] = 'Product';
            else
                $data[$key] = $key;
        }
        unset($data['Campaign']);
        return $data;
    }
}
