<?php

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/*
|--------------------------------------------------------------------------
| General Helper Functions
|--------------------------------------------------------------------------
*/

/* ==================== Numbers ==================== */

if (!function_exists('display_number')) {
    /**
     * @param float|int|string $number
     * @param int              $decimal
     * @param string           $decimalPoint
     * @param string           $thousandsSeparator
     *
     * @return string
     */
    function display_number(float|int|string $number, int $decimal = 2, string $decimalPoint = '.', string $thousandsSeparator = ','): string
    {
        return number_format($number, $decimal, $decimalPoint, $thousandsSeparator);
    }
}

if (!function_exists('to_number')) {
    /**
     * @param float|int|string $number
     * @param int              $decimal
     * @param string           $decimalPoint
     * @param string           $thousandsSeparator
     *
     * @return string
     */
    function to_number(float|int|string $number, int $decimal = 2, string $decimalPoint = '.', string $thousandsSeparator = ''): string
    {
        return display_number($number, $decimal, $decimalPoint, $thousandsSeparator);
    }
}

if (!function_exists('human_readable_number')) {
    /**
     * @param float|int|string $number
     * @param int              $decimal
     * @param string           $decimalPoint
     * @param string           $thousandsSeparator
     *
     * @return string
     */
    function human_readable_number(float|int|string $number, int $decimal = 2, string $decimalPoint = '.', string $thousandsSeparator = ','): string
    {
        return display_number($number, $decimal, $decimalPoint, $thousandsSeparator);
    }
}

if (!function_exists('is_set')) {
    /**
     * @param mixed $value
     *
     * @return bool
     */
    function is_set(mixed $value): bool
    {
        return isset($value) && !empty($value);
    }
}

if (!function_exists('is_zero')) {
    /**
     * @param mixed $number
     *
     * @return bool
     */
    function is_zero(mixed $number): bool
    {
        return is_numeric($number) && $number === 0;
    }
}

if (!function_exists('is_negative')) {
    /**
     * @param mixed $number
     *
     * @return bool
     */
    function is_negative(mixed $number): bool
    {
        return is_numeric($number) && $number < 0;
    }
}

if (!function_exists('is_negative_or_zero')) {
    /**
     * @param mixed $number
     *
     * @return bool
     */
    function is_negative_or_zero(mixed $number): bool
    {
        return is_numeric($number) && $number <= 0;
    }
}

if (!function_exists('is_positive')) {
    /**
     * @param mixed $number
     *
     * @return bool
     */
    function is_positive(mixed $number): bool
    {
        return is_numeric($number) && $number > 0;
    }
}

if (!function_exists('is_positive_or_zero')) {
    /**
     * @param mixed $number
     *
     * @return bool
     */
    function is_positive_or_zero(mixed $number): bool
    {
        return is_numeric($number) && $number >= 0;
    }
}

