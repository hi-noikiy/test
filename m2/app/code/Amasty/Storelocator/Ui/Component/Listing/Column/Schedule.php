<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Helper\Data as locatorHelper;
use Amasty\Storelocator\Ui\DataProvider\Form\ScheduleDataProvider;

class Schedule extends Column
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var locatorHelper
     */
    private $helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Serializer $serializer,
        locatorHelper $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $scheduleString = '';
                $scheduleArray = $this->serializer->unserialize($item['schedule']);
                if (is_array($scheduleArray)) {
                    foreach ($this->helper->getDaysNames() as $dayKey => $day) {
                        $scheduleString .= $day->getText() . ':<br />' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::OPEN_TIME][ScheduleDataProvider::HOURS] . ':' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::OPEN_TIME][ScheduleDataProvider::MINUTES] . ' - ' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::START_BREAK_TIME][ScheduleDataProvider::HOURS] . ':' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::START_BREAK_TIME][ScheduleDataProvider::MINUTES] . '<br />' .

                            $scheduleArray[$dayKey][ScheduleDataProvider::END_BREAK_TIME][ScheduleDataProvider::HOURS] . ':' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::END_BREAK_TIME][ScheduleDataProvider::MINUTES] . ' - ' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::CLOSE_TIME][ScheduleDataProvider::HOURS] . ':' .
                            $scheduleArray[$dayKey][ScheduleDataProvider::CLOSE_TIME][ScheduleDataProvider::MINUTES] . '<br />';
                    }
                    $item['schedule'] = $scheduleString;
                }
            }
        }

        return $dataSource;
    }
}
