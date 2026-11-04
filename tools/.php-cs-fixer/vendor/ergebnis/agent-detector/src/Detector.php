<?php

declare(strict_types=1);

/**
 * Copyright (c) 2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/agent-detector
 */

namespace Ergebnis\AgentDetector;

/**
 * The agent detector is inspired by shipfastlabs/agent-detector, originally licensed under MIT by Pushpak Chhajed.
 *
 * @see https://github.com/shipfastlabs/agent-detector
 * @see https://github.com/pushpak1300
 */
final class Detector
{
    /**
     * @see https://no-color.org
     */
    private const DISABLE_ENVIRONMENT_VARIABLE = 'AGENT_DETECTOR_DISABLE';

    /**
     * @var list<Condition\Condition>
     */
    private array $conditions;

    public function __construct()
    {
        $this->conditions = [
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'AGENT_CONTEXT_OUT',
                'https://kiro.dev/docs/reference/built-in-tools/',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'AGENT_DISPLAY_OUT',
                'https://kiro.dev/docs/reference/built-in-tools/',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'AI_AGENT',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'AMP_CURRENT_THREAD_ID',
                'https://github.com/shipfastlabs/agent-detector/blob/main/src/AgentDetector.php',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'ANTIGRAVITY_AGENT',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'AUGMENT_AGENT',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CLAUDECODE',
                'https://github.com/anthropics/claude-code/blob/main/src/utils/env.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CLAUDE_CODE',
                'https://github.com/anthropics/claude-code/blob/main/src/utils/env.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CLAUDE_CODE_IS_COWORK',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CLINE_ACTIVE',
                'https://github.com/cline/cline/blob/main/apps/vscode/src/hosts/vscode/terminal/VscodeTerminalRegistry.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CODEX_CI',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CODEX_SANDBOX',
                'https://github.com/openai/codex/blob/main/codex-rs/core/src/seatbelt.rs',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CODEX_THREAD_ID',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'COPILOT_CLI',
                'https://github.com/github/copilot-cli',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'CURSOR_AGENT',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetTo::create(
                'CURSOR_EXTENSION_HOST_ROLE',
                'agent-exec',
                'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'GEMINI_CLI',
                'https://github.com/google-gemini/gemini-cli/blob/main/packages/core/src/tools/shell/shell-tool.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'GOOSE_TERMINAL',
                'https://github.com/aaif-goose/goose/blob/main/crates/goose/src/agents/retry.rs',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'GROK_AGENT',
                'https://github.com/xai-org/grok-build/blob/main/crates/codegen/xai-grok-tools/src/util/env.rs',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'JUNIE_DATA',
                'https://github.com/JetBrains/junie/blob/main/templates/junie.shim.sh',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'JUNIE_SHIM_PATH',
                'https://github.com/JetBrains/junie/blob/main/templates/junie.shim.sh',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'OPENCLAW_SHELL',
                'https://github.com/openclaw/openclaw/blob/main/src/agents/bash-tools.exec-runtime.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'OPENCODE',
                'https://github.com/shipfastlabs/agent-detector/blob/main/src/AgentDetector.php',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'OPENCODE_CLIENT',
                'https://github.com/sst/opencode/blob/dev/packages/opencode/src/flag/flag.ts',
            ),
            Condition\EnvironmentVariableIsSetToNonEmptyString::create(
                'PI_CODING_AGENT',
                'https://github.com/badlogic/pi-mono/blob/main/packages/coding-agent/src/index.ts',
            ),
        ];
    }

    public function isAgentPresent(): bool
    {
        $disable = \getenv(self::DISABLE_ENVIRONMENT_VARIABLE);

        if (
            \is_string($disable)
            && '' !== $disable
        ) {
            return false;
        }

        foreach ($this->conditions as $condition) {
            if ($condition->isMet()) {
                return true;
            }
        }

        return false;
    }
}
