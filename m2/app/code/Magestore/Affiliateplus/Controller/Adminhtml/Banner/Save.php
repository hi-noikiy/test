<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Controller\Adminhtml\Banner;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Action Index
 */
class Save extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectBack = $this->getRequest()->getParam('back', false);
        $storeViewId = $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_bannerFactory->create();

            if ($id = $this->getRequest()->getParam('banner_id')) {
                $model->setStoreViewId($storeViewId)
                    ->load($id);
            }
            if (isset($_FILES['source_file']) && isset($_FILES['source_file']['name']) && strlen($_FILES['source_file']['name'])) {
                /*
                 * Save image upload
                 */
                try {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'source_file']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'swf']);

                    /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                    $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();

                    $uploader->addValidateCallback('banner_image', $imageAdapter, 'validateUploadFile');
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                    $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                        ->getDirectoryRead(DirectoryList::MEDIA);

                    $result = $uploader->save(
                        $mediaDirectory->getAbsolutePath(\Magestore\Affiliateplus\Model\Banner::BASE_MEDIA_PATH)
                    );

                    $data['source_file'] = $result['file'];
                } catch (\Exception $e) {
                    if ($e->getCode() == 0) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }
            }

            try{
                $model->setStoreViewId($storeViewId)->setData($data)
                    ->save();

                $this->messageManager->addSuccess(__('The banner has been saved.'));
                $this->_getSession()->setFormData(false);

                if($redirectBack == 'edit'){
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'banner_id' => $model->getId(),
                            '_current' => true,
                            'store' => $storeViewId
                        ]
                    );
                } elseif($redirectBack) {
                    return $resultRedirect->setPath(
                        '*/*/new',
                        [
                            '_current' => TRUE,
                            'store' => $storeViewId
                        ]
                    );
                } else {
                    return $resultRedirect->setPath(
                        '*/*/',
                        [
                            '_current' => TRUE,
                            'store' => $storeViewId
                        ]
                    );
                }
            } catch(\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError($e->getMessage());
                }
            }

        }
        return $resultRedirect->setPath(
            '*/*/',
            [
                '_current' => TRUE,
                'store' => $storeViewId
            ]
        );
    }
}
