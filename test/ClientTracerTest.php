<?php

use SplunkTracingBase\Client\ClientTracer;
use SplunkTracingBase\Client\Transports\TransportHTTPJSON;

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
            'http_json' => ['http_json', TransportHTTPJSON::class],
        ];
    }
}
