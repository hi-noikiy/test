<?php

require_once 'Mage/Rss/controllers/CatalogController.php';
class Gearup_Sds_CatalogController extends Mage_Rss_CatalogController {
    public function sdsAction() {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }
}