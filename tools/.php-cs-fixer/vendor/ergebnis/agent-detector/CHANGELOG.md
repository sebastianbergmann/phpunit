# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

For a full diff see [`2.0.0...main`][2.0.0...main].

## [`2.0.0`][2.0.0]

For a full diff see [`1.3.0...2.0.0`][1.3.0...2.0.0].

### Added

- Added support for disabling the detection by setting the `AGENT_DETECTOR_DISABLE` environment variable to a non-empty value ([#140]), by [@localheinz]

### Changed

- Adjusted `Detector::isAgentPresent()` to read the known agent environment variables itself instead of expecting all environment variables as an argument ([#122]), by [@localheinz]

### Fixed

- Adjusted `Detector::isAgentPresent()` to treat environment variables with empty values as not set ([#134]), by [@localheinz]
- Adjusted `Detector::isAgentPresent()` to detect the presence of an agent via the `CURSOR_EXTENSION_HOST_ROLE` environment variable only when it is set to `agent-exec` ([#137]), by [@localheinz]

### Removed

- Stopped detecting the presence of an agent when the `COPILOT_GITHUB_TOKEN` environment variable is set ([#133]), by [@localheinz]
- Stopped detecting the presence of an agent when the `REPL_ID` environment variable is set ([#135]), by [@localheinz]
- Stopped detecting the presence of an agent when the `CURSOR_TRACE_ID` environment variable is set ([#136]), by [@localheinz]
- Stopped detecting the presence of an agent when the `COPILOT_ALLOW_ALL` environment variable is set ([#138]), by [@localheinz]
- Stopped detecting the presence of an agent when the `COPILOT_MODEL` environment variable is set ([#139]), by [@localheinz]

## [`1.3.0`][1.3.0]

For a full diff see [`1.2.0...1.3.0`][1.2.0...1.3.0].

### Added

- Added support for detecting the presence of an agent when the `CLINE_ACTIVE` environment variable is set ([#118]), by [@localheinz]
- Added support for detecting the presence of an agent when the `GOOSE_TERMINAL` environment variable is set ([#119]), by [@localheinz]
- Added support for detecting the presence of an agent when the `GROK_AGENT` environment variable is set ([#120]), by [@localheinz]
- Added support for detecting the presence of an agent when the `JUNIE_DATA` or `JUNIE_SHIM_PATH` environment variables are set ([#121]), by [@localheinz]
- Added support for detecting the presence of an agent when the `AGENT_CONTEXT_OUT` or `AGENT_DISPLAY_OUT` environment variables are set ([#123]), by [@localheinz]
- Added support for detecting the presence of an agent when the `OPENCLAW_SHELL` environment variable is set ([#124]), by [@localheinz]

## [`1.2.0`][1.2.0]

For a full diff see [`1.1.1...1.2.0`][1.1.1...1.2.0].

### Added

- Added support for detecting the presence of an agent when the `COPILOT_CLI` or `PI_CODING_AGENT` environment variables are set ([#10]), by [@raphaelstolt]

## [`1.1.1`][1.1.1]

For a full diff see [`1.1.0...1.1.1`][1.1.0...1.1.1].

### Fixed

- Allowed installation on PHP 8.6 ([#4]), by [@localheinz]

## [`1.1.0`][1.1.0]

For a full diff see [`1.0.1...1.1.0`][1.0.1...1.1.0].

### Added

- Added support for detecting the presence of an agent when the `CURSOR_EXTENSION_HOST_ROLE` environment variable is set ([#2]), by [@localheinz]

## [`1.0.1`][1.0.1]

For a full diff see [`2655ea1...1.0.1`][2655ea1...1.0.1].

### Added

- Added `Detector` ([#1]), by [@localheinz]

[1.0.1]: https://github.com/ergebnis/agent-detector/releases/tag/1.0.1
[1.1.0]: https://github.com/ergebnis/agent-detector/releases/tag/1.1.0
[1.1.1]: https://github.com/ergebnis/agent-detector/releases/tag/1.1.1
[1.2.0]: https://github.com/ergebnis/agent-detector/releases/tag/1.2.0
[1.3.0]: https://github.com/ergebnis/agent-detector/releases/tag/1.3.0
[2.0.0]: https://github.com/ergebnis/agent-detector/releases/tag/2.0.0

[2655ea1...1.0.1]: https://github.com/ergebnis/agent-detector/compare/2655ea1...1.0.1
[1.0.1...1.1.0]: https://github.com/ergebnis/agent-detector/compare/1.0.1...1.1.0
[1.1.0...1.1.1]: https://github.com/ergebnis/agent-detector/compare/1.1.0...1.1.1
[1.1.1...1.2.0]: https://github.com/ergebnis/agent-detector/compare/1.1.1...1.2.0
[1.2.0...1.3.0]: https://github.com/ergebnis/agent-detector/compare/1.2.0...1.3.0
[1.3.0...2.0.0]: https://github.com/ergebnis/agent-detector/compare/1.3.0...2.0.0
[2.0.0...main]: https://github.com/ergebnis/agent-detector/compare/2.0.0...main

[#1]: https://github.com/ergebnis/agent-detector/pull/1
[#2]: https://github.com/ergebnis/agent-detector/pull/2
[#4]: https://github.com/ergebnis/agent-detector/pull/4
[#10]: https://github.com/ergebnis/agent-detector/pull/10
[#118]: https://github.com/ergebnis/agent-detector/pull/118
[#119]: https://github.com/ergebnis/agent-detector/pull/119
[#120]: https://github.com/ergebnis/agent-detector/pull/120
[#121]: https://github.com/ergebnis/agent-detector/pull/121
[#122]: https://github.com/ergebnis/agent-detector/pull/122
[#123]: https://github.com/ergebnis/agent-detector/pull/123
[#124]: https://github.com/ergebnis/agent-detector/pull/124
[#133]: https://github.com/ergebnis/agent-detector/pull/133
[#134]: https://github.com/ergebnis/agent-detector/pull/134
[#135]: https://github.com/ergebnis/agent-detector/pull/135
[#136]: https://github.com/ergebnis/agent-detector/pull/136
[#137]: https://github.com/ergebnis/agent-detector/pull/137
[#138]: https://github.com/ergebnis/agent-detector/pull/138
[#139]: https://github.com/ergebnis/agent-detector/pull/139
[#140]: https://github.com/ergebnis/agent-detector/pull/140

[@localheinz]: https://github.com/localheinz
[@raphaelstolt]: https://github.com/raphaelstolt
