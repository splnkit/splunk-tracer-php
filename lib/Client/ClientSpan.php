<?php
namespace SplunkTracingBase\Client;

use SplunkTracingBase\Tracer;

require_once(dirname(__FILE__) . "/Util.php");

class ClientSpan implements \SplunkTracingBase\Span {

    protected $_tracer = NULL;

    protected $_guid = "";
    protected $_traceGUID = "";
    protected $_parentGUID = NULL;
    protected $_operation = "";
    protected $_tags = [];
    protected $_baggage = [];
    protected $_startMicros = 0;
    protected $_endMicros = 0;
    protected $_errorFlag = false;
    protected $_runtimeGUID = "";

    protected $_joinIds = [];
    protected $_logRecords = [];

    protected $maxPayloadDepth = 0;

    public function __construct($tracer, $maxPayloadDepth) {
        $this->_tracer = $tracer;
        $this->_traceGUID = $tracer->_generateUUIDString();
        $this->_guid = $tracer->_generateUUIDString();
        $this->$maxPayloadDepth = $maxPayloadDepth;
    }

    public function __destruct() {
        // Use $_endMicros as a indicator this span has not been finished
        if ($this->_endMicros == 0) {
            $this->warnf("finish() never closed on span (operaton='%s')", $this->_operation, $this->_joinIds);
            $this->finish();
        }
    }

    public function tracer() {
        return $this->_tracer;
    }

    public function guid() {
        return $this->_guid;
    }

    public function setRuntimeGUID($guid) {
        $this->_runtimeGUID = $guid;
    }

    public function traceGUID() {
        return $this->_traceGUID;
    }

    public function setTraceGUID($guid) {
        $this->_traceGUID = $guid;
        return $this;
    }

    public function setStartMicros($start) {
        $this->_startMicros = $start;
        return $this;
    }

    public function setEndMicros($start) {
        $this->_endMicros = $start;
        return $this;
    }

    public function finish() {
        $this->_tracer->_finishSpan($this);
    }

    public function setOperationName($name) {
        $this->_operation = $name;
        return $this;
    }

    public function addTraceJoinId($key, $value) {
        $this->_joinIds[$key] = $value;
        return $this;
    }

    public function setEndUserId($id) {
        $this->addTraceJoinId(SPLUNK_JOIN_KEY_END_USER_ID, $id);
        return $this;
    }

    public function setTag($key, $value) {
        $this->_tags[$key] = $value;
        return $this;
    }

    public function setTags($tags){
      foreach ($tags as $key => $value){
        $this->_tags[$key] = $value;
      }
    }

    public function setBaggageItem($key, $value) {
        $this->_baggage[$key] = $value;
        return $this;
    }

    public function getBaggageItem($key) {
        return $this->_baggage[$key];
    }

    public function getBaggage() {
        return $this->_baggage;
    }

    public function setParent($span) {
        // Inherit any join IDs from the parent that have not been explicitly
        // set on the child
        foreach ($span->_joinIds as $key => $value) {
            if (!array_key_exists($key, $this->_joinIds)) {
                $this->_joinIds[$key] = $value;
            }
        }

        $this->_traceGUID = $span->_traceGUID;
        // $this->setTag("parent_span_guid", $span->guid());
        $this->_parentGUID = $span->guid();
        return $this;
    }

    public function setParentGUID($guid) {
        $this->_parentGUID = $guid;
        return $this;
    }

    public function getParentGUID() {
        return $this->_parentGUID;
    }

    public function logEvent($event, $payload = NULL) {
        $this->log([
            'event' => strval($event),
            'payload' => $payload,
        ]);
    }

    public function log($fields) {
        $record = [];
        $payload = NULL;

        if (!empty($fields['event'])) {
            $record['event'] = strval($fields['event']);
        }

        if (!empty($fields['timestamp'])) {
            $record['timestamp_micros'] = intval(1000 * $fields['timestamp']);
        }
        // no need to verify value of fields['payload'] as it will be checked by _rawLogRecord
        $this->_rawLogRecord($record, $fields['payload']);
    }

    public function infof($fmt) {
        $this->_log('I', false, $fmt, func_get_args());
        return $this;
    }

    public function warnf($fmt) {
        $this->_log('W', false, $fmt, func_get_args());
        return $this;
    }

    public function errorf($fmt) {
        $this->_errorFlag = true;
        $this->_log('E', true, $fmt, func_get_args());
        return $this;
    }

    public function fatalf($fmt) {
        $this->_errorFlag = true;
        $text = $this->_log('F', true, $fmt, func_get_args());
        die($text);
    }

    protected function _log($level, $errorFlag, $fmt, $allArgs) {
        // The $allArgs variable contains the $fmt string
        array_shift($allArgs);
        $text = vsprintf($fmt, $allArgs);

        $this->_rawLogRecord([
            'level' => $level,
            'error_flag' => $errorFlag,
            'message' => $text,
        ], $allArgs);
        return $text;
    }

    /**
     * Internal use only.
     */
    public function _rawLogRecord($fields, $payloadArray) {

        if (empty($fields['timestamp_micros'])) {
            $fields['timestamp_micros'] = intval(Util::nowMicros());
        }

        // TODO: data scrubbing and size limiting
        if (!empty($payloadArray)) {
            // $json == FALSE on failure
            //
            // Examples that will cause failure:
            // - "Resources" (e.g. file handles)
            // - Circular references
            // - Exceeding the max depth (i.e. it *does not* trim, it rejects)
            //
            $json = json_encode($payloadArray, 0, $this->maxPayloadDepth);
            if (is_string($json)) {
                $fields["payload_json"] = $json;
            }
        }

        $rec = new LogRecord($fields);
        $this->_logRecords[] = $rec;
    }


    /**
     * @return Span A Proto representation of this object.
     */
    public function toJSON($runtime) {
        $reportObjs = [];
        $spanContext = array('event' => array(
            'runtime_guid' => $this->_runtimeGUID,
            'trace_id' => $this->traceGUID(),
            'span_id' => $this->guid(),
            'operation_name' => strval($this->_operation),
            'timestamp' => $this->_startMicros / 1000000,
            'tags' => $this->_tags,
            'duration' => $this->_endMicros-$this->_startMicros,
            'baggage' => $this->_baggage,
            'component_name' => $runtime->getGroupName(),
            'parent_span_id' => $this->_parentGUID,
            'tracer_platform' => $runtime->getAttr("tracer_platform"),
            'tracer_platform_version' => $runtime->getAttr("tracer_platform_version"),
            'tracer_version'  => $runtime->getAttr("tracer_version"),
            'device'  => $runtime->getAttr("device"),
        ),
        'time' => $this->_startMicros / 1000000,
        'sourcetype' => 'splunktracing:span',
        );
        $reportObjs[] = json_encode($spanContext);
        $logContext = $spanContext["event"];
        unset($logContext["timestamp"]);
        unset($logContext["duration"]);
        foreach ($this->_logRecords as $lR) {
            $logObj = array('event' => $logContext,
                'time' => $lR->getField("timestamp_micros") / 1000000,
                'sourcetype' => 'splunktracing:log',
            );    
            $logObj["event"]["timestamp"] = $lR->getField("timestamp_micros") / 1000000;
            $logObj["event"]["fields"] = $lR->getFields();
            $reportObjs[] = json_encode($logObj);;
        }
        return "\n".join($reportObjs);
    }
}
