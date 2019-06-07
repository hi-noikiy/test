<?php

class Justselling_Assetminify_Model_SelftesterCommon extends Justselling_Assetminify_Model_Selftester_Abstract
{
    public $messages = array();
    public $errorOccurred = false;
    protected $_fix = false;

    public function main ()
    {
        $this->messages[] = 'Starting ' . get_class($this);
        $failed = false;
        try {
            if (!$this->selfCheckLocation()) {
                $failed = true;
            }
            if (Mage::app()->getRequest()->getParam('fix') == 'true') {
                $this->_fix = true;
            }
            if (!$this->checkFileLocations()) {
                $failed = true;
            }
            if (!$this->magentoRewrites()) {
                $failed = true;
            }
            if (!$this->dbCheck()) {
                $failed = true;
            }
            if (!$this->hasSettings()) {
                $failed = true;
            }
            if (!$failed) {
                $this->messages[] = 'Result: success';
            } else {
                $this->messages[] = 'Result: failure';
                $this->errorOccurred = true;
            }
        } catch (Exception $e) {
            $this->errorOccurred = true;
            $this->messages[] = $e->getMessage();
        }
        $this->messages[] = 'Self-test finished';
        return $this;
    }

    public function selfCheckLocation() {
        if (file_exists('app' . DIRECTORY_SEPARATOR . 'Mage.php')) {
            require_once 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
            Mage::app();
            $this->messages[] = "Default store loaded";
            $this->_getVersions();
        } else {
            $this->messages[] = 'Can\'t instantiate Magento. Is the file uploaded to your root Magento folder?';
            throw new Exception();
        }
        return true;
    }

    public function shouldFix() {
        return $this->_fix;
    }

    public function checkFileLocations() {
        $returnVal = true;
        $this->messages[] = "Checking file locations";
        foreach ($this->_getFiles() as $currentRow) {

            if (empty($currentRow)) {
                continue;
            }
            try {
                if (!file_exists($currentRow)) {
                    throw new Exception('File ' . $currentRow . ' does not exist');
                }
                if (!is_readable($currentRow)) {
                    throw new Exception(
                        'Can\'t read file ' . $currentRow . ' - please check file permissions and file owner.'
                    );
                }

                $handleExtFile = fopen($currentRow, "r");
                if (!$handleExtFile) {
                    throw new Exception(
                        'Can\'t read file contents ' . $currentRow
                        . ' - please check if the file got corrupted in the upload process.'
                    );
                }
                fclose($handleExtFile);
            } catch (Exception $e) {
                $this->messages[] = $e->getMessage();
                $returnVal = false;
            }
        }
        return $returnVal;
    }

    public function magentoRewrites () {
        $returnVal = true;
        $this->messages[] = "Checking rewrites";

        foreach ($this->_getRewrites() as $currentRow) {

            if (empty($currentRow) || !$currentRow) {
                continue;
            }
            try {
                $this->_testRewriteRow($currentRow);
            } catch (Exception $e) {
                $this->messages[] = $e->getMessage();
                $returnVal = false;
            }
        }
        return $returnVal;
    }

    public function dbCheck() {
        $dbCheckModel = new Justselling_Assetminify_Model_Selftester_Db();
        return $dbCheckModel->dbCheck($this);
    }

    public function hasSettings() {
        foreach ($this->_getSettings() as $table => $tableValues) {

            $this->messages[] = $table;
            foreach ($tableValues as $setting) {
                $msg = array();
                foreach ($setting as $key => $value) {
                    $msg[] = $key . ': ' . $value;
                }
                $this->messages[] = implode(' | ', $msg);
            }

        }
        return true;
    }
}
