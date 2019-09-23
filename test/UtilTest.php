<?php

use SplunkTracingBase\Client\Util;

class UtilTest extends BaseSplunkTracingTest {

    public function testHexToDec() {
        $util = new Util();
        for ($x = 0; $x <= 100; $x++) {
            $uuidStr = $util->_generateUUIDString();
            $uuidInt = Util::hexdec($uuidStr);
            $this->assertTrue($uuidInt > 0);
            $this->assertEquals($uuidStr, Util::dechex($uuidInt));
        }
    }
}