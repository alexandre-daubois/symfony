<?php

namespace Symfony\Component\VarDumper\Caster;

class JsonStub extends ScalarStub
{
    public ?string $formatted = null;

    public function __construct(mixed $value, bool $format = false)
    {
        parent::__construct($value);

        if ($format) {
            $this->formatted = \json_encode(\json_decode($value, true), JSON_PRETTY_PRINT);
        }
    }
}
