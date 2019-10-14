<?php

namespace Oddvalue\BackpackMediaLibrary;

class ExampleTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testTrueIsTrue()
    {
        $media = new Media;
        $this->assertEquals('test', $media->echoPhrase('test'));
    }
}
