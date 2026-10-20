# Changelog

## 0.6.7 (2025-12-23)

Just in time for the holidays, we are happy to announce the release of `v0.6.7`! 🎄🎉

This is a compatibility release that contains backported features from the `0.7.x` branch.
Once v0.7 is released, it will be the way forward for this project.

*   Feature: Improve PHP 8.5+ support by replacing binary cast with string cast.
    (#119 by @kubawerlos and @WyriHaximus)

## 0.6.6 (2025-01-01)

This is a compatibility release that contains backported features from the `0.7.x` branch.
Once v0.7 is released, it will be the way forward for this project.

*   Feature: Improve PHP 8.4+ support by avoiding implicitly nullable types.
    (#114 by @clue)

*   Improve test suite to run tests on latest PHP versions and report failed assertions.
    (#113 by @clue)

## 0.6.5 (2022-09-16)

*   Feature: Full support for PHP 8.1 and PHP 8.2 release.
    (#91 by @SimonFrings and #99 by @WyriHaximus)

*   Feature / Fix: Improve error reporting when custom error handler is used.
    (#94 by @clue)

*   Minor documentation improvements.
    (#92 by @SimonFrings and #95 by @nhedger)

*   Improve test suite, skip failing tests on bugged versions and fix legacy HHVM build.
    (#96 and #98 by @clue and #93 by @SimonFrings)

## 0.6.4 (2021-10-12)

*   Feature / Fix: Skip sigchild check if `phpinfo()` has been disabled.
    (#89 by @clue)

*   Fix: Fix detecting closed socket pipes on PHP 8.
    (#90 by @clue)

## 0.6.3 (2021-07-11)

A major new feature release, see [**release announcement**](https://clue.engineering/2021/announcing-reactphp-default-loop).

*   Feature: Simplify usage by supporting new [default loop](https://reactphp.org/event-loop/#loop).
    (#87 by @clue)

    ```php
    // old (still supported)
    $process = new React\ChildProcess\Process($command);
    $process->start($loop);

    // new (using default loop)
    $process = new React\ChildProcess\Process($command);
    $process->start();
    ```

## 0.6.2 (2021-02-05)

*   Feature: Support PHP 8 and add non-blocking I/O support on Windows with PHP 8.
    (#85 by @clue)

*   Minor documentation improvements.
    (#78 by @WyriHaximus and #80 by @gdejong)

*   Improve test suite and add `.gitattributes` to exclude dev files from exports.
    Run tests on PHPUnit 9, switch to GitHub actions and clean up test suite.
    (#75 by @reedy, #81 by @gdejong, #82 by @SimonFrings and #84 by @clue)

## 0.6.1 (2019-02-15)

*   Feature / Fix: Improve error reporting when spawning child process fails.
    (#73 by @clue)

## 0.6.0 (2019-01-14)

A major feature release with some minor API improvements!
This project now has limited Windows support and supports passing custom pipes
and file descriptors to the child process.

This update involves a few minor BC breaks. We've tried hard to avoid BC breaks
where possible and minimize impact otherwise. We expect that most consumers of
this package will actually not be affected by any BC breaks, see below for more
details.

*   Feature / BC break: Support passing custom pipes and file descriptors to child process,
    expose all standard I/O pipes in an array and remove unused Windows-only options.
    (#62, #64 and #65 by @clue)

    > BC note: The optional `$options` parameter in the `Process` constructor
      has been removed and a new `$fds` parameter has been added instead. The
      previous `$options` parameter was Windows-only, available options were not
      documented or referenced anywhere else in this library, so its actual
      impact is expected to be relatively small. See the documentation and the
      following changelog entry if you're looking for Windows support.

*   Feature: Support spawning child process on Windows without process I/O pipes.
    (#67 by @clue)

*   Feature / BC break: Improve sigchild compatibility and support explicit configuration.
    (#63 by @clue)

    ```php
    // advanced: not recommended by default
    Process::setSigchildEnabled(true);
    ```

    > BC note: The old public sigchild methods have been removed, but its
      practical impact is believed to be relatively small due to the automatic detection.

*   Improve performance by prefixing all global functions calls with \ to skip
    the look up and resolve process and go straight to the global function.
    (#68 by @WyriHaximus)

*   Minor documentation improvements and docblock updates.
    (#59 by @iamluc and #69 by @CharlotteDunois)

*   Improve test suite to test against PHP7.2 and PHP 7.3, improve HHVM compatibility,
    add forward compatibility with PHPUnit 7 and run tests on Windows via Travis CI.
    (#66 and #71 by @clue)

## 0.5.2 (2018-01-18)

*   Feature: Detect "exit" immediately if last process pipe is closed
    (#58 by @clue)

    This introduces a simple check to see if the program is already known to be
    closed when the last process pipe is closed instead of relying on a periodic
    timer. This simple change improves "exit" detection significantly for most
    programs and does not cause a noticeable penalty for more advanced use cases.

*   Fix forward compatibility with upcoming EventLoop releases
    (#56 by @clue)

## 0.5.1 (2017-12-22)

*   Fix: Update Stream dependency to work around SEGFAULT in legacy PHP < 5.4.28
    and PHP < 5.5.12
    (#50 and #52 by @clue)

*   Improve test suite by simplifying test bootstrapping logic via Composer and
    adding forward compatibility with PHPUnit 6
    (#53, #54 and #55 by @clue)

## 0.5.0 (2017-08-15)

* Forward compatibility: react/event-loop 1.0 and 0.5, react/stream 0.7.2 and 1.0, and Événement 3.0
  (#38 and #44 by @WyriHaximus, and #46 by @clue)
* Windows compatibility: Documentate that windows isn't supported in 0.5 unless used from within WSL
  (#41 and #47 by @WyriHaximus)
* Documentation: Termination examples
  (#42 by @clue)
* BC: Throw LogicException in Process instanciating when on Windows or when proc_open is missing (was `RuntimeException`)
  (#49 by @mdrost)

## 0.4.3 (2017-03-14)

* Ease getting started by improving documentation and adding examples
  (#33 and #34 by @clue)

* First class support for PHP 5.3 through PHP 7.1 and HHVM
  (#29 by @clue and #32 by @WyriHaximus)

## 0.4.2 (2017-03-10)

* Feature: Forward compatibility with Stream v0.5
  (#26 by @clue)

* Improve test suite by removing AppVeyor and adding PHPUnit to `require-dev`
  (#27 and #28 by @clue)

## 0.4.1 (2016-08-01)

* Standalone component
* Test against PHP 7 and HHVM, report test coverage, AppVeyor tests
* Wait for stdout and stderr to close before watching for process exit
  (#18 by @mbonneau)

## 0.4.0 (2014-02-02)

* Feature: Added ChildProcess to run async child processes within the event loop (@jmikola)
