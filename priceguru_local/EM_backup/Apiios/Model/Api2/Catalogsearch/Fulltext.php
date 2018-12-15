<?php
class EM_Apiios_Model_Api2_Catalogsearch_Fulltext extends Mage_CatalogSearch_Model_Fulltext
{

    /**
     * Prepare results for query
     *
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    public function prepareResult($query = null)
    {
        if (!$query instanceof Mage_CatalogSearch_Model_Query) {
            $query = Mage::helper('apiios/catalogsearch')->getQuery();
        }
        $queryText = Mage::helper('catalogsearch')->getQueryText();
        if ($query->getSynonymFor()) {
            $queryText = $query->getSynonymFor();
        }
		$query->setStoreId(Mage::registry('curent_store')->getId());
        $this->getResource()->prepareResult($this, $queryText, $query);
        return $this;
    }

}