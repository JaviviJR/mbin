<?php declare(strict_types=1);

namespace App\Markdown\CommonMark;

use App\Utils\RegPatterns;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MagazineLinkParser extends AbstractLocalLinkParser
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getPrefix(): string
    {
        return '!';
    }

    protected function kbinPrefix(): bool
    {
        return false;
    }

    public function getUrl(string $suffix): string
    {
        return $this->urlGenerator->generate(
            'front_magazine',
            [
                'name' => $suffix,
            ]
        );
    }

    public function getRegex(): string
    {
        return RegPatterns::LOCAL_MAGAZINE;
    }

    public function getApRegex(): string
    {
        return RegPatterns::AP_MAGAZINE;
    }
}
