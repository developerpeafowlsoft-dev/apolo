/**
 * Provider-Independent EDC Terminal POS Integration Engine
 */

var activeEdcAttemptId = null;
var activeEdcToken = null;
var edcPollTimer = null;
var isEdcPaymentActive = false;
var activeTerminalConfig = null;

$(document).ready(function () {
    fetchCurrentTerminalInfo();
    setupEdcKeyboardShortcuts();
});

function fetchCurrentTerminalInfo() {
    var counterId = $('#counter_id').val() || 1;
    $.ajax({
        url: '/shop/pos/terminals/current?counter_id=' + counterId,
        type: 'GET',
        success: function (res) {
            if (res.status && res.terminal) {
                activeTerminalConfig = res.terminal;
                $('#edc-terminal-name').text(res.terminal.name);
                $('#edc-provider').text(res.terminal.provider.toUpperCase());
                $('#edc-terminal-id').text(res.terminal.terminal_id);
                $('#edc-connection-badge').removeClass('bg-secondary bg-danger').addClass('bg-success').text('ONLINE');
                $('#edc-last-health').text(new Date().toLocaleTimeString());
            } else {
                $('#edc-connection-badge').removeClass('bg-secondary bg-success').addClass('bg-warning text-dark').text('NO TERMINAL');
            }
        },
        error: function () {
            $('#edc-connection-badge').removeClass('bg-secondary bg-success').addClass('bg-danger').text('OFFLINE');
        }
    });
}

function setupEdcKeyboardShortcuts() {
    $(document).on('keydown', function (e) {
        if (isEdcPaymentActive) {
            if (e.ctrlKey && (e.key === 'x' || e.key === 'X')) {
                e.preventDefault();
                cancelEdcPayment();
                return;
            }
            if (e.ctrlKey && e.key === 'F11') {
                e.preventDefault();
                checkEdcPaymentStatus();
                return;
            }
            return;
        }

        if (e.key === 'F9') {
            e.preventDefault();
            $('#payment-method').val('cash').trigger('change');
            $('#cash-received').focus();
            return;
        }

        if (e.shiftKey && (e.key === 'F10' || e.keyCode === 121)) {
            e.preventDefault();
            e.stopPropagation();
            $('#payment-method').val('paytm_card').trigger('change');
            startTerminalPayment('paytm_card');
            return;
        }

        if (e.shiftKey && (e.key === 'F11' || e.keyCode === 122)) {
            e.preventDefault();
            e.stopPropagation();
            $('#payment-method').val('paytm_upi').trigger('change');
            startTerminalPayment('paytm_upi');
            return;
        }

        if (e.key === 'F12') {
            e.preventDefault();
            if ($('#payment-method').val() === 'cash') {
                submitGridOrder();
            }
            return;
        }
    });
}

function togglePaymentInputs() {
    var mode = $('#payment-method').val();
    var grandTotal = parseFloat($('#big-bill-amount').text().replace(/[^0-9.]/g, '')) || 0;

    if (mode === 'cash') {
        $('#cash-received').prop('readonly', false);
        $('#card-received').prop('readonly', true).val('0.00');
    } else if (mode.includes('card') || mode.includes('upi')) {
        $('#cash-received').prop('readonly', true).val('0.00');
        $('#card-received').prop('readonly', true).val(grandTotal.toFixed(2));
    } else if (mode === 'split') {
        $('#cash-received').prop('readonly', false);
        $('#card-received').prop('readonly', false);
    }
    calculateDueBalance();
}

function lockPosUi(lock) {
    isEdcPaymentActive = lock;
    $('#payment-method, #counter_id, #customer_id, #cart_discount').prop('disabled', lock);
    $('.btn-add-product, .btn-clear-cart, .btn-hold-bill, .btn-remove-row').prop('disabled', lock);
}

function startTerminalPayment(selectedMethod) {
    var mode = selectedMethod || $('#payment-method').val();
    if (mode === 'cash') return;

    var payableAmount = parseFloat($('#big-bill-amount').text().replace(/[^0-9.]/g, '')) || 0;
    if (payableAmount <= 0) {
        toastr.error('Bill amount must be greater than zero.');
        return;
    }

    lockPosUi(true);

    var provider = mode.startsWith('paytm') ? 'paytm' : (mode.startsWith('phonepe') ? 'phonepe' : 'mock');
    var methodType = mode.endsWith('upi') ? 'upi' : 'card';
    var counterId = $('#counter_id').val() || 1;

    $('#edcModalTitle').html('<i class="fa-solid fa-cash-register me-2"></i>' + provider.toUpperCase() + ' Payment');
    $('#edcModalAmount').text($('#big-bill-amount').text());
    $('#edcModalMethod').text(provider.toUpperCase() + ' (' + methodType.toUpperCase() + ')');
    $('#edcModalStatusMsg').removeClass('alert-warning alert-success alert-danger').addClass('alert-info')
        .text('Please complete the payment on the selected EDC terminal.');
    $('#edcModalSpinner').show();
    $('#btn-edc-cancel').show();
    $('#btn-edc-check-status, #btn-edc-reprint').hide();
    $('#edcPaymentModal').modal('show');

    $.ajax({
        url: '/shop/pos/payments/initiate',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            counter_id: counterId,
            cart_name: 'POS_CART_' + counterId,
            payment_method: methodType,
            amount: payableAmount,
            provider: provider,
        },
        success: function (res) {
            if (res.status && res.attempt) {
                activeEdcAttemptId = res.attempt.attempt_id;
                activeEdcToken = res.bridge_session_token;

                $('#edc-detail-attempt-id').text(activeEdcAttemptId.substring(0, 8) + '...');
                $('#edc-detail-terminal').text(res.attempt.terminal.name);
                $('#edc-detail-status').text('SENT TO TERMINAL');

                invokeLocalBridge(res.attempt, res.bridge_session_token);
            } else {
                handleEdcError(res.message || 'Initiation failed');
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Server initiation error';
            handleEdcError(msg);
        }
    });
}

