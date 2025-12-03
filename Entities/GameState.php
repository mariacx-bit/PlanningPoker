<?php

class GameState
{
    /**
     * Crée un état de partie à partir d’un JSON de backlog simple (liste de tâches)
     * ou directement à partir d’un tableau de tâches PHP.
     */
    public static function createFromBacklog(array $tasks, array $players, string $mode): array
    {
        $normalizedTasks = [];
        $id = 1;

        foreach ($tasks as $t) {
            // Si le backlog d’origine est juste ["tache 1", "tache 2", ...]
            if (is_string($t)) {
                $normalizedTasks[] = [
                    'id'          => $id++,
                    'title'       => $t,
                    'description' => '',
                    'estimate'    => null,
                    'status'      => 'pending'
                ];
            } elseif (is_array($t)) {
                // Si c’est déjà un objet avec title/description
                $normalizedTasks[] = [
                    'id'          => $t['id']          ?? $id++,
                    'title'       => $t['title']       ?? ('Tâche '.$id),
                    'description' => $t['description'] ?? '',
                    'estimate'    => $t['estimate']    ?? null,
                    'status'      => $t['status']      ?? 'pending'
                ];
            }
        }

        return [
            'mode'          => $mode,
            'players'       => array_values($players),
            'current_index' => 0,
            'tasks'         => $normalizedTasks
        ];
    }

    public static function fromJson(string $json): ?array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }
        return $data;
    }

    public static function toJson(array $state): string
    {
        return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
