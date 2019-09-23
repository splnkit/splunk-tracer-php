<?php

namespace SplunkTracingBase\Client;

/**
 * Class LogRecord Encapsulates the fields of a log message.
 * @package SplunkTracingBase\Client
 */
class LogRecord
{
    protected $_fields = NULL;
    private $_util = NULL;

    /**
     * LogRecord constructor.
     * @param array $fields
     */
    public function __construct($fields) {
        $this->_fields = $fields;
        $this->_util = new Util();
    }
}