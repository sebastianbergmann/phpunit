<?php declare(strict_types=1);
/**
 * Determines the point in time that is used for the timestamp of the files
 * in the PHAR as well as for the timestamp of the SBOM in the PHAR.
 *
 * Release builds fail when this point in time cannot be determined from the
 * SOURCE_DATE_EPOCH environment variable or from the most recent annotated tag.
 *
 * @return array{epoch: int, reason: string}
 */
function sourceDateEpoch(string $type): array
{
    if (is_string(getenv('SOURCE_DATE_EPOCH'))) {
        return [
            'epoch'  => (int) getenv('SOURCE_DATE_EPOCH'),
            'reason' => 'based on environment variable SOURCE_DATE_EPOCH',
        ];
    }

    $tag = @shell_exec('git describe --abbrev=0 2>&1');

    if (is_string($tag) && strpos($tag, 'fatal') === false) {
        $tag  = trim($tag);
        $time = @shell_exec('git log -1 --format=%at ' . $tag . ' 2>&1');

        if (is_string($time) && is_numeric(trim($time))) {
            return [
                'epoch'  => (int) trim($time),
                'reason' => sprintf('based on when tag %s was created', $tag),
            ];
        }
    }

    if ($type === 'release') {
        fwrite(
            STDERR,
            'Cannot determine timestamp for files in PHAR and for SBOM: no annotated tag found and SOURCE_DATE_EPOCH is not set' . PHP_EOL
        );

        exit(1);
    }

    return [
        'epoch'  => time(),
        'reason' => 'based on current time',
    ];
}
