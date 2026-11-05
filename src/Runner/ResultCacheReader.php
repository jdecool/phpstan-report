<?php

declare(strict_types=1);

namespace JDecool\PHPStanReport\Runner;

use RuntimeException;

final class ResultCacheReader
{
    private const SERIALIZED_FILE_PREFIX = '<?php return; ?>';
    private const KEPT_SECTIONS = ['errors', 'locallyIgnoredErrors', 'linesToIgnore'];
    private const CALLBACK_SECTIONS = ['errors', 'locallyIgnoredErrors'];

    /**
     * @return array<string, mixed>
     */
    public static function read(string $file): array
    {
        if (!file_exists($file)) {
            throw new RuntimeException("PHPStan result cache file '{$file}' not found.");
        }

        $handle = @fopen($file, 'r');
        if ($handle === false) {
            throw new RuntimeException("PHPStan result cache file '{$file}' could not be opened.");
        }

        if (rtrim((string) fgets($handle), "\n") !== self::SERIALIZED_FILE_PREFIX) {
            fclose($handle);

            $data = require $file;
            if (!is_array($data)) {
                throw new RuntimeException("PHPStan result cache file '{$file}' is not in a supported format.");
            }

            return $data;
        }

        try {
            return self::readFrames($handle, $file);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param resource $handle
     * @return array<string, mixed>
     */
    private static function readFrames($handle, string $file): array
    {
        $data = [];

        while (($header = fgets($handle)) !== false) {
            $header = rtrim($header, "\n");
            if ($header === '') {
                continue;
            }

            $parts = explode(' ', $header, 2);
            if (count($parts) !== 2) {
                throw new RuntimeException("Malformed frame header \"{$header}\" in PHPStan result cache file '{$file}'.");
            }

            [$name, $size] = $parts;

            if (!str_ends_with($name, '*')) {
                $data[$name] = self::readFrame($handle, (int) $size, $file);

                continue;
            }

            $name = substr($name, 0, -1);
            $count = (int) $size;

            if (!in_array($name, self::KEPT_SECTIONS, true)) {
                self::skipEntryFrames($handle, $count, $file);

                continue;
            }

            $entries = self::readEntryFrames($handle, $count, $file);
            if (in_array($name, self::CALLBACK_SECTIONS, true)) {
                $data["{$name}Callback"] = static fn(): array => $entries;

                continue;
            }

            $data[$name] = $entries;
        }

        foreach (self::CALLBACK_SECTIONS as $name) {
            $data["{$name}Callback"] ??= static fn(): array => [];
        }

        $data['linesToIgnore'] ??= [];

        return $data;
    }

    /**
     * Since phpstan/phpstan 2.3.5, an entry header holds "<keyLength> <valueLength>" and is followed by the
     * key and the value serialized separately. Before, it held one length for a `serialize([$key => $value])` blob.
     *
     * @param resource $handle
     * @return array<mixed>
     */
    private static function readEntryFrames($handle, int $count, string $file): array
    {
        $entries = [];

        for ($i = 0; $i < $count; $i++) {
            [$length, $valueLength] = self::readEntryHeader($handle, $file);

            if ($valueLength !== null) {
                $key = self::readFrame($handle, $length, $file);
                if (!is_int($key) && !is_string($key)) {
                    throw new RuntimeException("An entry key of PHPStan result cache file '{$file}' is neither an int nor a string.");
                }

                $entries[$key] = self::readFrame($handle, $valueLength, $file);

                continue;
            }

            $entry = self::readFrame($handle, $length, $file);
            if (!is_array($entry)) {
                throw new RuntimeException("An entry frame of PHPStan result cache file '{$file}' does not contain an array.");
            }

            foreach ($entry as $key => $value) {
                $entries[$key] = $value;
            }
        }

        return $entries;
    }

    /**
     * @param resource $handle
     */
    private static function skipEntryFrames($handle, int $count, string $file): void
    {
        for ($i = 0; $i < $count; $i++) {
            [$length, $valueLength] = self::readEntryHeader($handle, $file);

            // fseek() past the end of a file succeeds, so the position is what catches a truncated section.
            if (fseek($handle, $length + ($valueLength ?? 0), SEEK_CUR) !== 0 || ftell($handle) === false) {
                throw new RuntimeException("PHPStan result cache file '{$file}' is truncated.");
            }
        }
    }

    /**
     * @param resource $handle
     * @return array{int, ?int} the value length is null for the single-blob entries written before phpstan/phpstan 2.3.5
     */
    private static function readEntryHeader($handle, string $file): array
    {
        $header = fgets($handle);
        if ($header === false) {
            throw new RuntimeException("PHPStan result cache file '{$file}' ended before its last entry.");
        }

        $lengths = explode(' ', rtrim($header, "\n"));

        return [(int) $lengths[0], isset($lengths[1]) ? (int) $lengths[1] : null];
    }

    /**
     * @param resource $handle
     */
    private static function readFrame($handle, int $length, string $file): mixed
    {
        if ($length <= 0) {
            throw new RuntimeException("Frame length {$length} of PHPStan result cache file '{$file}' is not positive.");
        }

        $blob = fread($handle, $length);
        if ($blob === false || strlen($blob) !== $length) {
            throw new RuntimeException("PHPStan result cache file '{$file}' is truncated.");
        }

        $value = @unserialize($blob);
        if ($value === false && $blob !== serialize(false)) {
            throw new RuntimeException("A frame of PHPStan result cache file '{$file}' could not be unserialized.");
        }

        return $value;
    }
}
