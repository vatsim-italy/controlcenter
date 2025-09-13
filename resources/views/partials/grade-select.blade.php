<style>
    .grade-i {
        color: #fff !important;
        background-color: #dc3545 !important; /* Bootstrap red */
        font-weight: bold;
    }

    .grade-s {
        color: #000 !important;
        background-color: #90ee90 !important; /* light green */
        font-weight: bold;
    }

    .grade-g {
        color: #fff !important;
        background-color: #198754 !important; /* Bootstrap dark green */
        font-weight: bold;
    }

    .result-incomplete {
        color: #000 !important;
        background-color: #ffc107 !important; /* yellow */
        font-weight: bold;
    }
</style>
<select name="{{ $name }}"
        class="form-select mb-3 grade-select"
        onchange="updateGradeColor(this)">
    <option value="N/A">N/A</option>
    <option value="I">I</option>
    <option value="S">S</option>
    <option value="G">G</option>
</select>
