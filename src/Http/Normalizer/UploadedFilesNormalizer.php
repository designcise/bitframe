<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Http\Normalizer;

use Psr\Http\Message\{
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    StreamInterface,
    UploadedFileInterface
};
use InvalidArgumentException;

use function is_array;

/**
 * Normalize uploaded files.
 */
final class UploadedFilesNormalizer
{
    public function __construct(
        private readonly StreamFactoryInterface
        & UploadedFileFactoryInterface $factory,
    ) {}

    /**
     * Transforms each value into an `UploadedFile` instance, and ensures that nested
     * arrays are normalized.
     *
     * @param array $files
     *
     * @return UploadedFileInterface[]
     *
     * @throws InvalidArgumentException
     */
    public function normalize(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = (isset($value['tmp_name']))
                    ? self::createUploadedFileFromSpec($value)
                    : self::normalize($value);
                continue;
            }

            throw new InvalidArgumentException('Invalid value in files specification');
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a `$_FILES` specification.
     *
     * If the specification represents an array of values, this method will loop
     * through all nested files and return a normalized array of `UploadedFileInterface`
     * instances.
     *
     * @param array $files `$_FILES` struct.
     *
     * @return UploadedFileInterface[]|UploadedFileInterface
     */
    private function createUploadedFileFromSpec(array $files): array|UploadedFileInterface
    {
        if (is_array($files['tmp_name'])) {
            $normalizedFiles = [];

            foreach ($files['tmp_name'] as $key => $file) {
                $normalizedFiles[$key] = self::createUploadedFileFromSpec([
                    'tmp_name' => $files['tmp_name'][$key],
                    'size' => $files['size'][$key],
                    'error' => $files['error'][$key],
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                ]);
            }

            return $normalizedFiles;
        }

        $stream = ($files['tmp_name'] instanceof StreamInterface)
            ? $files['tmp_name']
            : $this->factory->createStreamFromFile($files['tmp_name'], 'r+');

        return $this->factory->createUploadedFile(
            $stream,
            $files['size'],
            (int) $files['error'],
            $files['name'],
            $files['type']
        );
    }
}
