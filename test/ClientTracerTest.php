<?php

use SplunkTracingBase\Client\ClientTracer;
use SplunkTracingBase\Client\Transports\TransportHTTPJSON;
use SplunkTracingBase\Client\Transports\TransportHTTPPROTO;
use SplunkTracingBase\Client\Transports\TransportUDP;

class ClientTracerTest extends BaseSplunkTracingTest
{

    /**
     * @dataProvider transports
     */
    public function testCorrectTransportSelected($key, $class)
    {

        $tracer = new ClientTracer(['transport' => $key]);

        $this->assertInstanceOf($class, $this->readAttribute($tracer, '_transport'));
    }

    public function transports()
    {

        return [
            'udp' => ['udp', TransportUDP::class],
            'http_json' => ['http_json', TransportHTTPJSON::class],
            'http_proto' => ['http_proto', TransportHTTPPROTO::class],
        ];
    }
}
