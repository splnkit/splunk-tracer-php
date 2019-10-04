<?php
namespace SplunkTracingBase\Client;

require_once(dirname(__FILE__) . "/Util.php");

/**
 * Class Runtime encapsulates the data required to form a Runtime RPC object.
 * @package SplunkTracingBase\Client
 */
class Runtime
{
    protected $_guid = "";
    protected $_start_micros = 0;
    protected $_group_name = "";
    protected $_attrs = NULL;

    /**
     * Runtime constructor.
     * @param string $guid Unique identifier of the tracer.
     * @param int $start_micros Start time of the tracer.
     * @param string $group_name Name for the component.
     * @param array $attrs Additional attributes, like platform, version.
     */
    public function __construct($guid, $start_micros, $group_name, $attrs) {
        $this->_guid = $guid;
        $this->_start_micros = $start_micros;
        $this->_group_name = $group_name;
        $this->_attrs = $attrs;
    }

    public function getGroupName() {
        return $this->_group_name;
    }

    public function getAttr($attrKey) {
        return $this->_attrs[$attrKey];
    }

}