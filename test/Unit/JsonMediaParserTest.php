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
use BitFrame\Parser\JsonMediaParser;
use JsonException;

/**
 * @covers \BitFrame\Parser\JsonMediaParser
 */
class JsonMediaParserTest extends TestCase
{
    public function validInputProvider(): array
    {
        return [
            'empty_json' => ['{}', []],
            'basic_json' => [
                '{"name":"John", "age":30, "car":null}', [
                    'name' => 'John',
                    'age' => 30,
                    'car' => null,
                ]
            ],
            'json_array' => [
                '{"name":"John", "age":30, "cars":[ "Ford", "BMW", "Fiat" ]}', [
                    'name' => 'John',
                    'age' => 30,
                    'cars' => ['Ford', 'BMW', 'Fiat'],
                ]
            ],
            'empty_key' => ['{ "": { "foo": "" } }', ['' => ['foo' => '']]],
            'empty_key_value' => ['{ "": { "": "" } }', ['' => ['' => '']]],
        ];
    }

    /**
     * @dataProvider validInputProvider
     *
     * @param string $input
     * @param array $expected
     *
     * @throws JsonException
     */
    public function testCanParse(string $input, array $expected): void
    {
        $parser = new JsonMediaParser();
        $this->assertEquals($expected, $parser->parse($input));
    }

    public function invalidInputProvider(): array
    {
        return [
            'empty_str' => [''],
            'random_str' => ['foo'],
            'single_quotes' => ["{ 'bar': 'baz' }"],
            'missing_double_quotes_for_key' => ['{ bar: "baz" }'],
            'trailing_comma' => ['{ bar: "baz", }'],
        ];
    }

    /**
     * @dataProvider invalidInputProvider
     *
     * @param string $input
     *
     * @throws JsonException
     */
    public function testShouldThrowExceptionForInvalidInput(string $input): void
    {
        $parser = new JsonMediaParser();

        $this->expectException(JsonException::class);

        $this->assertTrue( $parser->parse($input));
    }

    /**
     * @throws JsonException
     */
    public function testShouldParseBigIntAsString()
    {
        $parser = new JsonMediaParser();
        $this->assertEquals([
            'number' => '12345678901234567890'],
            $parser->parse('{"number": 12345678901234567890}')
        );
    }
}
