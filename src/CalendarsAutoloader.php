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

CalendarsAutoloader::register();

abstract class CalendarsAutoloader
{
	public static function register()
	{
		if (function_exists('__autoload')) {
			// we can't afford clashes !!
            spl_autoload_register('__autoload');
        }
		spl_autoload_register(['CalendarsAutoloader', "autoLoad"]);
	}

	public static function autoLoad($className)
	{
		// our namespace
		$nsPrefix = 'Talal424\\Calendars';
		if (!static::startsWith($className, $nsPrefix) || class_exists($className,FALSE)) {
			// already loaded, or not a Calendars class request
			return false;
		}
		// separate namespace from class name
		$ns = "\\";
		$fileName = substr($className, strlen($nsPrefix . $ns));
		$fileName = str_replace($ns, DIRECTORY_SEPARATOR, $fileName);
		$filePath = dirname(__FILE__) . '/' . $fileName . '.php';
		
		if ((file_exists($filePath) === FALSE) || (is_readable($filePath) === FALSE)) {
			// file not found or can't be loaded
            return FALSE;
        }
        // all good to go
		require $filePath;
		return true;
	}

	/**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
	public static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }
}