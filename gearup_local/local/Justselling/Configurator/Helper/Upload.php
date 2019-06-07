<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Helper_Upload extends Mage_Core_Helper_Abstract {

    public function createAllDirectoriesFromPath($path){

        $targetFolder = Mage::getBaseDir('media') ;
        $pathArray = explode('/', $path);

        if(is_array($pathArray)){
            foreach($pathArray as $dir){
                $targetFolder = $targetFolder .DS .$dir;
                if (!file_exists(str_replace('//', '/', $targetFolder))) {
                    mkdir(str_replace('//', '/', $targetFolder), 0755, true);
                }
            }
        }else{
            $targetFolder = $targetFolder .DS .$path;
            if (!file_exists(str_replace('//', '/', $targetFolder))) {
                mkdir(str_replace('//', '/', $targetFolder), 0755, true);
            }
        }
    }

    /**
     * Using in initial call and after each upload, shows if we have any upload
     * @param $id (last insert, upload)
     * @param object $uploadCollection
     * @return string, whole prepare row for insert in table
     */
    public function getUploadRows($id, $uploadCollection = null) {
        if ($id) { // single row
            $upload = Mage::getModel("fileuploader/order")->load($id);
        }  else {
            $upload = $uploadCollection;
        }

        $width = Justselling_Fileuploader_Block_Myuploads::getThumbnailWidth();
        $height = Justselling_Fileuploader_Block_Myuploads::getThumbnailHeight()+9;
        $dataCheck = Mage::getStoreConfig('fileuploader/general/dataCheck');
        $dataCheckRow = $dataCheck == 1 ? '<td></td>' : '';

        $row = '
                <tr class="upload item" id="hasUpload-'. $upload->getId().'">
                    <td>'.Mage::helper('core')->formatDate($upload->getUploadedAt(), 'medium', true).'</td>
                    <td>
                        <div class="files rem-'. $upload->getId() .'">
                            <a target="_blank" href="'. Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).Mage::getStoreConfig('fileuploader/general/mediapath')."/".$upload->getFile().'">
                                <img
                                    src="'. Justselling_Fileuploader_Block_Myuploads::getFileIcon($upload).'"
                                    alt="'. basename($upload->getFile()) .'"
                                    title="'. basename($upload->getFile()) .'"
                                    style="width:'. $width .'px; height:'. $height .'px"
                                    />
                                <span>'. basename($upload->getFile()).'</span>
                            </a>
                        </div>
                    </td>
                    '. $dataCheckRow .'
                    <td class="deleteRow">
                        <div class="remove button remove_file rem-'. $upload->getId().'" data-itemId="'. $orderItemId = $upload->getOrderItemId().'" data-productid="'. $upload->getId().'" id="remove-'. $upload->getId().'">Delete</div>
                    </td>
                </tr>';

        return $row;
    }
}