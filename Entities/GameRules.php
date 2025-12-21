<?php

class GameRules
{
    /**
     * @param string $mode   'strict' ou 'moyenne'
     * @param array  $votes  ex: ['3', '5', '3', 'CAFÉ']
     * @param int    $round  numéro du tour pour cette tâche (1 = premier tour)
     *
     * @return array [
     *    'type'      => 'continue' | 'validated' | 'pause',
     *    'estimate'  => float|null,
     *    'reason'    => string (pour debug/logs)
     * ]
     */
    public static function decide(string $mode, array $votes, int $round): array
    {
        // Normalisation
        $cleanVotes = array_map(function ($v) {
            return strtoupper(trim((string)$v));
        }, $votes);

        $nbPlayers = count($cleanVotes);

        if ($nbPlayers === 0) {
            return [
                'type'     => 'continue',
                'estimate' => null,
                'reason'   => 'Aucun vote reçu'
            ];
        }

        // 1) Cas spécial : tout le monde joue café
        $allCoffee = true;
        foreach ($cleanVotes as $v) {
            if ($v !== 'CAFÉ' && $v !== 'CAFE') {
                $allCoffee = false;
                break;
            }
        }

        if ($allCoffee) {
            return [
                'type'     => 'pause',
                'estimate' => null,
                'reason'   => 'Tous les joueurs ont choisi la carte café'
            ];
        }

        // Ne garder que les votes numériques
        $numericVotes = [];
        foreach ($cleanVotes as $v) {
            // on accepte "1", "2", "3", "5", etc.
            if (is_numeric($v)) {
                $numericVotes[] = (float)$v;
            }
        }

        if (count($numericVotes) === 0) {
            // Personne n'a donné de valeur exploitable (mélange bizarre de cafés/autre)
            return [
                'type'     => 'continue',
                'estimate' => null,
                'reason'   => 'Aucune valeur numérique exploitable'
            ];
        }

        // 2) Premier tour : toujours STRICT (unanimité)
        if ($round === 1) {
            $first = $numericVotes[0];
            $unanimous = true;
            foreach ($numericVotes as $v) {
                if ($v !== $first) {
                    $unanimous = false;
                    break;
                }
            }

            if ($unanimous && count($numericVotes) === $nbPlayers) {
                return [
                    'type'     => 'validated',
                    'estimate' => $first,
                    'reason'   => 'Unanimité au premier tour'
                ];
            }

            // Pas d’unanimité → discussion, on recommence
            return [
                'type'     => 'continue',
                'estimate' => null,
                'reason'   => 'Pas d’unanimité au premier tour'
            ];
        }

        // 3) Tours suivants : dépend du mode choisi
        if ($mode === 'strict') {
            // Toujours unanimité
            $first = $numericVotes[0];
            $unanimous = true;
            foreach ($numericVotes as $v) {
                if ($v !== $first) {
                    $unanimous = false;
                    break;
                }
            }

            if ($unanimous && count($numericVotes) === $nbPlayers) {
                return [
                    'type'     => 'validated',
                    'estimate' => $first,
                    'reason'   => 'Unanimité en mode strict'
                ];
            }

            return [
                'type'     => 'continue',
                'estimate' => null,
                'reason'   => 'Mode strict, pas d’unanimité'
            ];
        }

        if ($mode === 'moyenne') {
            // Moyenne des votes numériques
            $sum = 0.0;
            foreach ($numericVotes as $v) {
                $sum += $v;
            }
            $avg = $sum / count($numericVotes);

            // tu peux arrondir si tu veux coller à une échelle (ex. Fibonacci)
            // ici on met 2 décimales
            $avg = round($avg, 2);

            return [
                'type'     => 'validated',
                'estimate' => $avg,
                'reason'   => 'Validation par moyenne'
            ];
        }

        // Cas par défaut : on ne connaît pas le mode → on continue
        return [
            'type'     => 'continue',
            'estimate' => null,
            'reason'   => 'Mode inconnu'
        ];
    }
}
