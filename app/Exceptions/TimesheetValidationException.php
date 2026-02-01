<?php

namespace App\Exceptions;

use Exception;

class TimesheetValidationException extends Exception
{
    public const TIMESHEET_NOT_EDITABLE = 'timesheet_not_editable';

    public const INVALID_HOURS = 'invalid_hours';

    public const DAILY_LIMIT_EXCEEDED = 'daily_limit_exceeded';

    public const DATE_OUTSIDE_WEEK = 'date_outside_week';

    public const MISSING_TARGET = 'missing_target';

    protected string $errorCode;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        string $errorCode,
        protected array $context = []
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public static function timesheetNotEditable(): self
    {
        return new self(
            'Timeseddel kan ikke redigeres.',
            self::TIMESHEET_NOT_EDITABLE
        );
    }

    public static function invalidHours(float $hours): self
    {
        return new self(
            'Timer må være mellom 0.5 og 24.',
            self::INVALID_HOURS,
            ['hours' => $hours]
        );
    }

    public static function dailyLimitExceeded(string $date, float $currentTotal, float $newHours, float $limit = 24): self
    {
        return new self(
            sprintf(
                'Kan ikke registrere %.1f timer på %s. Total ville bli %.1f timer, som overstiger maks %d timer per dag.',
                $newHours,
                $date,
                $currentTotal + $newHours,
                (int) $limit
            ),
            self::DAILY_LIMIT_EXCEEDED,
            [
                'date' => $date,
                'current_total' => $currentTotal,
                'new_hours' => $newHours,
                'limit' => $limit,
            ]
        );
    }

    public static function dateOutsideWeek(string $date, string $weekStart, string $weekEnd): self
    {
        return new self(
            sprintf('Dato %s er utenfor timeseddelens uke (%s - %s).', $date, $weekStart, $weekEnd),
            self::DATE_OUTSIDE_WEEK,
            [
                'date' => $date,
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
            ]
        );
    }

    public static function missingTarget(): self
    {
        return new self(
            'En timeregistrering må ha enten prosjekt, arbeidsordre eller beskrivelse.',
            self::MISSING_TARGET
        );
    }
}
