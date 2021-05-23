<?php

class Logger
{
    protected $_files;
    protected $_entries;
    protected $_start;
    protected $_end;

    protected function _sum($values)
    {
        $total = 0;

        foreach ($values as $value) {
            $total += $value;
        }

        return $total;
    }

    protected function _average($values)
    {
        return $this->_sum($values) / count($values);
    }

    public function __construct($options)
    {
        if (!isset($options["file"])) {
            throw new Exception("Log file invalid.");
        }

        $this->_file    = $options["file"];
        $this->_entries = [];
        $this->_start   = microtime();
    }

    public function log($message)
    {
        $this->_entries[] = [
            "message" => "[" . (new DateTime())->format("Y-m-d H:i:s") . "] " . $message,
            "time"    => microtime()
        ];
    }

    public function __destruct()
    {
        $messages = "";
        $last     = $this->_start;
        $times    = [];

        foreach ($this->_entries as $entry) {
            $messages .= $entry["message"] ."\n";
            $times[]   = $entry["time"] - $last;
            $last      = $entry["time"];
        }

        $messages .= "Average: " . $this->_average($times);
        $messages .= ", Longest: " . max($times);
        $messages .= ", Shortest: " . min($times);
        $messages .= ", Total: " . (microtime() - $this->_start);
        $messages .= "\n";

        file_put_contents($this->_file, $messages, FILE_APPEND);
    }
}
