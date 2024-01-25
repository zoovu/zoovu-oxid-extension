<?php

declare(strict_types=1);

namespace Semknox\Productsearch\Application\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MethodExistsExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('method_exists', [$this, 'methodExists'])
        ];
    }

    /**
     * @param mixed $object
     * @param string $methodName
     *
     * @return bool
     */
    public function methodExists($object, string $methodName): bool
    {
        return method_exists($object, $methodName);
    }
}
