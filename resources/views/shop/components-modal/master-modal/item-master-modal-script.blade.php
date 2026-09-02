<script>
    function showCustomLoader(position = 'center') {
        const positions = {
            'center': 'translate(-50%, -50%)',
            'top': 'translate(-50%, 0)',
            'bottom': 'translate(-50%, -100%)',
            'left': 'translate(0, -50%)',
            'right': 'translate(-100%, -50%)',
        };

        HoldOn.open({
            theme: "custom",
            content: `
                <div style="
                    position: absolute;
                    top: ${position === 'center' ? '50%' : position === 'top' ? '10%' : position === 'bottom' ? '90%' : '50%'};
                    left: ${position === 'center' ? '50%' : position === 'left' ? '10%' : position === 'right' ? '90%' : '50%'};
                    transform: ${positions[position]};
                    z-index: 9999;
                ">
                    <img src="{{ asset('assets/images/APOLO_GIF.gif') }}" alt="Loading..." style="width:80px; height:auto;" />
                </div>
            `,
        });
    }

    function modelItemMasterDataLoad(callback = null) {
        $('#item_id').val('');
        $('#formDataItemMaster')[0].reset();
        $(".errorSpan,#slug").empty();

        $("#brand_id").empty().append('<option value="">{{ __("Select Brand") }}</option>');
        $("#category").empty().append('<option value="" selected disabled>{{ __("Select Category") }}</option>');
        $("#hsn_master_id").empty().append('<option value="">{{ __("Select HSN Code") }}</option>');
        $("#vat_tax_id").empty().append('<option value="">{{ __("Select tax type") }}</option>');
        $("#salesman_id").empty().append('<option value="">{{ __("Select Salesman") }}</option>');
        $("#material_id").empty().append('<option value="">{{ __("Select Material") }}</option>');
        $("#unit_id").empty().append('<option value="">{{ __("Select Unit") }}</option>');
        $('select[name="colorIds[]"]').empty().append(' <option value="all">{{ __("Select All") }}</option>');
        $('select[name="sizeIds[]"]').empty().append(' <option value="all">{{ __("Select All") }}</option>');
        $('select[name="sub_category[]"]').prop('disabled', true).empty();
        $("#vat_tax_id_hidden").val('');
        $('#salesman_id').val('').trigger('change');
        $('#commission_type').val('').trigger('change');

        $('#selectedColorsTableBody').empty();
        $('#selectedSizesTableBody').empty();
        $('#colorBox').hide();
        $('#sizeBox').hide();

        $.ajax({
            url: '{{ route('shop.itemMaster.modalData') }}',
            type: 'GET',
            success: function (data) {
                data.brands.forEach(function (brand) {
                    $("#brand_id").append(`<option value="${brand.id}">${brand.name}</option>`);
                });

                data.categories.forEach(function (category) {
                    $("#category").append(`<option value="${category.id}">${category.name}</option>`);
                });

                data.colors.forEach(function (color) {
                    $('select[name="colorIds[]"]').append(
                        `<option value="${color.id}" data-color="${color.color_code}" data-name="${color.name}">${color.name}</option>`
                    );
                });

                data.hsnMasters.forEach(function (hsnMaster) {
                    $("#hsn_master_id").append(`<option value="${hsnMaster.id}" data-vattax="${hsnMaster.vat_tax_id}">${hsnMaster.hsn_code}</option>`);
                });

                data.taxs.forEach(function (tax) {
                    $("#vat_tax_id").append(`<option value="${tax.id}">${tax.name} ${tax.percentage}%</option>`);
                });

                data.sizes.forEach(function (size) {
                    $('select[name="sizeIds[]"]').append(
                        `<option value="${size.id}" data-size="${size.name}">${size.name}</option>`
                    );
                });

                data.salesmans.forEach(function (salesman) {
                    $("#salesman_id").append(`<option value="${salesman.id}">${salesman.name}</option>`);
                });

                data.units.forEach(function (unit) {
                    $("#unit_id").append(`<option value="${unit.id}">${unit.name}</option>`);
                });

                data.materials.forEach(function (material) {
                    $("#material_id").append(`<option value="${material.id}">${material.name}</option>`);
                });

                $('#brand_id,#category,#hsn_master_id,#vat_tax_id,#salesman_id,#unit_id,#material_id,#sub_category').each(function() {
                    if (typeof $ !== 'undefined' && $.fn && $.fn.select2 && $(this).hasClass('select2-hidden-accessible')) {
                        try {
                            $(this).select2('destroy');
                        } catch(e) {}
                    }
                });

                $('#brand_id,#category,#hsn_master_id,#vat_tax_id,#salesman_id,#unit_id,#material_id,#sub_category').select2({
                    width: '100%',
                    dropdownParent: $('#itme-master-modal')
                });

                $("#itme-master-modal").modal("show");
                HoldOn.close();

                // 🔁 Call the callback if provided
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function () {
                toastr.error("Failed to load.");
            }
        });
    }

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var $state = $(
            '<span class="d-flex align-items-center"> <span style="background-color:' + state.element.dataset
                .color +
            ';width:20px;height:20px;display:inline-block; border-radius:5px;margin-right:5px;"></span>' + state
                .text + '</span>'
        );
        return $state;
    };

    $(document).ready(function (){
        // Auto-focus search input field when any Select2 dropdown opens
        $(document).on('select2:open', () => {
            setTimeout(function() {
                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 10);
        });

        // Enter key sequential focus traversal inside Add Item Master modal
        $(document).on('keydown', '#formDataItemMaster input, #formDataItemMaster select', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                // If Select2 search dropdown is actively open, let standard select2 selection proceed
                if ($('.select2-container--open').length > 0) {
                    return;
                }

                e.preventDefault();

                // Find all focusable fields inside the modal form
                const fields = $('#formDataItemMaster').find('input:not([readonly]):visible, select:visible, button:visible').filter(function() {
                    return !$(this).hasClass('select2-search__field') && $(this).attr('type') !== 'hidden' && !$(this).hasClass('btn-close') && !$(this).hasClass('btn-outline-secondary');
                });

                const idx = fields.index(this);
                if (idx > -1 && idx < fields.length - 1) {
                    const nextField = fields.eq(idx + 1);

                    // Auto-open next field if it is a Select2 dropdown
                    if (nextField.hasClass('select2-hidden-accessible')) {
                        nextField.select2('open');
                    } else {
                        nextField.focus().select();
                    }
                } else if (idx === fields.length - 1) {
                    // Trigger submit
                    $('#btnSubmit').click();
                }
            }
        });

        $('select[name="category"]').on('change', function() {
            var categoryId = $(this).val();
            var subCategorySelected = $('select[name="sub_category[]"]');
            subCategorySelected.empty().val([]).trigger('change');

            if (categoryId) {
                $.ajax({
                    url: '/api/sub-categories?category_id=' + categoryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        subCategorySelected.empty();
                        $.each(data.data.sub_categories, function(key, value) {
                            subCategorySelected.append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        subCategorySelected.val([]).trigger('change').prop('disabled', false);
                    },
                    error: function() {
                        console.log('Error retrieving subcategories. Please try again.');
                    }
                });
            } else {
                subCategorySelected.empty().val([]).trigger('change').prop('disabled', true);
            }
        });



        $('.colorSelect').select2({
            templateResult: formatState
        });

        // Size All Select Code
        $(document).on('change', 'select[name="sizeIds[]"]', function () {
            let $select = $(this);
            let selected = $select.val();

            if (selected && selected.includes("all")) {
                let allValues = $select.find('option:not([value="all"])').map(function () {
                    return $(this).val();
                }).get();

                $select.val(allValues).trigger('change');
            }
        });

        // Color All Select Code
        $(document).on('change', 'select[name="colorIds[]"]', function () {
            let $select = $(this);
            let selected = $select.val();

            if (selected && selected.includes("all")) {
                let allValues = $select.find('option:not([value="all"])').map(function () {
                    return $(this).val();
                }).get();

                $select.val(allValues).trigger('change');
            }
        });

        $('.sizeSelector').select2();

        $(document).on('input','#price',function (){
            var productPrice = $(this).val() ?? 0;
            var mrp = $('#mrp').val() ?? 0;

            console.log("productPrice " + productPrice)
            console.log("mrp " + mrp)
            console.log($('#mrp').val())
            var mainPrice = mrp > 0 ? mrp : productPrice;
            $('.mainProductPrice').text(mainPrice);
        });

        // $('#price').on('input', function() {
        //     var productPrice = $(this).val() ?? 0;
        //     var productDiscountPrice = $('#discount_percentage').val() ?? 0;
        //     console.log("productPrice " + productPrice)
        //     console.log("productDiscountPrice " + productDiscountPrice)
        //     var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;
        //     $('.mainProductPrice').text(mainPrice);
        // });

        $(document).on('input','#discount_percentage',function (){
            var productPrice = $('#price').val() ?? 0;
            var mrp = $('#mrp').val() ?? 0;
            console.log("productPrice " + productPrice)
            console.log("mrp " + mrp)
            console.log($('#mrp').val())
            var mainPrice = mrp > 0 ? mrp : productPrice;
            $('.mainProductPrice').text(mainPrice);
        });
        // $('#discount_percentage').on('input', function() {
        //     var productPrice = $('#price').val() ?? 0;
        //     var productDiscountPrice = $(this).val() ?? 0;
        //     console.log("productPrice " + productPrice)
        //     console.log("productDiscountPrice " + productDiscountPrice)
        //     console.log($('#mrp').val())
        //     var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;
        //     $('.mainProductPrice').text(mainPrice);
        // });

        $('.sizeSelector').on('change', function() {

            // var productPrice = $('#price').val() ?? 0;
            // var productDiscountPrice = $('#discount_price').val() ?? 0;
            // var mainPrice = productDiscountPrice > 0 ? productDiscountPrice : productPrice;

            var productPrice = $('#price').val() ?? 0;
            var mrp = $('#mrp').val() ?? 0;
            var mainPrice = mrp > 0 ? mrp : productPrice;

            // Get the selected options
            var selectedOptions = $(this).find(':selected');

            // Check if there are selected options
            if (selectedOptions.length > 0) {
                $('#sizeBox').show();
            } else {
                $('#sizeBox').hide();
            }

            selectedOptions.each(function() {
                var sizeName = $(this).data('size');
                var sizeId = $(this).val();

                // Check if the row already exists
                if (!$(`#selectedSizeRow_${sizeId}`).length) {
                    $('#selectedSizesTableBody').append(`
                        <tr id="selectedSizeRow_${sizeId}" style="display: table-row !important">
                            <td>
                                <h4 class="mb-0 boxName">${sizeName}</h4>
                                <input type="hidden" name="size[${sizeId}][name]" value="${sizeName}">
                                <input type="hidden" name="size[${sizeId}][id]" value="${sizeId}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bolder mainProductPrice">${mainPrice}</span>
                                    <span class="bg-light px-2 py-1 rounded">
                                        <i class="fa-solid fa-plus"></i>
                                    </span>
                                    <input type="text" class="form-control extraPriceForm" name="size[${sizeId}][price]" value="0" style="width: 140px;" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{0,2}|\d*)$/, '$1');">
                                </div>
                            </td>
                            <td>
                                <button class="btn circleIcon btn-outline-danger btn-sm" type="button"
                                    onclick="deleteSizeRow(${sizeId})">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                }
            });

            $(this).find(':not(:selected)').each(function() {
                var sizeId = $(this).val();
                $(`#selectedSizeRow_${sizeId}`).remove();
            });

            setDefaultPrice();
        });

        // Add color wise extra price
        $('.colorSelect').on('change', function() {
            var selectedOptions = $(this).find(':selected');

            if (selectedOptions.length > 0) {
                $('#colorBox').show();
            } else {
                $('#colorBox').hide();
            }

            var productPrice = $('#price').val() ?? 0;
            var mrp = $('#mrp').val() ?? 0;
            var mainPrice =  mrp > 0 ? mrp : productPrice;

            selectedOptions.each(function() {
                var colorName = $(this).data('name');
                var colorCode = $(this).data('color');
                var colorId = $(this).val();

                // Check if the row already exists
                if (!$(`#selectedColorRow_${colorId}`).length) {
                    $('#selectedColorsTableBody').append(`
                            <tr id="selectedColorRow_${colorId}" style="display: table-row !important">
                                <td>
                                    <h4 class="mb-0 boxName d-flex align-items-center gap-1">
                                        <span style="background-color:${colorCode};width:20px;height:19px;display:inline-block; border-radius:5px;"></span>
                                        ${colorName}
                                    </h4>
                                    <input type="hidden" name="color[${colorId}][name]" value="${colorName}">
                                    <input type="hidden" name="color[${colorId}][id]" value="${colorId}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bolder mainProductPrice">${mainPrice}</span>
                                        <span class="bg-light px-2 py-1 rounded">
                                            <i class="fa-solid fa-plus"></i>
                                        </span>
                                        <input type="text" class="form-control extraPriceForm" name="color[${colorId}][price]" value="0" style="width: 140px" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{0,2}|\d*)$/, '$1');">
                                    </div>
                                </td>
                                <td>
                                    <button class="btn circleIcon btn-outline-danger btn-sm" type="button"
                                        onclick="deleteColorRow(${colorId})">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                }
            });

            // Remove the row from the table
            $(this).find(':not(:selected)').each(function() {
                var colorId = $(this).val();
                $(`#selectedColorRow_${colorId}`).remove();
            });

            setDefaultPrice();
        });
    });

    $(document).ready(function (){
        $("#hsn_master_id").on("change",function (){

            const vatTaxId = $(this).find(":selected").data("vattax");

            if(vatTaxId !== ''){
                $("#vat_tax_id").val(vatTaxId).trigger('change');
                $("#vat_tax_id_hidden").val(vatTaxId);
            }
        });
    });

    const generateItemMasterCode = () => {
        const code = document.getElementById('code');
        code.value = Math.floor(Math.random() * 90000000) + 10000000;
    }

    function loadSubcategories(categoryId, selectedSubIds = []) {
        if (!categoryId) return;

        $.ajax({
            url: '/api/sub-categories?category_id=' + categoryId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                var subCategorySelect = $('select[name="sub_category[]"]');
                subCategorySelect.empty();

                $.each(data.data.sub_categories, function(key, value) {
                    subCategorySelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                });

                subCategorySelect.val(selectedSubIds).trigger('change');
                subCategorySelect.prop('disabled', false);
            },
            error: function() {
                console.log('Error retrieving subcategories. Please try again.');
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        let nameInput = document.querySelector("input[name='name']");
        let slugInput = document.getElementById("slug");

        nameInput.addEventListener("keyup", function () {
            let slug = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.textContent = slug;
        });
    });

    $(document).on('input keyup', 'input[name="buy_price"], input[name="price"], input[name="discount_percentage"]', function () {
        calculateAll();
    });

    function calculateAll() {
        let buy_price = parseFloat($('input[name="buy_price"]').val()) || 0;
        let price = parseFloat($('input[name="price"]').val()) || 0;
        let discount_percentage = parseFloat($('input[name="discount_percentage"]').val()) || 0;

        let mrp = 0;
        let mark_up = 0;
        let mark_down = 0;

        if (discount_percentage > 0) {
            let discount_value = (price * discount_percentage) / 100;
            mrp = price - discount_value;
        } else {
            mrp = price;
        }

        $('input[name="mrp"]').val(mrp.toFixed(2));

        if (buy_price > 0 && mrp > 0) {
            mark_up = ((mrp - buy_price) / buy_price) * 100;
            mark_down = ((mrp - buy_price) / mrp) * 100;
        }

        $('input[name="mark_up"]').val(mark_up.toFixed(2));
        $('input[name="mark_down"]').val(mark_down.toFixed(2));
    }


    // remove size from price section
    function deleteSizeRow(id) {
        $(`#selectedSizeRow_${id}`).remove();

        $('.sizeSelector option').each(function() {
            if ($(this).val() == id) {
                $(this).prop('selected', false);
            }
        });
        $('.sizeSelector').trigger('change');
        setDefaultPrice();
    }

    // remove color from price section
    function deleteColorRow(id) {
        $(`#selectedColorRow_${id}`).remove();

        $('.colorSelect option').each(function() {
            if ($(this).val() == id) {
                $(this).prop('selected', false);
            }
        });
        $('.colorSelect').trigger('change');
        setDefaultPrice();

    }

    // set default price
    function setDefaultPrice() {
        $('#selectedColorsTableBody').find('tr').each(function() {
            let index = $(this).index();
            var rowId = $(this).attr('id').split('_')[1];

            if (index == 0) {
                var priceInput = $(`input[name="color[${rowId}][price]"]`);
                priceInput.val(0);
                priceInput.attr('type', 'hidden');

                $('#defaultPriceColor').remove();
                $(`<span id="defaultPriceColor" class="defaultPrice fst-italic">Default Price</span>`)
                    .insertAfter(priceInput);
            }
        });

        $('#selectedSizesTableBody').find('tr').each(function() {
            let index = $(this).index();
            var rowId = $(this).attr('id').split('_')[1];

            if (index == 0) {
                var priceInput = $(`input[name="size[${rowId}][price]"]`);
                priceInput.val(0);
                priceInput.attr('type', 'hidden');

                $('#defaultPriceSize').remove();
                $(`<span id="defaultPriceSize" class="defaultPrice fst-italic">Default Price</span>`)
                    .insertAfter(priceInput);
            }
        });
    }

    $(document).ready(function () {
        const $commType = $("#commission_type");
        const $commDiv = $("#salesman_comm_div");
        const $commAmtDiv = $("#salesman_comm_amt_div");
        const $commInput = $("input[name='salesman_comm']");
        const $commAmtInput = $("input[name='salesman_comm_amt']");

        $commType.change(function () {
            const value = $(this).val();
            const type = $(this).find(':selected').data('type');

            // Hide both divs & remove 'required' from both inputs
            $commDiv.hide();
            $commAmtDiv.hide();
            $commInput.val('0.00');
            $commAmtInput.val('0.00');
            $commInput.removeAttr('required');
            $commAmtInput.removeAttr('required');

            // Show the right one and add 'required'
            if (value === '1' && type === 'comm%') {
                $commDiv.show();
                $commInput.attr('required', true);
            } else if (value === '2' && type === 'comm₹') {
                $commAmtDiv.show();
                $commAmtInput.attr('required', true);
            }
        });
    });

    $(document).ready(function () {
        const $salesman = $("#salesman_id");
        const $commType = $("#commission_typeDiv");
        const $commTypeValue = $("#commission_type");

        $salesman.on('change',function (){
            const value = $(this).val();
            $commType.hide();
            $commTypeValue.val('').trigger('change');
            if (value !== '') {
                $commType.show();
            }
        })
    });

    // Refresh Data
    function refreshItemMasterList() {
        $.ajax({
            url: "{{ route('shop.itemMaster.index') }}",
            type: "GET",
            success: function (data) {
                console.log(data)
                $("#itemMasterList").html(data);
            },
            error: function () {
                toastr.error("Failed to refresh list");
            }
        });
    }

    // Data submit
    $(document).ready(function (){
        $("#formDataItemMaster").on('submit',function (e){
            e.preventDefault();

            var submitButton = $(this).find('button[type="submit"]');
            var originalButtonText = submitButton.text();
            var formData = new FormData(this);
            var slugData = $("#slug").text();

            formData.append('slug', slugData);

            var itemId = $('#item_id').val();

            if (itemId) {
                // Update
                url = `/shop/item-master/${itemId}/update`;
                method = 'POST';
                formData.append('_method', 'PUT');
            } else {
                // Create
                url = "{{ route('shop.itemMaster.store') }}";
                method = 'POST';
            }

            $.ajax({
                url: url,
                type: method,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Laravel ke liye
                },
                processData: false,  // must be false
                contentType: false,  // must be false
                cache: false,
                success: function (res) {
                    if (res.status) {
                        refreshItemMasterList();
                        toastr.success(res.message);
                        $("#itme-master-modal").modal("hide");

                        // Auto-fill in Inward Product Item if active input or row exists
                        var activeRow = window.lastFocusedInwardRow || (window.lastFocusedInwardItemInput ? $(window.lastFocusedInwardItemInput).closest('tr')[0] : null);
                        if (res.product && activeRow) {
                            var $row = $(activeRow);
                            var $input = $row.find('input.item');

                            $input.val(res.product.name);
                            $row.find('input[name="itemid[]"]').val(res.product.id);

                            if (res.product.hsn_master) {
                                $row.find('input[name="taxCode[]"]').val(res.product.hsn_master.hsn_code);
                                $row.find('input[name="taxCodeId[]"]').val(res.product.hsn_master.id);
                            }
                            if (res.product.vat_tax) {
                                $row.find('input[name="sgst[]"]').val(res.product.vat_tax.percentage);
                                $row.find('input[name="sgstId[]"]').val(res.product.vat_tax.id);
                            }

                            // Move focus to Design input of the same row
                            $row.find('input[name="designNo[]"]').focus().select();
                        }
                    }
                    submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $(".errorSpan").text('');

                        // Show new errors
                        $.each(errors, function (key, messages) {
                            var errorSpan = $('#' + key +'ErrorMessage');

                            if (errorSpan.length) {
                                errorSpan.text(messages[0]);
                            }
                        });
                        submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                    } else {
                        console.log("Unexpected error:", xhr);
                        submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                    }
                }

            });
        })

    });

    function renderSizesWithPrices(sizes) {
        if (!Array.isArray(sizes)) return;

        $('select[name="sizeIds[]"]').val(sizes.map(size => size.id)).trigger('change');

        $('#selectedSizesTableBody').empty();

        const productPrice = $('#price').val() ?? 0;
        const mrp = $('#mrp').val() ?? 0;
        const mainPrice = mrp > 0 ? mrp : productPrice;

        sizes.forEach(size => {
            const sizeId = size.id;
            const sizeName = size.name;
            const price = size.pivot?.price ?? 0;

            $('#selectedSizesTableBody').append(`
            <tr id="selectedSizeRow_${sizeId}" style="display: table-row !important">
                <td>
                    <h4 class="mb-0 boxName">${sizeName}</h4>
                    <input type="hidden" name="size[${sizeId}][name]" value="${sizeName}">
                    <input type="hidden" name="size[${sizeId}][id]" value="${sizeId}">
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bolder mainProductPrice">${mainPrice}</span>
                        <span class="bg-light px-2 py-1 rounded">
                            <i class="fa-solid fa-plus"></i>
                        </span>
                        <input type="text" class="form-control extraPriceForm" name="size[${sizeId}][price]" value="${price}" style="width: 140px" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\\.\d{0,2}|\\d*)$/, '$1');">
                    </div>
                </td>
                <td>
                    <button class="btn circleIcon btn-outline-danger btn-sm" type="button"
                        onclick="deleteSizeRow(${sizeId})">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </td>
            </tr>
        `);
        });

        $('#sizeBox').show();
    }

    function renderColorsWithPrices(colors) {
        if (!Array.isArray(colors)) return;

        // Set selected values in the multiselect
        $('select[name="colorIds[]"]').val(colors.map(color => color.id)).trigger('change');
        $('#selectedColorsTableBody').empty();

        const productPrice = $('#price').val() ?? 0;
        const mrp = $('#mrp').val() ?? 0;
        const mainPrice = mrp > 0 ? mrp : productPrice;

        colors.forEach(color => {
            const colorId = color.id;
            const colorName = color.name;
            const colorCode = color.color_code;
            const price = color.pivot?.price ?? 0;

            $('#selectedColorsTableBody').append(`
            <tr id="selectedColorRow_${colorId}" style="display: table-row !important">
                <td>
                    <h4 class="mb-0 boxName d-flex align-items-center gap-1">
                        <span style="background-color:${colorCode};width:20px;height:19px;display:inline-block; border-radius:5px;"></span>
                        ${colorName}
                    </h4>
                    <input type="hidden" name="color[${colorId}][name]" value="${colorName}">
                    <input type="hidden" name="color[${colorId}][id]" value="${colorId}">
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bolder mainProductPrice">${mainPrice}</span>
                        <span class="bg-light px-2 py-1 rounded">
                            <i class="fa-solid fa-plus"></i>
                        </span>
                        <input type="text" class="form-control extraPriceForm" name="color[${colorId}][price]" value="${price}" style="width: 140px"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\\.\d{0,2}|\\d*)$/, '$1');">
                    </div>
                </td>
                <td>
                    <button class="btn circleIcon btn-outline-danger btn-sm" type="button"
                        onclick="deleteColorRow(${colorId})">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </td>
            </tr>
        `);
        });

        $('#colorBox').show();
    }

    $('#itme-master-modal').on('shown.bs.modal', function () {
        $('#name').focus();
    });

    // Ensure Quick Create Brand Modal is a direct child of <body> to prevent nested DOM stacking issues
    $(document).ready(function() {
        const modalEl = document.getElementById('modalQuickCreateBrand');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }
    });

    // Helper function to safely show Quick Create Brand Modal
    function showQuickCreateBrandModal() {
        const modalEl = document.getElementById('modalQuickCreateBrand');
        if (!modalEl) return;

        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (window.bootstrap && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: true,
                keyboard: true
            });
            modalInstance.show();
        } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            $(modalEl).modal('show');
        }

        $(modalEl).css('z-index', '1080');
        $(modalEl).find('.modal-dialog').css('z-index', '1085');
        $(modalEl).find('.modal-content').css({
            'z-index': '1090',
            'pointer-events': 'auto',
            'position': 'relative'
        });

        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            } else if (backdrops.length === 1) {
                $(backdrops[0]).css('z-index', '1075');
            }
            $('#quick_brand_name').focus();
        }, 100);
    }

    // Helper function to safely hide Quick Create Brand Modal
    function hideQuickCreateBrandModal() {
        const modalEl = document.getElementById('modalQuickCreateBrand');
        if (!modalEl) return;

        // 1. Synchronously hide via jQuery & CSS
        $(modalEl).removeClass('show').hide().css('display', 'none').attr('aria-hidden', 'true');

        // 2. Hide via Bootstrap API instance if active
        if (window.bootstrap && bootstrap.Modal) {
            try {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } catch (e) {}
        }
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            try {
                $(modalEl).modal('hide');
            } catch (e) {}
        }

        // 3. Remove child backdrop and retain body modal-open for itme-master-modal
        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.last().remove();
        } else if (backdrops.length === 1 && !$('#itme-master-modal').is(':visible')) {
            backdrops.remove();
        }

        if ($('#itme-master-modal').is(':visible') || $('#itme-master-modal').hasClass('show')) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
        }

        // 4. Reset form inputs
        $('#quick_brand_name').val('');
        $('#quick_brand_name_error').text('');
    }

    // Plus (+) Button Click Handler
    $(document).on('click', '#btnQuickCreateBrand', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showQuickCreateBrandModal();
    });

    // Explicit Close Handler for Quick Create Brand Modal Close Button & X Icon
    $(document).on('click', '#btnCloseQuickBrandBtn, #btnCloseQuickBrandX, #modalQuickCreateBrand .btn-close, #modalQuickCreateBrand [data-bs-dismiss="modal"], #modalQuickCreateBrand [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideQuickCreateBrandModal();
    });

    // Nested Modal Backdrop Stacking & Focus Management
    $('#modalQuickCreateBrand').on('show.bs.modal shown.bs.modal', function () {
        $(this).css('z-index', 1080);
        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            }
        }, 0);
        $('#quick_brand_name').focus();
    });

    // Prevent parent modal focus trap from stealing focus from child modal input
    $(document).on('focusin', function (e) {
        if ($('#modalQuickCreateBrand').hasClass('show') || $('#modalQuickCreateBrand').is(':visible')) {
            if ($(e.target).closest('#modalQuickCreateBrand').length) {
                e.stopPropagation();
            }
        }
    });

    $('#modalQuickCreateBrand').on('hidden.bs.modal', function () {
        $('#quick_brand_name').val('');
        $('#quick_brand_name_error').text('');
        if ($('#itme-master-modal').hasClass('show') || $('#itme-master-modal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

    // Quick Brand Creation via AJAX
    $('#formQuickCreateBrand').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#btnSubmitQuickBrand');
        $('#quick_brand_name_error').text('');

        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Saving...") }}');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Brand") }}');
                if (res.status && res.brand) {
                    hideQuickCreateBrandModal();
                    $form[0].reset();

                    // Check if brand option already exists
                    if ($(`#brand_id option[value="${res.brand.id}"]`).length === 0) {
                        $('#brand_id').append(new Option(res.brand.name, res.brand.id, true, true));
                    } else {
                        $('#brand_id').val(res.brand.id);
                    }
                    $('#brand_id').trigger('change');

                    toastr.success(res.message || '{{ __("Brand created successfully") }}');
                } else {
                    toastr.error(res.message || '{{ __("Failed to create brand") }}');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Brand") }}');
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#quick_brand_name_error').text(errors.name[0]);
                    }
                } else {
                    toastr.error('{{ __("An error occurred while creating the brand") }}');
                }
            }
        });
    });

    // Cross-Platform Shortcut Key: Alt + B / Option + B / Cmd + Shift + B
    $(document).on('keydown', function (e) {
        const isAltB = (e.altKey && (e.key === 'b' || e.key === 'B' || e.code === 'KeyB'));
        const isCmdShiftB = (e.metaKey && e.shiftKey && (e.key === 'b' || e.key === 'B' || e.code === 'KeyB'));

        if (isAltB || isCmdShiftB) {
            if ($('#modalQuickCreateBrand').hasClass('show') || $('#modalQuickCreateBrand').is(':visible')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            if (!$('#itme-master-modal').hasClass('show') && !$('#itme-master-modal').is(':visible')) {
                $('#create-modal-btn').click();
                setTimeout(function() {
                    showQuickCreateBrandModal();
                }, 350);
            } else {
                showQuickCreateBrandModal();
            }
        }
    });

    $('#modalQuickCreateBrand').on('shown.bs.modal', function () {
        $('#quick_brand_name').focus();
    });

    // Ensure Quick Create Category Modal is a direct child of <body>
    $(document).ready(function() {
        const categoryModal = document.getElementById('modalQuickCreateCategory');
        if (categoryModal && categoryModal.parentNode !== document.body) {
            document.body.appendChild(categoryModal);
        }
    });

    // Helper function to safely show Quick Create Category Modal
    function showQuickCreateCategoryModal() {
        const modalEl = document.getElementById('modalQuickCreateCategory');
        if (!modalEl) return;

        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        if (window.bootstrap && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: true,
                keyboard: true
            });
            modalInstance.show();
        } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            $(modalEl).modal('show');
        }

        $(modalEl).css('z-index', '1080');
        $(modalEl).find('.modal-dialog').css('z-index', '1085');
        $(modalEl).find('.modal-content').css({
            'z-index': '1090',
            'pointer-events': 'auto',
            'position': 'relative'
        });

        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            } else if (backdrops.length === 1) {
                $(backdrops[0]).css('z-index', '1075');
            }
            $('#quick_category_name').focus();
        }, 100);
    }

    // Helper function to safely hide Quick Create Category Modal
    function previewQuickCategoryImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('quickCategoryImgPreviewTag').src = e.target.result;
                document.getElementById('quickCategoryImgPreviewContainer').classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function hideQuickCreateCategoryModal() {
        const modalEl = document.getElementById('modalQuickCreateCategory');
        if (!modalEl) return;

        $(modalEl).removeClass('show').hide().css('display', 'none').attr('aria-hidden', 'true');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            } catch (e) {}
        }
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            try {
                $(modalEl).modal('hide');
            } catch (e) {}
        }

        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.last().remove();
        } else if (backdrops.length === 1 && !$('#itme-master-modal').is(':visible')) {
            backdrops.remove();
        }

        if ($('#itme-master-modal').is(':visible') || $('#itme-master-modal').hasClass('show')) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
        }

        $('#quick_category_name, #quick_category_thumbnail, #quick_category_description').val('');
        $('#quick_category_name_error, #quick_category_thumbnail_error, #quick_category_description_error').text('');
        $('#quickCategoryImgPreviewContainer').addClass('d-none');
    }

    // Plus (+) Button Click Handler for Category
    $(document).on('click', '#btnQuickCreateCategory', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showQuickCreateCategoryModal();
    });

    // Close Handler for Quick Create Category Modal
    $(document).on('click', '#btnCloseQuickCategoryBtn, #btnCloseQuickCategoryX, #modalQuickCreateCategory .btn-close, #modalQuickCreateCategory [data-bs-dismiss="modal"], #modalQuickCreateCategory [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideQuickCreateCategoryModal();
    });

    $('#modalQuickCreateCategory').on('show.bs.modal shown.bs.modal', function () {
        $(this).css('z-index', 1080);
        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            }
        }, 0);
        $('#quick_category_name').focus();
    });

    $(document).on('focusin', function (e) {
        if ($('#modalQuickCreateCategory').hasClass('show') || $('#modalQuickCreateCategory').is(':visible')) {
            if ($(e.target).closest('#modalQuickCreateCategory').length) {
                e.stopPropagation();
            }
        }
    });

    $('#modalQuickCreateCategory').on('hidden.bs.modal', function () {
        $('#quick_category_name, #quick_category_thumbnail, #quick_category_description').val('');
        $('#quick_category_name_error, #quick_category_thumbnail_error, #quick_category_description_error').text('');
        $('#quickCategoryImgPreviewContainer').addClass('d-none');
        if ($('#itme-master-modal').hasClass('show') || $('#itme-master-modal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

    $(document).on('input change', '#quick_category_name', function() {
        $('#quick_category_name_error').text('');
    });
    $(document).on('change', '#quick_category_thumbnail', function() {
        $('#quick_category_thumbnail_error').text('');
    });
    $(document).on('input change', '#quick_category_description', function() {
        $('#quick_category_description_error').text('');
    });

    // Quick Category Creation via AJAX
    $('#formQuickCreateCategory').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#btnSubmitQuickCategory');
        $('#quick_category_name_error, #quick_category_thumbnail_error, #quick_category_description_error').text('');

        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Saving...") }}');

        let hasClientError = false;
        if (!$("#quick_category_name").val().trim()) {
            $("#quick_category_name_error").text('{{ __("The name field is required.") }}');
            hasClientError = true;
        }
        const fileInput = $('#quick_category_thumbnail')[0];
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            $('#quick_category_thumbnail_error').text('{{ __("Category image is required.") }}');
            hasClientError = true;
        }
        if (hasClientError) {
            $submitBtn.prop('disabled', false).html('{{ __("Save Category") }}');
            return false;
        }

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Category") }}');
                if (res.status && res.category) {
                    hideQuickCreateCategoryModal();
                    $form[0].reset();
                    $('#quickCategoryImgPreviewContainer').addClass('d-none');

                    const categorySelect = $('select[name="category"]');
                    if (categorySelect.find(`option[value="${res.category.id}"]`).length === 0) {
                        categorySelect.append(new Option(res.category.name, res.category.id, true, true));
                    } else {
                        categorySelect.val(res.category.id);
                    }
                    categorySelect.trigger('change');

                    toastr.success(res.message || '{{ __("Category created successfully") }}');
                } else {
                    toastr.error(res.message || '{{ __("Failed to create category") }}');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Category") }}');
                $('#quick_category_name_error, #quick_category_thumbnail_error, #quick_category_description_error').text('');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#quick_category_name_error').text(errors.name[0]);
                        $('#quick_category_name').focus();
                    }
                    if (errors.thumbnail) {
                        $('#quick_category_thumbnail_error').text(errors.thumbnail[0]);
                    }
                    if (errors.description) {
                        $('#quick_category_description_error').text(errors.description[0]);
                    }
                } else {
                    toastr.error('{{ __("An error occurred while creating the category") }}');
                }
            }
        });
    });

    // Cross-Platform Shortcut Key for Category: Alt + C / Option + C / Cmd + Shift + C
    $(document).on('keydown', function (e) {
        const isAltC = (e.altKey && (e.key === 'c' || e.key === 'C' || e.code === 'KeyC'));
        const isCmdShiftC = (e.metaKey && e.shiftKey && (e.key === 'c' || e.key === 'C' || e.code === 'KeyC'));

        if (isAltC || isCmdShiftC) {
            if ($('#modalQuickCreateCategory').hasClass('show') || $('#modalQuickCreateCategory').is(':visible')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            if (!$('#itme-master-modal').hasClass('show') && !$('#itme-master-modal').is(':visible')) {
                $('#create-modal-btn').click();
                setTimeout(function() {
                    showQuickCreateCategoryModal();
                }, 350);
            } else {
                showQuickCreateCategoryModal();
            }
        }
    });

    // Helper functions for Quick Create Sub Category Modal
    function previewQuickSubCategoryImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('quickSubCategoryImgPreviewTag').src = e.target.result;
                document.getElementById('quickSubCategoryImgPreviewContainer').classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function populateQuickSubCategoryCategories(callback = null) {
        const $select = $('#quick_subcategory_category_select');
        const selectedCategoryInItemMaster = $('select[name="category"]').val();

        function applyCategoryOptionsAndSelect2(categoriesList) {
            if (typeof $ !== 'undefined' && $.fn && $.fn.select2 && $select.hasClass('select2-hidden-accessible')) {
                try {
                    $select.select2('destroy');
                } catch (e) {}
            }

            $select.empty();

            if (categoriesList && categoriesList.length) {
                categoriesList.forEach(function(cat) {
                    if (cat.id && cat.name) {
                        $select.append(new Option(cat.name, cat.id, false, false));
                    }
                });
            }

            if (selectedCategoryInItemMaster) {
                const valArray = Array.isArray(selectedCategoryInItemMaster) ? selectedCategoryInItemMaster : [selectedCategoryInItemMaster];
                $select.val(valArray);
            } else {
                $select.val([]);
            }

            if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                $select.select2({
                    placeholder: "{{ __('Select Category') }}",
                    allowClear: true,
                    dropdownParent: $('#modalQuickCreateSubCategory'),
                    width: '100%'
                });
            }

            if (typeof callback === 'function') {
                callback();
            }
        }

        const itemMasterCategorySelect = $('#category');
        const existingOptions = itemMasterCategorySelect.find('option').filter(function() {
            return $(this).val() !== "" && $(this).val() !== null;
        });

        if (existingOptions.length > 0) {
            const categoriesFromDom = [];
            existingOptions.each(function() {
                categoriesFromDom.push({
                    id: $(this).val(),
                    name: $(this).text()
                });
            });
            applyCategoryOptionsAndSelect2(categoriesFromDom);
        } else {
            $.ajax({
                url: '{{ route('shop.itemMaster.modalData') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data && data.categories && data.categories.length) {
                        applyCategoryOptionsAndSelect2(data.categories);
                    } else {
                        applyCategoryOptionsAndSelect2([]);
                    }
                },
                error: function() {
                    applyCategoryOptionsAndSelect2([]);
                }
            });
        }
    }

    function showQuickCreateSubCategoryModal() {
        const modalEl = document.getElementById('modalQuickCreateSubCategory');
        if (!modalEl) return;

        if ($(modalEl).parent()[0] !== document.body) {
            $(modalEl).appendTo('body');
        }

        populateQuickSubCategoryCategories();

        $(modalEl).css({
            'z-index': 1080,
            'display': 'block'
        }).addClass('show').removeAttr('aria-hidden');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                }
                modalInstance.show();
            } catch (e) {}
        }

        setTimeout(function() {
            $('#quick_subcategory_name').focus();
        }, 100);
    }

    function hideQuickCreateSubCategoryModal() {
        const modalEl = document.getElementById('modalQuickCreateSubCategory');
        if (!modalEl) return;

        $(modalEl).removeClass('show').hide().css('display', 'none').attr('aria-hidden', 'true');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            } catch (e) {}
        }
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            try {
                $(modalEl).modal('hide');
            } catch (e) {}
        }

        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.last().remove();
        } else if (backdrops.length === 1 && !$('#itme-master-modal').is(':visible')) {
            backdrops.remove();
        }

        if ($('#itme-master-modal').is(':visible') || $('#itme-master-modal').hasClass('show')) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
        }

        $('#quick_subcategory_name, #quick_subcategory_thumbnail').val('');
        $('#quick_subcategory_category_error, #quick_subcategory_name_error, #quick_subcategory_thumbnail_error').text('');
        $('#quickSubCategoryImgPreviewContainer').addClass('d-none');
    }

    $(document).on('click', '#btnQuickCreateSubCategory', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showQuickCreateSubCategoryModal();
    });

    $(document).on('click', '#btnCloseQuickSubCategoryBtn, #btnCloseQuickSubCategoryX, #modalQuickCreateSubCategory .btn-close, #modalQuickCreateSubCategory [data-bs-dismiss="modal"], #modalQuickCreateSubCategory [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideQuickCreateSubCategoryModal();
    });

    $('#modalQuickCreateSubCategory').on('show.bs.modal shown.bs.modal', function () {
        $(this).css('z-index', 1080);
        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            }
        }, 0);
        $('#quick_subcategory_name').focus();
    });

    $(document).on('focusin', function (e) {
        if ($('#modalQuickCreateSubCategory').hasClass('show') || $('#modalQuickCreateSubCategory').is(':visible')) {
            if ($(e.target).closest('#modalQuickCreateSubCategory').length) {
                e.stopPropagation();
            }
        }
    });

    $('#modalQuickCreateSubCategory').on('hidden.bs.modal', function () {
        $('#quick_subcategory_name, #quick_subcategory_thumbnail').val('');
        $('#quick_subcategory_category_error, #quick_subcategory_name_error, #quick_subcategory_thumbnail_error').text('');
        $('#quickSubCategoryImgPreviewContainer').addClass('d-none');
        if ($('#itme-master-modal').hasClass('show') || $('#itme-master-modal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

    $(document).on('input change', '#quick_subcategory_name', function() {
        $('#quick_subcategory_name_error').text('');
    });
    $(document).on('change', '#quick_subcategory_category_select', function() {
        $('#quick_subcategory_category_error').text('');
    });
    $(document).on('change', '#quick_subcategory_thumbnail', function() {
        $('#quick_subcategory_thumbnail_error').text('');
    });

    // Quick SubCategory Creation via AJAX
    $('#formQuickCreateSubCategory').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#btnSubmitQuickSubCategory');
        $('#quick_subcategory_category_error, #quick_subcategory_name_error, #quick_subcategory_thumbnail_error').text('');

        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Saving...") }}');

        let hasClientError = false;
        if (!$("#quick_subcategory_category_select").val() || !$("#quick_subcategory_category_select").val().length) {
            $("#quick_subcategory_category_error").text('{{ __("The category field is required.") }}');
            hasClientError = true;
        }
        if (!$("#quick_subcategory_name").val().trim()) {
            $("#quick_subcategory_name_error").text('{{ __("The name field is required.") }}');
            hasClientError = true;
        }
        const subCategoryFileInput = $('#quick_subcategory_thumbnail')[0];
        if (!subCategoryFileInput || !subCategoryFileInput.files || !subCategoryFileInput.files.length) {
            $('#quick_subcategory_thumbnail_error').text('{{ __("Sub category image is required.") }}');
            hasClientError = true;
        }
        if (hasClientError) {
            $submitBtn.prop('disabled', false).html('{{ __("Save Sub Category") }}');
            return false;
        }

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Sub Category") }}');
                if (res.status && res.sub_category) {
                    hideQuickCreateSubCategoryModal();
                    $form[0].reset();
                    $('#quickSubCategoryImgPreviewContainer').addClass('d-none');

                    const subCategorySelect = $('select[name="sub_category[]"]');
                    const categoryId = $('select[name="category"]').val();

                    if (categoryId) {
                        $.ajax({
                            url: '/api/sub-categories?category_id=' + categoryId,
                            type: "GET",
                            dataType: "json",
                            success: function(data) {
                                let currentSelected = subCategorySelect.val() || [];
                                subCategorySelect.empty();
                                $.each(data.data.sub_categories, function(key, value) {
                                    subCategorySelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                                });

                                if (!currentSelected.map(String).includes(String(res.sub_category.id))) {
                                    currentSelected.push(String(res.sub_category.id));
                                }

                                subCategorySelect.val(currentSelected).trigger('change').prop('disabled', false);
                            }
                        });
                    } else {
                        if (subCategorySelect.find(`option[value="${res.sub_category.id}"]`).length === 0) {
                            subCategorySelect.append(new Option(res.sub_category.name, res.sub_category.id, true, true));
                        }
                        let currentSelected = subCategorySelect.val() || [];
                        if (!currentSelected.map(String).includes(String(res.sub_category.id))) {
                            currentSelected.push(String(res.sub_category.id));
                        }
                        subCategorySelect.val(currentSelected).trigger('change').prop('disabled', false);
                    }

                    toastr.success(res.message || '{{ __("Subcategory created successfully") }}');
                } else {
                    toastr.error(res.message || '{{ __("Failed to create subcategory") }}');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Sub Category") }}');
                $('#quick_subcategory_category_error, #quick_subcategory_name_error, #quick_subcategory_thumbnail_error').text('');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.category) {
                        $('#quick_subcategory_category_error').text(errors.category[0]);
                    }
                    if (errors.name) {
                        $('#quick_subcategory_name_error').text(errors.name[0]);
                        $('#quick_subcategory_name').focus();
                    }
                    if (errors.thumbnail) {
                        $('#quick_subcategory_thumbnail_error').text(errors.thumbnail[0]);
                    }
                } else {
                    toastr.error('{{ __("An error occurred while creating the subcategory") }}');
                }
            }
        });
    });

    // Cross-Platform Shortcut Key for Sub Category: Alt + S / Option + S / Cmd + Shift + S
    $(document).on('keydown', function (e) {
        const isAltS = (e.altKey && (e.key === 's' || e.key === 'S' || e.code === 'KeyS'));
        const isCmdShiftS = (e.metaKey && e.shiftKey && (e.key === 's' || e.key === 'S' || e.code === 'KeyS'));

        if (isAltS || isCmdShiftS) {
            if ($('#modalQuickCreateSubCategory').hasClass('show') || $('#modalQuickCreateSubCategory').is(':visible')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            if (!$('#itme-master-modal').hasClass('show') && !$('#itme-master-modal').is(':visible')) {
                $('#create-modal-btn').click();
                setTimeout(function() {
                    showQuickCreateSubCategoryModal();
                }, 350);
            } else {
                showQuickCreateSubCategoryModal();
            }
        }
    });

    // Helper functions for Quick Create Unit Modal
    let quickUnitModalOpenedTime = 0;

    function showQuickCreateUnitModal() {
        quickUnitModalOpenedTime = Date.now();
        const modalEl = document.getElementById('modalQuickCreateUnit');
        if (!modalEl) return;

        if ($(modalEl).parent()[0] !== document.body) {
            $(modalEl).appendTo('body');
        }

        $('#quick_unit_name').val('');
        $('#quick_unit_name_error').text('');

        $(modalEl).css({
            'z-index': 1080,
            'display': 'block'
        }).addClass('show').removeAttr('aria-hidden');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                }
                modalInstance.show();
            } catch (e) {}
        }

        setTimeout(function() {
            $('#quick_unit_name').val('').focus();
            setTimeout(function() {
                const curVal = $('#quick_unit_name').val();
                if (curVal && /[¨ˆ˜`´∫çßµ]/.test(curVal)) {
                    $('#quick_unit_name').val(curVal.replace(/[¨ˆ˜`´∫çßµ]/g, ''));
                }
            }, 60);
        }, 100);
    }

    function hideQuickCreateUnitModal() {
        const modalEl = document.getElementById('modalQuickCreateUnit');
        if (!modalEl) return;

        $(modalEl).removeClass('show').hide().css('display', 'none').attr('aria-hidden', 'true');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            } catch (e) {}
        }
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            try {
                $(modalEl).modal('hide');
            } catch (e) {}
        }

        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.last().remove();
        } else if (backdrops.length === 1 && !$('#itme-master-modal').is(':visible')) {
            backdrops.remove();
        }

        if ($('#itme-master-modal').is(':visible') || $('#itme-master-modal').hasClass('show')) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
        }

        $('#quick_unit_name').val('');
        $('#quick_unit_name_error').text('');
    }

    $(document).on('click', '#btnQuickCreateUnit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showQuickCreateUnitModal();
    });

    $(document).on('click', '#btnCloseQuickUnitBtn, #btnCloseQuickUnitX, #modalQuickCreateUnit .btn-close, #modalQuickCreateUnit [data-bs-dismiss="modal"], #modalQuickCreateUnit [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideQuickCreateUnitModal();
    });

    $('#modalQuickCreateUnit').on('show.bs.modal shown.bs.modal', function () {
        $(this).css('z-index', 1080);
        $('#quick_unit_name').val('');
        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            }
            $('#quick_unit_name').val('').focus();
        }, 0);
    });

    $(document).on('focusin', function (e) {
        if ($('#modalQuickCreateUnit').hasClass('show') || $('#modalQuickCreateUnit').is(':visible')) {
            if ($(e.target).closest('#modalQuickCreateUnit').length) {
                e.stopPropagation();
            }
        }
    });

    $('#modalQuickCreateUnit').on('hidden.bs.modal', function () {
        $('#quick_unit_name').val('');
        $('#quick_unit_name_error').text('');
        if ($('#itme-master-modal').hasClass('show') || $('#itme-master-modal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

    $(document).on('input compositionend change', '#quick_unit_name', function() {
        $('#quick_unit_name_error').text('');
        if (Date.now() - quickUnitModalOpenedTime < 600) {
            const val = $(this).val();
            if (val && /[¨ˆ˜`´∫çßµ]/.test(val)) {
                $(this).val(val.replace(/[¨ˆ˜`´∫çßµ]/g, ''));
            }
        }
    });

    // Quick Unit Creation via AJAX
    $('#formQuickCreateUnit').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#btnSubmitQuickUnit');
        $('#quick_unit_name_error').text('');

        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Saving...") }}');

        if (!$("#quick_unit_name").val().trim()) {
            $("#quick_unit_name_error").text('{{ __("The name field is required.") }}');
            $submitBtn.prop('disabled', false).html('{{ __("Save Unit") }}');
            return false;
        }

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Unit") }}');
                if (res.status && res.unit) {
                    hideQuickCreateUnitModal();
                    $form[0].reset();

                    const unitSelect = $('select[name="unit_id"], #unit_id');
                    if (unitSelect.find(`option[value="${res.unit.id}"]`).length === 0) {
                        unitSelect.append(new Option(res.unit.name, res.unit.id, true, true));
                    } else {
                        unitSelect.val(res.unit.id);
                    }
                    unitSelect.trigger('change');

                    toastr.success(res.message || '{{ __("Unit created successfully") }}');
                } else {
                    toastr.error(res.message || '{{ __("Failed to create unit") }}');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Unit") }}');
                $('#quick_unit_name_error').text('');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#quick_unit_name_error').text(errors.name[0]);
                        $('#quick_unit_name').focus();
                    }
                } else {
                    toastr.error('{{ __("An error occurred while creating the unit") }}');
                }
            }
        });
    });

    // Cross-Platform Shortcut Key for Unit: Alt + U / Option + U / Ctrl + U / Cmd + U / Cmd + Shift + U
    $(document).on('keydown', function (e) {
        const isAltU = (e.altKey && (e.key === 'u' || e.key === 'U' || e.code === 'KeyU'));
        const isCtrlU = (e.ctrlKey && !e.altKey && (e.key === 'u' || e.key === 'U' || e.code === 'KeyU'));
        const isCmdU = (e.metaKey && !e.shiftKey && (e.key === 'u' || e.key === 'U' || e.code === 'KeyU'));
        const isCmdShiftU = (e.metaKey && e.shiftKey && (e.key === 'u' || e.key === 'U' || e.code === 'KeyU'));

        if (isAltU || isCtrlU || isCmdU || isCmdShiftU) {
            if ($('#modalQuickCreateUnit').hasClass('show') || $('#modalQuickCreateUnit').is(':visible')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            if (!$('#itme-master-modal').hasClass('show') && !$('#itme-master-modal').is(':visible')) {
                $('#create-modal-btn').click();
                setTimeout(function() {
                    showQuickCreateUnitModal();
                }, 350);
            } else {
                showQuickCreateUnitModal();
            }
        }
    });

    $(document).on('keyup', function (e) {
        const isUKey = (e.key === 'u' || e.key === 'U' || e.code === 'KeyU');
        if (isUKey && (Date.now() - quickUnitModalOpenedTime < 600)) {
            e.preventDefault();
            e.stopPropagation();
            const currentVal = $('#quick_unit_name').val();
            if (currentVal && /[¨ˆ˜`´∫çßµ]/.test(currentVal)) {
                $('#quick_unit_name').val(currentVal.replace(/[¨ˆ˜`´∫çßµ]/g, ''));
            }
        }
    });

    // Helper functions for Quick Create Material Modal
    let quickMaterialModalOpenedTime = 0;

    function generateQuickMaterialCode() {
        const codeInput = document.getElementById('quick_material_code');
        if (codeInput) {
            codeInput.value = Math.floor(Math.random() * 9000) + 1000;
        }
    }

    function showQuickCreateMaterialModal() {
        quickMaterialModalOpenedTime = Date.now();
        const modalEl = document.getElementById('modalQuickCreateMaterial');
        if (!modalEl) return;

        if ($(modalEl).parent()[0] !== document.body) {
            $(modalEl).appendTo('body');
        }

        $('#quick_material_name').val('');
        $('#quick_material_name_error, #quick_material_code_error').text('');
        generateQuickMaterialCode();

        $(modalEl).css({
            'z-index': 1080,
            'display': 'block'
        }).addClass('show').removeAttr('aria-hidden');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
                }
                modalInstance.show();
            } catch (e) {}
        }

        setTimeout(function() {
            $('#quick_material_name').val('').focus();
            setTimeout(function() {
                const curVal = $('#quick_material_name').val();
                if (curVal && /[¨ˆ˜`´∫çßµ]/.test(curVal)) {
                    $('#quick_material_name').val(curVal.replace(/[¨ˆ˜`´∫çßµ]/g, ''));
                }
            }, 60);
        }, 100);
    }

    function hideQuickCreateMaterialModal() {
        const modalEl = document.getElementById('modalQuickCreateMaterial');
        if (!modalEl) return;

        $(modalEl).removeClass('show').hide().css('display', 'none').attr('aria-hidden', 'true');

        if (window.bootstrap && bootstrap.Modal) {
            try {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            } catch (e) {}
        }
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            try {
                $(modalEl).modal('hide');
            } catch (e) {}
        }

        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.last().remove();
        } else if (backdrops.length === 1 && !$('#itme-master-modal').is(':visible')) {
            backdrops.remove();
        }

        if ($('#itme-master-modal').is(':visible') || $('#itme-master-modal').hasClass('show')) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
        }

        $('#quick_material_name, #quick_material_code').val('');
        $('#quick_material_name_error, #quick_material_code_error').text('');
    }

    $(document).on('click', '#btnQuickCreateMaterial', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showQuickCreateMaterialModal();
    });

    $(document).on('click', '#btnCloseQuickMaterialBtn, #btnCloseQuickMaterialX, #modalQuickCreateMaterial .btn-close, #modalQuickCreateMaterial [data-bs-dismiss="modal"], #modalQuickCreateMaterial [data-dismiss="modal"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideQuickCreateMaterialModal();
    });

    $('#modalQuickCreateMaterial').on('show.bs.modal shown.bs.modal', function () {
        $(this).css('z-index', 1080);
        $('#quick_material_name').val('');
        setTimeout(function() {
            const backdrops = $('.modal-backdrop');
            if (backdrops.length > 1) {
                $(backdrops[backdrops.length - 1]).css('z-index', '1075');
            }
            $('#quick_material_name').val('').focus();
        }, 0);
    });

    $(document).on('focusin', function (e) {
        if ($('#modalQuickCreateMaterial').hasClass('show') || $('#modalQuickCreateMaterial').is(':visible')) {
            if ($(e.target).closest('#modalQuickCreateMaterial').length) {
                e.stopPropagation();
            }
        }
    });

    $('#modalQuickCreateMaterial').on('hidden.bs.modal', function () {
        $('#quick_material_name, #quick_material_code').val('');
        $('#quick_material_name_error, #quick_material_code_error').text('');
        if ($('#itme-master-modal').hasClass('show') || $('#itme-master-modal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

    $(document).on('input compositionend change', '#quick_material_name', function() {
        $('#quick_material_name_error').text('');
        if (Date.now() - quickMaterialModalOpenedTime < 600) {
            const val = $(this).val();
            if (val && /[¨ˆ˜`´∫çßµ]/.test(val)) {
                $(this).val(val.replace(/[¨ˆ˜`´∫çßµ]/g, ''));
            }
        }
    });
    $(document).on('input change', '#quick_material_code', function() {
        $('#quick_material_code_error').text('');
    });

    // Quick Material Creation via AJAX
    $('#formQuickCreateMaterial').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#btnSubmitQuickMaterial');
        $('#quick_material_name_error, #quick_material_code_error').text('');

        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Saving...") }}');

        let hasClientError = false;
        if (!$("#quick_material_code").val().trim()) {
            $("#quick_material_code_error").text('{{ __("Code is required.") }}');
            hasClientError = true;
        }
        if (!$("#quick_material_name").val().trim()) {
            $("#quick_material_name_error").text('{{ __("The name field is required") }}');
            hasClientError = true;
        }
        if (hasClientError) {
            $submitBtn.prop('disabled', false).html('{{ __("Save Material") }}');
            return false;
        }

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (res) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Material") }}');
                if (res.status && res.material) {
                    hideQuickCreateMaterialModal();
                    $form[0].reset();

                    const materialSelect = $('select[name="material_id"], #material_id');
                    if (materialSelect.find(`option[value="${res.material.id}"]`).length === 0) {
                        materialSelect.append(new Option(res.material.name, res.material.id, true, true));
                    } else {
                        materialSelect.val(res.material.id);
                    }
                    materialSelect.trigger('change');

                    toastr.success(res.message || '{{ __("Material created successfully") }}');
                } else {
                    toastr.error(res.message || '{{ __("Failed to create material") }}');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html('{{ __("Save Material") }}');
                $('#quick_material_name_error, #quick_material_code_error').text('');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.code) {
                        $('#quick_material_code_error').text(errors.code[0]);
                    }
                    if (errors.name) {
                        $('#quick_material_name_error').text(errors.name[0]);
                        $('#quick_material_name').focus();
                    }
                } else {
                    toastr.error('{{ __("An error occurred while creating the material") }}');
                }
            }
        });
    });

    // Cross-Platform Shortcut Key for Material: Alt + M / Option + M / Ctrl + M / Cmd + M / Cmd + Shift + M
    $(document).on('keydown', function (e) {
        const isAltM = (e.altKey && (e.key === 'm' || e.key === 'M' || e.code === 'KeyM'));
        const isCtrlM = (e.ctrlKey && !e.altKey && (e.key === 'm' || e.key === 'M' || e.code === 'KeyM'));
        const isCmdM = (e.metaKey && !e.shiftKey && (e.key === 'm' || e.key === 'M' || e.code === 'KeyM'));
        const isCmdShiftM = (e.metaKey && e.shiftKey && (e.key === 'm' || e.key === 'M' || e.code === 'KeyM'));

        if (isAltM || isCtrlM || isCmdM || isCmdShiftM) {
            if ($('#modalQuickCreateMaterial').hasClass('show') || $('#modalQuickCreateMaterial').is(':visible')) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            if (!$('#itme-master-modal').hasClass('show') && !$('#itme-master-modal').is(':visible')) {
                $('#create-modal-btn').click();
                setTimeout(function() {
                    showQuickCreateMaterialModal();
                }, 350);
            } else {
                showQuickCreateMaterialModal();
            }
        }
    });

    $(document).on('keyup', function (e) {
        const isMKey = (e.key === 'm' || e.key === 'M' || e.code === 'KeyM');
        if (isMKey && (Date.now() - quickMaterialModalOpenedTime < 600)) {
            e.preventDefault();
            e.stopPropagation();
            const currentVal = $('#quick_material_name').val();
            if (currentVal && /[¨ˆ˜`´∫çßµ]/.test(currentVal)) {
                $('#quick_material_name').val(currentVal.replace(/[¨ˆ˜`´∫çßµ]/g, ''));
            }
        }
    });
</script>