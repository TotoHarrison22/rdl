document.addEventListener('DOMContentLoaded', function() {
    // Only run on the report page
    if (!document.getElementById('agencies-container')) return;

    let agencyIndex = 0;

    window.addAgencyRow = function() {
        const container = document.getElementById('agencies-container');
        const template = document.getElementById('agency-row-template');
        const noMsg = document.getElementById('no-agency-msg');

        if (noMsg) noMsg.style.display = 'none';

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.agency-row');

        // Update labels
        row.querySelector('.agency-index').textContent = agencyIndex + 1;

        // Update name attributes to use array index
        const inputs = row.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('INDEX', agencyIndex);
            }
        });

        // Populate Agency Dropdown
        const select = row.querySelector('.agency-select');
        if (typeof availableAgencies !== 'undefined') {
            availableAgencies.forEach(ag => {
                const option = document.createElement('option');
                option.value = ag.id_agencia;
                option.textContent = ag.nombre_agencia;
                select.appendChild(option);
            });
        }

        // Add delete functionality
        row.querySelector('.remove-agency').addEventListener('click', function() {
            row.remove();
            if (container.querySelectorAll('.agency-row').length === 0) {
                if (noMsg) noMsg.style.display = 'block';
            }
        });

        container.appendChild(row);
        agencyIndex++;
    };
});
