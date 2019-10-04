<?php

namespace SplunkTracingBase\Client;

/**
 * Class ReportRequest encapsulates all of the information required to make an RPC call to the SplunkTracing satellite.
 * @package SplunkTracingBase\Client
 */
class ReportRequest
{
    protected $_runtime = NULL;
    protected $_reportStartTime = 0;
    protected $_now = 0;
    protected $_spanRecords = NULL;
    protected $_counters = NULL;

    /**
     * ReportRequest constructor.
     * @param Runtime $runtime
     * @param int $reportStartTime
     * @param int $now
     * @param array $spanRecords
     * @param array $counters
     */
    public function __construct($runtime, $reportStartTime, $now, $spanRecords, $counters) {
        $this->_runtime = $runtime;
        $this->_reportStartTime = $reportStartTime;
        $this->_now = $now;
        $this->_spanRecords = $spanRecords;
        $this->_counters = $counters;
    }

    /**
     * @param Auth $auth
     * @return JSONReportRequest A JSON representation of this object.
     */
    public function toJSON() {

        $spans = [];
        foreach ($this->_spanRecords as $sr) {
            // $spans[] = $sr->toJSON($this->_runtime);
            array_push($spans, $sr->toJSON($this->_runtime));

        }
        return $spans;
    }
}