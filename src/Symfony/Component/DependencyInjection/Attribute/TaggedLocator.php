<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Attribute;

/**
 * Autowires a locator of services based on a tag name.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class TaggedLocator extends AutowireLocator
{
    /**
     * @param                 $tag                   The tag to look for to populate the locator
     * @param                 $indexAttribute        The name of the attribute that defines the key referencing each service in the tagged collection
     * @param                 $defaultIndexMethod    The static method that should be called to get each service's key when their tag doesn't define the previous attribute
     * @param                 $defaultPriorityMethod The static method that should be called to get each service's priority when their tag doesn't define the "priority" attribute
     * @param string|string[] $exclude               A service id or a list of service ids to exclude
     * @param                 $excludeSelf           Whether to automatically exclude the referencing service from the locator
     */
    public function __construct(
        public string $tag,
        public ?string $indexAttribute = null,
        public ?string $defaultIndexMethod = null,
        public ?string $defaultPriorityMethod = null,
        public string|array $exclude = [],
        public bool $excludeSelf = true,
    ) {
        parent::__construct($tag, $indexAttribute, $defaultIndexMethod, $defaultPriorityMethod, $exclude, $excludeSelf);
    }
}
