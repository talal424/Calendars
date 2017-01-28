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

final class RegionalOptions
{
	/**
     * static instance container for RegionalOptions
     *
     * @var RegionalOptions|null
     */
	static protected $instance;

	/**
	 * Create a new RegionalOptions instance.
	 *
	 * @param array $regionalOptions Exception messeges for each language available.
	 * @param string|null $language The language code to use for localisation.
	 */
	public function __construct($regionalOptions = [], $language = null)
	{
		static::$instance = $this;
		static::setRegionalOptions($regionalOptions, $language);
	}

	/**
	 * Store localisation for a specific language into RegionalOptions object
	 * or the instance provided.
	 *
	 * @param array $regionalOptions Exception messeges for a specific language.
	 * @param object|null $instance The object to store localisation into.
	 */
	public static function addRegionalOptions($regionalOptions = [], $instance = null)
	{
		if (is_null($instance) || !is_object($instance)) {
			$instance = static::selfInstance();
		}
		foreach ($regionalOptions as $key => $value) {
			$instance->{$key} = $value;
		}
	}

	/**
	 * Add/Change localisation to a specific instance or RegionalOptions if non provided
	 * if no language is specified or cannot be found in array the first one will be chosen
	 *
	 * @param array $regionalOptions Exception messeges for each language available.
	 * @param string|null $language The language code to use for localisation.
	 * @param object|null $instance The object to store localisation into.
	 * @throws Exception if no language can be found.
	 */
	public static function setRegionalOptions($regionalOptions = [], $language = null, $instance = null)
	{
		if (is_null($instance) || !is_object($instance)) {
			$instance = static::selfInstance();
		}
		if (is_null($language) || empty($regionalOptions[$language])) {
			reset($regionalOptions);
			$language = key($regionalOptions);
			if (empty($language)) {
				throw new \Exception(static::getReplace('missingRegionalOptions',get_class($instance)));
			}
		}
		static::addRegionalOptions($regionalOptions[$language], $instance);
	}

	/**
	 * Retrieve a localisation property value
	 *
	 * @param string $name Localisation key.
	 * @return string|null return null if key doesnt exist
	 */
	public static function get($name)
	{
		$self = static::selfInstance();
		if (property_exists($self,$name)) {
			return $self->{$name};
		}
		return null;
	}

	/**
	 * Retrieve a localisation property value
	 * and replace tags ({0} {1}) with arguments provided
	 * arguments can be a string or array of strings
	 *
	 * @param string $name Localisation key.
	 * @param string|array $arguments Arguments to replace tags.
	 * @return string return replaced text
	 */
	public static function getReplace($name, $arguments = '')
	{
		return static::replace(static::get($name),$arguments);
	}

	/**
	 * Replace tags ({0} {1}) with arguments provided
	 *
	 * @param string $text text to search for tags.
	 * @param string|array $arguments Arguments to replace tags.
	 * @return string return replaced text
	 */
	public static function replace($text = '', $arguments = '')
	{
		if (empty($text)) {
			return '';
		}
		if (!is_array($arguments)) {
			$arguments = [$arguments];
		}
		return str_replace(['{0}','{1}','{2}','{3}','{4}'], $arguments, $text);
	}

	/**
	 * Returns RegionalOptions instance and creates one if not created before.
	 *
	 * @return RegionalOptions
	 */
	public static function selfInstance()
    {
        if (static::$instance === null) {
            $class = __CLASS__;
            static::$instance = new $class;
        }
        return static::$instance;
    }
}