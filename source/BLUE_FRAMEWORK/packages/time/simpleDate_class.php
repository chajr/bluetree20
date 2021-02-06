<?php
/**
 * allow to make simple date manipulations
 *
 * @category    BlueFramework
 * @package     time
 * @subpackage  simpleDate
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.3.1
 *
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
class simpleDate_class
{
    /**
     * return formatted current time
     * 
     * @param integer $stamp optionaly unix timestamp
     * @return string
    */
    static function getFormattedTime($stamp = NULL)
    {
        $cfg = '%H:%M:%S - %d-%m-%Y';

        if (!$stamp) {
            $stamp = time();
        }

        return strftime($cfg, $stamp);
    }

    /**return date formatted in dd-mm-yyyy or in yyyy-mm-dd
     * 
     * @param integer|boolean $stamp optionally unix timestamp
     * @param boolean $yearFirst type of returned date, if TRUE year will be first
     * @return string
     * @example getDate(FALSE, TRUE)
     * @example getDate()
     * @example getDate(2354365346)
     */
    static function getDate($stamp = FALSE, $yearFirst = FALSE)
    {
        if (!$stamp) {
            $stamp = time();
        }

        if ($yearFirst) {
            return strftime('%Y-%m-%d', $stamp);
        }

        return strftime('%d-%m-%Y', $stamp);
    }

    /**
     * return time in hh-mm-ss format
     * 
     * @param integer $stamp optionally unix timestamp
     * @return string
     */
    static function getTime($stamp = NULL)
    {
        if (!$stamp) {
            $stamp = time();
        }

        return strftime('%H:%M:%S', $stamp);
    }

    /**
     * return name of month
     * in example, month length = 2764800s (60*60*24*32)
     * 
     * @param integer|boolean $stamp optionally unix timestamp
     * @param boolean $short if TRUE return name in short version
     * @return string
     * @example monthName() - current month name
     * @example monthName(254543534); - name of month from given timestamp
     * @example monthName('8'); - will return August
     * @example monthName('13'); - return January (> 12 is take as timestamp)
     */
    public static function getMonthName($stamp = FALSE, $short = FALSE)
    {
        if (!$stamp) {
            $stamp = time();
        }

        if ($short) {
            $monthType = '%b';
        } else {
            $monthType = '%B';
        }

        if (is_string($stamp)) {
            switch ($stamp){
                case '1':
                    return strftime($monthType, 1);
                    break;

                case '2':
                    return strftime($monthType, 2764800);
                    break;

                case '3':
                    return strftime($monthType, 5529600);
                    break;

                case '4':
                    return strftime($monthType, 8294400);
                    break;

                case '5':
                    return strftime($monthType, 11059200);
                    break;

                case '6':
                    return strftime($monthType, 13824000);
                    break;

                case '7':
                    return strftime($monthType, 16588800);
                    break;

                case '8':
                    return strftime($monthType, 19353600);
                    break;

                case '9':
                    return strftime($monthType, 22118400);
                    break;

                case '10':
                    return strftime($monthType, 24883200);
                    break;

                case '11':
                    return strftime($monthType, 27648000);
                    break;

                case '12':
                    return strftime($monthType, 30412800);
                    break;

                default:
                    return strftime($monthType, $stamp);
                    break;
            }
        }

        return strftime($monthType, $stamp);
    }

    /**
     * return day name in week
     * in example, day length = 90000s (60*60*22)
     * 
     * @param integer|array|string|boolean $stamp unix timestamp or array (day, month, year) or day number as string
     * @param boolean $short if TRUE will return a short version of day name
     * @return mixed day name, or its number in week, or FALSE if wrong data was given
     * @example getDayName(23424234);
     * @example getDayName(array(12, 12, 1983))
     * @example getDayName(0); - sunday
     * @example getDayName('0', TRUE); - sunday, short version
     */
    static function getDayName($stamp = NULL, $short = NULL)
    {
        if (is_string($stamp)) {
            $tab['wday'] = $stamp;
        } else {

            if (!$stamp ){
                $stamp = time();

            } elseif (is_array ($stamp)) {
                $stamp = mktime(0, 0, 0, $stamp[1], $stamp[0], $stamp[2]);
            }

            $tab = getdate($stamp);
        }

        if ($short) {
            $short = '%a';
        } else {
            $short = '%A';
        }

        switch ($tab['wday']) {
            case '0':
                return strftime($short, 1312077709);
                break;

            case '1':
                return strftime($short, 1312167709);
                break;

            case '2':
                return strftime($short, 1312257709);
                break;

            case '3':
                return strftime($short, 1312347709);
                break;

            case '4':
                return strftime($short, 1312437709);
                break;

            case '5':
                return strftime($short, 1312527709);
                break;

            case '6':
                return strftime($short, 1312617709);
                break;
        }

        return FALSE;
    }

    /**
     * return number of day in year (from 1 to 356)
     * number of month or day in year without 0 at start
     * 
     * @param string|array $data unix timestamp or array (day, month, year)
     * @return integer
     * @example getDayNumber(23424234);
     * @example getDayNumber(array(12, 12, 1983))
     */
    static function getDayNumber($data = NULL)
    {
        if (!$data) {
            $data = time();
        } elseif (is_array ($data)) {
            $data = mktime(0, 0, 0, $data[1], $data[0], $data[2]);
        }

        $tab = getdate($data);
        return $tab['yday'] +1;
    }

    /**
     * return number of days in month
     * 
     * @param string|array|boolean $stamp unix timestamp or array(month, year) if NULL use current month
     * @return integer|boolean
     * @example getDayInMonth()
     * @example getDayInMonth(34234234)
     * @example getDayInMonth(array(12, 1983))
     */
    static function getDayInMonth($stamp = NULL)
    {
        if (is_array ($stamp)) {
            $month  = $stamp[0];
            $year   = $stamp[1];
        } else {
            if (!$stamp) {
                $stamp = time();
            }

            $month  = self::getMonth($stamp);
            $year   = self::getYear($stamp);
        }

        $year = self::isLeapYear($year);

        switch ($month) {
            case '1':
                return 31;
                break;

            case '2':
                if ($year) {
                    return 29;
                } else {
                    return 28;
                }
                break;

            case '3':
                return 31;
                break;

            case '4':
                return 30;
                break;

            case '5':
                return 31;
                break;

            case '6':
                return 30;
                break;

            case '7':
                return 31;
                break;

            case '8':
                return 31;
                break;

            case '9':
                return 30;
                break;

            case '10':
                return 31;
                break;

            case '11':
                return 30;
                break;

            case '12':
                return 31;
                break;

            default:
                return FALSE;
                break;
        }
    }

    /**
     * return array of months with days in year
     * 
     * @param integer|boolean|string $stamp unix timestamp, if NULL current year, if string year
     * @example getMonths(23423423423)
     * @example getMonths('2011')
     * @return array
     */
    static function getMonths($stamp = NULL)
    {
        if (is_string($stamp)) {
            $stamp = mktime(0, 0, 0, '24', '9', $stamp);
        }

        if (!$stamp) {
            $stamp = time();
        }

        $list = array();
        $year = self::getYear($stamp);

        for ($i = 1; $i <= 12; $i++) {
            $list[$i] = self::getDayInMonth(array($i, $year));
        }

        return $list;
    }

    /**
     * check that date is correct
     * 
     * @param integer|array|boolean $stamp unix timestamp, date array or current date
     * @return boolean return TRUE if date is correct
     * @example valid()
     * @example valid(34234234)
     * @example valid(array(12, 12, 1983)) day, month, year
     */
    static function valid($stamp = NULL)
    {
        if (is_array($stamp)) {

            $month  = $stamp[1];
            $year   = $stamp[2];
            $day    = $stamp[0];

        } else {

            if (!$stamp) {
                $stamp = time();
            }

            $month  = self::getMonth($stamp);
            $year   = self::getYear($stamp);
            $day    = self::getDay($stamp);
        }

        return checkdate($month, $day, $year );
    }

    /**
     * check that year is leap-year
     * 
     * @param integer|boolean $rok year to check, or current year
     * @return boolean TRUE if year is an leap-year
     */
    static function isLeapYear($rok = NULL)
    {
        if (!$rok) {
            $rok = self::getYear();
        }

        if ($rok%4 === 0 && $rok%100 !== 0 || $rok%400 === 0){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * return given from unix timestamp or current day or ith name in month
     * 
     * @param integer|boolean $stamp
     * @param boolean $name if TRUE return as name
     * @param boolean $short if TRUE return as short name
     * @return integer|string
     * @example getDay()
     * @example getDay(252453, 0, 1)
     * @example getDay(0, 1)
     * @example getDay(2423424, 1, 1)
     */
    static function getDay($stamp = NULL, $name = FALSE, $short = FALSE)
    {
        if(!$stamp){
            $stamp = time();
        }
        
        if ($name) {

            if ($short) {
                return self::getDayName($stamp, 1);
            }

            return self::getDayName($stamp);

        } else {
            return strftime('%d', $stamp);
        }
    }

    /**
     * current month or given from timestamp
     * 
     * @param integer|boolean $stamp
     * @param boolean $name if TRUE return as name
     * @param boolean $short if TRUE return as short name
     * @return integer|string
     * @example getMonth(3424234, 1)
     * @example getMonth()
     * @example getMonth(234234234, 1, 1) short version
     */
    static function getMonth($stamp = NULL, $name = FALSE, $short = FALSE)
    {
        if (!$stamp) {
            $stamp = time();
        }

        if ($name) {

            if ($short) {
                return self::getMonthName($stamp, 1);
            }

            return self::getMonthName($stamp);

        } else {
            return strftime('%m', $stamp);
        }
    }

    /**
     * current year, or given in timestamp
     * 
     * @param integer $stamp
     * @return integer
     */
    static function getYear($stamp = NULL)
    {
        if (!$stamp) {
            $stamp = time();
        }

        return strftime('%Y', $stamp);
    }

    /**
     * return current hour, or given in timestamp
     * 
     * @param integer $stamp
     * @return integer
     */
    static function getHour($stamp = NULL)
    {
        if (!$stamp) {
            $stamp = time();
        }

        return strftime('%H', $stamp);
    }

    /**
     * return current minute or given in timestamp
     * 
     * @param integer $stamp
     * @return integer
     */
    static function getMinutes($stamp = NULL)
    {
        if (!$stamp) {
            $stamp = time();
        }

        return strftime('%M', $stamp);
    }

    /**
     * return current second or given in timestamp
     * 
     * @param integer $stamp
     * @return integer
     */
    static function getSeconds($stamp = NULL)
    {
        if (!$stamp) {
            $stamp = time();
        }

        return strftime('%S', $stamp);
    }

    /**
     * return current week number or given in timestamp correct with ISO8601:1998
     * 
     * @param integer $stamp
     * @return integer
     */
    static function getWeek($stamp = NULL)
    {
        if (!$stamp) {
            $stamp = time();
        }

        return strftime('%W', $stamp);
    }

    /**
     * convert ISO string to UTF-8
     * default from ISO-8859-2 to UTF-8
     * 
     * @param string $string
     * @param string $from
     * @param string $to
     * @return string
     */
    static function convert($string, $from = 'ISO-8859-2', $to = 'UTF-8')
    {
        return iconv($from, $to, $string);
    }
}
