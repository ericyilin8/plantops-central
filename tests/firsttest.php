<?php
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase {
    public function test_math_works() {
        $this->assertEquals(4, 2 + 2);
    }
}