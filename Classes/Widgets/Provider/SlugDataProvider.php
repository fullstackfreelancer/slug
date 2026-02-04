<?php
declare(strict_types=1);
namespace KOHLERCODE\Slug\Widgets\Provider;

use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

class SlugDataProvider implements NumberWithIconDataProviderInterface
{
    public function getNumber(int $secondsBack = 86400): int
    {
        $dir = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3conf/ext/*';
        return count(array_filter(glob($dir), "is_dir"));
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
