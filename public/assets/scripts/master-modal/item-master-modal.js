// function showCustomLoader(position = 'center') {
//     const positions = {
//         'center': 'translate(-50%, -50%)',
//         'top': 'translate(-50%, 0)',
//         'bottom': 'translate(-50%, -100%)',
//         'left': 'translate(0, -50%)',
//         'right': 'translate(-100%, -50%)',
//     };
//
//     HoldOn.open({
//         theme: "custom",
//         content: `
//                 <div style="
//                     position: absolute;
//                     top: ${position === 'center' ? '50%' : position === 'top' ? '10%' : position === 'bottom' ? '90%' : '50%'};
//                     left: ${position === 'center' ? '50%' : position === 'left' ? '10%' : position === 'right' ? '90%' : '50%'};
//                     transform: ${positions[position]};
//                     z-index: 9999;
//                 ">
//                     <img src="{{ asset('assets/images/APOLO_GIF.gif') }}" alt="Loading..." style="width:80px; height:auto;" />
//                 </div>
//             `,
//     });
// }
//
// function modelDataLoad(callback = null) {
//     $('#item_id').val('');
//     $('#formDataItemMaster')[0].reset();
//     $(".errorSpan,#slug").empty();
//
//     $("#brand_id").empty().append('<option value="">{{ __("Select Brand") }}</option>');
//     $("#category").empty().append('<option value="" selected disabled>{{ __("Select Category") }}</option>');
//     $("#hsn_master_id").empty().append('<option value="">{{ __("Select HSN Code") }}</option>');
//     $("#vat_tax_id").empty().append('<option value="">{{ __("Select tax type") }}</option>');
//     $("#salesman_id").empty().append('<option value="">{{ __("Select Salesman") }}</option>');
//     $("#material_id").empty().append('<option value="">{{ __("Select Material") }}</option>');
//     $("#unit_id").empty().append('<option value="">{{ __("Select Unit") }}</option>');
//     $('select[name="colorIds[]"]').empty().append(' <option value="all">{{ __("Select All") }}</option>');
//     $('select[name="sizeIds[]"]').empty().append(' <option value="all">{{ __("Select All") }}</option>');
//     $('select[name="sub_category[]"]').prop('disabled', true).empty();
//     $("#vat_tax_id_hidden").val('');
//     $('#salesman_id').val('').trigger('change');
//     $('#commission_type').val('').trigger('change');
//
//     $('#selectedColorsTableBody').empty();
//     $('#selectedSizesTableBody').empty();
//     $('#colorBox').hide();
//     $('#sizeBox').hide();
//
//     $.ajax({
//         url: '{{ route('shop.itemMaster.modalData') }}',
//         type: 'GET',
//         success: function (data) {
//             data.brands.forEach(function (brand) {
//                 $("#brand_id").append(`<option value="${brand.id}">${brand.name}</option>`);
//             });
//
//             data.categories.forEach(function (category) {
//                 $("#category").append(`<option value="${category.id}">${category.name}</option>`);
//             });
//
//             data.colors.forEach(function (color) {
//                 $('select[name="colorIds[]"]').append(
//                     `<option value="${color.id}" data-color="${color.color_code}" data-name="${color.name}">${color.name}</option>`
//                 );
//             });
//
//             data.hsnMasters.forEach(function (hsnMaster) {
//                 $("#hsn_master_id").append(`<option value="${hsnMaster.id}" data-vattax="${hsnMaster.vat_tax_id}">${hsnMaster.hsn_code}</option>`);
//             });
//
//             data.taxs.forEach(function (tax) {
//                 $("#vat_tax_id").append(`<option value="${tax.id}">${tax.name} ${tax.percentage}%</option>`);
//             });
//
//             data.sizes.forEach(function (size) {
//                 $('select[name="sizeIds[]"]').append(
//                     `<option value="${size.id}" data-size="${size.name}">${size.name}</option>`
//                 );
//             });
//
//             data.salesmans.forEach(function (salesman) {
//                 $("#salesman_id").append(`<option value="${salesman.id}">${salesman.name}</option>`);
//             });
//
//             data.units.forEach(function (unit) {
//                 $("#unit_id").append(`<option value="${unit.id}">${unit.name}</option>`);
//             });
//
//             data.materials.forEach(function (material) {
//                 $("#material_id").append(`<option value="${material.id}">${material.name}</option>`);
//             });
//
//             $("#itme-master-modal").modal("show");
//             HoldOn.close();
//
//             // 🔁 Call the callback if provided
//             if (typeof callback === 'function') {
//                 callback();
//             }
//         },
//         error: function () {
//             toastr.error("Failed to load.");
//         }
//     });
// }

