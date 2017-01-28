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

use DateTime, ReflectionObject;

abstract class BaseCalendar
{
	/**
     * "Stack" to turn validation on/off.
     *
     * @var int
     */
	public $validateLevel = 0;

	/**
     * UNIX timestamp epoch
     *
     * @var int
     */
	protected $UNIX_EPOCH = 2440587.5;

	/**
     * The number of seconds per day
     *
     * @var int
     */
	protected $SECS_PER_DAY = 86400;

	/**
     * 1 January 0001 CE
     *
     * @var int
     */
	protected $TICKS_EPOCH = 1721425.5;

	/**
     * The number of ticks per day
     *
     * @var int
     */
	protected $TICKS_PER_DAY = 864000000000;
	
	/**
	 * Create a new date within this calendar.
	 * you can specify date or today if no date is given.
	 *
	 * @param CDate|int|null $year The date to copy or the year for the date.
	 * @param int|null $month The month for the date.
	 * @param int|null $day The day for the date.
	 * @return CDate The new date.
	 * @throws Exception if not a valid date or a different calendar used.
	 */
	public function newDate($year = null, $month = null, $day = null)
	{
		if (is_null($year)) {
			return $this->today();
		}
		if ($year instanceof CDate) {
			$this->validate($year, $month, $day, 'invalidDate');
			$day = $year->day();
			$month = $year->month();
			$year = $year->year();
		}
		return new CDate($this, $year, $month, $day);
	}

	/**
	 * Create a new date for today.
	 *
	 * @return CDate Today's date.
	 */
	public function today()
	{
		return $this->fromPHPDate(new DateTime);
	}

	/**
	 * Retrieve the epoch designator for this date, e.g. BCE or CE.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @return string The current epoch
	 * @throws Exception if an invalid year or a different calendar used.
	 */
	public function epoch($year = null)
	{
		$date = $this->validate($year, $this->minMonth, $this->minDay, 'invalidYear');
		$epochs = $this->local('epochs');
		return ($date->year() < 0 ? $epochs[0] : $epochs[1]);
	}

	/**
	 * Format the year, if not a simple sequential number
	 *
	 * @param CDate|int|null $year The date to format or the year to format.
	 * @return string The formatted year.
	 * @throws Exception if an invalid year or a different calendar used.
	 */
	public function formatYear($year = null)
	{
		$date = $this->validate($year, $this->minMonth, $this->minDay, 'invalidYear');
		return ($date->year() < 0 ? '-' : '') + $this->pad(abs($date->year()),4);
	}

	/**
	 * Retrieve the number of months in a year.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @return int The number of months.
	 * @throws Exception if an invalid year or a different calendar used.
	 */
	public function monthsInYear($year = null)
	{
		$this->validate($year, $this->minMonth, $this->minDay, 'invalidYear');
		return 12;
	}

	/**
	 * Calculate the month's ordinal position within the year -
	 * for those calendars that don't start at month 1!
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @param int|null $month The month to examine.
	 * @return int The ordinal position, starting from <code>minMonth</code>.
	 * @throws Exception if an invalid year/month or a different calendar used.
	 */
	public function monthOfYear($year = null, $month = null)
	{
		$date = $this->validate($year, $month, $this->minDay, 'invalidMonth');
		return ($date->month() + $this->monthsInYear($date) - $this->firstMonth) % $this->monthsInYear($date) + $this->minMonth;
	}

	/**
	 * Calculate actual month from ordinal position, starting from minMonth.
	 *
	 * @param int|null $year The year to examine.
	 * @param int|null $ord The month's ordinal position.
	 * @return int The month's number.
	 * @throws Exception if an invalid year/month.
	 */
	public function fromMonthOfYear($year = null, $ord = null)
	{
		$month = ($ord + $this->firstMonth - 2 * $this->minMonth) % $this->monthsInYear($year) + $this->minMonth;
		$this->validate($year, $month, $this->minDay, 'invalidMonth');
		return $month;
	}

	/**
	 * Retrieve the number of days in a year.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @return int The number of days.
	 * @throws Exception if an invalid year or a different calendar used.
	 */
	public function daysInYear($year = null)
	{
		$date = $this->validate($year, $this->minMonth, $this->minDay, 'invalidYear');
		return ($this->leapYear($date) ? 366 : 365);
	}

