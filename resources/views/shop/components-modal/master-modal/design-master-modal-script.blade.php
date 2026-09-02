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

    function modelDesignMasterDataLoad(callback = null) {
        $('#design_id').val('');
        $('#formDataDesignMaster')[0].reset();
        $(".errorSpan").empty();

        $("#itemname").empty().append('<option value="">{{ __("Select Item Name") }}</option>');
        $("#account_master").empty().append('<option value="">{{ __("Select Account Master") }}</option>');

        $.ajax({
            url: '{{ route('shop.designMaster.modalData') }}',
            type: 'GET',
            success: function (data) {
                // data.itemMasters.forEach(function (itemMaster) {
                //     $("#itemname").append(`<option value="${itemMaster.id}">${itemMaster.name}</option>`);
                // });
                //
                // data.accountMasters.forEach(function (accountMaster) {
                //     $("#account_master").append(`<option value="${accountMaster.id}" data-accName="${accountMaster.accountName}">${accountMaster.accountshortcode} - ${accountMaster.accountName}</option>`);
                // });


                $("#design-master-modal").modal("show");
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

    // Account Master On Change Name Get
    $(document).ready(function () {
        // Enter key sequential focus traversal inside Add Design Master modal
        $(document).on('keydown', '#formDataDesignMaster input, #formDataDesignMaster select, #formDataDesignMaster textarea', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                // If Select2 search dropdown is actively open, let standard select2 selection proceed
                if ($('.select2-container--open').length > 0) {
                    return;
                }

                e.preventDefault();

                // Find all focusable fields inside the modal form
                const fields = $('#formDataDesignMaster').find('input:not([readonly]):visible, select:visible, textarea:visible, button:visible').filter(function() {
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

        $('#account_master').on('select2:select', function (e) {
            const data = e.params.data;
            $("#account_master_name").val(data.accName || '');
        });

        // Select clear hone par input blank
        $('#account_master').on('select2:unselect', function (e) {
            $("#account_master_name").val('');
        });
    });

    // Design Code
    const generateCode = () => {
        const code = document.getElementById('design_number');
        code.value = Math.floor(Math.random() * 900000) + 100000;
    }

    // Upper Case
    $("#design_number").on('keyup', function () {
        this.value = this.value.toUpperCase();
    });


    //Markup And Markdown Get
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

    // Search Item Name Record Get
    $(document).on('shown.bs.modal', '#design-master-modal', function () {

        // Item Name Search
        $('#itemname').select2({
            dropdownParent: $('#design-master-modal'),
            placeholder: "Select Item Name",
            allowClear: true,
            ajax: {
                url: '{{ route('shop.designMaster.modalData') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {searchItemName: params.term || ''};
                },
                processResults: function (data) {
                    console.log(data);
                    return {
                        results: data.itemMasters.map(function (item) {
                            return {id: item.id, text: item.name};
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        // Account Code And Name Search
        $('#account_master').select2({
            dropdownParent: $('#design-master-modal'),
            placeholder: "Select Account Master",
            allowClear: true,
            ajax: {
                url: '{{ route('shop.designMaster.modalData') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {searchAccountMaster: params.term || ''};
                },
                processResults: function (data) {
                    return {
                        results: data.accountMasters.map(function (accountMaster) {
                            return {
                                id: accountMaster.id,
                                text: accountMaster.accountshortcode + ' - ' + accountMaster.accountName,
                                accName: accountMaster.accountName
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

    });


    // Data Submit
    $(document).ready(function () {
        $("#formDataDesignMaster").on('submit', function (e) {
            e.preventDefault();

            var submitButton = $(this).find('button[type="submit"]');
            var originalButtonText = submitButton.text();
            var formData = new FormData(this);

            var itemId = $('#design_id').val();

            if (itemId) {
                // Update
                url = `/shop/design-master/${itemId}/update`;
                method = 'POST';
                formData.append('_method', 'PUT');
            } else {
                // Create
                url = "{{ route('shop.designMaster.store') }}";
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
                        refreshDesignMasterList();
                        toastr.success(res.message);
                        $("#design-master-modal").modal("hide");

                        // Auto-fill in Inward Product Design if active input or row exists
                        var activeRow = window.lastFocusedInwardRow || (window.lastFocusedInwardDesignInput ? $(window.lastFocusedInwardDesignInput).closest('tr')[0] : null);
                        if (res.design && activeRow) {
                            var $row = $(activeRow);
                            var $input = $row.find('input.designno');

                            $input.val(res.design.design_number);
                            $row.find('input[name="designid[]"]').val(res.design.id);

                            if (res.design.products) {
                                $row.find('input[name="item[]"]').val(res.design.products.name);
                                $row.find('input[name="itemid[]"]').val(res.design.products.id);

                                if (res.design.products.hsn_master) {
                                    $row.find('input[name="taxCode[]"]').val(res.design.products.hsn_master.hsn_code);
                                    $row.find('input[name="taxCodeId[]"]').val(res.design.products.hsn_master.id);
                                }
                                if (res.design.products.vat_tax) {
                                    $row.find('input[name="sgst[]"]').val(res.design.products.vat_tax.percentage);
                                    $row.find('input[name="sgstId[]"]').val(res.design.products.vat_tax.id);
                                }
                            }

                            $row.find('input[name="qty[]"]').val(res.design.quantity || 0);
                            $row.find('input[name="purcRate[]"]').val(res.design.buy_price || 0.00);
                            $row.find('input[name="mrp[]"]').val(res.design.mrp || 0.00);
                            $row.find('input[name="mark_up[]"]').val(res.design.mark_up || 0.00);
                            $row.find('input[name="mark_down[]"]').val(res.design.mark_down || 0.00);
                            $row.find('input[name="disc[]"]').val(res.design.discount_percentage || 0);
                            $row.find('input[name="amount[]"]').val(res.design.price || 0.00);
                            $row.find('input[name="netPurcRate[]"]').val((res.design.mrp || 0.00) * (res.design.quantity || 0));

                            // Move focus to Qty input
                            $row.find('input[name="qty[]"]').focus().select();
                        }
                    }
                    submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $(".errorSpan").text('');

                        // Show new errors
                        $.each(errors, function (key, messages) {
                            var errorSpan = $('#' + key + 'ErrorMessage');

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

    // Refresh Data
    function refreshDesignMasterList() {
        $.ajax({
            url: "{{ route('shop.designMaster.index') }}",
            type: "GET",
            success: function (data) {
                console.log(data)
                $("#designMasterList").html(data);
            },
            error: function () {
                toastr.error("Failed to refresh list");
            }
        });
    }

    $('#design-master-modal').on('shown.bs.modal', function () {
        $('#design_number').focus();
    });


</script>