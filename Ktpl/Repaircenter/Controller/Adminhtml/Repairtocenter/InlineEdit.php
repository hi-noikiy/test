<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Repaircenter\Controller\Adminhtml\Repairtocenter;

use Magento\Backend\App\Action\Context;
use Ktpl\Repaircenter\Api\RepairtocenterRepositoryInterface as PageRepository;
use Magento\Framework\Controller\Result\JsonFactory;
//use Magento\Cms\Api\Data\PageInterface;


class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var PageRepository  */
    protected $pageRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param PageRepository $pageRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
       // PostDataProcessor $dataProcessor,
        PageRepository $pageRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
       // $this->dataProcessor = $dataProcessor;
        $this->pageRepository = $pageRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $pageId) {
            /** @var \Magento\Cms\Model\Page $page */
            $page = $this->pageRepository->getById($pageId);
            try {
                $pageData = $postItems[$pageId];
                //$this->validatePost($pageData, $page, $error, $messages);
                $extendedPageData = $page->getData();
                //echo '<pre />'; print_r($extendedPageData); exit;
                if($pageData['status']==2){
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $rmodel = $objectManager->create('\Ktpl\Repaircenter\Model\Repairtocustomer')->load($page->getRepairId(), 'repair_center_id');
                    $rmodel->setRepairCenterId($page['repair_id']);
                    $rmodel->setCustomer($page['customer']);
                    $rmodel->setProduct($page['product']);
                    $rmodel->save(); 
                }
                $this->setPageData($page, $extendedPageData, $pageData);
                $this->pageRepository->save($page);
            } 
            catch (\Exception $e) {
                $messages = [__('Something went wrong while saving the page.')];
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
   
    
    public function setPageData(\Ktpl\Repaircenter\Model\Repairtocenter $page, array $extendedPageData, array $pageData)
    {
        $page->setData(array_merge($page->getData(), $extendedPageData, $pageData));
        return $this;
    }
}
