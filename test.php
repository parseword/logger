<?php

require_once('src/Logger.php');

use parseword\logger\Logger;

/*
 * Set a temporary log file name
 */
Logger::setFilename('logger-test.log');
if (Logger::getFilename() != 'logger-test.log') {
    throw new \Exception("Logger::setFilename() didn't work as expected");
}

/*
 * Call a variety of Logger methods using different severity filters.
 * Some of the log messages should end up in the file; others shouldn't.
 * The default severity filter is Logger::SEVERITY_ERROR.
 */
Logger::error("01 This ERROR message should appear in the log");
Logger::warning("02 This WARNING message should NOT appear in the log");
Logger::info("03 This INFO message should NOT appear in the log");
Logger::debug("04 This DEBUG message should NOT appear in the log");

Logger::setSeverityFilter(Logger::SEVERITY_WARNING);
Logger::error("05 This ERROR message should appear in the log");
Logger::warning("06 This WARNING message should appear in the log");
Logger::info("07 This INFO message should NOT appear in the log");
Logger::debug("08 This DEBUG message should NOT appear in the log");

Logger::setSeverityFilter(Logger::SEVERITY_INFO);
Logger::error("09 This ERROR message should appear in the log");
Logger::warning("10 This WARNING message should appear in the log");
Logger::info("11 This INFO message should appear in the log");
Logger::debug("12 This DEBUG message should NOT appear in the log");

Logger::setSeverityFilter(Logger::SEVERITY_DEBUG);
Logger::error("13 This ERROR message should appear in the log");
Logger::warning("14 This WARNING message should appear in the log");
Logger::info("15 This INFO message should appear in the log");
Logger::debug("16 This DEBUG message should appear in the log");

/*
 * Test the log file contents for entries that should and shouldn't be present
 */
$data = file_get_contents(Logger::getFilename());
foreach (['01', '05', '06', '09', '10', '11', '13', '14', '15', '16'] as $var) {
    if (!preg_match("|{$var} This |m", $data)) {
        throw new \Exception("Expected log entry {$var} was missing");
    }
}
foreach (['02', '03', '04', '07', '08', '12'] as $var) {
    if (preg_match("|{$var} This |m", $data)) {
        throw new \Exception("Unexpected log entry {$var} was found");
    }
}

/*
 * Try to set an invalid severity filter value, expecting an exception
 */
$exception = false;
try {
    Logger::setSeverityFilter(12345);
}
catch (\Exception $ex) {
    $exception = true;
}
if ($exception === false) {
    throw new \Exception("Invalid parameter to setSeverityFilter() didn't "
            . 'throw an exception as expected');
}

/*
 * Truncate the file and write a fresh log entry
 */
Logger::truncate();
Logger::info("Taking tests can be exhausting!");

/*
 * Verify truncation worked by testing the log file contents again
 */
$data = file_get_contents(Logger::getFilename());
if (strpos($data, 'exhausting') === false) {
    throw new \Exception("Expected log entry 'exhausting' was missing");
}
if (strpos($data, '01 This') !== false) {
    throw new \Exception("Unexpected log entry '01 This' was found");
}

/*
 * Write a message containing newlines and ensure the default setting of
 * collapseEntries replaces them with spaces.
 */
Logger::error("This ERROR\n\n\nmessage contains\n\n\nseveral newlines.");
$data = file_get_contents(Logger::getFilename());
if (strpos($data, "ERROR\n\n\nmessage") !== false) {
    throw new \Exception('Newlines that should have been removed were present');
}

/*
 * Now set collapseEntries to false, and verify the newlines remain intact.
 */
Logger::setCollapseEntries(false);
Logger::error("This ERROR\n\n\nmessage contains\n\n\nseveral newlines.");
$data = file_get_contents(Logger::getFilename());
if (strpos($data, "ERROR\n\n\nmessage") === false) {
    throw new \Exception('Newlines that should have been present were not');
}

/*
 * Delete the log file, set the filter to Logger::SEVERITY_NONE, send log
 * messages of each severity, and ensure the file wasn't created.
 */
unlink(Logger::getFilename());
Logger::setSeverityFilter(Logger::SEVERITY_NONE);
Logger::error('Error');
Logger::warning('Warning');
Logger::info('Info');
Logger::debug('Debug');
if (file_exists(Logger::getFilename())) {
    throw new \Exception('Messages were unexpectedly logged at SEVERITY_NONE');
}

/*
 * Reset the filter and write a new message to ensure the file is properly
 * reinitialized.
 */
Logger::setSeverityFilter(Logger::SEVERITY_DEBUG);
Logger::info('Info');
if (!file_exists(Logger::getFilename())) {
    throw new \Exception('Log file was not properly reinitialized');
}

/*
 * Remove the log file.
 */
unlink(Logger::getFilename());

echo "Tests for Logger have passed" . PHP_EOL;
