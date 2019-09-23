<?php

namespace SplunkTracingBase\Client;


/**
 * Class KeyValue is a simple key/value pairing.
 * @package SplunkTracingBase\Client
 */
class KeyValue
{
    protected $_key = "";
    protected $_value = "";

    /**
     * KeyValue constructor.
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value) {
        $this->_key = $key;
        $this->_value = $value;
    }

    /**
     * @return string The key.
     */
    public function getKey() {
        return $this->_key;
    }

    /**
     * @return string The value.
     */
    public function getValue() {
        return $this->_value;
    }
}