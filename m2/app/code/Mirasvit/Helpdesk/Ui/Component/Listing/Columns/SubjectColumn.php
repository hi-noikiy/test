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
 * @package   mirasvit/module-helpdesk
 * @version   1.1.59
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Helpdesk\Ui\Component\Listing\Columns;

class SubjectColumn extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var int
     */
    private $len = 50;

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($this->getData('name'), $item);
            }
        }

        return $dataSource;
    }


    /**
     * Format data.
     *
     * @param string $fieldName
     * @param array  $item
     * @return string
     */
    protected function prepareItem($fieldName, array $item)
    {
        $subject = $item[$fieldName];
        if (strlen($subject) > $this->len) {
            $subject = mb_substr($subject, 0, $this->len).'...';
        }

        return $subject;
    }
}
