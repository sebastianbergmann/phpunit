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
     * @see https://github.com/shipfastlabs/agent-detector
     * @see https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts
     *
     * @var array<string, string>
     */
    private const AGENT_ENVIRONMENT_VARIABLES = [
        'AGENT_CONTEXT_OUT' => 'https://kiro.dev/docs/reference/built-in-tools/',
        'AGENT_DISPLAY_OUT' => 'https://kiro.dev/docs/reference/built-in-tools/',
        'AI_AGENT' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'AMP_CURRENT_THREAD_ID' => 'https://github.com/shipfastlabs/agent-detector/blob/main/src/AgentDetector.php',
        'ANTIGRAVITY_AGENT' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'AUGMENT_AGENT' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'CLAUDECODE' => 'https://github.com/anthropics/claude-code/blob/main/src/utils/env.ts',
        'CLAUDE_CODE' => 'https://github.com/anthropics/claude-code/blob/main/src/utils/env.ts',
        'CLAUDE_CODE_IS_COWORK' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'CLINE_ACTIVE' => 'https://github.com/cline/cline/blob/main/apps/vscode/src/hosts/vscode/terminal/VscodeTerminalRegistry.ts',
        'CODEX_CI' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'CODEX_SANDBOX' => 'https://github.com/openai/codex/blob/main/codex-rs/core/src/seatbelt.rs',
        'CODEX_THREAD_ID' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'COPILOT_ALLOW_ALL' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'COPILOT_CLI' => 'https://github.com/github/copilot-cli',
        'COPILOT_GITHUB_TOKEN' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'COPILOT_MODEL' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'CURSOR_AGENT' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'CURSOR_EXTENSION_HOST_ROLE' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'CURSOR_TRACE_ID' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
        'GEMINI_CLI' => 'https://github.com/google-gemini/gemini-cli/blob/main/packages/core/src/tools/shell/shell-tool.ts',
        'GOOSE_TERMINAL' => 'https://github.com/aaif-goose/goose/blob/main/crates/goose/src/agents/retry.rs',
        'GROK_AGENT' => 'https://github.com/xai-org/grok-build/blob/main/crates/codegen/xai-grok-tools/src/util/env.rs',
        'JUNIE_DATA' => 'https://github.com/JetBrains/junie/blob/main/templates/junie.shim.sh',
        'JUNIE_SHIM_PATH' => 'https://github.com/JetBrains/junie/blob/main/templates/junie.shim.sh',
        'OPENCLAW_SHELL' => 'https://github.com/openclaw/openclaw/blob/main/src/agents/bash-tools.exec-runtime.ts',
        'OPENCODE' => 'https://github.com/shipfastlabs/agent-detector/blob/main/src/AgentDetector.php',
        'OPENCODE_CLIENT' => 'https://github.com/sst/opencode/blob/dev/packages/opencode/src/flag/flag.ts',
        'PI_CODING_AGENT' => 'https://github.com/badlogic/pi-mono/blob/main/packages/coding-agent/src/index.ts',
        'REPL_ID' => 'https://github.com/vercel/vercel/blob/main/packages/detect-agent/src/index.ts',
    ];

    /**
     * @param array<string, string> $environmentVariables
     */
    public function isAgentPresent(array $environmentVariables): bool
    {
        foreach (self::AGENT_ENVIRONMENT_VARIABLES as $variable => $url) {
            if (\array_key_exists($variable, $environmentVariables)) {
                return true;
            }
        }

        return false;
    }
}
