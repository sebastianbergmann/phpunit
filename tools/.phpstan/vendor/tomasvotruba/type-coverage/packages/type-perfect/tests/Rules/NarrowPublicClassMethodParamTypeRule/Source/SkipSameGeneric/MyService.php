<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Source\SkipSameGeneric;

use Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\Fixture\Generics\SkipSameGeneric;

final class MyService
{

    private SkipSameGeneric $articleModel;

    private function populateCustomizationDimensions(int $spracheid): void
    {
        $this->articleModel->setWerbemasse($this->fetchCustomizationPositionsByArtidAndSprache());
    }

    /**
     * @return MyIterator<MyGroup>
     */
    public function fetchCustomizationPositionsByArtidAndSprache(int $artid, int $spracheid): MyIterator
    {
    }

}
