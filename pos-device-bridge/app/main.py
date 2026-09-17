from fastapi import FastAPI, Request, HTTPException, Depends
from fastapi.middleware.cors import CORSMiddleware
from typing import Dict, Any
from app.config import settings
from app.security import verify_bridge_signature
from app.storage import attempt_storage
from app.providers.mock import MockTerminalProvider
from app.providers.paytm import PaytmTerminalProvider
from app.providers.phonepe import PhonePeTerminalProvider
from app.printers.mock import MockPrinter

app = FastAPI(
    title="Local POS Device Bridge",
    version="1.0.0",
    docs_url="/docs",
    redoc_url=None
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.POS_ORIGINS,
    allow_credentials=True,
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=[
        "Content-Type",
        "X-Bridge-Device-ID",
        "X-Bridge-Timestamp",
        "X-Bridge-Nonce",
        "X-Bridge-Signature",
    ],
)

providers = {
    "mock": MockTerminalProvider(),
    "paytm": PaytmTerminalProvider(),
    "phonepe": PhonePeTerminalProvider(),
}
mock_printer = MockPrinter()

@app.get("/v1/health")
def health_check():
    return {
        "status": "healthy",
        "bridge_version": "1.0.0",
        "providers": list(providers.keys()),
    }

@app.get("/v1/terminals")
def get_terminals(request: Request):
    body_bytes = b""
    verify_bridge_signature(request, body_bytes)

    return {
        "terminals": [
            {"terminal_id": "MOCK-TERM-001", "name": "Mock Terminal", "provider": "mock", "is_online": True},
            {"terminal_id": "PAYTM-ECR-001", "name": "Paytm ECR", "provider": "paytm", "is_online": True},
            {"terminal_id": "PHONEPE-EDC-001", "name": "PhonePe EDC", "provider": "phonepe", "is_online": True},
        ]
    }

@app.post("/v1/payments/sale")
async def process_sale(request: Request):
    body_bytes = await request.body()
    verify_bridge_signature(request, body_bytes)
    data = await request.json()

    provider_name = data.get("provider", "mock").lower()
    provider = providers.get(provider_name, providers["mock"])

    result = provider.sale(data)
    attempt_id = data.get("attempt_id")
    if attempt_id:
        attempt_storage.set(attempt_id, result)

    return result

@app.post("/v1/payments/status")
async def process_status(request: Request):
    body_bytes = await request.body()
    verify_bridge_signature(request, body_bytes)
    data = await request.json()

    provider_name = data.get("provider", "mock").lower()
    provider = providers.get(provider_name, providers["mock"])

    result = provider.status(data)
    attempt_id = data.get("attempt_id")
    if attempt_id:
        attempt_storage.set(attempt_id, result)

    return result

@app.post("/v1/payments/cancel")
async def process_cancel(request: Request):
    body_bytes = await request.body()
    verify_bridge_signature(request, body_bytes)
    data = await request.json()

    provider_name = data.get("provider", "mock").lower()
    provider = providers.get(provider_name, providers["mock"])

    return provider.cancel(data)

@app.post("/v1/payments/void")
async def process_void(request: Request):
    body_bytes = await request.body()
    verify_bridge_signature(request, body_bytes)
    data = await request.json()

    provider_name = data.get("provider", "mock").lower()
    provider = providers.get(provider_name, providers["mock"])

    return provider.void(data)

@app.post("/v1/payments/refund")
async def process_refund(request: Request):
    body_bytes = await request.body()
    verify_bridge_signature(request, body_bytes)
    data = await request.json()

    provider_name = data.get("provider", "mock").lower()
    provider = providers.get(provider_name, providers["mock"])

    return provider.refund(data)

@app.post("/v1/print/invoice")
async def print_invoice(request: Request):
    body_bytes = await request.body()
    verify_bridge_signature(request, body_bytes)
    data = await request.json()

    return mock_printer.print_invoice(data)
