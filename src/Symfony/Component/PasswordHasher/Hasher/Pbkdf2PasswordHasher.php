<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\PasswordHasher\Hasher;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Exception\LogicException;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;

/**
 * Pbkdf2PasswordHasher uses the PBKDF2 (Password-Based Key Derivation Function 2).
 *
 * Providing a high level of Cryptographic security,
 *  PBKDF2 is recommended by the National Institute of Standards and Technology (NIST).
 *
 * But also warrants a warning, using PBKDF2 (with a high number of iterations) slows down the process.
 * PBKDF2 should be used with caution and care.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Andrew Johnson
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Pbkdf2PasswordHasher implements LegacyPasswordHasherInterface
{
    use CheckPasswordLengthTrait;

    private int $encodedLength = -1;

    /**
     * @param $algorithm          The digest algorithm to use
     * @param $encodeHashAsBase64 Whether to base64 encode the password hash
     * @param $iterations         The number of iterations to use to stretch the password hash
     * @param $length             Length of derived key to create
     */
    public function __construct(
        private string $algorithm = 'sha512',
        private bool $encodeHashAsBase64 = true,
        private int $iterations = 1000,
        private int $length = 40,
    ) {
        try {
            $this->encodedLength = \strlen($this->hash('', 'salt'));
        } catch (\LogicException) {
            // ignore unsupported algorithm
        }
    }

    public function hash(#[\SensitiveParameter] string $plainPassword, ?string $salt = null): string
    {
        if ($this->isPasswordTooLong($plainPassword)) {
            throw new InvalidPasswordException();
        }

        if (!\in_array($this->algorithm, hash_algos(), true)) {
            throw new LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
        }

        $digest = hash_pbkdf2($this->algorithm, $plainPassword, $salt ?? '', $this->iterations, $this->length, true);

        return $this->encodeHashAsBase64 ? base64_encode($digest) : bin2hex($digest);
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword, ?string $salt = null): bool
    {
        if (\strlen($hashedPassword) !== $this->encodedLength || str_contains($hashedPassword, '$')) {
            return false;
        }

        return !$this->isPasswordTooLong($plainPassword) && hash_equals($hashedPassword, $this->hash($plainPassword, $salt));
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
