Calendars
===================
This is a replica of [Keith Wood's Calendar jQuery plugin](http://keith-wood.name/calendars.html) written in PHP

This plugin provides support for various world calendars.

Until now the available calendars are:

* Georgian calendar
* Umm al-Qura calendar (originally written by Amro Osama)

still yet to come and contributes are very welcome.

Install:
===================

* clone from Git or download then require the autoloader:
```php
require 'path/to/Calendars/src/CalendarsAutoloader.php';
```
* or require from composer then require composer's autoloader:
```bash
composer require talal424/calendars
```
```php
require 'vendor/autoload.php';
```

Usage & examples:
===================

```php

<?php

use Talal424\Calendars\Calendars;

// get UmmAlQura calendar
$UmmAlQura = Calendars::calendar('UmmAlQura'); //object Talal424\Calendars\UmmAlQuraCalendar
// get Gregorian calendar
$Gregorian = Calendars::calendar('Gregorian'); // returns object Talal424\Calendars\GregorianCalendar

// get a new date
// $year, $month, $day 
$date = $UmmAlQura->newDate(1406,4,9); // returns object Talal424\Calendars\CDate

// $format, CDate $date, $settings
echo $UmmAlQura->formatDate('yyyy/mm/dd E',$date,['localNumbers'=>false]); // returns string
// 1406/04/09 AH

// or

// $format, $settings
echo $date->formatDate('yyyy/mm/dd'); // returns string
// ١٤٠٦/٠٤/٠٩

// convert UmmAlQura date to Julian date
$jd = $date->toJD(); // returns string

$date = $Gregorian->fromJD($jd); // returns object Talal424\Calendars\CDate

// $format, $settings
echo $date->formatDate('yyyy/mm/dd'); // returns string
// 1985/12/21

// you can also do this
$date = $Gregorian->newDate(); // returns today's date object Talal424\Calendars\CDate
// this would use the default format of the calendar (Gregorian) 'mm/dd/yyyy'
echo $date; // returns string
// 01/28/2017
```

Localisation and Languages
===================

Talal424\Calendars\Calendars
---------------------------------
this class has the exception messages stored as $regionalOptions

```php
$defaultLanguage = 'english'; // this will be used for all calendars later on unless language code is set when calendar is called.

$regionalOptions = [
	//Language Code
	'Arabic' => [
		'invalidCalendar' => 'لم يتم العثور على التقويم: {0}',
	],
	'English' => [
		'invalidCalendar' => 'Calendar {0} not found',
	]
];
```

it can be overwritten by any calendar, so you can make a special message for a specific calendar. this will be explain later on.

```php
Calendars::setLanguage('Arabic'); // this will set Arabic as the default language

// get UmmAlQura calendar
$UmmAlQura = Calendars::calendar('UmmAlQura'); // this will load the calendar with the default language and settings.

$UmmAlQura = Calendars::calendar('UmmAlQura','Arabic'); // this will load the calendar with the Arabic language and settings.
```
if the language is not set the default language will be used.
if the default language is not set or not found, the first language will be used.
if none is found an exception will be thrown.

Other Calendars
----------------------------
classes of calendars have the language and settings stored in $regionalOptions

each language code can have its own settings and language, and it can have these attributes:
<ul>
<li> epochs: The epoch names. </li>
<li> monthNames: The long names of the months of the year. </li>
<li> monthNamesShort: The short names of the months of the year. </li>
<li> dayNames: The long names of the days of the week. </li>
<li> dayNamesShort: The short names of the days of the week. </li>
<li> localNumbers: true/false subtitue the digits with ones supplied e.g Arabic/Indian digits </li>
<li> digits: The digits to subtitute </li>
<li> dateFormat:  The date format for this calendar. </li>
</ul>

plus any exception message.

example:
```php
$regionalOptions = [
	'US' => [
		'invalidYear' => 'Dude! {0} year is not right', // example for overriding an exception message
		'epochs' => ['BCE', 'CE'],
		'monthNames' => ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'],
		'monthNamesShort' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		'dayNames' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		'dayNamesShort' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		'digits' => null,
		'localNumbers' => false,
		'dateFormat' => 'mm/dd/yyyy',
	],
	'UK' => [
		'epochs' => ['BCE', 'CE'],
		'monthNames' => ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'],
		'monthNamesShort' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		'dayNames' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		'dayNamesShort' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		'digits' => null,
		'localNumbers' => false,
		'dateFormat' => 'dd/mm/yyyy',
	],
];
```

you can also if you use one language but different settings - just like our example above - by setting days and months names as default properties and then make the ' can be changed ' settings inside the $regionalOptions array

example:

```php
class GregorianCalendar extends BaseCalendar
{
	public $name = 'Gregorian';
	public $hasYearZero = false;
	public $minMonth = 1;
	public $firstMonth = 1;
	public $minDay = 1;
	public $daysPerMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	public $jdEpoch = 1721425.5;

	public $epochs = ['BCE', 'CE'];
	public $monthNames = ['January', 'February', 'March', 'April', 'May', 'June','July', 'August', 'September', 'October', 'November', 'December'];
	public $monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	public $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	public $dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
	public $digits = null;
	public $localNumbers = false;


	public $regionalOptions = [
		'US' => [
			'dateFormat' => 'mm/dd/yyyy',
		],
		'UK' => [
			'dateFormat' => 'dd/mm/yyyy',
		],
	];
}
```


Date formats:
==============

The format can be combinations of the following:

<ul>
<li>d  - day of month (no leading zero)</li>
<li>dd - day of month (two digit)</li>
<li>o  - day of year (no leading zeros)</li>
<li>oo - day of year (three digit)</li>
<li>D  - day name short</li>
<li>DD - day name long</li>
<li>w  - week of year (no leading zero)</li>
<li>ww - week of year (two digit)</li>
<li>m  - month of year (no leading zero)</li>
<li>mm - month of year (two digit)</li>
<li>M  - month name short</li>
<li>MM - month name long</li>
<li>E - the epoch designator for this date, e.g. BCE or CE.</li>
<li>yy - year (two digit)</li>
<li>yyyy - year (four digit)</li>
<li>YYYY - formatted year</li>
<li>J  - Julian date (days since January 1, 4713 BCE Greenwich noon)</li>
<li>@  - Unix timestamp (s since 01/01/1970)</li>
<li>!  - Windows ticks (100ns since 01/01/0001)</li>
<li>'...' - literal text</li>
<li>'' - single quote</li>
</ul>
