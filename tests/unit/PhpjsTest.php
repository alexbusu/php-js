<?php

declare(strict_types=1);

namespace Alexbusu\Tests\Unit;

use Alexbusu\Phpjs;
use PHPUnit\Framework\TestCase;

class PhpjsTest extends TestCase
{
    public function test__construct(): void
    {
        $sut = new Phpjs(['debug' => true]);

        $this->assertTrue($sut::$debug);

        $sut = new Phpjs(['debug' => false]);

        $this->assertFalse($sut::$debug);
    }

    public function testToArray()
    {
        $sut = new Phpjs(['debug' => false, 'version' => 2]);

        $this->assertSame(['_' => []], $sut->toArray());
    }

}
