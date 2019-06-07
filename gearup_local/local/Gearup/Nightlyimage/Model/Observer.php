<?php
class Gearup_Nightlyimage_Model_Observer {
    public function Productsavebefore(Varien_Event_Observer $observer) {
        $filename = Mage::getBaseDir()."/import/image_check.csv";
        $product = $observer->getProduct();
        if(isset($_POST['product']['media_gallery']['images'])) {
            $images = json_decode($_POST['product']['media_gallery']['images']);
            foreach($images as $image) {
                if(isset($image->removed) && $image->removed == 1) {
                    $img = explode('/', $image->file);
                    $temp[] = $img[3];
                }
                if(isset($image->survive) && $image->survive == 1) {
                    $temp2[] = $image->file;
                }
            }
            if(isset($temp)) {
                $model = Mage::getModel('nightlyimage/nightlyrm');
                $model->load($product->getSku(), 'sku');
                if($model->getImage()) {
                    $oldimg = explode(',',$model->getImage());
                    $temp = array_merge($temp, $oldimg);
                }
                $model->setSku($product->getSku());
                $model->setImage(implode(',',$temp));
                $model->save();
            }
            if(isset($temp2)) {
                $model = Mage::getModel('nightlyimage/nightly');
                $model->load($product->getSku(), 'sku');
                $model->setSku($product->getSku());
                $model->setImage(implode(',',$temp2));
                $model->save();
            }
        }
        /*$model = Mage::getModel('nightlyimage/nightly');
        $model->load($product->getSku(), 'sku');
        if(($product->getSurvive_nightly()!= 'no_selection') && ($product->getSurvive_nightly()!='')) {
            //$img = explode('/',$product->getSurvive_nightly());
            $model->setSku($product->getSku());
            $model->setImage($product->getSurvive_nightly());
            $model->save();
        } else {
            $model->delete();
        } */
    }
		
}
