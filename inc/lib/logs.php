<?php
/**
 * Class logger
 * Write log in file or standart out
 */
class CLogger
{
    //--- agent name
    private static $m_agent = '';
    //--- is write log
    private static $m_is_write_log = true;
    //--- file path when write file
    private static $m_file_path = '';
    //--- log file prefix
    private static $m_file_prefix = '';
    //--- what logs write, default write all logs
    private static $m_status_write;

    /**
     * @param string $agent - agent name
     * @param bool $is_write_log - is write any logs
     * @param string $file_path - file path, if not null all logs write in files
     * @param string $file_prefix - log file prefix
     */
    public static function Init($agent, $is_write_log = true, $file_path = '/data/tmp/', $file_prefix = '')
    {
        //--- set first data
        self::$m_agent = $agent;
        self::$m_is_write_log = $is_write_log;
        self::$m_file_prefix = $file_prefix;
        //---
        if (!empty($file_path)) {
            self::setFilePath($file_path);
        }
        //--- write only error logs
        self::$m_status_write = CLoggerType::ERROR | CLoggerType::DEBUG;
    }

    /**
     * Function write message to file or standart output
     * @param int $type - type of error
     * @param string $message
     * @return void
     */
    public static function write($type, $message)
    {
        if (!self::$m_is_write_log) return;
        if (!(self::$m_status_write & $type)) return;
        //---
        $str = date("Y.m.d H:i:s", time()) . "\t" . $type . "\t" . self::$m_agent . ':' . $message . "\r\n";
        //-- need write to file or standart output

        if (!empty(self::$m_file_path)) {
            $filename = self::$m_file_path . self::$m_file_prefix . date('Y_m_d', time()) . '.log';
            error_log((string)$str, 3, $filename);
        } else echo $str, "\r\n";
    }

    /**
     * Set new agent name
     * @param string $new_agent
     * @return void
     */
    public static function setAgent($new_agent)
    {
        self::$m_agent = $new_agent;
    }

    /**
     * Get current agent
     * @return string
     */
    public static function getAgent()
    {
        return self::$m_agent;
    }

    /**
     * Set flag is write log
     * @param bool $is_write_log
     * @return void
     */
    public static function setIsWriteLog($is_write_log)
    {
        self::$m_is_write_log = $is_write_log;
    }

    /**
     * Get flag is write log
     * @return bool
     */
    public static function getIsWriteLog()
    {
        return self::$m_is_write_log;
    }

    /**
     * Set file path where write logs
     * @param string $file_path
     * @return void
     */
    public static function setFilePath($file_path)
    {
        self::$m_file_path = $file_path;
        $last_symbol = self::$m_file_path[strlen(self::$m_file_path) - 1];
        if ($last_symbol != '/' && $last_symbol != '\\') self::$m_file_path .= '/';
        //--- create logs path
        if (!file_exists(self::$m_file_path)) mkdir(self::$m_file_path, 0777, true);
    }

    /**
     * Get current file path
     * @return string
     */
    public static function getFilePath()
    {
        return self::$m_file_path;
    }

    /**
     * Log files prefix
     * @param string $file_prefix
     * @return void
     */
    public static function setFilePrefix($file_prefix)
    {
        self::$m_file_prefix = $file_prefix;
    }

    /**
     * Get current log files prefix
     * @return string
     */
    public static function getFilePrefix()
    {
        return self::$m_file_prefix;
    }

    /**
     * Set or unset flag write MTLoggerType::DEBUG logs
     * @param bool $is_write
     * @return void
     */
    public static function setWriteDebug($is_write)
    {
        if ($is_write) self::$m_status_write |= CLoggerType::DEBUG;
        else           self::$m_status_write &= ~CLoggerType::DEBUG;
    }
}

/**
 * Type of log
 */
class CLoggerType
{
    const DEBUG = 1;
    const ERROR = 2;
}

?>