if (!function_exists('calculate_age')) {
    /**
     * @param string|Carbon      $dateOfBirth
     * @param string|Carbon|null $dateTill
     * @param bool               $todayIncluded
     *
     * @return int|null
     */
    function calculate_age(Carbon|string $dateOfBirth, Carbon|string $dateTill = null, bool $todayIncluded = true): int|null
    {
        try {
            $dateTill    = Carbon::parse($dateTill, app_timezone());
            $dateOfBirth = Carbon::parse($dateOfBirth, app_timezone());
            return $todayIncluded
                ? Carbon::parse($dateOfBirth)->diffInYears($dateTill)
                : Carbon::parse($dateOfBirth)->diffInYears($dateTill->subDay());
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('is_age_acceptable')) {
    /**
     * @param string|Carbon      $dateOfBirth 'Y-m-d'
     * @param string|Carbon|null $dateTill    'Y-m-d'
     * @param string             $operator    "<", "lt", "<=", "le", ">", "gt", ">=", "ge", "==", "=", "eq", "!=", "<>", "ne"
     * @param int                $criteria    '16'
     *
     * @return bool|null
     */
    function is_age_acceptable(Carbon|string $dateOfBirth, Carbon|string $dateTill = null, string $operator = '<=', int $criteria = 16): bool|null
    {
        try {
            if (!in_array($operator, ["<", "lt", "<=", "le", ">", "gt", ">=", "ge", "==", "=", "eq", "!=", "<>", "ne"])) throw new InvalidArgumentException('invalid operator symbol provided.');
            $age = calculate_age(
                Carbon::parse($dateOfBirth, app_timezone())->format('Y-m-d'),
                Carbon::parse($dateTill, app_timezone())->format('Y-m-d')
            );
            return version_compare($age, $criteria, $operator);
            //            return match ($operator) {
            //                '<'   => $age < $criteria,
            //                '<='  => $age <= $criteria,
            //                '>'   => $age > $criteria,
            //                '>='  => $age >= $criteria,
            //                '=='  => $age == $criteria,
            //                '===' => $age === $criteria,
            //                '<>'  => $age <> $criteria,
            //                '!='  => $age != $criteria,
            //                '!==' => $age !== $criteria,
            //                '<=>' => $age <=> $criteria,
            //            };
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('number_to_words')) {
    /**
     * @param float|int|string $number
     *
     * @return string
     */
    function number_to_words(float|int|string $number): string
    {
        try {
            $number = str_replace(',', '', $number);

            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $spell     = $formatter->format($number);
            $spell     = strtolower($spell);

            return $spell;
        } catch (Exception) {
            return '';
        }
    }
}

if (!function_exists('get_percentage_of_value')) {
    /**
     * @param float|int|string $current
     * @param float|int|string $total
     *
     * @return float|int|string
     */
    function get_percentage_of_value(float|int|string $current, float|int|string $total): float|int|string
    {
        if (!(is_numeric($current) && is_numeric($total))) {
            return 0;
        }
        if (is_zero($total)) {
            return 0;
        }
        return ($current / $total) * 100;
    }
}

if (!function_exists('get_value_of_percentage')) {
    /**
     * @param float|int|string $percentage
     * @param float|int|string $total
     *
     * @return float|int|string
     */
    function get_value_of_percentage(float|int|string $percentage, float|int|string $total): float|int|string
    {
        if (!(is_numeric($percentage) && is_numeric($total))) {
            return 0;
        }
        if (is_zero($percentage)) {
            return 0;
        }
        return ($percentage / 100) * $total;
    }
}

if (!function_exists('get_total_from_amount_n_percentage')) {
    /**
     * @param $amount
     * @param $percentage
     *
     * @return float|int
     */
    function get_total_from_amount_n_percentage(float|int|string $amount, float|int|string $percentage): float|int|string
    {
        if (is_zero($amount)) return 0;
        if (is_zero($percentage)) return 0;
        return ($amount / $percentage) * 100;
    }
}

if (!function_exists('percentage_difference')) {
    /**
     * if true it will calculate the difference b/w amount1 and amount2 in percentage if false it will tell amount2 is ? % increment/decrement of amount2
     *
     * @param float $amount1    Numeric value 1
     * @param float $amount2    Numeric value 1
     * @param bool  $difference if true it will calculate the difference b/w amount1 and amount2 in percentage if false it will tell amount2 is ? % increment/decrement of amount2
     *
     * @return float|int
     */
    function percentage_difference(float $amount1, float $amount2, bool $difference = true)
    {
        if ($difference) {
            return (abs($amount1 - $amount2) / (($amount1 + $amount2) / 2)) * 100;
        }

        return (abs($amount1 - $amount2) / $amount1) * 100;
    }
}

if (!function_exists('percentage_change')) {
    /**
     * This function will increment/decrement the given amount by given percentage
     *
     * @param float $amount     Numeric value
     * @param float $percentage Percentage value
     * @param bool  $increment  if true it will increment if false it will decrement
     *
     * @return float|int
     */
    function percentage_change(float $amount, float $percentage, bool $increment = true)
    {
        return $increment ? $amount * (1 + ($percentage / 100)) : $amount * (1 - ($percentage / 100));
    }
}


/* ==================== Dates ==================== */

if (!function_exists('now_now')) {
    /**
     * @param string $timezone
     *
     * @return Carbon
     */
    function now_now(string $timezone = 'UTC'): Carbon
    {
        return now(app_timezone($timezone));
    }
}

if (!function_exists('get_date_periods_between')) {
    /**
     * @param Carbon|string      $startDate
     * @param Carbon|string|null $endDate
     * @param string             $format
     *
     * @return array
     */
    function get_date_periods_between($startDate, $endDate = null, string $format = 'd M'): array
    {
        $endDate = $endDate ?? now();

        $periods     = CarbonPeriod::create($startDate, $endDate);
        $datePeriods = [];
        foreach ($periods as $date) $datePeriods[] = $date->format($format);
        return $datePeriods;
    }
}

if (!function_exists('is_datetime_between')) {
    /**
     * @param Carbon|string|null $start
     * @param Carbon|string|null $end
     * @param Carbon|string|null $date
     * @param bool               $includeBorderDates
     * @param bool               $includeTime
     * @param string             $timezone
     *
     * @return bool
     */
    function is_datetime_between($start, $end, $date = null, bool $includeBorderDates = true, bool $includeTime = true, string $timezone = 'UTC'): bool
    {
        if (!isset($start, $end)) return false;

        $timezone = app_timezone($timezone);
        $format   = $includeTime ? 'Y-m-d H:i:s' : 'Y-m-d';

        $date      = Carbon::parse($date ?? now_now($timezone))->format($format);
        $startDate = Carbon::parse($start, $timezone)->format($format);
        $endDate   = Carbon::parse($end, $timezone)->format($format);

        return $includeBorderDates
            ? (new Carbon($date))->betweenIncluded($startDate, $endDate)
            : (new Carbon($date))->betweenExcluded($startDate, $endDate);
    }
}

if (!function_exists('is_date_between')) {
    /**
     * @param Carbon|string $date
     * @param Carbon|string $start
     * @param Carbon|string $end
     * @param string        $timezone
     *
     * @return bool
     */
    function is_date_between($date, $start, $end, string $timezone = 'UTC'): bool
    {
        return is_datetime_between($start, $end, $date, false, false, $timezone);
    }
}

if (!function_exists('is_today_between')) {
    /**
     * @param Carbon|string $start
     * @param Carbon|string $end
     * @param string        $timezone
     *
     * @return bool
     */
    function is_today_between($start, $end, string $timezone = 'UTC'): bool
    {
        return is_date_between(now_now($timezone), $start, $end, $timezone);
    }
}

if (!function_exists('display_datetime')) {
    /**
     * @param Carbon|string|null $dateTime
     * @param string             $format
     * @param string             $timezone
     * @param string             $formatType could be empty string or iso
     * @param bool               $showTodayDefault
     *
     * @return string
     */
    function display_datetime($dateTime = null, string $format = 'l jS M, Y', string $timezone = 'UTC', string $formatType = '', bool $showTodayDefault = true): string
    {
        $timezone = app_timezone($timezone);
        if (!isset($dateTime) && $showTodayDefault) {
            $date = now_now($timezone);
            return strtolower($formatType) === 'iso' ? $date->isoFormat($format) : $date->format($format);
        }
        if (is_numeric($dateTime)) {
            $date = Carbon::createFromTimestamp($dateTime, $timezone);
            return strtolower($formatType) === 'iso' ? $date->isoFormat($format) : $date->format($format);
        }
        if (isset($dateTime)) {
            $date = Carbon::parse($dateTime, $timezone);
            return strtolower($formatType) === 'iso' ? $date->isoFormat($format) : $date->format($format);
        }
        return '';
    }
}

if (!function_exists('diff_for_humans')) {
    /**
     * @param Carbon|string $date
     * @param string        $timezone
     *
     * @return string
     */
    function diff_for_humans($date, string $timezone = 'UTC'): string
    {
        $timezone = app_timezone($timezone);
        return is_numeric($date)
            ? Carbon::createFromTimestamp($date, $timezone)->diffForHumans()
            : Carbon::parse($date, $timezone)->diffForHumans();
    }
}

if (!function_exists('remaining_days_of_month')) {
    /**
     * @param Carbon|string|null $date
     * @param bool               $useGivenDateEndOfMonth
     * @param string             $timezone
     *
     * @return int
     */
    function remaining_days_of_month($date = null, bool $useGivenDateEndOfMonth = false, string $timezone = 'UTC'): int
    {
        try {
            $timezone   = app_timezone($timezone);
            $date       = Carbon::parse($date, $timezone);
            $endOfMonth = $useGivenDateEndOfMonth
                ? Carbon::parse($date, $timezone)->endOfMonth()
                : Carbon::now($timezone)->endOfMonth();

            if ($date->gt($endOfMonth)) return -1;

            return $date->diffInDays($endOfMonth);
        } catch (Exception $exception) {
            return -1;
        }
    }
}

if (!function_exists('days_between_dates')) {
    /**
     * @param Carbon|string      $end
     * @param Carbon|string|null $start
     * @param string             $timezone
     *
     * @return int
     */
    function days_between_dates($end, $start = null, string $timezone = 'UTC'): int
    {
        try {
            $timezone = app_timezone($timezone);
            $end      = Carbon::parse($end, $timezone);
            $start    = isset($start) ? Carbon::parse($start, $timezone) : Carbon::now($timezone);
            if ($start->gt($end)) return -1;
            return $start->diffInDays($end);
        } catch (Exception $exception) {
            return -1;
        }
    }
}

if (!function_exists('remaining_days_till')) {
    /**
     * @param Carbon|string      $end
     * @param Carbon|string|null $start
     * @param string             $timezone
     *
     * @return int
     */
    function remaining_days_till($end, $start = null, string $timezone = 'UTC'): int
    {
        return days_between_dates($end, $start, $timezone);
    }
}

if (!function_exists('days_in_month')) {
    /**
     * @param Carbon|string|null $date
     * @param string             $timezone
     *
     * @return int
     */
    function days_in_month($date = null, string $timezone = 'UTC'): int
    {
        try {
            return Carbon::parse($date ?? now_now(), app_timezone($timezone))->daysInMonth;
        } catch (Exception $exception) {
            return -1;
        }
    }
}

/* ==================== Time ==================== */
/* Todo: handle proper time format */
if (!function_exists('time_format_to_number')) {
    function time_format_to_number($time, $splitter = ':')
    {
        [$hours, $minutes] = explode($splitter, $time);
        return (((int) $hours) * 60) + ((int) $minutes);
    }
}

if (!function_exists('number_to_time_format')) {
    function number_to_time_format($number, $join = ':')
    {
        $hours   = str_pad((int) ($number / 60), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($number % 60, 2, '0', STR_PAD_LEFT);
        return "$hours$join$minutes";
    }
}


/* ==================== String/Sanitize ==================== */

if (!function_exists('remove_script_tag')) {
    /**
     * @param string $string
     *
     * @return string
     */
    function remove_script_tag(string $string): string
    {
        return trim(preg_replace('/<script\b[^>]*>(.*?)<\/script>/m', "", $string));
    }
}

if (!function_exists('remove_invalid_html_tags')) {
    /**
     * @param string $string
     *
     * @return string
     */
    function remove_invalid_html_tags(string $string): string
    {
        return trim(preg_replace('/<(p|div|span|small|td|tr|h1|h2|h3|h4|h5|h6)[^>]*><\/(p|div|span|small|td|tr|h1|h2|h3|h4|h5|h6)[^>]*>/mis', "", $string));
    }
}

if (!function_exists('remove_script_tag_from_string')) {
    /**
     * @param string $string
     * @param bool   $clearEmptyTag
     *
     * @return string
     */
    function remove_script_tag_from_string(string $string, bool $clearEmptyTag = true): string
    {
        $string = remove_script_tag($string);
        if ($clearEmptyTag) $string = remove_invalid_html_tags($string);
        return $string;
    }
}

if (!function_exists('sanitize_text_editor_text')) {
    /**
     * @param string $text
     *
     * @return string
     */
    function sanitize_text_editor_text(string $text): string
    {
        try {
            $text = remove_script_tag($text);
            $text = remove_invalid_html_tags($text);
            $text = sanitize_text_editor_search_and_replace($text);
        } catch (Exception $exception) {
        }

        return $text;
    }
}

if (!function_exists('sanitize_text_editor_search_and_replace')) {
    /**
     * @param string $text
     * @param int    $offset
     *
     * @return string
     */
    function sanitize_text_editor_search_and_replace(string $text, int $offset = 0): string
    {
        try {
            $searchStart       = 'style="';
            $searchEnd         = '">';
            $searchStartLength = strlen($searchStart);

            if ($positionStart = strpos($text, $searchStart, $offset)) {
                $positionEnd       = strpos($text, $searchEnd, $positionStart);
                $substring         = substr($text, ($positionStart + $searchStartLength), ($positionEnd - ($positionStart + $searchStartLength)));
                $substringSanitize = str_replace('"', "'", $substring);
                $text              = str_replace($substring, $substringSanitize, $text);

                $positionStart = $positionStart + 1;
                if (strpos($text, $searchStart, $positionStart)) {
                    $text = sanitize_text_editor_search_and_replace($text, $positionStart);
                }
            }
        } catch (Exception $exception) {
        }

        return $text;
    }
}

if (!function_exists('telegram_string_sanitizer')) {
    /**
     * @param string $string
     *
     * @return string
     */
    function telegram_string_sanitizer(string $string): string
    {
        $string = preg_replace('/[_*]/', ' ', $string);
        $string = trim($string);

        return $string;
    }
}

if (!function_exists('secret_value')) {
    /**
     * @param string $string
     * @param array  $display
     * @param bool   $displayBetween
     * @param string $char
     *
     * @return string
     */
    function secret_value(string $string, array $display = [4, -4], bool $displayBetween = false, string $char = '*'): string
    {
        $length = strlen($string);

        $display    = is_array($display) ? $display : [4, -4];
        $display[0] = $display[0] ?? 4;
        $display[1] = $display[1] ?? -4;
        $display[1] = is_negative($display[1]) ? $display[1] : -1 * $display[1];

        $display[1] = is_zero($display[1]) ? -$length : $display[1];

        if ($displayBetween) {
            $mask_number = str_repeat($char, abs($display[0])) . substr($string, abs($display[0]), $display[1]) . str_repeat($char, abs($display[1]));
        } else {
            $lengthHidden = $length - (abs($display[0]) + abs($display[1]));
            $mask_number  = substr($string, 0, abs($display[0])) . str_repeat($char, $lengthHidden) . substr($string, abs($display[0]) + $lengthHidden);
        }

        return $mask_number;
    }
}

if (!function_exists('words_fc')) {
    /**
     * @param string $string
     * @param string $delimiter
     * @param bool   $uppercase
     * @param int    $limit
     *
     * @return string
     */
    function words_fc(string $string, string $delimiter = ' ', bool $uppercase = true, int $limit = 0): string
    {
        $words = explode($delimiter, trim($string));

        $limit = $limit > 0 ? $limit : count($words);
        $limit = count($words) < $limit ? count($words) : $limit;

        $acronym = '';
        for ($i = 0; $i < $limit; $i++) {
            $acronym .= $words[$i][0];
        }

        return $uppercase ? strtoupper($acronym) : strtolower($acronym);
    }
}

if (!function_exists('take_words')) {
    /**
     * @param string $string
     * @param int    $count
     * @param string $end
     *
     * @return string
     */
    function take_words(string $string, int $count = 2, string $end = '...'): string
    {
        return Str::words($string, $count, $end); // str($string)->words($count, '')->trim();
    }
}

if (!function_exists('camel_case')) {
    /**
     * @param string $string
     * @param bool   $capitalizeFirstCharacter
     *
     * @return array|string|string[]
     */
    function camel_case(string $string, bool $capitalizeFirstCharacter = false)
    {

        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        if (!$capitalizeFirstCharacter) {
            $string[0] = strtolower($string[0]);
        }

        return $string;
    }
}

if (!function_exists('snake_case')) {
    /**
     * @param string $string
     *
     * @return string
     */
    function snake_case(string $string): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}

if (!function_exists('pascal_case')) {
    /**
     * @param string $string
     *
     * @return string
     */
    function pascal_case(string $string): string
    {
        return implode('.', array_map('ucwords', explode('.', Str::studly($string))));
    }
}


/* ==================== Google ==================== */

if (!function_exists('get_lat_lng_from_address')) {
    /**
     * @param string $address
     *
     * @return array|null
     */
    function get_lat_lng_from_address(string $address, $apiKey = null): ?array
    {
        $apiKey = $apiKey ?? config('services.google.map.api_key');

        try {
            $address = str_replace(" ", "+", $address);

            $json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}");
            $json = json_decode($json);

            if (isset($json->{'results'}) && count($json->{'results'}) > 0) {
                $lat  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                return ['lat' => $lat, 'lng' => $long];
            }

            return null;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('lat_long_dist_of_two_points')) {
    /**
     * @param $latitudeFrom
     * @param $longitudeFrom
     * @param $latitudeTo
     * @param $longitudeTo
     * @param $earthRadius
     *
     * @return float|int
     */
    function lat_long_dist_of_two_points($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 3959)
    {
        if (($latitudeFrom == $latitudeTo) && ($longitudeFrom == $longitudeTo)) {
            return 0;
        }

        /* -------------------- Method 1 -------------------- */
        //        $theta = $longitudeFrom - $longitudeTo;
        //        $dist  = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        //        $dist  = acos($dist);
        //        $dist  = rad2deg($dist);
        //        $miles = $dist * 60 * 1.1515;
        //        $unit  = strtoupper($unit);
        //
        //        if ($unit == "K") {
        //            return ($miles * 1.609344);
        //        } else if ($unit == "N") {
        //            return ($miles * 0.8684);
        //        } else {
        //            return $miles;
        //        }

        /* -------------------- Method 2 -------------------- */
        // 3959 = result in miles, 6371000 = result in meters
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo   = deg2rad($latitudeTo);
        $lonTo   = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        // $a        = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $a = ((cos($latTo) * sin($lonDelta)) ** 2) + ((cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta)) ** 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;

        /* -------------------- Method 3 -------------------- */
        //        $pi = pi();
        //        $x  = sin($latitudeFrom * $pi / 180) *
        //            sin($latitudeTo * $pi / 180) +
        //            cos($latitudeFrom * $pi / 180) *
        //            cos($latitudeTo * $pi / 180) *
        //            cos(($longitudeTo * $pi / 180) - ($longitudeFrom * $pi / 180));
        //        $x  = atan((sqrt(1 - pow($x, 2))) / $x);
        //        return abs((1.852 * 60.0 * (($x / $pi) * 180)) / 1.609344);
    }
}


/* ==================== Arrays ==================== */

if (!function_exists('arrayify')) {
    function arrayify($value, $separator = ',', $default = [], $filter = true)
    {
        $result = match (true) {
            is_array($value)                      => $value,
            blank($value)                         => arrayify($default, $separator, [], $filter),
            is_string($value), is_numeric($value) => array_map('trim', explode($separator, $value)),
            default                               => [$value],
        };

        //        if (is_null($value)) $result = arrayify($default, $separator, [], $filter);
        //        elseif (is_string($value)) $result = array_map('trim', explode($separator, $value));
        //        else $result = $value;

        return $filter ? array_filter($result) : $result;
    }
}

if (!function_exists('nested_array_filter')) {
    /**
     * @param array $array
     *
     * @return array|false
     */
    function nested_array_filter(array $array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) $value = nested_array_filter($value);
        }
        return array_filter($array);
    }
}

if (!function_exists('replace_array_keys')) {
    /**
     * @param array $array
     *
     * @return array|false
     */
    function replace_array_keys(array $array)
    {
        $replaced_keys = str_replace('_', '-', array_keys($array));
        return array_combine($replaced_keys, $array);
    }
}

if (!function_exists('array_keys_to_snake_case')) {
    /**
     * @param array $array
     *
     * @return array
     */
    function array_keys_to_snake_case(array $array): array
    {
        $snakeCaseArray = [];
        foreach ($array as $key => $item) {
            if ($item instanceof JsonResource) {
                $item = $item->toArray(request());
            }

            if (is_array($item)) $snakeCaseArray[str_replace('__', '_', str_replace('._', '.', Str::snake(str_replace(' ', '_', $key))))] = array_keys_to_snake_case($item);
            else $snakeCaseArray[str_replace('__', '_', Str::snake(str_replace(' ', '_', $key)))] = $item;
        }
        return $snakeCaseArray;
    }
}

if (!function_exists('array_search_recursive')) {
    /**
     * @param array  $haystack
     * @param string $needle
     *
     * @return mixed|null
     */
    function array_search_recursive(array $haystack, string $needle)
    {
        $iterator  = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($recursive as $key => $value) {
            if ($value === $needle) {
                return $key;
            }
        }

        return null;
    }
}

if (!function_exists('array_search_item')) {
    /**
     * @param array  $haystack
     * @param string $needle
     *
     * @return int|string|null
     */
    function array_search_item(array $haystack, string $needle)
    {
        $iterator = new RecursiveArrayIterator($haystack);
        foreach ($iterator as $key => $value) {
            if (in_array($needle, $value)) {
                return $key;
            }
        }

        return null;
    }
}

if (!function_exists('array_flatten')) {
    /**
     * @param array $array
     *
     * @return array|false
     */
    function array_flatten(array $array)
    {
        if (!is_array($array)) return false;

        $result = [];
        foreach ($array as $key => $value) {
            //             if (is_array($value)) $result = array_merge($result, array_flatten($value));
            if (is_array($value)) $result = [...$result, ...array_flatten($value)];
            else $result[] = $value;
        }

        return $result;
    }
}

if (!function_exists('set_nested_array_value')) {
    /**
     * Sets a value in a nested array based on path
     * See https://stackoverflow.com/a/9628276/419887
     *
     * @param array  $array     The array to modify
     * @param string $path      The path in the array
     * @param mixed  $value     The value to set
     * @param string $delimiter The separator for the path
     *
     * @return string The previous value
     */
    function set_nested_array_value(array &$array, string $path, &$value, string $delimiter = '/')
    {
        //    $temp = &$array;
        //    foreach(explode($delimiter, $path) as $key) {
        //        $temp = &$temp[$key];
        //    }
        //    $temp = $value;
        //    unset($temp);
        $pathParts = explode($delimiter, $path);

        $current = &$array;
        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }

        $backup  = $current;
        $current = $value;

        return $backup;
    }
}


/* ==================== Symbols/Icons ==================== */

if (!function_exists('html_symbols')) {
    /**
     * @param string|null $name
     *
     * @return mixed|string|string[]
     */
    function html_symbols(?string $name = null)
    {
        try {
            $symbols = [
                'arrow_top'          => '↑',
                'arrow_left'         => '←',
                'arrow_right'        => '→',
                'arrow_bottom'       => '↓',
                'arrow_top_left'     => '↖',
                'arrow_top_right'    => '↗',
                'arrow_bottom_left'  => '↙',
                'arrow_bottom_right' => '↘',
                'copyright'          => '©',
                'registered'         => '®',
                'trademark'          => '™',
                '@'                  => '@',
                'at'                 => '@',
                '&'                  => '&',
                'ampersand'          => '&',
                'check'              => '✓',
                'celsius'            => '℃',
                'fahrenheit'         => '℉',
                'dollar'             => '$',
                'cent'               => '¢',
                'pound'              => '£',
                'euro'               => '€',
                'yen'                => '¥',
                'indian'             => '₹',
                'ruble'              => '₽',
                'yuan'               => '元',
                '+'                  => '+',
                'plus'               => '+',
                'add'                => '+',
                '-'                  => '−',
                'minus'              => '−',
                'subtract'           => '−',
                'dash'               => '−',
                'en'                 => '−',
                '*'                  => '×',
                'asterisk'           => '×',
                'multiply'           => '×',
                '/'                  => '/',
                'division'           => '/',
                'divide'             => '/',
                'forward_slash'      => '/',
                '='                  => '=',
                'equal'              => '=',
                '!='                 => '≠',
                'notequal'           => '≠',
                '<'                  => '<>',
                'lessthan'           => '<>',
                '>'                  => '>',
                'greaterthan'        => '>',
                '!'                  => '!',
                'exclamation'        => '!',
                '?'                  => '?',
                'question'           => '?',
                '--'                 => '—',
                'em'                 => '—',
                'doubledash'         => '—',
                'singleleft'         => '‹',
                'singleright'        => '›',
                'doubleleft'         => '«',
                'doubleright'        => '»',
            ];
            return isset($name) ? $symbols[$name] : $symbols;
        } catch (Exception $exception) {
            return '';
        }
    }
}

if (!function_exists('html_symbol_codes')) {
    /**
     * @param string|null $name
     *
     * @return mixed|string|string[]
     */
    function html_symbol_codes(?string $name = null)
    {
        try {
            $codes = [
                'arrow_top'          => '&#8593;',
                'arrow_left'         => '&#8592;',
                'arrow_right'        => '&#8594;',
                'arrow_bottom'       => '&#8595;',
                'arrow_top_left'     => '&#8598;',
                'arrow_top_right'    => '&#8599;',
                'arrow_bottom_left'  => '&#8601;',
                'arrow_bottom_right' => '&#8600;',
                'copyright'          => '&#169;',
                'registered'         => '&#174;',
                'trademark'          => '&#8482;',
                '@'                  => '&#64;',
                'at'                 => '&#64;',
                '&'                  => '&#38;',
                'ampersand'          => '&#38;',
                'check'              => '&#10003;',
                'celsius'            => '&#8451;',
                'fahrenheit'         => '&#8457;',
                'dollar'             => '&#36;',
                'cent'               => '&#162;',
                'pound'              => '&#163;',
                'euro'               => '&#8364;',
                'yen'                => '&#165;',
                'indian'             => '&#8377;',
                'ruble'              => '&#8381;',
                'yuan'               => '&#20803;',
                '+'                  => '&#43;',
                'plus'               => '&#43;',
                'add'                => '&#43;',
                '-'                  => '&#8722;',
                'minus'              => '&#8722;',
                'subtract'           => '&#8722;',
                'dash'               => '&#8722;',
                'en'                 => '&#8722;',
                '*'                  => '&#215;',
                'asterisk'           => '&#215;',
                'multiply'           => '&#215;',
                '/'                  => '&#247;',
                'division'           => '&#247;',
                'divide'             => '&#247;',
                'forward_slash'      => '&#247;',
                '='                  => '&#61;',
                'equal'              => '&#61;',
                '!='                 => '&#8800;',
                'notequal'           => '&#8800;',
                '<'                  => '&#60;',
                'lessthan'           => '&#60;',
                '>'                  => '&#62;',
                'greaterthan'        => '&#62;',
                '!'                  => '&#33;',
                'exclamation'        => '&#33;',
                '?'                  => '&#63;',
                'question'           => '&#63;',
                '--'                 => '&#8212;',
                'em'                 => '&#8212;',
                'doubledash'         => '&#8212;',
                'singleleft'         => '&#8249;',
                'singleright'        => '&#8250;',
                'doubleleft'         => '&#171;',
                'doubleright'        => '&#187;',
            ];

            return isset($name) ? $codes[$name] : $codes;
        } catch (Exception $exception) {
            return '';
        }
    }
}


/* ==================== Json/Xml ==================== */

if (!function_exists('json_to_xml')) {
    /**
     * @param string      $json
     * @param bool        $useFirstKeyAsRootTag
     * @param string|null $path
     *
     * @return bool|string|null
     */
    function json_to_xml(string $json, bool $useFirstKeyAsRootTag = false, ?string $path = null)
    {
        try {
            $array = json_decode($json, true);
            return array_to_xml($array, $useFirstKeyAsRootTag, $path);
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('array_to_xml')) {
    /**
     * @param array       $array
     * @param bool        $useFirstKeyAsRootTag
     * @param string|null $path
     *
     * @return bool|string|null
     */
    function array_to_xml(array $array, bool $useFirstKeyAsRootTag = false, ?string $path = null)
    {
        try {
            $root  = $useFirstKeyAsRootTag ? array_key_first($array) : 'root';
            $array = $useFirstKeyAsRootTag ? $array[$root] : $array;

            $simpleXmlElement = new SimpleXMLElement(sprintf("<?xml version=\"1.0\"?><%s></%s>", $root, $root));
            array_to_xml_conversion_script($array, $simpleXmlElement);
            return isset($path) ? $simpleXmlElement->asXML($path) : $simpleXmlElement->asXML();
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('xml_to_array')) {
    /**
     * @param             $xml
     * @param string|null $wrap
     *
     * @return array|mixed|null
     */
    function xml_to_array($xml, ?string $wrap = null)
    {
        try {
            $xml         = simplexml_load_string($xml);
            $jsonConvert = json_encode($xml);
            $jsonConvert = json_decode($jsonConvert, true);
            if (isset($wrap)) $finalJson[$wrap] = $jsonConvert;
            else $finalJson = $jsonConvert;
            return $finalJson;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('xml_to_json')) {
    /**
     * @param             $xml
     * @param string|null $wrap
     *
     * @return false|string|null
     */
    function xml_to_json($xml, ?string $wrap = null)
    {
        try {
            return json_encode(xml_to_array($xml, $wrap));
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('array_to_xml_conversion_script')) {
    /**
     * @param array $array
     * @param       $simpleXmlElement
     *
     * @return null
     */
    function array_to_xml_conversion_script(array $array, &$simpleXmlElement)
    {
        try {
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $simpleXmlElement->addChild("$key", "$value");
                    continue;
                }

                if (is_numeric($key)) {
                    array_to_xml_conversion_script($value, $simpleXmlElement);
                    continue;
                }

                $isAssoc = Arr::isAssoc($value);
                if ($isAssoc) {
                    $subnode = $simpleXmlElement->addChild("$key");
                    array_to_xml_conversion_script($value, $subnode);
                    continue;
                }

                $jump = false;
                foreach ($value as $k => $v) {
                    $key = is_numeric($k) ? $key : $k;
                    if (is_array($v)) {
                        $subnode = $simpleXmlElement->addChild("$key");
                        array_to_xml_conversion_script($v, $subnode);
                        $jump = true;
                    }
                }

                if ($jump) continue;
                array_to_xml_conversion_script($value, $subnode);
            }
            return null;
        } catch (Exception $exception) {
            return null;
        }
    }
}


/* ==================== Exception ==================== */

if (!function_exists('exception_response')) {
    /**
     * @param $exception
     *
     * @return array|Exception|mixed
     */
    function exception_response($exception)
    {
        try {
            if ($exception instanceof Exception) {
                $exception = [
                    'message' => $exception->getMessage(),
                    'file'    => $exception->getFile() . ' : ' . $exception->getLine(),
                    'code'    => $exception->getCode(),
                ];
                return $exception;
            }
            return $exception;
        } catch (Exception $exception) {
            return $exception;
        }
    }
}


/* ==================== Pagination ==================== */

if (!function_exists('pagination_stats')) {
    /**
     * @param $paginationCollection
     * @param $perPage
     *
     * @return array{firstPage: int, lastPage: mixed, currentPage: mixed, perPage: mixed, total: mixed, url_page: mixed, start: float|int|mixed, end: float|int|mixed}
     */
    function pagination_stats($paginationCollection, $perPage = null): array
    {

        $total       = $paginationCollection->total();
        $lastPage    = $paginationCollection->lastPage();
        $perPage     = $perPage ?? $paginationCollection->perPage();
        $currentPage = $paginationCollection->currentPage();

        $page  = $currentPage;
        $start = $page == 1 ? $page : ((($page - 1) * $perPage) + 1);
        $start = is_zero($total) ? $total : ($start > $total ? 0 : $start);
        $end   = is_zero($start) ? $start : ($total < $perPage ? $total : (min($total, ($page * $perPage))));
        // $total < $perPage ? $total : ($total < ($page * $perPage) ? $total : ($page * $perPage))

        //        $page = !isset(request()->page) ? 1 : (request()->page < 1 ? 1 : request()->page);
        //        $start = $page == 1 ? 1 : ((($page - 1) * $perPage) + 1);
        //        $start = is_zero($total) ? 0 : ( $start > $total ? 0 : $start );
        //        $end = $total < $perPage ? ( $total < ($page * $perPage) ? 0 : $total) : ($total < ($page * $perPage) ? $total : ($page * $perPage));

        return [
            'firstPage'   => 1,
            'lastPage'    => $lastPage,
            'currentPage' => $currentPage,
            'perPage'     => $perPage,
            'total'       => $total,
            'url_page'    => request()->page,
            'start'       => $start,
            'end'         => $end,
        ];
    }
}

if (!function_exists('length_aware_paginator')) {
    /**
     * @param $items
     * @param $perPage
     * @param $page
     * @param $options
     *
     * @return LengthAwarePaginator
     */
    function length_aware_paginator($items, $perPage = 15, $page = null, $options = []): LengthAwarePaginator
    {
        $page  ??= (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

if (!function_exists('simple_pagination')) {
    /**
     * @param $items
     * @param $total
     * @param $page
     * @param $perPage
     *
     * @return array{url: string, items: int, total: int|mixed, per_page: int|mixed, current_page: mixed, first_page: int, previous_page: int|mixed|null, next_page: int|mixed|null, last_page: mixed}
     */
    function simple_pagination($items, $total = 0, $page = null, $perPage = 15): array
    {
        $page     = max($page, 1);
        $lastPage = max((int) ceil($total / $perPage), 1);

        return [
            'url'           => url()->current(),
            'items'         => count($items),
            'total'         => $total,
            'per_page'      => $perPage,
            'current_page'  => $page,
            'first_page'    => 1,
            'previous_page' => ($page - 1) ?: null,
            'next_page'     => $page >= $lastPage ? null : $page + 1,
            'last_page'     => $lastPage,
        ];
    }
}

/* ==================== Other ==================== */

if (!function_exists('days_list')) {
    /**
     * @return array{monday: string, tuesday: string, wednesday: string, thursday: string, friday: string, saturday: string, sunday: string}
     */
    function days_list(): array
    {
        return [
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
        ];
    }
}

if (!function_exists('months_list')) {
    /**
     * @return string[]
     */
    function months_list(): array
    {
        return [
            'jan' => 'January',
            'feb' => 'February',
            'mar' => 'March',
            'apr' => 'April',
            'may' => 'May',
            'jun' => 'June',
            'jul' => 'July',
            'aug' => 'August',
            'sep' => 'September',
            'oct' => 'October',
            'nov' => 'November',
            'dec' => 'December',
        ];
    }
}

if (!function_exists('is_leap_year')) {
    function is_leap_year($year = null): bool
    {
        $year = match ($year) {
            is_int($year)           => $year,
            is_null($year)          => now_now()->format('Y'),
            is_string($year)        => $year,
            $year instanceof Carbon => $year->format('Y'),
            default                 => throw new \Exception('Unexpected year value provided'),
        };

        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }
}

if (!function_exists('random_color_hex_part')) {
    /**
     * @return string
     */
    function random_color_hex_part(): string
    {
        return str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generate_random_color_hex')) {
    /**
     * @return string
     */
    function generate_random_color_hex(): string
    {
        return '#' . random_color_hex_part() . random_color_hex_part() . random_color_hex_part();
    }
}

if (!function_exists('generate_git_branch')) {
    /**
     * @param string $type Type Could be [ Fix | Imp | Debug | Func | HotFix | etc. ]
     * @param string $name
     *
     * @return string
     */
    function generate_git_branch(string $type, string $name): string
    {
        /* Todo: Dont know what was i thinking ... will see */
        return '';
    }
}






