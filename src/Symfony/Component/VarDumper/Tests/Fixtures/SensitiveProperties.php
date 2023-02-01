<?php

namespace Symfony\Component\VarDumper\Tests\Fixtures;

use Symfony\Component\VarDumper\Attribute\SensitiveElement;

class SensitiveProperties
{
    private string $username = 'root';

    #[SensitiveElement]
    private string $password = 'toor';

    protected SensitiveFoo $sensitiveFoo;

    public SensitiveBarProperties $sensitiveBarProperties;

    public function __construct()
    {
        $this->sensitiveFoo = new SensitiveFoo();
        $this->sensitiveBarProperties = new SensitiveBarProperties();
    }
}

#[SensitiveElement]
class SensitiveFoo
{
}

class SensitiveBarProperties
{
    #[SensitiveElement]
    private string $sensitiveInfo = 'password';

    private int $publicInfo = 123;
}
