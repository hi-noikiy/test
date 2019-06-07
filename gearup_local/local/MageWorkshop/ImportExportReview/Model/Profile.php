<?php

/**
 * Class MageWorkshop_ImportExportReview_Model_Profile
 *
 * @method MageWorkshop_ImportExportReview_Model_Profile setUseFullImagePath(int|bool $value)
 * @method string getSyncReviewIds()
 * @method MageWorkshop_ImportExportReview_Model_Profile setSyncReviewIds(string $value)
 */
class MageWorkshop_ImportExportReview_Model_Profile extends Mage_Core_Model_Abstract
{
    CONST IMPORT = 0;
    CONST EXPORT = 1;

    CONST EXPORT_FILE_NAME = 'export_reviews.csv';
    CONST EXPORT_DIR       = 'var/export/drie/';

    CONST IMPORT_FILE_NAME = 'import_reviews.csv';
    CONST IMPORT_DIR       = 'var/import/drie/';

    protected function _construct()
    {
        $this->_init('mageworkshop_importexportreview/profile');
    }

    /**
     * @return array
     */
    public function getProfileTypes()
    {
        return array(
            self::IMPORT => Mage::helper('mageworkshop_importexportreview')->__('Import'),
            self::EXPORT => Mage::helper('mageworkshop_importexportreview')->__('Export'),
        );
    }

    /**
     * @return array
     */
    public function getStoresForForm()
    {
        $stores = array(0 => Mage::helper('mageworkshop_importexportreview')->__('All Stores'));
        /** @var Mage_Core_Model_Store $store */
        foreach (Mage::app()->getStores() as $store) {
            $stores[$store->getId()] = $store->getName();
        }

        return $stores;
    }

    /**
     * @param bool $yotpo
     * @param string $file
     * @param string $dir
     * @param bool $isSync
     * @return $this
     */
    public function generateXML($yotpo = false, $file = self::EXPORT_FILE_NAME, $dir = self::EXPORT_DIR, $isSync = false)
    {
        if ($this->getType() == self::EXPORT) {
            $xml = $this->_generateExportXML($file, $dir);
        } else {
            if (!$isSync) {
                $file = self::IMPORT_FILE_NAME;
                $dir  = self::IMPORT_DIR;
            }
            $xml = $this->_generateImportXML($file, $dir, $yotpo);
        }

        $this->setActionsXml($xml);

        return $this;
    }

    /**
     * @param $importFile
     * @param $importDir
     * @param bool $yotpo
     * @return string
     */
    protected function _generateImportXML($importFile, $importDir, $yotpo)
    {
        $storeId          = $this->getStoreId();
        $fullImagesPath   = $yotpo ? true : $this->getUseFullImagePath();
        $doCreateRatings  = $this->getCreateRating();
        $doCreateProsCons = $this->getCreateProscons();
        $profileId        = $this->getId();
        $maxWidth         = $this->getMaxWidth();
        $maxHeight        = $this->getMaxHeight();
        $yotpo            = (int) $yotpo;
        $xml = <<<XML
<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">$importDir</var>
    <var name="filename"><![CDATA[$importFile]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>

<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="store"><![CDATA[$storeId]]></var>
    <var name="adapter">MageWorkshop_ImportExportReview_Model_Convert_Adapter_Import</var>
    <var name="method">parse</var>
    <var name="doCreateRatings">$doCreateRatings</var>
    <var name="doCreateProsCons">$doCreateProsCons</var>
    <var name="full_images_path">$fullImagesPath</var>
    <var name="max_width">$maxWidth</var>
    <var name="max_height">$maxHeight</var>
    <var name="profile_id">$profileId</var>
    <var name="yotpo">$yotpo</var>
</action>
XML;

        return $xml;
    }

    /**
     * @param string $exportFile
     * @param string $exportDir
     * @return string
     */
    protected function _generateExportXML($exportFile, $exportDir)
    {
        $storeId = $this->getStoreId();
        $fullImagesPath = $this->getUseFullImagePath();
        $reviewIds = $this->hasData('sync_review_ids') ? $this->getSyncReviewIds() : '0';
        $xml = <<<XML
<action type="MageWorkshop_ImportExportReview_Model_Convert_Parser_Export" method="unparse">
    <var name="store"><![CDATA[$storeId]]></var>
    <var name="full_images_path">$fullImagesPath</var>
    <var name="sync_review_ids"><![CDATA[$reviewIds]]></var>
</action>

<action type="dataflow/convert_mapper_column" method="map" />

<action type="dataflow/convert_parser_csv" method="unparse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
</action>

<action type="dataflow/convert_adapter_io" method="save">
    <var name="type">file</var>
    <var name="path">$exportDir</var>
    <var name="filename"><![CDATA[$exportFile]]></var>
</action>
XML;

        return $xml;
    }

    /**
     * @return string
     */
    public function getExportFilePath()
    {
        return Mage::getBaseDir() . DS . self::EXPORT_DIR . $this->getExportFileName();
    }

    /**
     * @return string
     */
    public function getImportFilePath()
    {
        return Mage::getBaseDir() . DS . self::IMPORT_DIR . self::IMPORT_FILE_NAME;
    }
    
    /**
     * @return string
     */
    public function getExportFileName()
    {
        return self::EXPORT_FILE_NAME;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->getData('type');
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @return bool
     */
    public function getUseFullImagePath()
    {
        return (bool) $this->getData('use_full_image_path');
    }

    /**
     * @return $this
     */
    public function run()
    {
        /**
         * Prepare xml convert profile actions data
         */
        $xml = '<convert version="1.0"><profile name="default">' . $this->getActionsXml()
            . '</profile></convert>';
        $profile = Mage::getModel('core/convert')
            ->importXml($xml)
            ->getProfile('default');
        /* @var $profile Mage_Dataflow_Model_Convert_Profile */

        try {
            $batch = Mage::getSingleton('dataflow/batch')
                ->setProfileId($this->getId())
                ->setStoreId($this->getStoreId())
                ->save();
            $this->setBatchId($batch->getId());

            $profile->setDataflowProfile($this->getData());
            $profile->run();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $this->setExceptions($profile->getExceptions());

        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function prepareBatchModel()
    {
        $batchModel = Mage::getSingleton('dataflow/batch');
        if ($batchModel->getId()) {
            if ($batchModel->getAdapter()) {
                $batchImportModel = $batchModel->getBatchImportModel();
                $importIds = $batchImportModel->getIdCollection();
                $this->_finishImport($batchModel, $importIds);
            } else {
                $batchModel->delete();
            }
        }
    }

    /**
     * @param Mage_Dataflow_Model_Batch $batchModel
     * @param $rowIds
     */
    protected function _finishImport(Mage_Dataflow_Model_Batch $batchModel, $rowIds)
    {
        if (!$batchModel->getId()) {
            return;
        }
        if (!is_array($rowIds) || count($rowIds) < 1) {
            return;
        }
        if (!$batchModel->getAdapter()) {
            return;
        }

        $batchImportModel = $batchModel->getBatchImportModel();
        $adapter = Mage::getModel($batchModel->getAdapter());
        $adapter->setBatchParams($batchModel->getParams());
        foreach ($rowIds as $importId) {
            $batchImportModel->load($importId);
            if (!$batchImportModel->getId()) {
                continue;
            }
            $importData = $batchImportModel->getBatchData();
            $adapter->saveRow($importData);
        }
    }
}