	/**
	 * Retrieve the day of the year for a date.
	 *
	 * @param CDate|int|null $year The date to convert or the year to convert.
	 * @param int|null $month The month to convert.
	 * @param int|null $day The day to convert.
	 * @return int The day of the year.
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function dayOfYear($year = null, $month = null, $day = null)
	{
		$date = $this->validate($year, $month, $day, 'invalidDate');
		return $date->toJD() - $this->newDate($date->year(), $this->fromMonthOfYear($date->year(), $this->minMonth), $this->minDay)->toJD() + 1;
	}

	/**
	 * Retrieve the number of days in a week.
	 *
	 * @return int The number of days.
	 */
	public function daysInWeek()
	{
		return 7;
	}

	/**
	 * Retrieve the day of the week for a date.
	 *
	 * @param CDate|int|null $year The date to examine or the year to examine.
	 * @param int|null $month The month to examine.
	 * @param int|null $day The day to examine.
	 * @return int The day of the week: 0 to number of days - 1.
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function dayOfWeek($year = null, $month = null, $day = null)
	{
		$date = $this->validate($year, $month, $day, 'invalidDate');
		return (floor($this->toJD($date)) + 2) % $this->daysInWeek();
	}

	/**
	 * Add period(s) to a date. Cater for no year zero.
	 *
	 * @param CDate $date The starting date.
	 * @param int|null $offset The number of periods to adjust by.
	 * @param string|null $period One of 'y' for year, 'm' for month, 'w' for week, 'd' for day.
	 * @return CDate The updated date.
	 * @throws Exception if a different calendar used.
	 */
	public function add(CDate $date, $offset = null, $period = null)
	{
		$this->validate($date, $this->minMonth, $this->minDay, 'invalidDate');
		return $this->correctAdd($date, $this->_add($date, $offset, $period), $offset, $period);
	}

	/**
	 * Add period(s) to a date.
	 *
	 * @param CDate $date The starting date.
	 * @param int|null $offset The number of periods to adjust by.
	 * @param string|null $period One of 'y' for year, 'm' for month, 'w' for week, 'd' for day.
	 * @return CDate The updated date.
	 * @throws Exception if any error may encoutered.
	 */
	protected function _add(CDate $date, $offset = null, $period = null)
	{
		$this->validateLevel++;
		if ($period === 'd' || $period === 'w') {
			$jd = $date->toJD() + $offset * ($period === 'w' ? $this->daysInWeek() : 1);
			$d = $date->calendar()->fromJD($jd);
			$this->validateLevel--;
			return [$d->year(), $d->month(), $d->day()];
		}
		try {
			$y = $date->year() + ($period === 'y' ? $offset : 0);
			$m = $date->monthOfYear() + ($period === 'm' ? $offset : 0);
			$d = $date->day();
			$resyncYearMonth = function($calendar) use(&$y, &$m) {
				while ($m < $calendar->minMonth) {
					$y--;
					$m += $calendar->monthsInYear($y);
				}
				$yearMonths = $calendar->monthsInYear($y);
				while ($m > $yearMonths - 1 + $calendar->minMonth) {
					$y++;
					$m -= $yearMonths;
					$yearMonths = $calendar->monthsInYear($y);
				}
			};
			if ($period === 'y') {
				if ($date->month() !== $this->fromMonthOfYear($y, $m)) {
					$m = $this->newDate($y, $date->month(), $this->minDay)->monthOfYear();
				}
				$m = min($m, $this->monthsInYear($y));
				$d = min($d, $this->daysInMonth($y, $this->fromMonthOfYear($y, $m)));
			} elseif ($period === 'm') {
				$resyncYearMonth($this);
				$d = min($d, $this->daysInMonth($y, $this->fromMonthOfYear($y, $m)));
			}
			$ymd = [$y, $this->fromMonthOfYear($y, $m), $d];
			$this->validateLevel--;
			return $ymd;
		} catch (\Exception $e) {
			$this->validateLevel--;
			throw $e;
		}
	}

