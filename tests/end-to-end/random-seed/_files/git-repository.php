<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Creates a Git repository in the repository directory of $directory, with
 * an empty commit whose hash, e2d56a3774bb906e688e542595528f9cce3a6872, does
 * not depend on who makes it and when, nor on the Git configuration of the
 * system and of the user.
 */
function createGitRepositoryWithOneCommit(string $directory): void
{
    removeDirectory($directory);

    \mkdir($directory . \DIRECTORY_SEPARATOR . 'repository', 0o777, true);
    \file_put_contents($directory . \DIRECTORY_SEPARATOR . 'gitconfig', '');

    \putenv('GIT_DIR');
    \putenv('GIT_WORK_TREE');
    \putenv('GIT_CONFIG_NOSYSTEM=1');
    \putenv('GIT_CONFIG_GLOBAL=' . $directory . \DIRECTORY_SEPARATOR . 'gitconfig');
    \putenv('GIT_AUTHOR_NAME=PHPUnit');
    \putenv('GIT_AUTHOR_EMAIL=phpunit@example.com');
    \putenv('GIT_AUTHOR_DATE=2000-01-01T00:00:00+0000');
    \putenv('GIT_COMMITTER_NAME=PHPUnit');
    \putenv('GIT_COMMITTER_EMAIL=phpunit@example.com');
    \putenv('GIT_COMMITTER_DATE=2000-01-01T00:00:00+0000');

    runGit($directory, ['init', '--quiet', 'repository']);
    runGit($directory . \DIRECTORY_SEPARATOR . 'repository', ['commit', '--quiet', '--allow-empty', '--message', 'commit']);
}

/**
 * @param list<string> $arguments
 */
function runGit(string $workingDirectory, array $arguments): void
{
    $process = \proc_open(['git', ...$arguments], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $workingDirectory);

    \stream_get_contents($pipes[1]);
    \stream_get_contents($pipes[2]);

    \fclose($pipes[1]);
    \fclose($pipes[2]);

    \proc_close($process);
}

function removeDirectory(string $directory): void
{
    if (!\is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $file) {
        if ($file->isDir() && !$file->isLink()) {
            \rmdir($file->getPathname());

            continue;
        }

        // the objects of a Git repository are read-only, which keeps them
        // from being deleted on Windows
        \chmod($file->getPathname(), 0o666);
        \unlink($file->getPathname());
    }

    \rmdir($directory);
}
