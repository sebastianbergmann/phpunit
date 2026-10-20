# agent-detector

[![Integrate](https://github.com/ergebnis/agent-detector/actions/workflows/integrate.yaml/badge.svg?branch=main)](https://github.com/ergebnis/agent-detector/actions/workflows/integrate.yaml)
[![Merge](https://github.com/ergebnis/agent-detector/actions/workflows/merge.yaml/badge.svg)](https://github.com/ergebnis/agent-detector/actions/workflows/merge.yaml)
[![Release](https://github.com/ergebnis/agent-detector/actions/workflows/release.yaml/badge.svg)](https://github.com/ergebnis/agent-detector/actions/workflows/release.yaml)
[![Renew](https://github.com/ergebnis/agent-detector/actions/workflows/renew.yaml/badge.svg)](https://github.com/ergebnis/agent-detector/actions/workflows/renew.yaml)

[![Code Coverage](https://codecov.io/gh/ergebnis/agent-detector/branch/main/graph/badge.svg)](https://codecov.io/gh/ergebnis/agent-detector)

[![Latest Stable Version](https://poser.pugx.org/ergebnis/agent-detector/v/stable)](https://packagist.org/packages/ergebnis/agent-detector)
[![Total Downloads](https://poser.pugx.org/ergebnis/agent-detector/downloads)](https://packagist.org/packages/ergebnis/agent-detector)
[![Monthly Downloads](https://poser.pugx.org/ergebnis/agent-detector/d/monthly)](https://packagist.org/packages/ergebnis/agent-detector)

This project provides a [`composer`](https://getcomposer.org) package with a detector for detecting the presence of an agent.

## Installation

Run

```sh
composer require ergebnis/agent-detector
```

## Usage

### Detecting the presence of an agent

```php
<?php

declare(strict_types=1);

use Ergebnis\AgentDetector;

$detector = new AgentDetector\Detector();

$isAgentPresent = $detector->isAgentPresent(\getenv());
```

### Supported agents

This package detects the presence of the following agents via environment variables:

| Agent | Environment Variable |
|---|---|
| [Amp](https://amp.dev) | `AMP_CURRENT_THREAD_ID` |
| [Antigravity](https://antigravity.dev) | `ANTIGRAVITY_AGENT` |
| [Augment](https://augmentcode.com) | `AUGMENT_AGENT` |
| [Claude Code](https://github.com/anthropics/claude-code) | `CLAUDECODE`, `CLAUDE_CODE`, `CLAUDE_CODE_IS_COWORK` |
| [Cline](https://cline.bot) | `CLINE_ACTIVE` |
| [Codex](https://github.com/openai/codex) | `CODEX_CI`, `CODEX_SANDBOX`, `CODEX_THREAD_ID` |
| [Cursor](https://cursor.com) | `CURSOR_AGENT`, `CURSOR_EXTENSION_HOST_ROLE`, `CURSOR_TRACE_ID` |
| [Gemini CLI](https://github.com/google-gemini/gemini-cli) | `GEMINI_CLI` |
| [GitHub Copilot](https://github.com/features/copilot) | `COPILOT_ALLOW_ALL`, `COPILOT_CLI`, `COPILOT_GITHUB_TOKEN`, `COPILOT_MODEL |
| [Goose](https://github.com/block/goose) | `GOOSE_TERMINAL` |
| [Grok](https://x.ai/grok) | `GROK_AGENT` |
| [Junie](https://www.jetbrains.com/junie) | `JUNIE_DATA`, `JUNIE_SHIM_PATH` |
| [Kiro CLI](https://kiro.dev) | `AGENT_CONTEXT_OUT`, `AGENT_DISPLAY_OUT` |
| [OpenClaw](https://openclaw.ai) | `OPENCLAW_SHELL` |
| [OpenCode](https://github.com/sst/opencode) | `OPENCODE`, `OPENCODE_CLIENT` |
| [Pi](https://pi.dev) | `PI_CODING_AGENT` |
| [Replit](https://replit.com) | `REPL_ID` |

### Indicating the presence of an agent

In addition, the generic `AI_AGENT` environment variable can be set to indicate the presence of an agent.

## Changelog

The maintainers of this project record notable changes to this project in a [changelog](CHANGELOG.md).

## Contributing

The maintainers of this project suggest following the [contribution guide](.github/CONTRIBUTING.md).

## Code of Conduct

The maintainers of this project ask contributors to follow the [code of conduct](https://github.com/ergebnis/.github/blob/main/CODE_OF_CONDUCT.md).

## General Support Policy

The maintainers of this project provide limited support.

## PHP Version Support Policy

This project currently supports the following PHP versions:

- [PHP 7.4](https://www.php.net/releases/#7.4.0) (has reached its end of life on November 28, 2022)
- [PHP 8.0](https://www.php.net/releases/#8.0.0) (has reached its end of life on November 26, 2023)
- [PHP 8.1](https://www.php.net/releases/#8.1.0) (has reached its end of life on December 31, 2025)
- [PHP 8.2](https://www.php.net/releases/#8.2.0)
- [PHP 8.3](https://www.php.net/releases/#8.3.0)
- [PHP 8.4](https://www.php.net/releases/#8.4.0)
- [PHP 8.5](https://www.php.net/releases/#8.5.0)

The maintainers of this project add support for a PHP version following its initial release and _may_ drop support for a PHP version when it has reached its [end of life](https://www.php.net/supported-versions.php).

## Security Policy

This project has a [security policy](.github/SECURITY.md).

## License

This project uses the [MIT license](LICENSE.md).


## Credits

The agent detector is inspired by [`shipfastlabs/agent-detector`](https://github.com/shipfastlabs/agent-detector), originally licensed under MIT by [Pushpak Chhajed](https://github.com/pushpak1300).

## Social

Follow [@localheinz](https://x.com/intent/follow?screen_name=localheinz) and [@ergebnis](https://x.com/intent/follow?screen_name=ergebnis) on X.
