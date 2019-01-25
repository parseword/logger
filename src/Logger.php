<?php

namespace parseword\logger;

/**
 * Logger is a text file logging facility implemented as a singleton.
 *
 * The logger has multiple severity levels. Convenience methods exist to send
 * log messages at each level. Whether or not a message is written to the log
 * file depends on the filter level configured with setSeverityFilter().
 *
 * See the included test.php file or GitHub for usage examples.
 *
 * *****************************************************************************
 *
 * Copyright 2006, 2019 Shaun Cummiskey <shaun@shaunc.com> <https://shaunc.com/>
 * Repository: <https://github.com/parseword/logger/>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class Logger
{

    /**
     * A label for messages sent using Logger::debug()
     */
    const SEVERITY_DEBUG = 2;

    /**
     * A label for messages sent using Logger::info()
     */
    const SEVERITY_INFO = 4;

    /**
     * A label for messages sent using Logger::warning()
     */
    const SEVERITY_WARNING = 8;

    /**
     * A label for messages sent using Logger::error()
     */
    const SEVERITY_ERROR = 16;

    /**
     * Use this to write no messages; the Logger won't even create the log file.
     */
    const SEVERITY_NONE = 32;

    /**
     * A reference to the singleton instance of this class.
     *
     * @var \parseword\logger\Logger
     */
    private static $instance = null;

    /**
     * The severity level at which log messages should be filtered. Defaults
     * to self::SEVERITY_ERROR.
     *
     * @var int
     */
    private static $severityFilter = self::SEVERITY_ERROR;

    /**
     * A label to use in log messages. Defaults to 'logger'
     */
    private static $label = 'logger';

    /**
     * The filesystem path where log output will be written.
     *
     * @var string
     */
    private static $filename = 'logfile.log';

    /**
     * The date format to use when stamping log entries. The default setting
     * creates easy-to-split timestamps like: 2015-06-03,22:32:07.097 EDT
     *
     * @see https://secure.php.net/manual/en/function.date.php PHP date formats
     * @var string
     */
    private static $dateFormat = 'Y-m-d,H:i:s.v T';

    /**
     * File pointer.
     *
     * @var resource
     */
    private $fp = null;

    /**
     * Whether or not to strip newlines out of log entries.
     *
     * @var bool
     */
    private static $collapseEntries = true;

    /**
     * A private constructor to satisfy the singleton pattern.
     */
    private function __construct() {

    }

    /**
     * Return the singleton instance of this class, creating it first if needed.
     *
     * @return \parseword\phorml\Logger
     */
    public static function getInstance(): Logger {
        if (empty(self::$instance)) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    /**
     * Return the currently configured label.
     *
     * @return string The label to use in log messages
     */
    public static function getLabel(): string {
        return self::getInstance()::$label;
    }

    /**
     * Set the label to use in log messages.
     *
     * @param string $label The label to use in log messages
     * @return void
     */
    public static function setLabel(string $label): void {
        self::getInstance()::$label = $label;
    }

    /**
     * Return the currently configured location for the log file.
     *
     * @return string The filesystem path and filename
     */
    public static function getFilename(): string {
        return self::getInstance()::$filename;
    }

    /**
     * Set the log file location. Supply a valid filesystem path and filename;
     * the caller must have write permissions to this file.
     *
     * @param string $filename The filesystem path and filename
     * @return void
     */
    public static function setFilename(string $filename): void {
        self::getInstance()::$filename = $filename;
    }

    /**
     * Return the currently configured date format string.
     *
     * @return string The date format string
     */
    public static function getDateFormat(): string {
        return self::getInstance()::$dateFormat;
    }

    /**
     * Set the date format to use when stamping log entries.
     *
     * @param string $dateFormat The date format string
     * @return void
     * @see https://secure.php.net/manual/en/function.date.php PHP date formats
     */
    public static function setDateFormat(string $dateFormat): void {
        self::getInstance()::$dateFormat = $dateFormat;
    }

    /**
     * Set the severity filter level. Log messages with a lower severity level
     * will not be written to the file. Supply one of the Logger::SEVERITY_
     * constants defined in this class.
     *
     * @param int $severityFilter The severity level at which to filter.
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function setSeverityFilter(int $severityFilter): void {
        if (!in_array($severityFilter, self::getValidSeverities())) {
            throw new \InvalidArgumentException(
                    'Argument to Logger::setSeverityFilter() MUST be one of the '
                    . 'following: Logger::'
                    . join(', Logger::', array_keys(self::getValidSeverities()))
            );
        }
        self::getInstance()::$severityFilter = $severityFilter;
    }

    /**
     * Set whether or not to strip newlines out of log entries. The default is
     * true, which reduces clutter and facilitates skimming the log, but if
     * you're logging queries, it can make them harder to read.
     *
     * @param bool $collapseEntries Whether or not to strip newlines
     * @return void
     */
    public static function setCollapseEntries(bool $collapseEntries = true): void {
        self::$collapseEntries = $collapseEntries;
    }

    /**
     * A convenience method to write a Logger::SEVERITY_DEBUG log message.
     *
     * @param string $message The log entry to write
     * @param bool $echo Whether to copy the log message to stdout
     * @return void
     */
    public static function debug(string $message, bool $echo = false): void {
        self::getInstance()->writeLogEntry($message, self::SEVERITY_DEBUG, $echo);
    }

    /**
     * A convenience method to write a Logger::SEVERITY_INFO log message.
     *
     * @param string $message The log entry to write
     * @param bool $echo Whether to copy the log message to stdout
     * @return void
     */
    public static function info(string $message, bool $echo = false): void {
        self::getInstance()->writeLogEntry($message, self::SEVERITY_INFO, $echo);
    }

    /**
     * A convenience method to write a Logger::SEVERITY_WARNING log message.
     *
     * @param string $message The log entry to write
     * @param bool $echo Whether to copy the log message to stdout
     * @return void
     */
    public static function warning(string $message, bool $echo = false): void {
        self::getInstance()->writeLogEntry($message, self::SEVERITY_WARNING,
                $echo);
    }

    /**
     * A convenience method to write a Logger::SEVERITY_ERROR log message.
     *
     * @param string $message The log entry to write
     * @param bool $echo Whether to copy the log message to stdout
     * @return void
     */
    public static function error(string $message, bool $echo = false): void {
        self::getInstance()->writeLogEntry($message, self::SEVERITY_ERROR, $echo);
    }

    /**
     * Write an entry to the log file. The convenience methods should be used
     * instead of calling this method directly.
     *
     * If the supplied $severity value is less than the configured value of
     * self::$severityFilter, the log message will be disregarded.
     *
     * If $echo is true, the log message will be printed to stdout (CLI) or the
     * output buffer (web) in addition to being logged.
     *
     * @param string $message The log entry
     * @param int $severity The severity level of this log entry
     * @param bool $echo Whether to copy the message to stdout
     * @return void
     * @throws \Exception
     */
    protected function writeLogEntry(string $message, int $severity,
            bool $echo = false): void {

        /* Bail if this message isn't severe enough to log */
        if ($severity < self::$severityFilter) {
            return;
        }

        /* Attempt to open the file if we haven't already done so */
        if (is_null(self::getInstance()->fp) || !file_exists(self::$filename)) {
            if (!self::getInstance()->fp = @fopen(self::$filename, 'a')) {
                throw new \Exception('Unable to open log file for writing. '
                        . 'Please check the permissions on ' . self::$filename);
            }
        }

        /* Set the message preface */
        switch ($severity) {
            case self::SEVERITY_ERROR:
                $preface = 'ERROR';
                break;
            case self::SEVERITY_WARNING:
                $preface = ' WARN';
                break;
            case self::SEVERITY_INFO:
                $preface = ' INFO';
                break;
            case self::SEVERITY_DEBUG:
                $preface = 'DEBUG';
                break;
            default:
                $preface = '  WTF';
                break;
        }

        /* Strip newlines from the log message if desired */
        if (self::$collapseEntries) {
            $message = preg_replace('|[\r\n]|s', ' ', $message);
        }

        /* Prepend metadata and write the message to disk */
        $message = '[' . (new \DateTime('now'))->format(self::$dateFormat)
                . "] {$preface}: " . self::$label . ": {$message}"
                . PHP_EOL;

        @fwrite(self::getInstance()->fp, $message);

        if ($echo === true) {
            echo $message;
        }
    }

    /**
     * Truncate the log file.
     *
     * @return void
     * @throws \Exception
     */
    public static function truncate(): void {
        self::getInstance()->fp = null;
        if (!self::getInstance()->fp = fopen(self::$filename, 'w+')) {
            throw new \Exception('Unable to open log file for writing. '
                    . 'Please check the permissions on ' . self::$filename);
        }
        self::getInstance()->fp = null;
    }

    /**
     * Return an array of eligible parameter values for setSeverityFilter().
     *
     * @return array The Logger::SEVERITY_ constants and their values
     */
    public static function getValidSeverities(): array {
        return array_filter((new \ReflectionClass(__CLASS__))->getConstants(),
                function($value, $key) {
            return (strpos($key, 'SEVERITY_') === 0);
        }, ARRAY_FILTER_USE_BOTH);
    }

}
