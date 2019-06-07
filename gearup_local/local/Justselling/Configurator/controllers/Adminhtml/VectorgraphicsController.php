<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_jobprocessor
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Justselling_Configurator_Adminhtml_VectorgraphicsController extends Mage_Adminhtml_Controller_Action {


    /**
     *
     */
    public function indexAction() {
        $params = $this->getRequest()->getParams();

        $id = $params['id'];
        if ($id) {

        }

        if($id){
            $file = Mage::getModel('configurator/vectorgraphics_file')->load($id);
            if ($file->getId()) {
                $body = "";
                foreach (unserialize($file->getBody()) as $line) {
                    $body .= $line."\n";
                }
                return $this->_prepareDownloadResponse(
                    $file->getOrderId()."-".Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.svg', $body,
                    'image/svg+xm'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no documents available.'));
                $this->_redirect('*/*/');
            }
        } else {
            $this->_getSession()->addError($this->__('There are no documents available.'));
            $this->_redirect('*/*/');
        }

        $this->_redirect('*/*/');
    }
}