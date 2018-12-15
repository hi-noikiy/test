<?php
class EM_Apiios_Helper_Address extends Mage_Directory_Helper_Data
{
    protected $_store = null;
    protected $_regionArray = null;
    protected $_countryArray = null;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Retrieve regions data array
     *
     * @return array
     */
    public function getRegionArray()
    {
        if (!$this->_regionArray) {
            $json = '';
            $cacheKey = 'DIRECTORY_REGIONS_ARRAY_STORE'.$this->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $countryIds = array();
                foreach ($this->getCountryCollection() as $country) {
                    $countryIds[] = $country->getCountryId();
                }
                $collection = Mage::getModel('directory/region')->getResourceCollection()
                    ->addCountryFilter($countryIds)
                    ->load();
                $regions = array(
                    'config' => array(
                        'show_all_regions' => $this->getShowNonRequiredState(),
                        'regions_required' => $this->getCountriesWithStatesRequired()
                    )
                );
                
                foreach ($collection as $region) {
                    if (!$region->getRegionId()) {
                        continue;
                    }
                    $regions[$region->getCountryId()][] = array(
                        'code' => $region->getCode(),
                        'name' => $this->__($region->getName()),
                        'region_id'=>$region->getRegionId()
                    );
                }
                $this->_regionArray = $regions;
                $json = Mage::helper('core')->jsonEncode($regions);

                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            } else{
                $this->_regionArray = Mage::helper('core')->jsonDecode($json);
            }
        }

        return $this->_regionArray;
    }

    /**
     * Get Country Array
     * @return array
     */
    public function getCountryArray()
    {
        if(is_null($this->_countryArray)){
            $json = '';
            $cacheKey = 'DIRECTORY_COUNTRY_ARRAY_STORE'.$this->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $collection = Mage::getModel('directory/country')->getResourceCollection()
                    ->loadByStore($this->getStore()->getId())
                    ->toOptionArray();
                $this->_countryArray = $collection;
                $json = Mage::helper('core')->jsonEncode($collection);
                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            } else {
                $this->_countryArray = Mage::helper('core')->jsonDecode($json);
            }
            
        }
        return $this->_countryArray;
    }
}
?>