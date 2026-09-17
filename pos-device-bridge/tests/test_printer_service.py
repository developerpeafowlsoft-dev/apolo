import pytest
from app.printers.mock import MockPrinter

@pytest.fixture
def mock_printer():
    return MockPrinter()

def test_printer_online_success(mock_printer):
    res = mock_printer.print_invoice({
        "order_number": "ORD-1001",
        "paper_width_mm": 80,
        "items": [{"name": "Item A", "price": 100.0}],
        "pulse_cash_drawer": True,
    })
    assert res["status"] == "printed"
    assert res["cash_drawer_pulsed"] is True

def test_printer_offline_error(mock_printer):
    res = mock_printer.print_invoice({"scenario": "printer_offline"})
    assert res["status"] == "failed"
    assert res["failure_code"] == "PRINTER_OFFLINE"

def test_no_paper_error(mock_printer):
    res = mock_printer.print_invoice({"scenario": "no_paper"})
    assert res["status"] == "failed"
    assert res["failure_code"] == "NO_PAPER"

def test_paper_misaligned_error(mock_printer):
    res = mock_printer.print_invoice({"scenario": "paper_misaligned"})
    assert res["status"] == "failed"
    assert res["failure_code"] == "PAPER_MISALIGNED"

def test_duplicate_print_call_prevention(mock_printer):
    res1 = mock_printer.print_invoice({"job_id": "JOB-99", "order_number": "ORD-1002"})
    assert res1["status"] == "printed"

    res2 = mock_printer.print_invoice({"job_id": "JOB-99", "order_number": "ORD-1002"})
    assert res2["status"] == "printed"
    assert res2["already_printed"] is True