	/**
	 * Correct a candidate date after adding period(s) to a date.
	 * Handle no year zero if necessary.
	 *
	 * @param CDate $date The starting date.
	 * @param int|null $ymd The added date.
	 * @param int|null $offset The number of periods to adjust by.
	 * @param string|null $period One of 'y' for year, 'm' for month, 'w' for week, 'd' for day.
	 * @return CDate The updated date.
	 */
	protected function correctAdd(CDate $date,$ymd = null, $offset = null, $period = null)
	{
		if (!$this->hasYearZero && ($period === 'y' || $period === 'm')) {
			if ($ymd[0] === 0 || ($date->year() > 0) !== ($ymd[0] > 0)) {
				$adj = [
					'y' => [1, 1, 'y'],
					'm' => [1, $this->monthsInYear(-1), 'm'],
					'w' => [$this->daysInWeek(), $this->daysInYear(-1), 'd'],
					'd' => [1, $this->daysInYear(-1), 'd']
				][$period];
				$dir = ($offset < 0 ? -1 : +1);
				$ymd = $this->_add($date, $offset * $adj[0] + $dir * $adj[1], $adj[2]);
			}
		}
		return $date->date($ymd[0], $ymd[1], $ymd[2]);
	}

	/**
	 * Set a portion of the date.
	 *
	 * @param CDate $date The starting date.
	 * @param int|null $value The new value for the period.
	 * @param string|null $period One of 'y' for year, 'm' for month, 'd' for day.
	 * @return CDate The updated date.
	 * @throws Exception if an invalid date or a different calendar used.
	 */
	public function set(CDate $date, $value = null, $period = null)
	{
		$this->validate($date, $this->minMonth, $this->minDay, 'invalidDate');
		$y = ($period === 'y' ? $value : $date->year());
		$m = ($period === 'm' ? $value : $date->month());
		$d = ($period === 'd' ? $value : $date->day());
		if ($period === 'y' || $period === 'm') {
			$d = min($d, $this->daysInMonth($y, $m));
		}
		return $date->date($y, $m, $d);
	}

	/**
	 * Determine whether a date is valid for this calendar.
	 *
	 * @param int|null $year The year to examine.
	 * @param int|null $month The month to examine.
	 * @param int|null $day The day to examine.
	 * @return boolean <code>true</code> if a valid date, <code>false</code> if not.
	 */
	public function isValid($year = null, $month = null, $day = null)
	{
		$this->validateLevel++;
		$valid = ($this->hasYearZero || $year !== 0);
		if ($valid) {
			$date = $this->newDate($year, $month, $this->minDay);
			$valid = ($month >= $this->minMonth && $month - $this->minMonth < $this->monthsInYear($date)) && ($day >= $this->minDay && $day - $this->minDay < $this->daysInMonth($date));
		}
		$this->validateLevel--;
		return $valid;
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
		return Calendars::instance()->fromJD($this->toJD($date))->toPHPDate();
	}

	/**
	 * Create a new date from a standard (Gregorian) PHP DateTime Date.
	 *
	 * @param DateTime $phpd The PHP DateTime to convert.
	 * @return CDate The equivalent date.
	 */
	public function fromPHPDate(DateTime $phpd)
	{
		return $this->fromJD(Calendars::instance()->fromPHPDate($phpd)->toJD());
	}

	/**
	 * Check that a candidate date is from the same calendar and is valid.
	 *
	 * @param CDate|int|null $year The date to validate or the year to validate.
	 * @param int|null $month The month to validate.
	 * @param int|null $day The day to validate.
	 * @param string $error Error message key to get from localisation if invalid.
	 * @return boolean <code>true</code> if a valid date, <code>false</code> if not.
	 * @throws Exception if different calendars used or invalid date.
	 */
	public function validate($year = null, $month = null, $day = null, $error = 'invalidArguments')
	{
		if ($year instanceof CDate) {
			if ($this->validateLevel === 0 && $this->name !== $year->calendar()->name) {
				throw new \Exception(RegionalOptions::replace($this->local('differentCalendars'),[$this->name,$year->calendar()->name]));
			}
			return $year;
		}
		try {
			$this->validateLevel++;
			if ($this->validateLevel === 1 && !$this->isValid($year, $month, $day)) {
				throw new \Exception(RegionalOptions::replace($this->local($error),$this->name));
			}
			$date = $this->newDate($year, $month, $day);
			$this->validateLevel--;
			return $date;
		} catch (\Exception $e) {
			$this->validateLevel--;
			throw $e;
		}
	}

