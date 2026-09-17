import pytest
from app.providers.paytm import PaytmTerminalProvider

@pytest.fixture
def paytm_provider():
    return PaytmTerminalProvider({
        "merchant_id": "TEST_MERCHANT_PAYTM",
        "terminal_id": "TERM_PAYTM_UAT_001",
        "port_name": "COM3",
        "environment": "uat",
    })

def test_paise_conversion(paytm_provider):
    assert paytm_provider._convert_rupees_to_paise(500.50) == 50050
    assert paytm_provider._convert_rupees_to_paise(1000.00) == 100000

def test_card_tap_insert_swipe(paytm_provider):
    res = paytm_provider.sale({"amount": 750.00, "payment_method": "card"})
    assert res["status"] == "success"
    assert res["approved_amount"] == 750.00
    assert res["provider"] == "paytm"
    assert res["masked_pan"] is not None
    assert res["safe_raw_response"]["amountInPaise"] == 75000

def test_upi_dynamic_qr(paytm_provider):
    res = paytm_provider.sale({"amount": 250.00, "payment_method": "upi"})
    assert res["status"] == "success"
    assert res["payment_method"] == "qr"
    assert res["upi_reference"] is not None

def test_customer_cancellation(paytm_provider):
    res = paytm_provider.sale({"amount": 100.00, "scenario": "user_cancelled"})
    assert res["status"] == "cancelled"
    assert res["failure_code"] == "USER_CANCELLED"

def test_declined_transaction(paytm_provider):
    res = paytm_provider.sale({"amount": 100.00, "scenario": "declined"})
    assert res["status"] == "failed"
    assert res["failure_code"] == "DECLINED_BY_BANK"

def test_cable_removal(paytm_provider):
    res = paytm_provider.sale({"amount": 100.00, "scenario": "cable_removed"})
    assert res["status"] == "failed"
    assert res["failure_code"] == "CABLE_DISCONNECTED"

def test_timeout_normalizes_to_unknown(paytm_provider):
    res = paytm_provider.sale({"amount": 100.00, "scenario": "timeout"})
    assert res["status"] == "unknown"
    assert res["failure_code"] == "RESPONSE_TIMEOUT"

def test_status_recovery(paytm_provider):
    res = paytm_provider.status({"transaction_id": "TX-RECOVER-123", "scenario": "status_recovery_success", "amount": 500.00})
    assert res["status"] == "success"
    assert res["provider_transaction_id"] == "TX-RECOVER-123"
    assert res["safe_raw_response"]["recovered"] is True

def test_void_and_refund(paytm_provider):
    void_res = paytm_provider.void({"transaction_id": "TX-VOID-001", "original_amount": 500.00})
    assert void_res["status"] == "voided"

    refund_res = paytm_provider.refund({"transaction_id": "TX-REFUND-001", "refund_amount": 250.00})
    assert refund_res["status"] == "refunded"
    assert refund_res["approved_amount"] == 250.00

def test_terminal_printer_error(paytm_provider):
    paper_res = paytm_provider.sale({"amount": 100.00, "scenario": "printer_no_paper"})
    assert paper_res["status"] == "failed"
    assert paper_res["failure_code"] == "PRINTER_NO_PAPER"

    malfunction_res = paytm_provider.sale({"amount": 100.00, "scenario": "printer_malfunction"})
    assert malfunction_res["status"] == "failed"
    assert malfunction_res["failure_code"] == "PRINTER_MALFUNCTION"

def test_port_discovery_and_recovery(paytm_provider):
    ports = paytm_provider.discover_ports()
    assert "COM3" in ports
    assert paytm_provider.recover_connection() is True
