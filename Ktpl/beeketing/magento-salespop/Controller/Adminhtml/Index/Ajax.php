<?php
/**
 * Admin ajax controller
 *
 * @author Beeketing <hi@beeketing.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Beeketing\SalesPop\Controller\Adminhtml\Index;

class Ajax extends \Magento\Backend\App\Action
{

    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Json helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * Module app api
     *
     * @var \Beeketing\SalesPop\Core\Api\App
     */
    private $app;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Beeketing\SalesPop\Core\Api\App $app
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Beeketing\SalesPop\Core\Api\App $app
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->app = $app;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            try {
                $apiKey = $this->getRequest()->getPost('api_key');
                if ($apiKey) {
                    $this->app->init();
                    $this->app->setApiKey($apiKey);
                    if ($this->app->updateShopAbsolutePath() && $this->app->installApp()) {
                        return $this->jsonResponse([
                            'success' => true,
                        ]);
                    }
                }

                return $this->jsonResponse([
                    'success' => false,
                ]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return $this->jsonResponse($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return $this->jsonResponse($e->getMessage());
            }
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
