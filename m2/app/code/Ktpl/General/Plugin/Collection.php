<?php

namespace Ktpl\General\Plugin;

class Collection
{
    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $_storeManager
    ){
        $this->_storeManager = $_storeManager;
     }

    public function aftertoOptionArray(\Magento\Directory\Model\ResourceModel\Region\Collection $subject, $result)
    {
        if($this->_storeManager->getStore()->getStoreId() == "7")
        {
            if(count($result) > 0 )
            {
                if($result['0']['title'] == "" && $result['0']['value'] == "")
                {
                    $result['0']['label'] = __('Region, state or province');
                }
            }
        }

        return $result;        
    }    
}