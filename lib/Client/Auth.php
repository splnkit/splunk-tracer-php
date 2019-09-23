<?php
namespace SplunkTracingBase\Client;

require_once(dirname(__FILE__) . "/Util.php");

/**
 * Class Auth encapsulates the data required to create an Auth object for RPC.
 * @package SplunkTracingBase\Client
 */
class Auth
{
    protected $_accessToken = "";

    /**
     * Auth constructor.
     * @param string $accessToken Identifier for a project, used to authenticate with SplunkTracing satellites.
     */
    public function __construct($accessToken) {
        $this->_accessToken = $accessToken;
    }

    /**
     * @return string The access token.
     */
    public function getAccessToken() {
        return $this->_accessToken;
    }

}