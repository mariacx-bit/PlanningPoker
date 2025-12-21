<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GameStateTest extends TestCase
{
    public function testCreateFromBacklogStringList(): void
    {
        $tasks = ['Login', 'Dashboard'];
        $players = ['alice', 'noun'];
        $state = GameState::createFromBacklog($tasks, $players, 'strict');

        $this->assertSame('strict', $state['mode']);
        $this->assertSame(['alice', 'noun'], $state['players']);
        $this->assertSame(0, $state['current_index']);

        $this->assertCount(2, $state['tasks']);
        $this->assertSame('Login', $state['tasks'][0]['title']);
        $this->assertSame('pending', $state['tasks'][0]['status']);
        $this->assertNull($state['tasks'][0]['estimate']);
    }

    public function testCreateFromBacklogObjects(): void
    {
        $tasks = [
            ['title' => 'PP-1', 'description' => 'Desc'],
            ['id' => 99, 'title' => 'PP-2']
        ];
        $state = GameState::createFromBacklog($tasks, ['a'], 'moyenne');

        $this->assertSame('moyenne', $state['mode']);
        $this->assertSame('PP-1', $state['tasks'][0]['title']);
        $this->assertSame('Desc', $state['tasks'][0]['description']);
        $this->assertSame(99, $state['tasks'][1]['id']);
        $this->assertSame('PP-2', $state['tasks'][1]['title']);
    }

    public function testToJsonFromJsonRoundtrip(): void
    {
        $state = GameState::createFromBacklog(['A'], ['alice'], 'strict');
        $json = GameState::toJson($state);

        $decoded = GameState::fromJson($json);
        $this->assertIsArray($decoded);
        $this->assertSame($state['mode'], $decoded['mode']);
        $this->assertSame($state['players'], $decoded['players']);
        $this->assertSame($state['tasks'][0]['title'], $decoded['tasks'][0]['title']);
    }

    public function testFromJsonInvalidReturnsNull(): void
    {
        $decoded = GameState::fromJson("{not valid json");
        $this->assertNull($decoded);
    }
}
