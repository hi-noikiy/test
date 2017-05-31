<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Customreport\Controller\Adminhtml\Cimorder;

use Magento\Backend\App\Action\Context;
use Ktpl\Customreport\Api\CimorderRepositoryInterface as PageRepository;
use Magento\Framework\Controller\Result\JsonFactory;
//use Magento\Cms\Api\Data\PageInterface;

/**
 * Cms page grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
                $this->setPageData($page, $extendedPageData, $pageData);
                $this->pageRepository->save($page);
            } 
            catch (\Exception $e) {
                $messages = $this->__('Something went wrong while saving the page.');
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
   
    
    public function setPageData(\Ktpl\Customreport\Model\Cimorder $page, array $extendedPageData, array $pageData)
    {
        $page->setData(array_merge($page->getData(), $extendedPageData, $pageData));
        return $this;
    }
}
