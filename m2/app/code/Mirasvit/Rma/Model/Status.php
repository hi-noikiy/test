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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method ResourceModel\Status\Collection getCollection()
 * @method $this load(int $id)
 * @method bool getIsMassDelete()
 * @method $this setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method $this setIsMassStatus(bool $flag)
 * @method ResourceModel\Status getResource()
 */
class Status extends AbstractModel implements \Mirasvit\Rma\Api\Data\StatusInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Status');
    }

    public function __construct(
        \Mirasvit\Rma\Helper\Locale $localeData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->localeData = $localeData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->localeData->getLocaleValue($this, self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->localeData->setLocaleValue($this, self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsShowShipping()
    {
        return $this->getData(self::KEY_IS_SHOW_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsShowShipping($isShowShipping)
    {
        return $this->setData(self::KEY_IS_SHOW_SHIPPING, $isShowShipping);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMessage()
    {
        $message = $this->localeData->getLocaleValue($this, self::KEY_CUSTOMER_MESSAGE);
        return $this->decodeMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerMessage($customerMessage)
    {
        $value = "";
        if ($customerMessage) {
            $value = json_encode($customerMessage);
        }
        $this->localeData->setLocaleValue($this, self::KEY_CUSTOMER_MESSAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminMessage()
    {
        $message = $this->localeData->getLocaleValue($this, self::KEY_ADMIN_MESSAGE);
        return $this->decodeMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminMessage($adminMessage)
    {
        $value = "";
        if ($adminMessage) {
            $value = json_encode($adminMessage);
        }
        $this->localeData->setLocaleValue($this, self::KEY_ADMIN_MESSAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryMessage()
    {
        $message = $this->localeData->getLocaleValue($this, self::KEY_HISTORY_MESSAGE);
        return $this->decodeMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setHistoryMessage($historyMessage)
    {
        $value = "";
        if ($historyMessage) {
            $value = json_encode($historyMessage);
        }
        $this->localeData->setLocaleValue($this, self::KEY_HISTORY_MESSAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Compatibility to old versions
     *
     * @param string $message
     * @return string
     */
    public function decodeMessage($message)
    {
        if ($decoded = json_decode($message, true)) {
            $message = $decoded;
        }
        if (is_array($message)) {
            return $message;
        } else {
            return [$message];
        }
    }
}
