import hmac
import hashlib
import time
import pytest
from fastapi.testclient import TestClient
from app.main import app
from app.config import settings

client = TestClient(app)

def generate_headers(device_id="DEV-01", body_str=""):
    timestamp = str(int(time.time()))
    nonce = f"nonce-{time.time()}"
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

def test_health_check_public_access():
    response = client.get("/v1/health")
    assert response.status_code == 200
    assert response.json()["status"] == "healthy"

def test_missing_security_headers_returns_401():
    response = client.get("/v1/terminals")
    assert response.status_code == 401
    assert "Missing required security headers" in response.json()["detail"]

def test_expired_timestamp_returns_401():
    headers = generate_headers()
    headers["X-Bridge-Timestamp"] = str(int(time.time()) - 100)
    
    payload_to_sign = f"DEV-01:{headers['X-Bridge-Timestamp']}:{headers['X-Bridge-Nonce']}:"
    headers["X-Bridge-Signature"] = hmac.new(
        settings.SECRET_KEY.encode('utf-8'),
        payload_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

    response = client.get("/v1/terminals", headers=headers)
    assert response.status_code == 401
    assert "expired" in response.json()["detail"]

def test_valid_signed_sale_request():
    body = '{"amount": 500.0, "provider": "mock", "scenario": "success"}'
    headers = generate_headers("DEV-01", body)

    response = client.post("/v1/payments/sale", content=body, headers=headers)
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "success"
    assert data["approved_amount"] == 500.0

def test_replay_attack_prevention():
    body = '{"amount": 100.0, "provider": "mock"}'
    headers = generate_headers("DEV-01", body)

    res1 = client.post("/v1/payments/sale", content=body, headers=headers)
    assert res1.status_code == 200

    res2 = client.post("/v1/payments/sale", content=body, headers=headers)
    assert res2.status_code == 401
    assert "Replay attack" in res2.json()["detail"]
