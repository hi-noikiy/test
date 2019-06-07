<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Fpc_Model_Blockmf_Messages extends Mirasvit_Fpc_Model_Blockmf_Abstract
{
     /**
     * @var array
     */
    protected $_messageStoreTypes = array(
        'core/session',
        'customer/session',
        'catalog/session',
        'checkout/session',
        'tag/session',
    );

    static protected $_messageReplace = false;

    /**
     * @param string $content
     * @return bool
     */
    public function applyToContentWithoutApp(&$content)
    {
        $sessionHelper = new Mirasvit_Fpc_Helper_Fpcmf_Sessionmf();

        if ($sessionHelper->getMessage() == true) {
            return false;
        }

        return true;
    }

    /**
     * @param string $content
     * @return void
     */
    public function applyToContentInApp(&$content)
    {
        $startTime = microtime(true);

        $layoutHelper = Mage::helper('fpc/layout');

        $block = $layoutHelper->renderBlock($this->getLayoutXml(), $this->getNameInLayout());

        if (!$block) {
            if  (self::$_messageReplace) {
                return false;
            }
            $message = false;
            $messages = Mage::getSingleton('core/session')->getMessages(true); //true - clear message
            if (is_object($messages)) {
                $message = $messages->getLastAddedMessage();
                $message =  $this->prepareMessage($message);
            }
            if ($message && strpos($content, 'messages_product_view') !== false) {
                $content = preg_replace('/<div id="messages_product_view">/ims', '<div id="messages_product_view">' . $message, $content, 1);
                self::$_messageReplace = true;
            }

            return false;
        }

        foreach ($this->_messageStoreTypes as $type) {
            $this->_addMessagesToBlock($type, $block);
        }

        $html = $block->toHtml();

        $html = $this->getWrappedHtml($html);

        $pattern = '/'.preg_quote($this->_getStartWrapperTag(), '/').'(.*?)'.preg_quote($this->_getEndWrapperTag(), '/').'/ims';

        Mirasvit_Fpc_Helper_Fpcmf_Debugmf::appendDebugInformationToBlock($html, $this, 1, microtime(true));

        $content = preg_replace($pattern, str_replace('$', '\\$', $html), $content, 1);

        $this->setRenderTime(microtime(true) - $startTime);
    }

    /**
     * @param  object $messagesStorage
     * @param  Mage_Core_Block_Messages $block
     * @return void
     */
    protected function _addMessagesToBlock($messagesStorage, Mage_Core_Block_Messages $block)
    {
        if ($storage = Mage::getSingleton($messagesStorage)) {
            $block->addMessages($storage->getMessages(true));
            $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
        }
    }

    /**
     * @param object $message
     * @return string
     */
    protected function prepareMessage($message)
    {
        if (!is_object($message)) {
            return false;
        }

        $messageText = '';
        if ($message->getType() == 'success') {
            $messageText = '<ul class="messages">
                                <li class="success-msg">
                                    <ul>
                                        <li>' . $message->getText() . '</li>
                                    </ul>
                                </li>
                            </ul>';
        } elseif ($message->getType() == 'note') {
            $messageText = '<ul class="messages">
                                <li class="note-msg">
                                    <ul>
                                        <li>' . $message->getText() . '</li>
                                    </ul>
                                </li>
                            </ul>';
        } elseif ($message->getType() == 'error') {
            $messageText = '<ul class="messages">
                                <li class="error-msg">
                                    <ul>
                                        <li>' . $message->getText() . '</li>
                                    </ul>
                                </li>
                            </ul>';
        }

        return $messageText;
    }
}
