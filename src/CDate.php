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

final class CDate
{
	/**
     * The underlying calendar implementation.
     *
     * @var BaseCalendar|null
     */
	protected $calendar;

	/**
     * The year for the date.
     *
     * @var int
     */
	protected $year;

	/**
     * The month for the date.
     *
     * @var int
     */
	protected $month;

	/**
     * The day for the date.
     *
     * @var int
     */
	protected $day;

	/**
	 * Create a new Generic date, based on a particular calendar.
	 *
	 * @param string|null $language The language code to use for localisation.
	 * @param BaseCalendar $calendar The underlying calendar implementation.
	 * @param int|null $year The year for this date.
	 * @param int|null $month The month for this date.
	 * @param int|null $day The day for this date.
	 * @throws Exception if an invalid date.
	 */
	public function __construct(BaseCalendar $calendar, $year = null, $month = null, $day = null)
	{
		$this->calendar = $calendar;
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
		if ($calendar->validateLevel === 0 && !$calendar->isValid($year,$month,$day)) {
			throw new \Exception(RegionalOptions::replace($calendar->local('invalidDate'),$calendar->name));
		}
	}

	/**
	 * Create a new date - for today if no other parameters given.
	 *
	 * @param CDate|int|null $year The date to copy or the year for the date.
	 * @param int|null $month The month for the date.
	 * @param int|null $day The day for the date.
	 * @param BaseCalendar|string|null $calendar The language code to use for localisation
	 * @param string|null $language The language code to use for localisation
	 * @return CDate The new date.
	 * @throws Exception if an invalid date.
	 */
	public function newDate($year = null, $month = null, $day = null)
	{
		return $this->calendar->newDate((is_null($year) ? $this : $year), $month, $day);
	}

	/**
	 * Set or retrieve the year for this date.
	 *
	 * @param int|null $year The year for the date to set.
	 * @return int|CDate The date's year (if no parameter) or the updated CDate.
	 * @throws Exception if an invalid date.
	 */
	public function year($year = null)
	{
		return is_null($year) ? $this->year : $this->set($year, 'y');
	}

	/**
	 * Set or retrieve the month for this date.
	 *
	 * @param int|null $month The month for the date to set.
	 * @return int|CDate The date's month (if no parameter) or the updated CDate.
	 * @throws Exception if an invalid date.
	 */
	public function month($month = null)
	{
		return is_null($month) ? $this->month : $this->set($month, 'm');
	}

	/**
	 * Set or retrieve the day for this date.
	 *
	 * @param int|null $day The day for the date to set.
	 * @return int|CDate The date's day (if no parameter) or the updated CDate.
	 * @throws Exception if an invalid date.
	 */
	public function day($day = null)
	{
		return is_null($day) ? $this->day : $this->set($day, 'd');
	}

	/**
	 * Set new values for this date.
	 *
	 * @param int|null $year The year for the date.
	 * @param int|null $month The month for the date.
	 * @param int|null $day The day for the date.
	 * @return CDate The updated date.
	 * @throws Exception if an invalid date.
	 */
	public function date($year = null, $month = null, $day = null)
	{
		if (!$calendar->isValid($year,$month,$day)) {
			throw new \Exception(RegionalOptions::replace($calendar->local('invalidDate'),$calendar->name));
		}
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
		return $this;
	}

	/**
	 * Determine whether this date is in a leap year.
	 *
	 * @return boolean <code>true</code> If this is a leap year <code>false</code> if not.
	 */
	public function leapYear()
	{
		return $this->calendar->leapYear($this);
	}

	/**
	 * Retrieve the epoch designator for this date, e.g. BCE or CE.
	 *
	 * @return string The current epoch
	 */
	public function epoch()
	{
		return $this->calendar->epoch($this);
	}

	/**
	 * Format the year, if not a simple sequential number.
	 *
	 * @return string The formatted year.
	 */
	public function formatYear()
	{
		return $this->calendar->formatYear($this);
	}

	/**
	 * Retrieve the month of the year for this date,
	 * i.e. the month's position within a numbered year.
	 *
	 * @return int The month of the year: <code>minMonth</code> to months per year.
	 */
	public function monthOfYear()
	{
		return $this->calendar->monthOfYear($this);
	}

	/**
	 * Retrieve the week of the year for this date.
	 *
	 * @return int The week of the year: 1 to weeks per year.
	 */
	public function weekOfYear()
	{
		return $this->calendar->weekOfYear($this);
	}

	/**
	 * Retrieve the number of days in the year for this date.
	 *
	 * @return int The number of days in this year.
	 */
	public function daysInYear()
	{
		return $this->calendar->daysInYear($this);
	}

	/**
	 * Retrieve the day of the year for this date.
	 *
	 * @return int The day of the year: 1 to days per year.
	 */
	public function dayOfYear()
	{
		return $this->calendar->dayOfYear($this);
	}