	/**
	 * Format a date object into a string value.
	 * to skip the format you can call the method
	 * with the date as first argument and the settings
	 * the second
	 *
	 * @param string|null $format The desired format of the date (defaults to calendar format).
	 * The format can be combinations of the following:
	 * <ul>
	 * 	<li>d  - day of month (no leading zero)</li>
	 * 	<li>dd - day of month (two digit)</li>
	 * 	<li>o  - day of year (no leading zeros)</li>
	 * 	<li>oo - day of year (three digit)</li>
	 * 	<li>D  - day name short</li>
	 * 	<li>DD - day name long</li>
	 * 	<li>w  - week of year (no leading zero)</li>
	 * 	<li>ww - week of year (two digit)</li>
	 * 	<li>m  - month of year (no leading zero)</li>
	 * 	<li>mm - month of year (two digit)</li>
	 * 	<li>M  - month name short</li>
	 * 	<li>MM - month name long</li>
	 * 	<li>E - the epoch designator for this date, e.g. BCE or CE.</li>
	 * 	<li>yy - year (two digit)</li>
	 * 	<li>yyyy - year (four digit)</li>
	 * 	<li>YYYY - formatted year</li>
	 * 	<li>J  - Julian date (days since January 1, 4713 BCE Greenwich noon)</li>
	 * 	<li>@  - Unix timestamp (s since 01/01/1970)</li>
	 * 	<li>!  - Windows ticks (100ns since 01/01/0001)</li>
	 * 	<li>'...' - literal text</li>
	 * 	<li>'' - single quote</li>
	 * </ul>
	 * @param CDate|null $date The date value to format.
	 * @param array $settings Addition options, whose attributes include:
	 * <ul>
	 * <li> dayNamesShort array: Abbreviated names of the days starting from Sunday. </li>
	 * <li> dayNames array: Names of the days from starting Sunday. </li>
	 * <li> monthNamesShort array: Abbreviated names of the months. </li>
	 * <li> monthNames array: Names of the months. </li>
	 * <li> localNumbers boolean: true/false subtitue the digits with ones supplied e.g Arabic/Indian digits </li>
	 * </ul>
	 * @return string The date in the above format.
	 * @throws Exception if the date is from a different calendar or invalid date.
	 */
	public function formatDate($format = null, $date = null, $settings = [])
	{
		if ($format instanceof CDate) {
			$settings = $date;
			$date = $format;
			$format = null;
		}
		if (!($date instanceof CDate)) {
			return '';
		}
		if ($date->calendar()->name !== $this->name) {
			throw new \Exception(RegionalOptions::replace($this->local('differentCalendars'),[$this->name,$date->calendar()->name]));
		}
		$format = empty($format) ? $this->local('dateFormat') : $format;
		$settings = empty($settings) ? [] : $settings;
		$dayNamesShort = !isset($settings['dayNamesShort']) ? $this->local('dayNamesShort') : $settings['dayNamesShort'];
		$dayNames = !isset($settings['dayNames']) ? $this->local('dayNames') : $settings['dayNames'];
		$monthNamesShort = !isset($settings['monthNamesShort']) ? $this->local('monthNamesShort') : $settings['monthNamesShort'];
		$monthNames = !isset($settings['monthNames']) ? $this->local('monthNames') : $settings['monthNames'];
		$localNumbers = !isset($settings['localNumbers']) ? $this->local('localNumbers') : $settings['localNumbers'];
		// Check whether a format character is doubled
		$doubled = function($match, $step = 1) use ($format, &$iFormat) {
			$matches = 1;
			while ($iFormat + $matches < strlen($format) && $format[$iFormat + $matches] === $match) {
				$matches++;
			}
			$iFormat += $matches - 1;
			return floor($matches / $step) > 1;
		};
		// Format a number, with leading zeroes if necessary
		$formatNumber = function($match, $value, $len, $step = 1) use ($doubled) {
			$num = '' + $value;
			if ($doubled($match, $step)) {
				while (strlen($num) < $len) {
					$num = '0' . $num;
				}
			}
			return $num;
		};
		// Format a name, short or long as requested
		$formatName = function($match, $value, $shortNames, $longNames) use ($doubled) {
			return ($doubled($match) ? $longNames[$value] : $shortNames[$value]);
		};
		// Localise numbers if requested and available
		$localiseNumbers = $this->localiseNumbers($localNumbers);
		$output = '';
		$literal = false;
		for ($iFormat = 0; $iFormat < strlen($format); $iFormat++) {
			if ($literal) {
				if ($format[$iFormat] === "'" && !$doubled("'")) {
					$literal = false;
				} else {
					$output .= $format[$iFormat];
				}
			} else {
				switch ($format[$iFormat]) {
					case 'd':
						$output .= $localiseNumbers($formatNumber('d', $date->day(), 2));
						break;
					case 'D': 
						$output .= $formatName('D', $date->dayOfWeek(),	$dayNamesShort, $dayNames);
						break;
					case 'o':
						$output .= $formatNumber('o', $date->dayOfYear(), 3);
						break;
					case 'w':
						$output .= $formatNumber('w', $date->weekOfYear(), 2);
						break;
					case 'm':
						$output .= $localiseNumbers($formatNumber('m', $date->month(), 2));
						break;
					case 'M':
						$output .= $formatName('M', $date->month() - $this->minMonth, $monthNamesShort, $monthNames);
						break;
					case 'y':
						$output .= $localiseNumbers(($doubled('y', 2) ? $date->year() : ($date->year() % 100 < 10 ? '0' : '') + $date->year() % 100));
						break;
					case 'Y':
						$output .= $doubled('Y', 2) ? $localiseNumbers($date->formatYear()) : '';
						break;
					case 'J':
						$output .= $date->toJD();
						break;
					case '@':
						$output .= ($date->toJD() - $this->UNIX_EPOCH) * $this->SECS_PER_DAY;
						break;
					case '!':
						$output .= ($date->toJD() - $this->TICKS_EPOCH) * $this->TICKS_PER_DAY;
						break;
					case "'":
						if ($doubled("'")) {
							$output .= "'";
						} else {
							$literal = true;
						}
						break;
					case "E":
						$output .= $this->epoch($date);
						break;
					default:
						$output .= $format[$iFormat];
				}
			}
		}
		return $output;
	}

