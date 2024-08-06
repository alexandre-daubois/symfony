<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Clock;

/**
 * A clock that always returns the same date, suitable for testing time-sensitive logic.
 *
 * Consider using ClockSensitiveTrait in your test cases instead of using this class directly.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class MockClock implements ClockInterface
{
    private ?DatePoint $now = null;
    /**
     * @var \Closure(): \DateTimeImmutable
     */
    private \Closure $timeProvider;
    private ?\DateTimeZone $timezone = null;

    /**
     * @param \DateTimeImmutable|string|(\Closure(): \DateTimeImmutable) $now The date to return when calling now(), a string that can be parsed into a DateTimeImmutable instance, or a closure that returns a DateTimeImmutable instance
     *
     * @throws \DateMalformedStringException When $now is invalid
     * @throws \DateInvalidTimeZoneException When $timezone is invalid
     */
    public function __construct(\DateTimeImmutable|string|\Closure $now = 'now', \DateTimeZone|string|null $timezone = null)
    {
        if (\PHP_VERSION_ID >= 80300 && \is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        } elseif (\is_string($timezone)) {
            try {
                $timezone = new \DateTimeZone($timezone);
            } catch (\Exception $e) {
                throw new \DateInvalidTimeZoneException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $this->timezone = $timezone;

        if ($now instanceof \Closure) {
            $this->timeProvider = $now;

            return;
        }

        if (\is_string($now)) {
            $now = new DatePoint($now, $timezone ?? new \DateTimeZone('UTC'));
        } elseif (!$now instanceof DatePoint) {
            $now = DatePoint::createFromInterface($now);
        }

        $this->now = null !== $timezone ? $now->setTimezone($timezone) : $now;
    }

    public function now(): DatePoint
    {
        return clone $this->now ??= $this->tryFromTimeProvider();
    }

    public function sleep(float|int $seconds): void
    {
        $now = (float) ($this->now ??= $this->tryFromTimeProvider())->format('Uu') + $seconds * 1e6;
        $now = substr_replace(\sprintf('@%07.0F', $now), '.', -6, 0);
        $timezone = $this->now->getTimezone();

        $this->now = DatePoint::createFromInterface(new \DateTimeImmutable($now, $timezone))->setTimezone($timezone);
    }

    /**
     * @throws \DateMalformedStringException When $modifier is invalid
     */
    public function modify(string $modifier): void
    {
        $this->now ??= $this->tryFromTimeProvider();

        if (\PHP_VERSION_ID < 80300) {
            $this->now = @$this->now->modify($modifier) ?: throw new \DateMalformedStringException(error_get_last()['message'] ?? \sprintf('Invalid modifier: "%s". Could not modify MockClock.', $modifier));

            return;
        }

        $this->now = $this->now->modify($modifier);
    }

    /**
     * @throws \DateInvalidTimeZoneException When the timezone name is invalid
     */
    public function withTimeZone(\DateTimeZone|string $timezone): static
    {
        if (\PHP_VERSION_ID >= 80300 && \is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        } elseif (\is_string($timezone)) {
            try {
                $timezone = new \DateTimeZone($timezone);
            } catch (\Exception $e) {
                throw new \DateInvalidTimeZoneException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $clone = clone $this;
        $clone->timezone = $timezone;
        $clone->now = $this->now?->setTimezone($timezone);

        return $clone;
    }

    public function reset(): void
    {
        if (isset($this->timeProvider)) {
            $this->now = null;
        }
    }

    private function tryFromTimeProvider(): DatePoint
    {
        $now = ($this->timeProvider)();

        if (!$now instanceof \DateTimeImmutable) {
            throw new \TypeError(\sprintf('The time provider closure returned a "%s" instance, expected a "%s" instance.', \is_object($now) ? \get_class($now) : \gettype($now), \DateTimeImmutable::class));
        }

        if ($this->timezone) {
            $now = $now->setTimezone($this->timezone);
        }

        return DatePoint::createFromInterface($now);
    }
}
