<?php
class Gearup_Bundlefilter_Model_Resource_Product_Indexer_Eav_Source 
    extends Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
{
    /**
     * Prepare data index for product relations
     *
     * @param array $parentIds the parent entity ids limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    protected function _prepareRelationIndex($parentIds = null)
    {
        // changed to do nothing (bundle children attributes should not be used in filter)
        return $this;
    }
}

?>