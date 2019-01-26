# Logger

Logger is a PHP text file logging facility, implemented as a singleton. It's 
nothing special, but I'm breaking a larger code base down into independent 
components and I needed this to be in its own repository.

## Usage

Implementing `Logger` in 4 easy steps:

1. Either `composer require parseword/logger` or copy the `Logger.php` file into 
your project.

2. Make sure `Logger.php` is discoverable by your autoloader, or `require_once` 
it manually.

3. Throughout your application code, call `Logger::debug()`, `Logger::info()`, 
`Logger::warning()` and `Logger::error()` to send log messages of differing 
severity levels.

4. In your application's config file or a common include file, you (or the user) 
call Logger::setFilename() to set the log file, and Logger::setSeverityFilter() 
to specify which log messages are written to disk.

## Example

In the following example, a file named `/tmp/my.log` is created. The severity 
filter is set so that only messages with severity of WARNING or higher will be 
written. Messages with lower severity will be disregarded.

```php
<?php
//Set up the logger in your config or global include file
use parseword\logger\Logger;
Logger::setFilename('/tmp/my.log');
Logger::setSeverityFilter(Logger::SEVERITY_WARNING);
Logger::setLabel('myCoolApp');

//Call the static Logger methods throughout your application code
Logger::info("Somebody set us up the bomb.");
Logger::debug("Main screen turn on.");
Logger::warning("All your base are belong to us.");
Logger::info("You have no chance to survive make your time.");
Logger::error("Unable to move 'ZIG', aborting");
```

The contents of `/tmp/my.log` will look like this:

```
[2019-01-25,14:40:13.797 CST]  WARN: myCoolApp: All your base are belong to us.
[2019-01-25,14:40:13.797 CST] ERROR: myCoolApp: Unable to move 'ZIG', aborting
```

Inspect or run the included `test.php` file for more examples.

## Method overview

These methods are used to configure the Logger:

* `setCollapseEntries()` - Whether or not to replace newlines in log entries 
with spaces, constraining each entry to a single line. Defaults to true.

* `setDateFormat()` - Set the date format to use when stamping log messages.

* `setFilename()` - Set the filesystem path and filename for the log file.

* `setLabel()` - Set an optional text string to include in log messages. If 
each instance of your application has a unique ID, setting it here can help 
with tracing and troubleshooting.

* `setSeverityFilter()` - Set which types of log messages are written to the file.

These methods control the logger's functionality:

* `debug()` - Send a log message of severity DEBUG.

* `error()` - Send a log message of severity ERROR.

* `warning()` - Send a log message of severity WARNING.

* `info()` - Send a log message of severity INFO.

* `truncate()` - Truncate the log file, wiping out any previous entries.

## Requirements

The Logger class requires PHP7 to support scalar type declarations.

## Errata

None at this time.
