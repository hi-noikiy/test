<?php
class EM_Apiios_Helper_Catalogsearch extends Mage_CatalogSearch_Helper_Data
{
    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        if (!$this->_query) {
			$catalogSearchHelper = Mage::getModel('catalogsearch/query');
			$catalogSearchHelper->setStoreId(Mage::registry('curent_store')->getId());
            $this->_query = $catalogSearchHelper
                ->loadByQuery($this->getQueryText());
            if (!$this->_query->getId()) {
                $this->_query->setQueryText($this->getQueryText());
            }
        }
        return $this->_query;
    }
}
?>