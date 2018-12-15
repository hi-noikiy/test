<?php

class MindMagnet_Attributes_Block_Product_View_Attributes extends Mage_Catalog_Block_Product_View_Attributes
{


    /**
     * Return Group Attribute Id by Group Name
     *
     * @param $groupName string
     * @return int | null
     */
    public function getAttributesCollection($groupName)
    {
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->load();

        foreach ($attributeSetCollection as $id=>$attributeGroup) {

            if ($attributeGroup->getAttributeGroupName() === $groupName)
                return Mage::getResourceModel('eav/entity_attribute_collection')
                    ->setAttributeGroupFilter($attributeGroup->getAttributeGroupId());
        }
        return $this;
    }
}