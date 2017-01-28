<?php
/**
 * Part of the Calendars package.
 * This is a replica of Keith Wood's Calendar plugin (for javascript)
 * http://keith-wood.name/calendars.html
 *
 * @package    Talal424\Calendars
 * @version    1.0.0
 * @author     Talal Alenizi <talal.alenizi@gmail.com> <@talal_alenizi>
 * @license    BSD License (3-clause)
 * @link       https://github.com/talal424/calendars
 */

namespace Talal424\Calendars;

use DateTime;

class GregorianCalendar extends BaseCalendar
{
	/**
     * The calendar name.
     *
     * @var string
     */
	public $name = 'Gregorian';

	/**
     * <code>true</code> if has a year zero, <code>false</code> if not.
     *
     * @var boolean
     */
	public $hasYearZero = false;

	/**
     * The minimum month number.
     *
     * @var int
     */
	public $minMonth = 1;

	/**
     * The first month in the year.
     *
     * @var int
     */
	public $firstMonth = 1;

	/**
     * The minimum day number.
     *
     * @var int
     */
	public $minDay = 1;

	/**
     * Days per month in a common year.
     *
     * @var array
     */
	public $daysPerMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

	/**
     * Julian date of start of Gregorian epoch: 1 January 0001 CE.
     *
     * @var int
     */
	public $jdEpoch = 1721425.5;

	/**
     * Localisations for the plugin.
     *
     * The array is indexed by the language code e.g 'english'
     * Each language code has the following attributes:
     * <ul>
	 * <li> epochs array: The epoch names. </li>
	 * <li> monthNames array: The long names of the months of the year. </li>
	 * <li> monthNamesShort array: The short names of the months of the year. </li>
	 * <li> dayNames array: The long names of the days of the week. </li>
	 * <li> dayNamesShort array: The short names of the days of the week. </li>
	 * <li> localNumbers boolean: true/false subtitue the digits with ones supplied e.g Arabic/Indian digits </li>
	 * <li> digits array: The digits to subtitute </li>
	 * <li> dateFormat string:  The date format for this calendar. </li>
	 * </ul>
     *
     * @var array
     */
	public $regionalOptions = [
		'english' => [
			'epochs' => ['BCE', 'CE'],
			'monthNames' => ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'],
			'monthNamesShort' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
			'dayNames' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
			'dayNamesShort' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
			'digits' => null,
			'localNumbers' => false,
			'dateFormat' => 'mm/dd/yyyy',
		]
	];

	/**
	 * Create a new Calendars instance.
	 *
	 * @param string $language The language code to use for localisation.
	 */
	public function __construct($language = null)
	{
		RegionalOptions::setRegionalOptions($this->regionalOptions,$language,$this);
	}

	/**
	 * Determine whether this date is in a leap year.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @return boolean <code>true</code> if this is a leap year, <code>false</code> if not.
	 * @throws Exception if an invalid year or a different calendar used.
	 */
	public function leapYear($year = null)
	{
		$date = $this->validate($year, $this->minMonth, $this->minDay, 'invalidYear');
		$year = $date->year() + ($date->year() < 0 ? 1 : 0); // No year zero
		return $year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0);
	}

	/**
	 * Determine the week of the year for a date - ISO 8601.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @param int|null $month The month to examine.
	 * @param int|null $day The day to examine.
	 * @return int The week of the year, starting from 1.
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function weekOfYear($year = null, $month = null, $day = null)
	{
		// Find Thursday of this week starting on Monday
		$checkDate = $this->newDate($year, $month, $day);
		$checkDate->add(4 - ($checkDate->dayOfWeek() ?: 7), 'd');
		return floor(($checkDate->dayOfYear() - 1) / 7) + 1;
	}

	/**
	 * Retrieve the number of days in the month for this date.
	 *
	 * @param CDate|int|null $year The date to examine or the year of the month.
	 * @param int|null $month The month.
	 * @return int The number of days in this month.
	 * @throws Exception if an invalid month/year or a different calendar used.
	 */
	public function daysInMonth($year = null, $month = null)
	{
		$date = $this->validate($year, $month, $this->minDay, 'invalidMonth');
		return $this->daysPerMonth[$date->month() - 1] + ($date->month() === 2 && $this->leapYear($date->year()) ? 1 : 0);
	}

	/**
	 * Determine whether this date is a week day.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @param int|null $month The month to examine.
	 * @param int|null $day The day to examine.
	 * @return boolean <code>true</code> if a week day, <code>false</code> if not.
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function weekDay($year = null, $month = null, $day = null)
	{
		return ($this->dayOfWeek($year, $month, $day) ?: 7) < 6;
	}

	/**
	 * Retrieve the Julian date equivalent for this date,
	 * i.e. days since January 1, 4713 BCE Greenwich noon.
	 *
	 * @param CDate|int|null $year The date to convert or the year to convert.
	 * @param int|null $month The month to convert.
	 * @param int|null $day The day to convert.
	 * @return int The equivalent Julian date.
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function toJD($year = null, $month = null, $day = null)
	{
		$date = $this->validate($year, $month, $day, 'invalidDate');
		$year = $date->year();
		$month = $date->month();
		$day = $date->day();
		//return cal_to_jd(CAL_GREGORIAN, $month, $day, $year);
		if ($year < 0) {
			// No year zero
			$year++;
		}
		// Jean Meeus algorithm, "Astronomical Algorithms", 1991
		if ($month < 3) {
			$month += 12;
			$year--;
		}
		$a = floor($year / 100);
		$b = 2 - $a + floor($a / 4);
		return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
	}

	/**
	 * Create a new date from a Julian date.
	 *
	 * @param int $jd The Julian date to convert.
	 * @return CDate The equivalent date.
	 */
	public function fromJD($jd)
	{
		// $date = cal_from_jd($jd, CAL_GREGORIAN);
		// return $this->newDate($date['year'], $date['month'], $date['day']);
		//Jean Meeus algorithm, "Astronomical Algorithms", 1991
		$z = floor($jd + 0.5);
		$a = floor(($z - 1867216.25) / 36524.25);
		$a = $z + 1 + $a - floor($a / 4);
		$b = $a + 1524;
		$c = floor(($b - 122.1) / 365.25);
		$d = floor(365.25 * $c);
		$e = floor(($b - $d) / 30.6001);
		$day = $b - $d - floor($e * 30.6001);
		$month = $e - ($e > 13.5 ? 13 : 1);
		$year = $c - ($month > 2.5 ? 4716 : 4715);
		if ($year <= 0) {
			// No year zero
			$year--;
		}
		return $this->newDate($year, $month, $day);
	}

	/**
	 * Convert this date to a standard (Gregorian) PHP DateTime Date.
	 *
	 * @param CDate|int|null $year The date to convert or the year to convert.
	 * @param int|null $month The month to convert.
	 * @param int|null $day The day to convert.
	 * @return DateTime The equivalent PHP DateTime date
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function toPHPDate($year = null, $month = null, $day = null)
	{
		$date = $this->validate($year, $month, $day, 'invalidDate');
		return new DateTime(implode('-', [$date->day(),$date->month(),$date->year()]));
	}

	/**
	 * Create a new date from a standard (Gregorian) PHP DateTime Date.
	 *
	 * @param DateTime $phpd The PHP DateTime to convert.
	 * @return CDate The equivalent date.
	 */
	public function fromPHPDate(DateTime $phpd)
	{
		return $this->newDate($phpd->format('Y'), $phpd->format('m'), $phpd->format('d'));
	}
}