	/**
	 * Retrieve the number of days in the month for this date.
	 *
	 * @return int The number of days.
	 */
	public function daysInMonth()
	{
		return $this->calendar->daysInMonth($this);
	}

	/**
	 * Retrieve the day of the week for this date.
	 *
	 * @return int The day of the week: 0 to number of days - 1.
	 */
	public function dayOfWeek()
	{
		return $this->calendar->dayOfWeek($this);
	}

	/**
	 * Determine whether this date is a week day.
	 *
	 * @return boolean <code>true</code> if a week day, <code>false</code> if not.
	 */
	public function weekDay()
	{
		return $this->calendar->weekDay($this);
	}

	/**
	 * Retrieve additional information about this date.
	 *
	 * @return array Additional information - contents depends on calendar.
	 */
	public function extraInfo()
	{
		return $this->calendar->extraInfo($this);
	}

	/**
	 * Create a new date - for today if no other parameters given.
	 *
	 * @param int $offset The number of periods to adjust by.
	 * @param string $period One of 'y' for year, 'm' for month, 'w' for week, 'd' for day.
	 * @return CDate The updated date.
	 */
	public function add($offset, $period)
	{
		return $this->calendar->add($this, $offset, $period);
	}

	/**
	 * Create a new date - for today if no other parameters given.
	 *
	 * @param int $value The new value for the period.
	 * @param string $period One of 'y' for year, 'm' for month, 'd' for day.
	 * @return CDate The updated date.
	 * @throws Exception if not a valid date.
	 */
	public function set($value, $period)
	{
		return $this->calendar->set($this, $value, $period);
	}

	/**
	 * Compare this date to another date.
	 *
	 * @param CDate $date The other date.
	 * @return int -1 if this date is before the other date,
	 * 0 if they are equal, or +1 if this date is after the other date.
	 * @throws Exception if different calendars used.
	 */
	public function compareTo(CDate $date)
	{
		if ($this->calendar->name !== $date->calendar->name) {
			//double check instance
			throw new \Exception(RegionalOptions::replace($calendar->local('differentCalendars'),[$calendar->name,$date->calendar()->name]));
		}
		//$compare = $this->year !== $date->year ? ($this->year - $date->year) : ($this->month !== $date->month ? ($this.monthOfYear() - $date.monthOfYear()) : ($this->day - $date->day));
		if ($this->year !== $date->year) {
			$compare = $this->year - $date->year;
		} elseif ($this->month !== $date->month) {
			$compare = $this->monthOfYear() - $date->monthOfYear();
		} else {
			$compare = $this->day - $date->day;
		}
		return $compare === 0 ? 0 : ($compare < 0 ? -1 : +1);
	}

	/**
	 * Retrieve the calendar backing this date.
	 *
	 * @return BaseCalendar The calendar implementation.
	 */
	public function calendar()
	{
		return $this->calendar;
	}

	/**
	 * Retrieve the Julian date equivalent for this date,
	 * i.e. days since January 1, 4713 BCE Greenwich noon.
	 *
	 * @return int The equivalent Julian date.
	 */
	public function toJD()
	{
		return $this->calendar->toJD($this);
	}

	/**
	 * Create a new date from a Julian date.
	 *
	 * @param int $jd The Julian date to convert.
	 * @return CDate The equivalent date.
	 */
	public function fromJD($jd)
	{
		return $this->calendar->fromJD($jd);
	}

	/**
	 * Convert this date to a standard (Gregorian) PHP DateTime Date.
	 *
	 * @return DateTime The equivalent PHP DateTime date
	 */
	public function toPHPDate()
	{
		return $this->calendar->toPHPDate($this);
	}

	/**
	 * Create a new date from a standard (Gregorian) PHP DateTime Date.
	 *
	 * @param DateTime $jsd The PHP DateTime to convert.
	 * @return CDate The equivalent date.
	 */
	public function fromPHPDate(DateTime $phpd)
	{
		return $this->calendar->fromPHPDate($phpd);
	}

	/**
	 * Format this date.
	 *
	 * @param string|null $format The date format.
	 * @param array|null $settings Options for the BaseCalendar::formatDate method.
	 * @return string The formatted date.
	 */
	public function formatDate($format = null, $settings = null)
	{
		if (is_array($format)) {
			$settings = $format;
			$format = null;
		}
		return $this->calendar->formatDate($format, $this, $settings);
	}

	/**
	 * Convert to a string for display.
	 *
	 * @return string This date as a string.
	 */
	public function __toString()
    {
        return $this->toString();
    }

    /**
	 * Convert to a string for display.
	 *
	 * @return string This date as a string.
	 */
	public function toString()
	{
		return $this->formatDate();
	}
}