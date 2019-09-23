<?php

class SplunkTracingTest extends BaseSplunkTracingTest {

    public function testGetInstance() {
        $inst = SplunkTracing::getInstance("test_group", "1234567890");
        $this->assertInstanceOf("\SplunkTracingBase\Client\ClientTracer", $inst);

        // Is it really a singleton?
        $inst2 = SplunkTracing::getInstance("test_group", "1234567890");
        $this->assertSame($inst, $inst2);
    }
}
