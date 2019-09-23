<?php
namespace SplunkTracingBase\Client\Transports;

use SplunkTracingBase\Client\SystemLogger;
use Psr\Log\LoggerInterface;

class TransportHTTPJSON {

    protected $_host = '';
    protected $_port = 0;
    protected $_verbose = 0;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger = null) {

        $this->logger = $logger ?: new SystemLogger;
    }

    public function ensureConnection($options) {
        $this->_verbose = $options['verbose'];

        $this->_host = $options['collector_host'];
        $this->_port = $options['collector_port'];

        // The prefixed protocol is only needed for secure connections
        if ($options['collector_secure'] == True) {
            $this->_host = "ssl://" . $this->_host;
        }
    }

    public function flushReport($auth, $report) {
        if (is_null($auth) || is_null($report)) {
            if ($this->_verbose > 0) {
                $this->logger->error("Auth or report not set.");
            }
            return NULL;
        }

        $jsonReport = $report->toJSON();

        if ($this->_verbose >= 3) {
            $this->logger->debug('report contents:', $jsonReport);
        }

        $content = "\n".join($jsonReport);
        $content = gzencode($content);

        $header = "Host: " . $this->_host . "\r\n";
        $header .= "User-Agent: SplunkTracing-PHP\r\n";
        $header .= "Authorization: Splunk " . $auth->getAccessToken() . "\r\n";
        $header .= "Content-Type: application/json\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "Content-Encoding: gzip\r\n";
        $header .= "Connection: keep-alive\r\n\r\n";

        // Use a persistent connection when possible
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        // Use a persistent connection when possible
        $fp = @stream_socket_client($this->_host.":".$this->_port, $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            if ($this->_verbose > 0) {
                $this->logger->error($errstr);
            }
            return NULL;
        }
        @fwrite($fp, "POST /services/collector HTTP/1.1\r\n");
        @fwrite($fp, $header . $content);
        @fflush($fp);
        @fclose($fp);

        return NULL;
    }
}
