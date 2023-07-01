<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2022 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Test\Http;

use BitFrame\Http\Normalizer\UploadedFilesNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use BitFrame\Factory\HttpFactory;

/**
 * @covers \BitFrame\Http\Normalizer\UploadedFilesNormalizer
 */
class UploadedFilesNormalizerTest extends TestCase
{
    /** @var string */
    private const ASSETS_DIR = __DIR__ . '/../../Asset/';

    private UploadedFilesNormalizer $normalizer;

    public function setUp(): void
    {
        $factory = HttpFactory::getFactory();
        $this->normalizer = new UploadedFilesNormalizer($factory);
    }

    public function testNormalizeUploadedFiles(): void
    {
        $stream = HttpFactory::createStreamFromFile('php://temp');
        $files = [
            'files' => [
                'tmp_name' => $stream,
                'size' => 0,
                'error' => 0,
                'name' => 'foo.bar',
                'type' => 'text/plain',
            ]
        ];

        $normalized = $this->normalizer->normalize($files);

        $expectedFiles = [
            'files' => HttpFactory::createUploadedFile($stream, 0, 0, 'foo.bar', 'text/plain')
        ];

        $this->assertEquals($expectedFiles, $normalized);
    }

    public function testAddUploadedFileFromFileSpecification(): void
    {
        $files = [
            'logo' => [
                'tmp_name' => self::ASSETS_DIR . 'logo.png',
                'name' => 'bitframe-logo.png',
                'size' => 8316,
                'type' => 'image/png',
                'error' => 0,
            ],
        ];

        $normalized = $this->normalizer->normalize($files);

        $this->assertCount(1, $normalized);
        $this->assertInstanceOf(UploadedFileInterface::class, $normalized['logo']);
        $this->assertEquals('bitframe-logo.png', $normalized['logo']->getClientFilename());
    }

    public function testTraversesNestedFileSpecificationToExtractUploadedFile(): void
    {
        $files = [
            'my-form' => [
                'details' => [
                    'logo' => [
                        'tmp_name' => self::ASSETS_DIR . 'logo.png',
                        'name' => 'bitframe-logo.png',
                        'size' => 8316,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],
        ];

        $normalized = $this->normalizer->normalize($files);

        $this->assertCount(1, $normalized);
        $this->assertEquals('bitframe-logo.png', $normalized['my-form']['details']['logo']->getClientFilename());
    }

    public function testTraversesNestedFileSpecificationContainingNumericIndicesToExtractUploadedFiles(): void
    {
        $files = [
            'my-form' => [
                'details' => [
                    'avatars' => [
                        'tmp_name' => [
                            0 => self::ASSETS_DIR . 'logo.png',
                            1 => self::ASSETS_DIR . 'logo-1.png',
                            2 => self::ASSETS_DIR . 'logo-2.png',
                        ],
                        'name' => [
                            0 => 'file1.txt',
                            1 => 'file2.txt',
                            2 => 'file3.txt',
                        ],
                        'size' => [
                            0 => 100,
                            1 => 240,
                            2 => 750,
                        ],
                        'type' => [
                            0 => 'plain/txt',
                            1 => 'image/jpg',
                            2 => 'image/png',
                        ],
                        'error' => [
                            0 => 0,
                            1 => 0,
                            2 => 0,
                        ],
                    ],
                ],
            ],
        ];

        $normalized = $this->normalizer->normalize($files);

        $this->assertCount(3, $normalized['my-form']['details']['avatars']);
        $this->assertEquals('file1.txt', $normalized['my-form']['details']['avatars'][0]->getClientFilename());
        $this->assertEquals('file2.txt', $normalized['my-form']['details']['avatars'][1]->getClientFilename());
        $this->assertEquals('file3.txt', $normalized['my-form']['details']['avatars'][2]->getClientFilename());
    }

    public function testInvalidNestedFileSpecShouldThrowException(): void
    {
        $files = [
            'test' => false,
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->normalizer->normalize($files);
    }

    public function testEmptyFileSpec(): void
    {
        $files = [];

        $normalized = $this->normalizer->normalize($files);

        $this->assertSame([], $normalized);
    }

    public function testCanAddAlreadyNormalizedUploadedFileSpec(): void
    {
        $stream = HttpFactory::createStreamFromFile('php://temp');
        $uploadedFile = HttpFactory::createUploadedFile($stream, 0, 0, 'foo.bar', 'text/plain');

        $files = [
            'foo_bar' => $uploadedFile,
        ];

        $normalized = $this->normalizer->normalize($files);

        $this->assertCount(1, $normalized);
        $this->assertInstanceOf(UploadedFileInterface::class, $normalized['foo_bar']);
    }
}
