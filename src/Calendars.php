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

final class Calendars
{
	/**
     * Default language localisation to use if no language is specified.
     *
     * @var string
     */
	public $defaultLanguage = 'english';

	/**
     * Exception messeges for each language available.
     *
     * @var array
     */
	public $regionalOptions = [
		'english' => [
			'invalidCalendar' => 'Calendar {0} not found',
			'invalidDate' => 'Invalid {0} date',
			'invalidMonth' => 'Invalid {0} month',
			'invalidYear' => 'Invalid {0} year',
			'invalidMethod' => 'Method {0} not found',
			'differentCalendars'=> 'Cannot mix {0} and {1} dates',
			'invalidArguments' => 'Invalid arguments',
			'invalidFormat' => 'Cannot format a date from another calendar',
			'missingNumberAt' => 'Missing number at position {0}',
			'unknownNameAt' => 'Unknown name at position {0}',
			'unexpectedLiteralAt' => 'Unexpected literal at position {0}',
			'unexpectedText' => 'Additional text found at end',
			'missingRegionalOptions' => 'Regional Options is missing from {0}'
		]
	];

	/**
     * Calendar instances container.
     *
     * @var array
     */
	protected $localCals = [];

	/**
     * static instance container for Calendars.
     *
     * @var Calendars|null
     */
	protected static $instance;

	/**
	 * Create a new Calendars instance.
	 *
	 * @param string|null $language The language code to use for localisation.
	 */
	public function __construct($language = null)
	{
		if (!is_null($language)) {
			$this->defaultLanguage = $language;
		}
		new RegionalOptions($this->regionalOptions,$this->defaultLanguage);
		static::$instance = $this;
	}

	/**
	 * Sets the default language.
	 *
	 * @param string|null $language The language code to use for localisation.
	 * @return Calendars The calendar instance.
	 */
	public static function setLanguage($language = null)
	{
		if (static::$instance === null) {
			return static::selfInstance($language);
		}
		$self = static::selfInstance();
		$self->defaultLanguage = $language;
		RegionalOptions::setRegionalOptions($language);
	}

	/**
	 * Obtain a calendar implementation.
	 *
	 * @param string|null $name The name of the calendar, e.g. 'gregorian', 'persian', 'islamic'.
	 * @param string|null $language The language code to use for localisation.
	 * @return BaseCalendar The calendar instance.
	 * @throws Exception if calendar not found.
	 */
	public static function instance($name = null,$language = null)
	{
		$self = static::selfInstance();
		$name = $name ?: 'gregorian';
		$language = $language ?: $self->defaultLanguage;
		if (empty($self->localCals[$name . '-' . $language])) {
			$namespaced = __NAMESPACE__ . '\\' . $name . 'Calendar';
			if (class_exists($namespaced)) {
				$self->localCals[$name . '-' . $language] = new $namespaced($language);
			} else {
				throw new \Exception(RegionalOptions::getReplace('invalidCalendar',$name));
			}
		}
		return $self->localCals[$name . '-' . $language];
	}

	/**
	 * an Alias for Calendars::instance().
	 */
	public static function calendar($name = null,$language = null)
	{
		return static::instance($name,$language);
	}

	/**
	 * Create a new date.
	 * you can specify date or today if no date is given.
	 * gregorian is default if no calendar is given
	 * first language in the calendar is default if no language is given
	 *
	 * @param CDate|int|null $year The date to copy or the year for the date.
	 * @param int|null $month The month for the date.
	 * @param int|null $day The day for the date.
	 * @param BaseCalendar|string|null $calendar The language code to use for localisation
	 * @param string|null $language The language code to use for localisation
	 * @return CDate The new date.
	 * @throws Exception if an invalid date.
	 */
	public static function newDate($year = null, $month = null, $day = null, $calendar = null, $language = null)
	{
		static::selfInstance();
		if ($year instanceof CDate) {
			$calendar = $year->calendar();
		} else {
			if (is_string($calendar)) {
				$calendar = static::instance($calendar, $language);
			} elseif (!($calendar instanceof BaseCalendar)) {
				$calendar = static::instance();
			}
		}
		return $calendar->newDate($year, $month, $day);
	}

	/**
	 * Returns Calendars instance and creates one if not created before.
	 *
	 * @return Calendars
	 */
	public static function selfInstance($language = null)
    {
        if (static::$instance === null) {
            $class = __CLASS__;
            static::$instance = new $class($language);
        }
        return static::$instance;
    }
}
