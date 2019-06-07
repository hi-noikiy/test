<?php

class MageWorkshop_ImportExportReview_Adminhtml_Drie_ProfileController extends Mage_Adminhtml_Controller_Action
{
    protected $_requiredCSVHeaders   = array('sku', 'title', 'detail', 'nickname');
    protected $_requiredYotpoHeaders = array('review_title', 'review_content', 'review_score', 'sku', 'display_name');

    public function indexAction()
    {
        $this->_title('Reviews Import/Export Profiles');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        /** @var MageWorkshop_ImportExportReview_Model_Profile $profile */
        $profile = Mage::getModel('mageworkshop_importexportreview/profile');
        if ($profileId = $this->getRequest()->getParam('id', false)) {
            $profile->load($profileId);
            if ($profile->getId() < 1) {
                $this->_getSession()->addError($this->__('This profile no longer exists.'));
                $this->_redirect('adminhtml/drie_profile/index');
                return;
            }
            $this->_title('Edit Profile');
        } else {
            $this->_title('New Profile');
        }

        $runImport = false;
        $activeTabId = null;
        if ($this->getRequest()->getParam('file_upload')) {
            if ($this->_processFileUpload()) {
                $runImport = true;
            }
            $activeTabId = 'run';
        } else {
            if ($postData = $this->getRequest()->getPost('profileData')) {
                try {
                    $this->_handlePostData($profile, $postData);
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $profile->getId()));
                        return;
                    }

                    $this->_redirect('adminhtml/drie_profile/index');
                    return;
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        Mage::register('current_drie_profile', $profile);

        $profileEditBlock = $this->getLayout()->createBlock('mageworkshop_importexportreview_adminhtml/profile_edit');
        $this->loadLayout();
        $tabsBlock = $this->getLayout()->createBlock('mageworkshop_importexportreview_adminhtml/profile_edit_tabs');
        $tabsBlock->setData('needed_tab_id', $activeTabId);
        $tabsBlock->setData('run_import', $runImport);
        $tabsBlock->setData('yotpo', (bool) $this->getRequest()->getParam('yotpo'));
        $this->_addContent($profileEditBlock);
        $this->_addLeft($tabsBlock);
        $this->renderLayout();
    }

    /**
     * Save file for further import
     *
     * @return bool
     */
    protected function _processFileUpload()
    {
        if ($error = $this->_validateImportFile()) {
            $this->_getSession()->addError($error);
            return false;
        } else {
            try {
                $fileName = MageWorkshop_ImportExportReview_Model_Profile::IMPORT_FILE_NAME;
                $path     = Mage::getBaseDir() . DS . MageWorkshop_ImportExportReview_Model_Profile::IMPORT_DIR;
                $uploader = new Varien_File_Uploader('import_review_file');
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->setAllowCreateFolders(true);
                $uploader->save($path . DS, $fileName );
                $csv = new SplFileObject($path . $fileName);
                $titles = $csv->fgetcsv();

                if ($this->getRequest()->getParam('yotpo')) {
                    $missingFields = array_diff($this->_requiredYotpoHeaders, $titles);
                } else {
                    $missingFields = array_diff($this->_requiredCSVHeaders, $titles);
                }

                if (!empty($missingFields)) {
                    throw new Exception($this->_getHelper()->__('Following fields are required: %s', implode(', ', $missingFields)));
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                return false;
            }

            $this->_getSession()->addSuccess($this->_getHelper()->__('Click "Run Profile" to start import!'));
            return true;
        }
    }

    /**
     * @return string
     */
    protected function _validateImportFile()
    {
        $error = '';
        if (!isset($_FILES) ||  !isset($_FILES['import_review_file']) || $_FILES['import_review_file']['name'] == '') {
            $error = $this->_getHelper()->__('Review Import File Is Not Attached');
        }

        return $error;
    }

    /**
     * @return MageWorkshop_ImportExportReview_Model_Profile
     */
    protected function _initProfile()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var MageWorkshop_ImportExportReview_Model_Profile $profile */
        $profile = Mage::getModel('mageworkshop_importexportreview/profile')->load($id);
        $profile->generateXML((bool) $this->getRequest()->getParam('yotpo'));
        Mage::register('current_convert_profile', $profile);

        return $profile;
    }

    public function runAction()
    {
        $profile = $this->_initProfile();
        $this->loadLayout();
        $this->renderLayout();
        if ($profile->getType() == MageWorkshop_ImportExportReview_Model_Profile::EXPORT) {
            $file = $profile->getExportFileName();
            $fullFilePath = $profile->getExportFilePath();
            $content = file_get_contents($fullFilePath);
            $contentType = 'text/csv';
            @unlink($fullFilePath);
            $this->_prepareDownloadResponse($file, $content, $contentType);
        } else {
            $fullFilePath = $profile->getImportFilePath();
            @unlink($fullFilePath);
        }
    }

    public function deleteAction()
    {
        try {
            $this->_initProfile()->delete();
            $this->_getSession()->addSuccess($this->_getHelper()->__('The profile was removed'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store'))));
    }

    /**
     * Process object save
     *
     * @param MageWorkshop_ImportExportReview_Model_Profile $profile
     * @param array $postData
     * @throws Exception
     */
    protected function _handlePostData(MageWorkshop_ImportExportReview_Model_Profile $profile, Array $postData)
    {
        $profile->addData($postData);
        $profile->save();
        $ratingMapping = $this->getRequest()->getPost('ratingMapping', array());
        /** @var MageWorkshop_ImportExportReview_Model_Resource_RatingMapping_Collection $ratingMappingCollection */
        $ratingMappingCollection = Mage::getResourceModel('mageworkshop_importexportreview/ratingMapping_collection');
        $ratingMappingCollection->addFieldToFilter('profile_id', $profile->getId());

        if ($ratingMappingCollection->count()) {
            foreach ($ratingMappingCollection as $ratingMappingModel) {
                $ratingId = $ratingMappingModel->getData('rating_id');
                if (isset($ratingMapping[$ratingId])) {
                    $ratingMappingModel->setData('mapping_value', $ratingMapping[$ratingId]['inFile']);
                    $ratingMappingModel->save();
                }
            }
        } else {
            foreach ($ratingMapping as $originId => $mapping) {
                /** @var MageWorkshop_ImportExportReview_Model_RatingMapping $ratingMappingModel */
                $ratingMappingModel = Mage::getModel('mageworkshop_importexportreview/ratingMapping');
                $ratingMappingModel->addData(array(
                        'profile_id'    => $profile->getId(),
                        'rating_id'     => $originId,
                        'mapping_value' => $mapping['inFile']
                    )
                );

                $ratingMappingModel->save();
            }
        }

        $this->_getSession()->addSuccess($this->__('The profile has been saved.'));
    }

    /**
     * @return MageWorkshop_ImportExportReview_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('mageworkshop_importexportreview');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/drie');
    }
}