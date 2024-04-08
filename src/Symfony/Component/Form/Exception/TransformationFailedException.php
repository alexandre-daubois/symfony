<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Exception;

/**
 * Indicates a value transformation error.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TransformationFailedException extends RuntimeException
{
    private ?string $invalidMessage;
    private array $invalidMessageParameters;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?string $invalidMessage = null, array $invalidMessageParameters = [])
    {
        parent::__construct($message, $code, $previous);

        $this->setInvalidMessage($invalidMessage, $invalidMessageParameters);
    }

    /**
     * Sets the message that will be shown to the user.
     *
     * @param $invalidMessage           The message or message key
     * @param $invalidMessageParameters Data to be passed into the translator
     */
    public function setInvalidMessage(?string $invalidMessage, array $invalidMessageParameters = []): void
    {
        $this->invalidMessage = $invalidMessage;
        $this->invalidMessageParameters = $invalidMessageParameters;
    }

    public function getInvalidMessage(): ?string
    {
        return $this->invalidMessage;
    }

    public function getInvalidMessageParameters(): array
    {
        return $this->invalidMessageParameters;
    }
}
