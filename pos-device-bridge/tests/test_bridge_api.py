import hmac
import hashlib
import time
import pytest
from fastapi.testclient import TestClient
from app.main import app
from app.config import settings

client = TestClient(app)

def generate_headers(device_id="DEV-02", body_str=""):
    timestamp = str(int(time.time()))
    nonce = f"nonce-{time.time()}-{hash(body_str)}"
    payload_to_sign = f"{device_id}:{timestamp}:{nonce}:{body_str}"
    signature = hmac.new(
        settings.SECRET_KEY.encode('utf-8'),
        payload_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

    return {
        "X-Bridge-Device-ID": device_id,
        "X-Bridge-Timestamp": timestamp,
        "X-Bridge-Nonce": nonce,
        "X-Bridge-Signature": signature,
    }

def test_mock_scenarios():
    body = '{"amount": 100.0, "provider": "mock", "scenario": "disconnected"}'
    headers = generate_headers("DEV-02", body)
    res = client.post("/v1/payments/sale", content=body, headers=headers)
    assert res.json()["status"] == "failed"
    assert res.json()["failure_code"] == "TERMINAL_DISCONNECTED"

    body = '{"amount": 500.0, "provider": "mock", "scenario": "amount_mismatch"}'
    headers = generate_headers("DEV-02", body)
    res = client.post("/v1/payments/sale", content=body, headers=headers)
    assert res.json()["status"] == "amount_mismatch"
    assert res.json()["approved_amount"] == 490.0

def test_print_invoice_endpoint():
    body = '{"order_number": "INV-2026-001", "items": [{"name": "Item 1", "qty": 1}]}'
    headers = generate_headers("DEV-02", body)
    res = client.post("/v1/print/invoice", content=body, headers=headers)
    assert res.status_code == 200
    assert res.json()["status"] == "printed"
    assert res.json()["order_number"] == "INV-2026-001"
