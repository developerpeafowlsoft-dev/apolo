<script>
    // Allow Select2 search input to retain focus inside Bootstrap modals across the system
    if ($.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype.enforceFocus = function () {};
        if ($.fn.modal.Constructor.prototype._enforceFocus) {
            $.fn.modal.Constructor.prototype._enforceFocus = function () {};
        }
    }

    // Auto focus search field when any Select2 dropdown opens
    $(document).on('select2:open', function () {
        setTimeout(function () {
            const searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        }, 50);
    });

    function initItemMasterSelect2() {
        const $modal = $('#itme-master-modal');
        if (!$modal.length) return;

        const selectSelectors = [
            '#brand_id',
            '#category',
            '#sub_category',
            '#hsn_master_id',
            '#vat_tax_id',
            '#unit_id',
            '#material_id',
            '#salesman_id',
            '#commission_type'
        ];

        selectSelectors.forEach(function (sel) {
            const $el = $modal.find(sel);
            if ($el.length) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    width: '100%',
                    dropdownParent: $modal
                });
            }
        });

        const $colorSelect = $modal.find('.colorSelect');
        if ($colorSelect.length) {
            if ($colorSelect.hasClass('select2-hidden-accessible')) {
                $colorSelect.select2('destroy');
            }
            $colorSelect.select2({
                width: '100%',
                dropdownParent: $modal,
                templateResult: formatState
            });
        }

        const $sizeSelect = $modal.find('.sizeSelector');
        if ($sizeSelect.length) {
            if ($sizeSelect.hasClass('select2-hidden-accessible')) {
                $sizeSelect.select2('destroy');
            }
            $sizeSelect.select2({
                width: '100%',
                dropdownParent: $modal
            });
        }
    }

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

                $("#itme-master-modal").modal("show");
                HoldOn.close();

                initItemMasterSelect2();

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
            if (categoryId) {
                $.ajax({
                    url: '/api/sub-categories?category_id=' + categoryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        var subCategorySelected = $('select[name="sub_category[]"]');
                        subCategorySelected.empty();

                        $.each(data.data.sub_categories, function(key, value) {
                            subCategorySelected.append('<option value="' + value
                                    .id +
                                '">' + value.name + '</option>');
                        });
                        subCategorySelected.trigger('change').prop('disabled', false);
                    },
                    error: function() {
                        console.log('Error retrieving subcategories. Please try again.');
                    }
                });
            } else {
                $('select[name="subCategory[]"]').empty();
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
        initItemMasterSelect2();
        $('#name').focus();
    });
</script>