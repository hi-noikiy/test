<?php

class Ktpl_Salesreport_Model_Resource_Order_Item_Collection extends Mage_Sales_Model_Resource_Order_Item_Collection
{
    public function getSize()
    {
        return sizeof( $this->getAllIds());
    }
}
