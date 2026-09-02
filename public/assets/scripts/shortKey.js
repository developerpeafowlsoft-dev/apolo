const keyMap = {
    "A": "#create-modal-btn",
    "C": "#modelClose",
};

$(document).on("keydown", function (e) {
    if (e.ctrlKey && e.shiftKey) {
        const key = e.key.toUpperCase();
        if (keyMap[key]) {
            e.preventDefault();
            $(keyMap[key]).trigger("click");
        }
    }
});

// Short Key Create Using Modal Open
$(document).ready(function () {
    $(document).on('keyup', function (event) {
        if (event.ctrlKey && event.key === 'D' && event.shiftKey) {

            let activeElement = document.activeElement;

            let select2Container = activeElement.closest('.select2-container');
            if (!select2Container) return;

            let originalSelect = $(select2Container).siblings('select');
            if (!originalSelect.length) return;

            let selectId = originalSelect.attr('id');

            console.log('Shift+D pressed on Select2 dropdown:', selectId);
            // alert(selectId)
            switch (selectId) {
                case 'itemname':
                    showCustomLoader('center');
                    new bootstrap.Modal(document.getElementById('itme-master-modal'), {
                        backdrop: false
                    }).show();
                    $('#brand_id,#category,#hsn_master_id,#vat_tax_id,#unit_id,#material_id,#salesman_id,#sub_category,#colorIds,#sizeIds,#commission_type').select2({
                        // theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('#itme-master-modal')
                    });

                    modelItemMasterDataLoad();
                    break;
                case 'account_master':
                    new bootstrap.Modal(document.getElementById('accountMasterModal')).show();
                    break;
                case 'design_master':
                    new bootstrap.Modal(document.getElementById('designMasterModal')).show();
                    break;
                // Add more cases as needed
                default:
                    console.log('No modal configured for this dropdown.');
            }
        }
    });
});