	/**
	 * Pad a numeric value with leading zeroes.
	 *
	 * @param int $value The number to format.
	 * @param int $length The minimum length.
	 * @return string The formatted number.
	 */
	public static function pad($value, $length)
	{
		$value = $value . '';
		return substr('000000', 0, $length - strlen($value)) . $value;
	}

	/**
	 * Retrieve a localisation property value
	 * if not found on this calendar it will look into
	 * RegionalOptions instance
	 *
	 * @param string $key Localisation key.
	 * @return string|null return null if key doesnt exist
	 */
	public function local($key)
	{
		if (property_exists($this,$key)) {
			return $this->{$key};
		}
		return RegionalOptions::get($key);
	}

	/**
	 * Checks option localNumbers and retrieve another closure.
	 * if no option is set then will check calendar localisation.
	 *
	 * @param boolean|null $localNumbers option enabled/disabled.
	 * @return callable the closure to check and subtitute numbers.
	 */
	public function localiseNumbers($localNumbers = null)
	{
		if (is_null($localNumbers)) {
			$localNumbers = $this->local('localNumbers');
		}
		$digits = $this->local('digits');
		$substituteDigits = $this->substituteDigits($digits);
		return function($value) use ($localNumbers, $digits, $substituteDigits) {
			return ($localNumbers && is_array($digits) ? $substituteDigits($value) : $value);
		};
	}

	/**
	 * A simple digit substitution function for localising numbers via the Calendar digits option.
	 *
	 * @param array $digits digits to subtitute
	 * @return callable the substitution closure.
	 */
	public function substituteDigits($digits)
	{
		return function ($value) use ($digits) {
			$output = '';
			preg_match_all('/[0-9]/', $value, $matches);
			foreach ($matches[0] as $digit) {
				$output .= $digits[$digit];
			}
			return $output;
		};
	}
	
	/**
	 * Invoke the original overridden parent class method
	 * this would invoke the method as it wasn't overridden
	 *
	 * @param BaseCalendar $instance The child class object (calendar)
	 * @param array $method The method to invoke
	 * @param array $arguments Arguments to pass on to the method
	 * @return mixed return from the invoked method
	 */
	protected function invokeParent(BaseCalendar $instance, $method, $arguments = [])
	{
		$reflection = new ReflectionObject($instance);

		$parentReflection = $reflection->getParentClass();

		$parentFooReflection = $parentReflection->getMethod($method);

		return $parentFooReflection->invokeArgs($instance, $arguments);
	}
}
