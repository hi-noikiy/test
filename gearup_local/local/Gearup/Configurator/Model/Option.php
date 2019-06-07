<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Gearup_Configurator_Model_Option extends Justselling_Configurator_Model_Option{

   
    public function saveTemplateOptionValue($value, $optionId, $templateId)
    {
        $valueModel = Mage::getModel("configurator/value")->load($value['id']);

        if ($value['is_delete'] == "1") {
            $default_value = $value['id'];
            $valueModel->delete();
            $option = Mage::getModel('configurator/option')->load($optionId);
            if($option->getDefaultValue()){
                if($option->getDefaultValue() == $default_value){
                    $option->setDefaultValue(null);
                    $option->save();
                }
            }

        } else {

            $valueModel->setOptionId($optionId);
            $valueModel->setTitle($value['title']);
            $valueModel->setValue($value['value']);
            $valueModel->setSortOrder($value['sort_order']);
            $valueModel->setPrice($value['price']);
            $valueModel->setSku($value['sku']);
            $valueModel->setIsRecommended($value['is_recommended']);
                        
            if (isset($value['product_id'])) {
                if ($value['product_id']) {
                    $valueModel->setProductId($value['product_id']);
                } else {
                    $valueModel->setProductId(NULL);
                }
            }

            if (!empty($value['thumbnail_size_x']))
                $valueModel->setThumbnailSizeX($value['thumbnail_size_x']);
            if (isset($value['thumbnail_size_x']) && $value['thumbnail_size_x'] == "") {
                $valueModel->setData("thumbnail_size_x", null);
            }

            if (!empty($value['thumbnail_size_y']))
                $valueModel->setThumbnailSizeY($value['thumbnail_size_y']);
            if (isset($value['thumbnail_size_y']) && $value['thumbnail_size_y'] == "") {
                $valueModel->setData("thumbnail_size_y", null);
            }

            if (!empty($value['image_size_x']))
                $valueModel->setImageSizeX($value['image_size_x']);
            if (isset($value['image_size_x']) && $value['image_size_x'] == "") {
                $valueModel->setData("image_size_x", null);
            }

            if (!empty($value['image_size_y']))
                $valueModel->setImageSizeY($value['image_size_y']);
            if (isset($value['image_size_y']) && $value['image_size_y'] == "") {
                $valueModel->setData("image_size_y", null);
            }

            if (!empty($value['image_offset_x']))
                $valueModel->setImageOffsetX($value['image_offset_x']);
            if (isset($value['image_offset_x']) && $value['image_offset_x'] == "") {
                $valueModel->setData("image_offset_x", null);
            }

            if (!empty($value['image_offset_y']))
                $valueModel->setImageOffsetY($value['image_offset_y']);
            if (isset($value['image_offset_y']) && $value['image_offset_y'] == "") {
                $valueModel->setData("image_offset_y", null);
            }

            if (!empty($value['info']))
                $valueModel->setInfo($value['info']);
            if (isset($value['info']) && $value['info'] == "") {
                $valueModel->setData("info", null);
            }

            if (!empty($value['more_info']))
                $valueModel->setMoreInfo($value['more_info']);
            if (isset($value['more_info']) && $value['more_info'] == "") {
                $valueModel->setData("more_info", null);
            }

            $valueModel->save();

            if (!empty($value['thumbnail'])) {
                try {
                    $tempFolder = Mage::getBaseDir('media') . '/tmp/upload/admin';
                    $tempFile = rtrim($tempFolder, '/') . '/' . $value['thumbnail'];

                    if (file_exists($tempFile)) {
                        $mediaFolder = Mage::getBaseDir('media') . DS . 'configurator';
                        $targetFolder = $templateId .DS .$optionId .DS .$valueModel->getId();

                        Mage::helper('configurator/upload')->createAllDirectoriesFromPath('configurator' .DS .$targetFolder);

                        $targetFileName = $targetFolder .DS .$value['id'] . '-' . $value['thumbnail'];
                        $targetFile = rtrim($mediaFolder, '/') . '/' . $targetFileName;
                        rename($tempFile, $targetFile);
                        $valueModel->setThumbnail($targetFileName);
                    }
                } catch (Exception $e) {
                }
            } else {
                if ($valueModel->getThumbnail()) {
                    $file = Mage::getBaseDir('media') . DS . 'configurator' . DS . $valueModel->getThumbnail();
                    if (file_exists($file)) {
                        if (!unlink($file)) {
                            Js_Log::log("Deleting file not possible: ".$file, $this, Zend_Log::NOTICE, true);
                        }
                    } else {
                        /* If we have image stored in specific width, we have to delete this file too */
                        $extension = Mage::helper("configurator/image")->getFileExtension($file);
                        $thumbwidth = $valueModel->getThumbnailSizeX();
                        $thumbFile = str_replace(".".$extension, "_".$thumbwidth."_thumbnail.".$extension, $file);
                        Js_Log::log("Deleteing thumbnail in specific width: ".$thumbFile, $this);
                        unlink($thumbFile);
                    }
                    $valueModel->setThumbnail(null);
                }
            }

            // deleted thumbnail alt ... should be not longer in use

            if (!empty($value['image'])) {
                try {
                    $tempFolder = Mage::getBaseDir('media') . '/tmp/upload/admin';
                    $tempFile = rtrim($tempFolder, '/') . '/' . $value['image'];

                    if (file_exists($tempFile)) {
                        $mediaFolder = Mage::getBaseDir('media') . DS . 'configurator';
                        $targetFolder = $templateId .DS .$optionId .DS .$valueModel->getId();

                        Mage::helper('configurator/upload')->createAllDirectoriesFromPath('configurator' .DS .$targetFolder);

                        $targetFileName = $targetFolder .DS .$value['id'] . '-' . $value['image'];
                        $targetFile = rtrim($mediaFolder, '/') . '/' . $targetFileName;
                        rename($tempFile, $targetFile);
                        $valueModel->setImage($targetFileName);
                    }
                } catch (Exception $e) {
                }
            } else {
                if ($valueModel->getImage()) {
                    $file = Mage::getBaseDir('media') . DS . 'configurator' . DS . $valueModel->getImage();
                    if (file_exists($file)) {
                        if (!unlink($file)) {
                            Js_Log::log("Deleting file not possible: ".$file, $this, Zend_Log::NOTICE, true);
                        }
                    }
                    $valueModel->setImage(null);
                }
            }

            $valueModel->save();

            if (!empty($value['status'])) {

                foreach ($value['status'] as $i => $vs) {
                    $valuestatusModel = Mage::getModel("configurator/valuechildstatus")->load($vs['id']);
                    $valuestatusModel->option_value_id = $valueModel->getId();
                    $valuestatusModel->option_id = $vs['option_id'];

                    if (isset($vs['is_require']))
                        $valuestatusModel->is_require = (int)$vs['is_require'];

                    if (isset($vs['price']))
                        $valuestatusModel->price = $vs['price'];

                    if (isset($vs['status']))
                        $valuestatusModel->status = $vs['status'];

                    if (isset($vs['min_value']) && !empty($childOptionValue['min_value']))
                        $valuestatusModel->min_value = $vs['min_value'];

                    if (isset($childOptionValue['max_value']) && !empty($childOptionValue['max_value']))
                        $valuestatusModel->max_value = $vs['max_value'];

                    //Zend_Debug::dump($vs); Zend_Debug::dump($valuestatusModel->getData()); exit;

                    $valuestatusModel->save();
                }

            }

            /* Value Tags */
            if (isset($value ['tags']) && !empty ($value ['tags'])) {
                $tag_array = explode(" ", trim($value ['tags']));

                /* Delete old Tags first */
                $collection = Mage::getModel("configurator/valuetag")->getCollection();
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());
                $collection->addFieldToFilter('tag', array('nin' => $tag_array));
                foreach ($collection as $tag) {
                    $tag->delete();
                }

                /* Insert new or edited tags */
                foreach ($tag_array as $tag_title) {
                    $tag = Mage::getModel("configurator/valuetag");
                    $tag->loadByOptionValueIdAndTag($valueModel->getId(), $tag_title);
                    if (!$tag->getId()) {
                        $tag->setOptionValueId($valueModel->getId());
                        $tag->setTag($tag_title);
                        $tag->save();
                    }
                }
            } elseif (isset($value ['details'])) { // Delete all existing tags if details are open
                $collection = Mage::getModel("configurator/valuetag")->getCollection();
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());
                foreach ($collection as $tags) {
                    $tags->delete();
                }
            }

            /* Blacklist */
            if (isset($value ['blacklist']) && !empty ($value ['blacklist'])) {
                /* Delete old Configuration first... */
                /* values */
                $collection = Mage::getModel("configurator/blacklist")->getCollection();
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());
                $collection->addFieldToFilter("child_option_id", array('null' => true));
                $collection->addFieldToFilter('child_option_value_id', array('nin' => $value ['blacklist']));
                foreach ($collection as $blacklist) {
                    $blacklist->delete();
                }

                /* Insert blacklisted values */
                foreach ($value ['blacklist'] as $bl) {
                    $blacklist = Mage::getModel("configurator/blacklist");
                    $blacklist->loadByOptionValueIdAndChildOptionValueId($valueModel->getId(), $bl);
                    if (!$blacklist->getId()) {
                        $blacklist->option_value_id = $valueModel->getId();
                        $blacklist->child_option_value_id = $bl;
                        $blacklist->save();
                    }
                }
            } elseif (isset($value ['details'])) { // Delete all existing blacklist values if details are open
                $collection = Mage::getModel("configurator/blacklist")->getCollection();
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());
                $collection->addFieldToFilter("child_option_id", array('null' => true));
                foreach ($collection as $blacklist) {
                    $blacklist->delete();
                }
            }

            /* Insert blacklisted options */
            if (isset($value['blacklistoption']) && !empty($value['blacklistoption'])) {
                /* delete old options */
                $collection = Mage::getModel("configurator/blacklist")->getCollection();
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());
                $collection->addFieldToFilter("child_option_value_id", array('null' => true));
                $collection->addFieldToFilter('child_option_id', array('nin' => array_keys($value['blacklistoption'])));
                foreach ($collection as $blacklist) {
                    $blacklist->delete();
                }

                foreach ($value ['blacklistoption'] as $child_option_id => $val) {
                    $blacklist = Mage::getModel("configurator/blacklist");
                    $blacklist->loadByOptionValueIdAndChildOptionId($valueModel->getId(), $child_option_id);
                    if (!$blacklist->getId()) {
                        $blacklist->setOptionValueId($valueModel->getId());
                        $blacklist->setChildOptionId($child_option_id);
                        $blacklist->save();
                    }
                }
            } elseif (isset($value ['details'])) { // Delete all existing blacklist values if details are open
                $collection = Mage::getModel("configurator/blacklist")->getCollection();
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());
                $collection->addFieldToFilter("child_option_value_id", array('null' => true));
                foreach ($collection as $blacklist) {
                    $blacklist->delete();
                }
            }

            if (isset($value['optionblacklist']) && !empty($value['optionblacklist'])) {
                $num = 0;
                foreach ($value['optionblacklist']["optionid"] as $opt) {
                    if ($value['optionblacklist']["operator"][$num] && $value['optionblacklist']["value"][$num] !== null) {
                        Mage::Log("setting blacklist value");
                        $blacklist = Mage::getModel("configurator/optionblacklist");
                        if (isset($value['optionblacklist']["blacklistid"][$num])) // Update, load before save
                            $blacklist->load($value['optionblacklist']["blacklistid"][$num]);
                        $blacklist->setOptionId($value['optionblacklist']["optionid"][$num]);
                        $blacklist->setOperator($value['optionblacklist']["operator"][$num]);
                        $blacklist->setValue($value['optionblacklist']["value"][$num]);
                        $blacklist->setChildOptionValueId($valueModel->getId());
                        $blacklist->save();
                    }

                    if (!$value['optionblacklist']["operator"][$num] && isset($value['optionblacklist']["blacklistid"][$num])) {
                        // Delete existing record
                        $blacklist = Mage::getModel("configurator/optionblacklist")->load($value['optionblacklist']["blacklistid"][$num]);
                        $blacklist->delete();
                    }
                    $num++;
                }
            }
            /* tag blacklisting */
            if (isset($value['optionvaluetagblacklist']) && !empty($value['optionvaluetagblacklist'])) {
                $collection = Mage::getModel("configurator/valuetagblacklist")->getCollection();
                $collection->addFieldToFilter("option_id", $optionId);
                $collection->addFieldToFilter("option_value_id", $valueModel->getId());

                foreach ($collection as $item){
                    $item->delete();
                }

                foreach ($value['optionvaluetagblacklist']['related_option_id'] as $related_option_id => $optionvaluetagblacklist) {
                    Mage::Log("SAVING " . var_export($value['optionvaluetagblacklist'], true));
                    Mage::Log("option id " . $optionId);
                    Mage::Log("related option id " . $related_option_id);
                    Mage::Log("child value id " . $valueModel->getId());


                    foreach ($optionvaluetagblacklist["tag"] as $opt) {
                        Mage::Log("setting optionvaluetagblacklist value " . var_export($opt, true));
                        $blacklist = Mage::getModel("configurator/valuetagblacklist");
                        $blacklist->setOptionId($optionId);
                        $blacklist->setOptionValueId($valueModel->getId());
                        $blacklist->setRelatedOptionId($related_option_id);
                        $blacklist->setTag($opt);
                        $blacklist->save();
                    }
                }
            }

            if (isset($value['pricelist']) && !empty($value['pricelist'])) {
                foreach ($value['pricelist'] as $i => $pl) {

                    $pricelistvalueModel = Mage::getModel("configurator/pricelistvalue")->load($pl['id']);

                    if ($pl['is_delete'] == "1") {
                        $pricelistvalueModel->delete();
                    } else {
                        $pricelistvalueModel->option_value_id = $valueModel->getId();
                        $pricelistvalueModel->price = $pl['price'];
                        $pricelistvalueModel->operator = $pl['operator'];
                        $pricelistvalueModel->value = $pl['value'];

                        $pricelistvalueModel->save();
                    }
                }
            }
        }
        return $valueModel;
    }

}
