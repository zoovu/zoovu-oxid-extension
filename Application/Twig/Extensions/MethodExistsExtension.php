<?php

declare(strict_types=1);

namespace Semknox\Productsearch\Application\Twig\Extensions;

if (class_exists('\Twig\TwigFunction') && class_exists('\Twig\Extension\AbstractExtension')) {

    // >= oxid7

    class MethodExistsExtension extends \Twig\Extension\AbstractExtension
    {
        /**
         * @return Twig\TwigFunction[]
         */
        public function getFunctions(): array
        {
            return [
                new \Twig\TwigFunction('method_exists', [$this, 'methodExists'])
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
} else {

    // < oxid7
    
    class MethodExistsExtension {}
}
