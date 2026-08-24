<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Fixture;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

final class SkipSymfonyForm
{
    public function create(Form $form): FormInterface
    {
        return $form;
    }
}
