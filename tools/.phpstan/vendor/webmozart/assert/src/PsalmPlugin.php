<?php

declare(strict_types=1);

namespace Webmozart\Assert;

use Psalm\Internal\Analyzer\Statements\Expression\ExpressionIdentifier;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\PluginRegistrationSocket;
use SimpleXMLElement;

final class PsalmPlugin implements PluginEntryPointInterface, AfterMethodCallAnalysisInterface
{
    public function __invoke(PluginRegistrationSocket $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        [$class, $method] = explode('::', $event->getAppearingMethodId());
        if ($class !== Assert::class) {
            return;
        }
        if (!isset(HasAssert::HAS_ASSERT[$method])) {
            return;
        }

        $firstArg = $event->getExpr()->getArgs()[0] ?? null;
        if ($firstArg === null) {
            return;
        }

        $varId = ExpressionIdentifier::getExtendedVarId(
            $firstArg->value,
            $event->getContext()->self,
            $event->getStatementsSource()
        );

        if ($varId === null || !isset($event->getContext()->vars_in_scope[$varId])) {
            return;
        }

        $candidateType = $event->getContext()->vars_in_scope[$varId];

        $event->setReturnTypeCandidate($candidateType);
    }
}
