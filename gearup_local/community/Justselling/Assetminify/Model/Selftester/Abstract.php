<?php

class Justselling_Assetminify_Model_Selftester_Abstract extends Mage_Core_Model_Abstract {
    protected function _testRewriteRow(array $currentRow) {
        switch ($currentRow[0]) {
            case 'resource-model':
                $model = Mage::getResourceModel($currentRow[1]);
                if (get_class($model) != $currentRow[2]) {
                    throw new Exception(
                        'Trying to load class ' . $currentRow[2] . 'returns ' . get_class($model)
                        . '. Please refresh your Magento configuration cache and check if you have any conflicting extensions installed.'
                    );
                }
                break;

            case 'model':
                $model = Mage::getModel($currentRow[1]);
                if (!($model instanceof $currentRow[2])) {
                    throw new Exception(
                        'Trying to load class ' . $currentRow[2] . ' returns ' . get_class($model)
                        . '. Please refresh your Magento configuration cache and check if you have any conflicting extensions installed.'
                    );
                }
                if (get_class($model) != $currentRow[2]) {
                    $this->messages[] =
                        'Trying to load class ' . $currentRow[2] . ' returns correct instance but unexpected class '
                        . get_class($model). '. This can be a likely cause of issues and will need to be investigated on a case by case basis if the other extension cannot be uninstalled.';
                }
                break;
            case 'block':
                $block = Mage::app()->getLayout()->createBlock($currentRow[1]);
                if (!($block instanceof $currentRow[2])) {
                    throw new Exception(
                        'Trying to load block ' . $currentRow[2] . ' returns ' . get_class($block)
                        . '. Please refresh your Magento configuration cache and check if you have any conflicting extensions installed.'
                    );
                }
                if (get_class($block) != $currentRow[2]) {
                    $this->messages[] =
                        'Trying to load block ' . $currentRow[2] . ' returns correct instance but unexpected class '
                        . get_class($block). '. This can be a likely cause of issues and will need to be investigated on a case by case basis if the other extension cannot be uninstalled.';
                }
                break;
        }
    }

    public function _getVersions() {
        $this->messages[] = "Magento version: " . Mage::getVersion();
    }

    public function _getDbFields() {
        return array();
    }

    public function _getRewrites () {
        return array();
    }

    public function _getFiles () {
        return array();
    }

    public function _getSettings() {
        return array();
    }

}
