<?php

namespace VCR\CodeTransform;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VCR\CodeTransform\AbstractCodeTransform;

class AbstractCodeTransformTest extends TestCase
{
    /**
     * @param array $methods
     *
     * @return MockObject&AbstractCodeTransform
     */
    protected function getFilter(array $methods = []): MockObject
    {
        $defaults = array_merge(
            ['transformCode'],
            $methods
        );

        $filter = $this->getMockBuilder(AbstractCodeTransform::class)
            ->setMethods($defaults)
            ->getMockForAbstractClass();

        if (in_array('transformCode', $methods, true)) {
            $filter
                ->expects($this->once())
                ->method('transformCode')
                ->with($this->isType('string'))
                ->will($this->returnArgument(0));
        }

        return $filter;
    }

    public function testRegisterAlreadyRegistered(): void
    {
        $filter = $this->getFilter();
        $filter->register();

        $this->assertContains(AbstractCodeTransform::NAME, stream_get_filters(), 'First attempt to register failed.');

        $filter->register();

        $this->assertContains(AbstractCodeTransform::NAME, stream_get_filters(), 'Second attempt to register failed.');
    }
}