function invokeLocalBridge(attempt, token) {
    var bridgeUrl = 'http://127.0.0.1:8089/v1/payments/sale';

    $.ajax({
        url: bridgeUrl,
        type: 'POST',
        contentType: 'application/json',
        headers: {
            'X-Bridge-Device-ID': 'POS-BROWSER-CLIENT-01',
            'X-Bridge-Timestamp': Math.floor(Date.now() / 1000).toString(),
            'X-Bridge-Nonce': 'nonce-' + Math.random().toString(36).substring(2, 15),
            'X-Bridge-Signature': attempt.bridge_signature.signature,
        },
        data: JSON.stringify({
            attempt_id: attempt.attempt_id,
            amount: attempt.amount,
            provider: attempt.provider,
            payment_method: attempt.payment_method,
            token: token,
        }),
        success: function (bridgeRes) {
            sendResultToLaravel(attempt.attempt_id, token, bridgeRes);
        },
        error: function () {
            handleEdcUnknown('Payment confirmation is delayed. Do not collect payment again. Use Check Status.');
        }
    });
}

function sendResultToLaravel(attemptId, token, bridgeRes) {
    $.ajax({
        url: '/shop/pos/payments/' + attemptId + '/result',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            bridge_token: token,
            status: bridgeRes.status || 'unknown',
            approved_amount: bridgeRes.approved_amount || 0,
            transaction_id: bridgeRes.provider_transaction_id || null,
            rrn: bridgeRes.rrn || null,
            approval_code: bridgeRes.approval_code || null,
            raw_response: bridgeRes.raw_response || bridgeRes,
        },
        success: function (res) {
            if (res.status && (res.attempt_status === 'success' || res.attempt_status === 'verified')) {
                handleEdcSuccess(attemptId);
            } else if (res.attempt_status === 'cancelled') {
                handleEdcCancelled();
            } else if (res.attempt_status === 'unknown') {
                handleEdcUnknown('Payment confirmation is delayed. Do not collect payment again. Use Check Status.');
            } else {
                handleEdcError('Payment declined or failed on terminal.');
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Result processing failed';
            handleEdcError(msg);
        }
    });
}

function handleEdcSuccess(attemptId) {
    $('#edcModalStatusMsg').removeClass('alert-info alert-warning alert-danger').addClass('alert-success')
        .text('Payment verified. Finalizing bill and printing invoice.');
    $('#edcModalSpinner').hide();
    $('#edc-detail-status').text('VERIFIED');

    setTimeout(function () {
        submitGridOrderWithAttempt(attemptId);
    }, 1000);
}

function submitGridOrderWithAttempt(attemptId) {
    var payload = typeof buildGridCheckoutPayload === 'function' ? buildGridCheckoutPayload() : {};
    payload.payment_attempt_id = attemptId;
    payload.idempotency_key = attemptId;

    $.ajax({
        url: '/shop/pos/checkout-grid',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.status) {
                $('#edcPaymentModal').modal('hide');
                lockPosUi(false);
                toastr.success('Order Finalized & Invoice Printed!');
                if (res.invoice_url) {
                    window.open(res.invoice_url, '_blank');
                }
                if (typeof resetPosGrid === 'function') resetPosGrid();
            } else {
                handleEdcPrintFailure(res.message || 'Order finalization failed.');
            }
        },
        error: function () {
            handleEdcPrintFailure('Order commit error.');
        }
    });
}

function handleEdcUnknown(msg) {
    $('#edcModalStatusMsg').removeClass('alert-info alert-success alert-danger').addClass('alert-warning').text(msg);
    $('#edcModalSpinner').hide();
    $('#btn-edc-check-status').show();
    $('#edc-detail-status').text('UNKNOWN / PENDING');
}

function handleEdcError(msg) {
    $('#edcModalStatusMsg').removeClass('alert-info alert-success alert-warning').addClass('alert-danger').text(msg);
    $('#edcModalSpinner').hide();
    $('#edc-detail-status').text('FAILED');
    lockPosUi(false);
}

function handleEdcCancelled() {
    $('#edcModalStatusMsg').removeClass('alert-info alert-success alert-warning').addClass('alert-secondary').text('Payment cancelled on EDC device.');
    $('#edcModalSpinner').hide();
    $('#edcPaymentModal').modal('hide');
    lockPosUi(false);
}

function handleEdcPrintFailure(msg) {
    $('#edcModalStatusMsg').removeClass('alert-info alert-success alert-warning').addClass('alert-danger')
        .text('Payment and bill are complete. Invoice printing failed. Use Reprint Invoice. Do not collect payment again.');
    $('#btn-edc-reprint').show();
    $('#btn-edc-cancel').hide();
}

function checkEdcPaymentStatus() {
    if (!activeEdcAttemptId) return;
    $.ajax({
        url: '/shop/pos/payments/' + activeEdcAttemptId + '/status',
        type: 'GET',
        success: function (res) {
            if (res.status && (res.attempt_status === 'success' || res.attempt_status === 'verified')) {
                handleEdcSuccess(activeEdcAttemptId);
            } else {
                toastr.info('Current status: ' + res.attempt_status.toUpperCase());
            }
        }
    });
}

function cancelEdcPayment() {
    if (!activeEdcAttemptId) {
        $('#edcPaymentModal').modal('hide');
        lockPosUi(false);
        return;
    }
    $.ajax({
        url: '/shop/pos/payments/' + activeEdcAttemptId + '/cancel',
        type: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function () {
            handleEdcCancelled();
        }
    });
}
