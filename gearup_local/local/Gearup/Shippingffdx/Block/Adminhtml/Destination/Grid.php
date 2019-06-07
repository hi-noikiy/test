<?php

class Gearup_Shippingffdx_Block_Adminhtml_Destination_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId("destinationGrid");
        $this->setDefaultSort("destination_id");
        $this->setDefaultDir("ASC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel("gearup_shippingffdx/destination")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn("destination_id", array(
            "header"    => Mage::helper("gearup_sds")->__("ID"),
            "align"     =>"right",
            "width"     => "80px",
            "index"     => "destination_id",
        ));

        $this->addColumn("courier_name", array(
            "header"    => Mage::helper("gearup_sds")->__("Courier Name"),
            "align"     => "left",
            "index"     => "courier_name",
        ));
        $this->addColumn("courier_nickname", array(
            "header"    => Mage::helper("gearup_sds")->__("Courier Nickname"),
            "align"     => "left",
            "index"     => "courier_nickname",
        ));
        $this->addColumn("destination", array(
            "header"    => Mage::helper("gearup_sds")->__("Destination"),
            "align"     => "left",
            "index"     => "destination",
        ));

        $this->addColumn("number", array(
            "header"    => Mage::helper("gearup_sds")->__("Courier Number"),
            "align"     => "left",
            "index"     => "number",
        ));
        $this->addColumn("tracking_url", array(
            "header"    => Mage::helper("gearup_sds")->__("Tracking Url"),
            "align"     => "left",
            "index"     => "tracking_url",
        ));

        $this->addColumn("action",
            array(
                "header"    =>  Mage::helper("gearup_sds")->__("Action"),
                "width"     => "100px",
                "type"      => "action",
                "getter"    => "getId",
                "actions"   => array(
                    array(
                        "caption"   => Mage::helper("gearup_sds")->__("Edit"),
                        "url"       => array("base"=> "*/*/edit"),
                        "field"     => "id"
                    )
                ),
                "filter"    => false,
                "sortable"  => false,
                "is_system" => true,
            )
        );

      return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return '';
    }

}
