<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Processor;

use DateTime;
use PrototypeIntegration\PrototypeIntegration\Formatter\DateTimeFormatter;

class DateDataProcessor
{
    /**
     * Default date format pattern
     *
     * @var string
     */
    public const DEFAULT_DATE_FORMAT = 'd LLL Y';

    /**
     * Default calendar format setting
     *
     * @var array
     */
    public const DEFAULT_CALENDER_FORMAT = [
        'day' => 'd',
        'dayOfWeek' => 'E',
        'month' => 'LL',
        'monthOfYear' => 'LLL',
        'year' => 'Y',
    ];

    protected DateTimeFormatter $dateTimeFormatter;

    public function __construct(
        DateTimeFormatter $dateTimeFormatter
    ) {
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    public function process(?DateTime $dateTime, array $formatPattern): ?array
    {
        if (is_null($dateTime)) {
            return null;
        }

        return [
            'itemprop' => $dateTime->format('Y-m-d'),
            'dateWithPattern' => $this->dateTimeFormatter->formatWithPattern($dateTime, $formatPattern['dateFormat']),
            'date' => $this->dateTimeFormatter->formatDate($dateTime),
            'calendar' => [
                'day' => $this->dateTimeFormatter->formatWithPattern($dateTime, $formatPattern['calendarFormat']['day']),
                'dayOfWeek' => $this->dateTimeFormatter->formatWithPattern($dateTime, $formatPattern['calendarFormat']['dayOfWeek']),
                'month' => $this->dateTimeFormatter->formatWithPattern($dateTime, $formatPattern['calendarFormat']['month']),
                'monthOfYear' => $this->dateTimeFormatter->formatWithPattern($dateTime, $formatPattern['calendarFormat']['monthOfYear']),
                'year' => $this->dateTimeFormatter->formatWithPattern($dateTime, $formatPattern['calendarFormat']['year']),
            ],
        ];
    }
}
