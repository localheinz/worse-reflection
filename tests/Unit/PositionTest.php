<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Position;

class PositionTest extends TestCase
{
    /**
     * @testdox It provides width
     */
    public function testWidth()
    {
        $position = Position::fromFullStartStartAndEnd(10, 15, 35);
        $this->assertEquals(10, $position->fullStart());
        $this->assertEquals(15, $position->start());
        $this->assertEquals(35, $position->end());
        $this->assertEquals(20, $position->width());
        $this->assertEquals(25, $position->fullWidth());
    }
}
