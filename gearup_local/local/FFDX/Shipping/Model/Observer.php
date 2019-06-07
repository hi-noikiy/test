<?php


class FFDX_Shipping_Model_Observer {
    /*
     * This method changing tracking title for title from configuration.
     * This method checking isObjectNew because when shipping label is generating all old tracks are saved again.
     */
    public function changeTrackingTitle($observer)
    {
          $track =  $observer->getTrack();
          $title = Mage::getStoreConfig("carriers/tablerate/title");
          $newTitle = Mage::getStoreConfig("ffdxshipping/general/track_title");
          if($track->isObjectNew() && $track->getTitle() == $title ) {
              $track->setTitle($newTitle);
          }
    }

}
