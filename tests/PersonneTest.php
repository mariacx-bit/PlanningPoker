<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PersonneTest extends TestCase
{
    public function testPasswordIsHashedAndVerifiable(): void
    {
        $p = new Personne(null, 'alice', 'a@a.com', 'secret');

        $this->assertNotSame('secret', $p->getMdp(), "Le mot de passe doit être hashé");
        $this->assertTrue($p->verifyPassword('secret'));
        $this->assertFalse($p->verifyPassword('wrong'));
    }

    public function testPasswordTooShortThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Personne(null, 'bob', 'b@b.com', '123'); // < 4
    }

    public function testMdpDejaHashKeepsHash(): void
    {
        $hash = password_hash('abcde', PASSWORD_DEFAULT);
        $p = new Personne(null, 'noun', 'n@n.com', $hash, '', true);

        $this->assertSame($hash, $p->getMdp());
        $this->assertTrue($p->verifyPassword('abcde'));
    }
}
