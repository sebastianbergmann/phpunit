<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source;

enum PickReasonEnum: string
{
    case BarcodeIssue = 'barcode_issue';
    case EmptyLocation = 'empty_location';
}
