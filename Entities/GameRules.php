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
     *    'reason'    => string
     * ]
     */
    public static function decide(string $mode, array $votes, int $round): array
    {
        // Normalisation (string + trim)
        $cleanVotes = array_map(function ($v) {
            return trim((string)$v);
        }, $votes);

        $nbPlayers = count($cleanVotes);

        if ($nbPlayers === 0) {
            return [
                'type'     => 'continue',
                'estimate' => null,
                'reason'   => 'Aucun vote reçu'
            ];
        }

        // ---- Helpers -------------------------------------------------------
        $isCoffee = function (string $v): bool {
            $v = trim($v);

            // emoji café
            if ($v === '☕') return true;

            // Normalise en uppercase
            $u = mb_strtoupper($v, 'UTF-8');

            // Enlever l'accent de CAFÉ -> CAFE (si possible)
            // (sans dépendance intl)
            $ascii = strtr($u, [
                'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
                'À' => 'A', 'Â' => 'A', 'Ä' => 'A',
                'Î' => 'I', 'Ï' => 'I',
                'Ô' => 'O', 'Ö' => 'O',
                'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
                'Ç' => 'C',
            ]);

            return in_array($ascii, ['CAFE', 'COFFEE'], true);
        };

        $toNumeric = function (string $v): ?float {
            $v = trim($v);
            if ($v === '') return null;

            // accepte virgule française
            $v = str_replace(',', '.', $v);
            return is_numeric($v) ? (float)$v : null;
        };
        // -------------------------------------------------------------------

        // 1) Cas spécial : tout le monde joue café
        $allCoffee = true;
        foreach ($cleanVotes as $v) {
            if (!$isCoffee($v)) {
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
            $n = $toNumeric($v);
            if ($n !== null) $numericVotes[] = $n;
        }

        if (count($numericVotes) === 0) {
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

            return [
                'type'     => 'continue',
                'estimate' => null,
                'reason'   => 'Pas d’unanimité au premier tour'
            ];
        }

        // 3) Tours suivants : dépend du mode choisi
        if ($mode === 'strict') {
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
            $sum = 0.0;
            foreach ($numericVotes as $v) $sum += $v;
            $avg = $sum / count($numericVotes);

            return [
                'type'     => 'validated',
                'estimate' => round($avg, 2),
                'reason'   => 'Validation par moyenne'
            ];
        }

        return [
            'type'     => 'continue',
            'estimate' => null,
            'reason'   => 'Mode inconnu'
        ];
    }
}
