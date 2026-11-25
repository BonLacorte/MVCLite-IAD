<?php
$officeRecord = $officeRecord ?? [];
$officePrefix = $officePrefix ?? 'f';
$centralValue = (string)($officeRecord['for_central_office'] ?? '');
$departmentValue = (string)($officeRecord['department_office_unit'] ?? '');
$regionValue = (string)($officeRecord['for_region_center'] ?? '');
$selectedOfficeType = ($regionValue !== '' && $centralValue === '') ? 'regional' : 'central';
$escapeOffice = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$branches = [
    'Office of the Administrator',
    'Administrative & Finance Branch',
    'Operations Branch',
    'Research & Development Branch',
];
$departments = [
    'Office of the Corporate Secretary',
    'Legal Affairs Service',
    'Corporate Planning Service',
    'Assessment & Monitoring Service',
    'Program Management Office',
    'Health and Medical Division',
    'Information Management System Unit',
    'Finance Department',
    'Budget Division',
    'Cash and Disbursement Division',
    'Accounting Division',
    'Administrative & General Services Department',
    'General Services Division',
    'Property Division',
    'Human Resource Division',
    'Operations Department',
    'Field Operations Division',
    'Farmers Welfare & Institutional Development Division',
    'Oil Palm & Other Special Concerns & Project Division',
    'Trade and Market Development Department',
    'Trade Information and Relation Division',
    'Market Research and Promotion Division',
    'Laboratory Services Division',
    'Internal Audit Department',
];
$regions = [
    'Region I',
    'Region II',
    'Region III',
    'Region IV-A',
    'Region IV-B',
    'Region V',
    'Region VI',
    'Region VII',
    'Region VIII',
    'Region IX',
    'Region X',
    'Region XI',
    'Region XII',
    'Region XIII',
    'NCR',
    'CAR',
    'BARMM',
];
?>

<div class="form-group">
    <label>Office Type <span style="color: var(--admin-danger);">*</span></label>
    <div class="radio-group">
        <label>
            <input type="radio" name="office_type" value="central" <?= $selectedOfficeType === 'central' ? 'checked' : '' ?>>
            Central Office
        </label>
        <label>
            <input type="radio" name="office_type" value="regional" <?= $selectedOfficeType === 'regional' ? 'checked' : '' ?>>
            Regional Office
        </label>
    </div>
</div>

<div class="office-assignment-central" data-office-panel="central">
    <div class="form-group">
        <label>Branch</label>
        <select name="for_central_office" id="<?= $escapeOffice($officePrefix) ?>central">
            <option value="">N/A</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?= $escapeOffice($branch) ?>" <?= $centralValue === $branch ? 'selected' : '' ?>>
                    <?= $escapeOffice($branch) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Dept / Office / Unit</label>
        <select name="department_office_unit" id="<?= $escapeOffice($officePrefix) ?>department">
            <option value="">N/A</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= $escapeOffice($department) ?>" <?= $departmentValue === $department ? 'selected' : '' ?>>
                    <?= $escapeOffice($department) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="office-assignment-regional" data-office-panel="regional">
    <div class="form-group">
        <label>Region Center</label>
        <select name="for_region_center" id="<?= $escapeOffice($officePrefix) ?>region">
            <option value="">Select Region</option>
            <?php foreach ($regions as $region): ?>
                <option value="<?= $escapeOffice($region) ?>" <?= $regionValue === $region ? 'selected' : '' ?>>
                    <?= $escapeOffice($region) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<script>
(function() {
const officeAssignmentScript = document.currentScript;
document.addEventListener('DOMContentLoaded', function() {
    const form = officeAssignmentScript.closest('main')?.querySelector('form') || document.querySelector('form');
    if (!form) return;

    const centralPanel = form.querySelector('[data-office-panel="central"]');
    const regionalPanel = form.querySelector('[data-office-panel="regional"]');
    const branch = form.querySelector('[name="for_central_office"]');
    const department = form.querySelector('[name="department_office_unit"]');
    const region = form.querySelector('[name="for_region_center"]');
    const radios = form.querySelectorAll('input[name="office_type"]');

    function syncOfficeFields() {
        const selected = form.querySelector('input[name="office_type"]:checked')?.value || 'central';
        const isCentral = selected === 'central';

        if (centralPanel) centralPanel.style.display = isCentral ? 'block' : 'none';
        if (regionalPanel) regionalPanel.style.display = isCentral ? 'none' : 'block';

        if (isCentral) {
            if (region) region.value = '';
        } else {
            if (branch) branch.value = '';
            if (department) department.value = '';
        }
    }

    radios.forEach(radio => radio.addEventListener('change', syncOfficeFields));
    form.addEventListener('submit', syncOfficeFields);
    syncOfficeFields();
});
})();
</script>
