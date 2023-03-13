<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class ResolveStaticConstructorPass extends AbstractRecursivePass
{
    protected function processValue(mixed $value, bool $isRoot = false): mixed
    {
        if ($value instanceof Definition && null !== $constructor = $value->getConstructor()) {
            if (null !== $value->getFactory()) {
                throw new RuntimeException(sprintf('The "%s" service cannot declare a factory as well as a constructor method.', $this->currentId));
            }

            try {
                $r = new \ReflectionMethod($value->getClass(), $constructor);
            } catch (\ReflectionException) {
                throw new RuntimeException(sprintf('The "%s" service does not define a method named "%s".', $this->currentId, $constructor));
            }

            if (!$r->isStatic() || !$r->isPublic()) {
                throw new RuntimeException(sprintf('To be used as a constructor, the "%s" method of the "%s" service must be defined as public and static.', $constructor, $this->currentId));
            }

            $value->setFactory([$value->getClass(), $constructor]);
        }

        return parent::processValue($value, $isRoot);
    }
}
