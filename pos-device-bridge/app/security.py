import hmac
import hashlib
import time
from typing import Dict, Set
from fastapi import Request, HTTPException, Security
from fastapi.security import APIKeyHeader
from app.config import settings

used_nonces: Set[str] = set()
rate_limit_counter: Dict[str, list] = {}

X_DEVICE_ID = APIKeyHeader(name="X-Bridge-Device-ID", auto_error=False)
X_TIMESTAMP = APIKeyHeader(name="X-Bridge-Timestamp", auto_error=False)
X_NONCE = APIKeyHeader(name="X-Bridge-Nonce", auto_error=False)
X_SIGNATURE = APIKeyHeader(name="X-Bridge-Signature", auto_error=False)

def verify_bridge_signature(request: Request, body_bytes: bytes = b""):
    device_id = request.headers.get("X-Bridge-Device-ID")
    timestamp_str = request.headers.get("X-Bridge-Timestamp")
    nonce = request.headers.get("X-Bridge-Nonce")
    signature = request.headers.get("X-Bridge-Signature")

    if not device_id or not timestamp_str or not nonce or not signature:
        raise HTTPException(status_code=401, detail="Missing required security headers.")

    try:
        req_timestamp = int(timestamp_str)
    except ValueError:
        raise HTTPException(status_code=400, detail="Invalid timestamp format.")

    now = int(time.time())
    if abs(now - req_timestamp) > settings.MAX_TIMESTAMP_DIFF_SECONDS:
        raise HTTPException(status_code=401, detail="Request timestamp expired (>30s window).")

    if nonce in used_nonces:
        raise HTTPException(status_code=401, detail="Replay attack detected: Nonce already used.")

    used_nonces.add(nonce)
    if len(used_nonces) > 10000:
        used_nonces.clear()

    timestamps = rate_limit_counter.get(device_id, [])
    timestamps = [t for t in timestamps if now - t < 60]
    if len(timestamps) > 100:
        raise HTTPException(status_code=429, detail="Rate limit exceeded.")
    timestamps.append(now)
    rate_limit_counter[device_id] = timestamps

    payload_to_sign = f"{device_id}:{timestamp_str}:{nonce}:{body_bytes.decode('utf-8', errors='ignore')}"
    expected_sig = hmac.new(
        settings.SECRET_KEY.encode('utf-8'),
        payload_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

    if not hmac.compare_digest(signature.lower(), expected_sig.lower()):
        fallback_payload = f"{device_id}:{timestamp_str}:{nonce}"
        fallback_sig = hmac.new(
            settings.SECRET_KEY.encode('utf-8'),
            fallback_payload.encode('utf-8'),
            hashlib.sha256
        ).hexdigest()
        if not hmac.compare_digest(signature.lower(), fallback_sig.lower()):
            raise HTTPException(status_code=401, detail="Invalid request signature.")
