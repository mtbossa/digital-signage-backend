<?php


namespace App\Enums;

enum RecurrenceCases
{
    case IsoWeekday;
    case IsoWeekdayDay;
    case IsoWeekdayMonth;
    case IsoWeekdayYear;
    case IsoWeekdayDayMonth;
    case IsoWeekdayDayYear;
    case IsoWeekdayDayMonthYear;
    case Day;
    case DayMonth;
    case DayYear;
    case DayMonthYear;
    case Month;
    case MonthYear;
    case Year;
}
