<?php

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Fixture;


use Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Source\MyIterator;
use Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\Source\MyPreise;

final class SkipSameGeneric
{
    protected function getPriceAndCalculateTableVM(): void
    {

        $prices = $this->getPricesForArticle();
        $this->getAmounts($prices);
    }

    /**
     * @param MyIterator<MyPreise> $prices
     */
    private function getAmounts(MyIterator $prices): void
    {
    }

    /**
     * @return MyIterator<MyPreise>
     */
    private function getPricesForArticle(): MyIterator
    {
        /** @var MyIterator<MyPreise> */
        return new MyIterator();
    }

}
