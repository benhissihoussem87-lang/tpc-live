<?php

/**
 * Normalize the lignes[] payload coming from the forfaitaire forms.
 * Only rows with both a project id and a prix value are kept.
 *
 * @param array $rawLines Raw $_POST['lignes'] payload
 * @return array<int,array{projet:int,prix_raw:string,adresse:string}>
 */
function tpc_extract_valid_forfait_lines(array $rawLines): array {
    $lines = [];
    foreach ($rawLines as $line) {
        if (!is_array($line)) {
            continue;
        }

        $projectId = isset($line['projet']) ? (int)$line['projet'] : 0;
        $priceRaw  = trim((string)($line['prix'] ?? $line['prixForfitaire'] ?? ''));
        $address   = trim((string)($line['adresse'] ?? $line['adresseClient'] ?? ''));

        if ($projectId <= 0 || $priceRaw === '') {
            continue;
        }

        $lines[] = [
            'projet'   => $projectId,
            'prix_raw' => $priceRaw,
            'adresse'  => $address,
        ];
    }

    return $lines;
}

/**
 * Prepare lignes[] data so the JS widget can re-hydrate user inputs
 * after a validation error.
 *
 * @param array $rawLines
 * @return array<int,array{projet:string,prix:string,adresse:string}>
 */
function tpc_prepare_forfait_lines_prefill(array $rawLines): array {
    $prefill = [];
    foreach ($rawLines as $line) {
        if (!is_array($line)) {
            continue;
        }
        $prefill[] = [
            'projet'  => isset($line['projet']) ? (string)$line['projet'] : '',
            'prix'    => (string)($line['prix'] ?? $line['prixForfitaire'] ?? ''),
            'adresse' => (string)($line['adresse'] ?? $line['adresseClient'] ?? ''),
        ];
    }
    return $prefill;
}

/**
 * Convert DB rows (facture_projets) to the structure expected by the widget.
 *
 * @param array $rows
 * @return array<int,array{projet:string,prix:string,adresse:string}>
 */
function tpc_prefill_lines_from_db(array $rows): array {
    $prefill = [];
    foreach ($rows as $row) {
        $prefill[] = [
            'projet'  => isset($row['projet']) ? (string)$row['projet'] : '',
            'prix'    => isset($row['prixForfitaire']) ? (string)$row['prixForfitaire'] : '',
            'adresse' => isset($row['adresseClient']) ? (string)$row['adresseClient'] : '',
        ];
    }
    return $prefill;
}

