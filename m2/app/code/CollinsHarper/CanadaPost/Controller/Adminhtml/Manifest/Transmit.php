<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Controller\Adminhtml\Manifest;


class Transmit extends \CollinsHarper\CanadaPost\Controller\Adminhtml\AbstractController
{

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::manifests');
    }

    /**
     * manifet grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $resultRedirect = $this->resultRedirectFactory->create();

        $this->chLogger->info(__METHOD__ . __LINE__);

        $manifestIds = $this->getRequest()->getParam('selected');

        $this->chLogger->info(__METHOD__ . __LINE__ . print_r($manifestIds, 1));

        if (!empty($manifestIds) && is_array($manifestIds)) {


            $this->chLogger->info(__METHOD__ . __LINE__);

            $transmitted = 0;
            foreach($manifestIds as $manifestId) {

                $this->chLogger->info(__METHOD__ . __LINE__);
                $manifest =  $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Manifest')->load($manifestId);

                if ($manifest->getId()) {

                    $this->chLogger->info(__METHOD__ . __LINE__);

                    $response =  $this->_objectManager->create('CollinsHarper\CanadaPost\Helper\Rest\Transmit')->transmit($manifest);

                    $xml = new \SimpleXMLElement($response);

                    if (count($xml->link) > 0) {

                        foreach ($xml->link as $link) {

                            $this->chLogger->info(__METHOD__ . __LINE__);

                            if (!empty($link['rel']) && $link['rel'] == 'manifest') {

                                $this->_objectManager->create('CollinsHarper\CanadaPost\Model\Manifestlink')
                                    ->setManifestId($manifestId)
                                    ->setLink($link['href'])
                                    ->save();


                            }

                        }

                        $manifest->setUrl('transmitted')
                            ->setStatus('transmitted')
                            ->setUpdatedAt( $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d H:i:s'))
                            ->save();

                        $transmitted++;

                    } else {

                        $this->chLogger->info("canada post transmit error: " . $response);

                        $this->messageManager->addError(__('Transmission Error'));

                    }

                } else {

                    $this->messageManager->addError(__('Manifest not found'));

                }
                $this->chLogger->info(__METHOD__ . __LINE__);
            }

            $this->chLogger->info(__METHOD__ . __LINE__);
            if ($transmitted > 0) {
                $this->chLogger->info(__METHOD__ . __LINE__);
                $this->messageManager->addSuccess(__('%1 of %2 Manifests have been successfully transmitted',$transmitted ,count($manifestIds)));

            }

        } else  {
            $this->messageManager->addSuccess(__('No manifests selected'));

        }

        return $resultRedirect->setPath('*/*/');

    }


}
