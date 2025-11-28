<?php
if (!isset($reglementRows) || !is_array($reglementRows) || empty($reglementRows)) {
    $reglementRows = [[
        'typeReglement' => '',
        'montant' => '',
        'numCheque' => '',
        'dateCheque' => '',
        'dateReglement' => '',
    ]];
}

$reglementRowsId = $reglementRowsId ?? 'reglementRows';
$reglementTemplateId = $reglementTemplateId ?? 'reglement-row-template';
$reglementTypeDatalistId = $reglementTypeDatalistId ?? 'reglement-type-options';
$addButtonLabel = $addButtonLabel ?? 'Ajouter un reglement';
?>

<div id="<?=htmlspecialchars($reglementRowsId)?>" class="w-100" data-reglement-list>
    <?php foreach ($reglementRows as $index => $row) { ?>
        <div class="reglement-row border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong class="reglement-row-title">Reglement <?=intval($index + 1)?></strong>
                <button type="button" class="btn btn-link text-danger p-0" data-remove-reglement>Supprimer</button>
            </div>
            <div class="row g-3">
                <div class="mb-3 col-2">
                    <label class="col-form-label">Type Reglement :</label>
                    <input list="<?=htmlspecialchars($reglementTypeDatalistId)?>" class="form-control" name="typeReglement[]" value="<?=htmlspecialchars($row['typeReglement'] ?? '')?>" />
                </div>
                <div class="mb-3 col-2">
                    <label class="col-form-label">Montant :</label>
                    <input type="text" class="form-control" name="montant[]" value="<?=htmlspecialchars($row['montant'] ?? '')?>" />
                </div>
                <div class="mb-3 col-3">
                    <label class="col-form-label">No Cheque :</label>
                    <input type="text" class="form-control" name="numCheque[]" value="<?=htmlspecialchars($row['numCheque'] ?? '')?>" />
                </div>
                <div class="mb-3 col-2">
                    <label class="col-form-label">Date Cheque:</label>
                    <input type="date" class="form-control" name="dateCheque[]" value="<?=htmlspecialchars($row['dateCheque'] ?? '')?>" />
                </div>
                <div class="mb-3 col-2">
                    <label class="col-form-label">Date Reglement:</label>
                    <input type="date" class="form-control" name="dateReglement[]" value="<?=htmlspecialchars($row['dateReglement'] ?? '')?>" />
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<datalist id="<?=htmlspecialchars($reglementTypeDatalistId)?>">
    <option value="Chéque"></option>
    <option value="Espèce"></option>
</datalist>

<button type="button" class="btn btn-outline-primary btn-sm mb-3" data-add-reglement="#<?=htmlspecialchars($reglementRowsId)?>"><?=$addButtonLabel?></button>

<template id="<?=htmlspecialchars($reglementTemplateId)?>">
    <div class="reglement-row border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong class="reglement-row-title">Reglement</strong>
            <button type="button" class="btn btn-link text-danger p-0" data-remove-reglement>Supprimer</button>
        </div>
        <div class="row g-3">
            <div class="mb-3 col-2">
                <label class="col-form-label">Type Reglement :</label>
                <input list="<?=htmlspecialchars($reglementTypeDatalistId)?>" class="form-control" name="typeReglement[]" />
            </div>
            <div class="mb-3 col-2">
                <label class="col-form-label">Montant :</label>
                <input type="text" class="form-control" name="montant[]" />
            </div>
            <div class="mb-3 col-3">
                <label class="col-form-label">No Cheque :</label>
                <input type="text" class="form-control" name="numCheque[]" />
            </div>
            <div class="mb-3 col-2">
                <label class="col-form-label">Date Cheque:</label>
                <input type="date" class="form-control" name="dateCheque[]" />
            </div>
            <div class="mb-3 col-2">
                <label class="col-form-label">Date Reglement:</label>
                <input type="date" class="form-control" name="dateReglement[]" />
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('<?=addslashes($reglementRowsId)?>');
    if (!container) {
        return;
    }
    var template = document.getElementById('<?=addslashes($reglementTemplateId)?>');
    var addButton = document.querySelector('[data-add-reglement="#<?=addslashes($reglementRowsId)?>"]');

    function refreshRows() {
        var rows = container.querySelectorAll('.reglement-row');
        rows.forEach(function (row, index) {
            var title = row.querySelector('.reglement-row-title');
            if (title) {
                title.textContent = 'Reglement ' + (index + 1);
            }
            var removeBtn = row.querySelector('[data-remove-reglement]');
            if (removeBtn) {
                removeBtn.style.display = rows.length > 1 ? '' : 'none';
            }
        });
    }

    if (addButton && template) {
        addButton.addEventListener('click', function () {
            var clone = template.content ? template.content.cloneNode(true) : null;
            if (!clone) {
                return;
            }
            container.appendChild(clone);
            refreshRows();
        });
    }

    container.addEventListener('click', function (event) {
        var removeBtn = event.target.closest('[data-remove-reglement]');
        if (!removeBtn) {
            return;
        }
        var row = removeBtn.closest('.reglement-row');
        if (row) {
            row.remove();
            refreshRows();
        }
    });

    refreshRows();
});
</script>
