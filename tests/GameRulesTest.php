<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GameRulesTest extends TestCase
{
    public function testTour1UnanimiteValideStrictEtMoyenne(): void
    {
        $votes = ['3', '3', '3'];

        $r1 = GameRules::decide('strict', $votes, 1);
        $this->assertSame('validated', $r1['type']);
        $this->assertSame(3.0, $r1['estimate']);

        $r2 = GameRules::decide('moyenne', $votes, 1);
        $this->assertSame('validated', $r2['type']);
        $this->assertSame(3.0, $r2['estimate']);
    }

    public function testTour1PasUnanimeContinue(): void
    {
        $votes = ['3', '5', '3'];
        $r = GameRules::decide('strict', $votes, 1);
        $this->assertSame('continue', $r['type']);
        $this->assertNull($r['estimate']);
    }

    public function testStrictTour2ExigeUnanimite(): void
    {
        $votesOk = ['8', '8', '8'];
        $ok = GameRules::decide('strict', $votesOk, 2);
        $this->assertSame('validated', $ok['type']);
        $this->assertSame(8.0, $ok['estimate']);

        $votesKo = ['8', '13', '8'];
        $ko = GameRules::decide('strict', $votesKo, 2);
        $this->assertSame('continue', $ko['type']);
        $this->assertNull($ko['estimate']);
    }

    public function testMoyenneTour2ValideAvecMoyenne(): void
    {
        $votes = ['13', '21']; // moyenne = 17
        $r = GameRules::decide('moyenne', $votes, 2);

        $this->assertSame('validated', $r['type']);
        $this->assertSame(17.0, $r['estimate']);
    }

    public function testCoffeePauseQuandToutLeMondeCafe(): void
    {
        $votes = ['CAFÉ', 'café', 'CAFE'];
        $r = GameRules::decide('strict', $votes, 1);
        $this->assertSame('pause', $r['type']);
        $this->assertNull($r['estimate']);
    }

    public function testCoffeeMixAvecNumeriqueNeDoitPasPause(): void
    {
        // mix café + numérique => pas pause
        $votes = ['CAFÉ', '8', '8'];
        $r = GameRules::decide('strict', $votes, 1);

        // tour 1 => unanimité requise et tous les joueurs ne sont pas numériques -> continue
        $this->assertSame('continue', $r['type']);
        $this->assertNull($r['estimate']);
    }

    public function testVotesVidesContinue(): void
    {
        $r = GameRules::decide('strict', [], 1);
        $this->assertSame('continue', $r['type']);
        $this->assertNull($r['estimate']);
    }

    public function testVotesNonNumeriquesContinue(): void
    {
        $votes = ['?', '??', 'abc'];
        $r = GameRules::decide('moyenne', $votes, 2);
        $this->assertSame('continue', $r['type']);
        $this->assertNull($r['estimate']);
    }

    public function testModeInconnuContinue(): void
    {
        $votes = ['5', '5'];
        $r = GameRules::decide('bidon', $votes, 2);
        $this->assertSame('continue', $r['type']);
        $this->assertNull($r['estimate']);
    }
}
