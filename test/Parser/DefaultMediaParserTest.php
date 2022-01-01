<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Parser;

use PHPUnit\Framework\TestCase;
use BitFrame\Parser\DefaultMediaParser;

/**
 * @covers \BitFrame\Parser\DefaultMediaParser
 */
class DefaultMediaParserTest extends TestCase
{
    public function validInputProvider(): array
    {
        return [
            'empty str' => ['', []],
            'single str with no value' => ['foo', ['foo' => '']],
            'query str' => [
                'first=value&arr[]=foo+bar&arr[]=baz',
                [
                    'first' => 'value',
                    'arr' => ['foo bar', 'baz']
                ],
            ],
            'mangled name' => [
                'My Value=Something',
                ['My_Value' => 'Something']
            ],
        ];
    }

    /**
     * @dataProvider validInputProvider
     *
     * @param string $input
     * @param array $expected
     */
    public function testCanParse(string $input, array $expected): void
    {
        $parser = new DefaultMediaParser();
        $this->assertSame($expected, $parser->parse($input));
    }
}
