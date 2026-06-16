<?php

namespace App\Support;

class InertiaData
{
    public static function options(): array
    {
        return [
            'currencies' => Options::CURRENCIES,
            'clientStatuses' => Options::CLIENT_STATUSES,
            'projectStatuses' => Options::PROJECT_STATUSES,
            'projectBillingTypes' => Options::PROJECT_BILLING_TYPES,
            'taskStatuses' => Options::TASK_STATUSES,
            'taskPriorities' => Options::TASK_PRIORITIES,
            'taskBillingTypes' => Options::TASK_BILLING_TYPES,
            'paymentStatuses' => Options::PAYMENT_STATUSES,
            'attachmentCategories' => Options::ATTACHMENT_CATEGORIES,
            'months' => [
                1 => 'Januar', 2 => 'Februar', 3 => 'Mart', 4 => 'April',
                5 => 'Maj', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
                9 => 'Septembar', 10 => 'Oktobar', 11 => 'Novembar', 12 => 'Decembar',
            ],
        ];
    }

    public static function settings(): array
    {
        return [
            'defaultHourlyRate' => (float) AppSettings::defaultHourlyRate(),
            'defaultCurrency' => AppSettings::defaultCurrency(),
            'allowedFileTypes' => AppSettings::allowedFileTypes(),
        ];
    }
}
