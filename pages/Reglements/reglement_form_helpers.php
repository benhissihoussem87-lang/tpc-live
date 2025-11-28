<?php
if (!function_exists('tpc_reglement_type_labels')) {
    function tpc_reglement_type_labels(): array
    {
        static $labels = null;
        if ($labels === null) {
            $labels = [
                'cheque' => html_entity_decode('Ch&eacute;que', ENT_QUOTES, 'UTF-8'),
                'espece' => html_entity_decode('Esp&egrave;ce', ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $labels;
    }
}

if (!function_exists('tpc_reglement_normalize_type_label')) {
    /**
     * Force consistent human-readable values for Type Reglement.
     */
    function tpc_reglement_normalize_type_label($value): string
    {
        $value = isset($value) ? trim((string)$value) : '';
        if ($value === '') {
            return '';
        }

        $labels = tpc_reglement_type_labels();
        $lower = strtolower($value);
        $lettersOnly = preg_replace('/[^a-z]/', '', $lower);

        if ($lettersOnly === 'cheque' || (strpos($lower, 'ch') === 0 && substr($lower, -3) === 'que')) {
            return $labels['cheque'];
        }
        if ($lettersOnly === 'espece' || strpos($lower, 'esp') === 0) {
            return $labels['espece'];
        }

        return $value;
    }
}

if (!function_exists('tpc_reglement_prepare_post')) {
    /**
     * Normalize POSTed règlement lines so blank rows are dropped and
     * all line-based arrays stay in sync.
     */
    function tpc_reglement_prepare_post(array $post): array
    {
        $fields = ['typeReglement', 'montant', 'numCheque', 'dateCheque', 'dateReglement'];
        $normalized = [];
        $maxCount = 0;

        foreach ($fields as $field) {
            $values = isset($post[$field]) && is_array($post[$field]) ? $post[$field] : [];
            $normalized[$field] = $values;
            $maxCount = max($maxCount, count($values));
        }

        $result = [];
        foreach ($fields as $field) {
            $result[$field] = [];
        }

        for ($i = 0; $i < $maxCount; $i++) {
            $rowValues = [];
            $isEmptyRow = true;
            foreach ($fields as $field) {
                $value = isset($normalized[$field][$i]) ? trim((string)$normalized[$field][$i]) : '';
                if ($field === 'typeReglement') {
                    $value = tpc_reglement_normalize_type_label($value);
                }
                if ($value !== '') {
                    $isEmptyRow = false;
                }
                $rowValues[$field] = $value;
            }

            if ($isEmptyRow) {
                continue;
            }

            foreach ($fields as $field) {
                $result[$field][] = $rowValues[$field];
            }
        }

        return $result;
    }
}

if (!function_exists('tpc_reglement_rows_from_arrays')) {
    /**
     * Build structured rows from comma-separated DB strings for display.
     */
    function tpc_reglement_rows_from_arrays(
        array $types,
        array $nums,
        array $dateCheques,
        array $montants,
        array $dateReglements
    ): array {
        $maxCount = max(
            count($types),
            count($nums),
            count($dateCheques),
            count($montants),
            count($dateReglements)
        );

        $rows = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $row = [
                'typeReglement' => isset($types[$i]) ? tpc_reglement_normalize_type_label($types[$i]) : '',
                'numCheque' => isset($nums[$i]) ? trim((string)$nums[$i]) : '',
                'dateCheque' => isset($dateCheques[$i]) ? trim((string)$dateCheques[$i]) : '',
                'montant' => isset($montants[$i]) ? trim((string)$montants[$i]) : '',
                'dateReglement' => isset($dateReglements[$i]) ? trim((string)$dateReglements[$i]) : '',
            ];

            if (
                $row['typeReglement'] === '' &&
                $row['numCheque'] === '' &&
                $row['dateCheque'] === '' &&
                $row['montant'] === '' &&
                $row['dateReglement'] === ''
            ) {
                continue;
            }

            $rows[] = $row;
        }

        if (empty($rows)) {
            $rows[] = [
                'typeReglement' => '',
                'numCheque' => '',
                'dateCheque' => '',
                'montant' => '',
                'dateReglement' => '',
            ];
        }

        return $rows;
    }
}
