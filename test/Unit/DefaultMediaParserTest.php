<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Unit;

use PHPUnit\Framework\TestCase;
use BitFrame\Parser\DefaultMediaParser;

/**
 * @covers \BitFrame\Http\Message\FileResponse
 */
class DefaultMediaParserTest extends TestCase
{
    public function validInputProvider(): array
    {
        return [
            'empty_str' => ['', []],
            'single_str_with_no_value' => ['foo', ['foo' => '']],
            'query_str' => [
                'first=value&arr[]=foo+bar&arr[]=baz',
                [
                    'first' => 'value',
                    'arr' => ['foo bar', 'baz']
                ],
            ],
            'mangled_name' => [
                'My Value=Something',
                ['My_Value' => 'Something']
            ],
        ];
    }

    /**
     * @dataProvider validInputProvider
     *
     * @param string $actual
     * @param array $expected
     */
    public function testCanParse(string $actual, array $expected): void
    {
        $parser = new DefaultMediaParser();
        $this->assertSame($expected, $parser->parse($actual));
    }
}
