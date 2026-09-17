import time
import uuid
from typing import Dict, Any
from app.providers.base import BaseTerminalProvider

class MockTerminalProvider(BaseTerminalProvider):
    def __init__(self):
        self.scenario_state: Dict[str, int] = {}

    def sale(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        scenario = request_data.get("scenario", "success")
        amount = float(request_data.get("amount", 0.0))
        attempt_id = request_data.get("attempt_id", str(uuid.uuid4()))

        if scenario == "disconnected":
            return {
                "status": "failed",
                "failure_code": "TERMINAL_DISCONNECTED",
                "failure_message": "Payment terminal cable or bluetooth connection lost.",
                "approved_amount": 0.0,
            }

        if scenario == "failure":
            return {
                "status": "failed",
                "failure_code": "DECLINED_BY_BANK",
                "failure_message": "Insufficient funds or card block.",
                "approved_amount": 0.0,
            }

        if scenario == "cancelled":
            return {
                "status": "cancelled",
                "failure_code": "USER_CANCELLED",
                "failure_message": "Customer cancelled transaction on terminal screen.",
                "approved_amount": 0.0,
            }

        if scenario == "amount_mismatch":
            return {
                "status": "amount_mismatch",
                "provider_transaction_id": f"MOCK-TX-{uuid.uuid4().hex[:8]}",
                "approved_amount": round(amount - 10.0, 2),
                "failure_code": "AMOUNT_MISMATCH",
                "failure_message": "Terminal approved partial amount.",
            }

        if scenario in ["pending", "pending_then_success"]:
            return {
                "status": "pending",
                "provider_transaction_id": f"MOCK-TX-{uuid.uuid4().hex[:8]}",
                "approved_amount": 0.0,
            }

        if scenario == "timeout":
            time.sleep(1)
            return {
                "status": "unknown",
                "failure_code": "RESPONSE_TIMEOUT",
                "failure_message": "Device timeout awaiting customer PIN.",
                "approved_amount": 0.0,
            }

        return {
            "status": "success",
            "provider": "mock",
            "provider_transaction_id": f"MOCK-TX-{uuid.uuid4().hex[:8]}",
            "payment_method": request_data.get("payment_method", "card"),
            "approved_amount": amount,
            "rrn": "123456789012",
            "approval_code": "887766",
            "masked_pan": "411111XXXXXX1111",
            "card_network": "VISA",
            "card_type": "CREDIT",
            "upi_reference": "mock@upi" if request_data.get("payment_method") == "upi" else None,
            "raw_response": {"scenario": scenario, "code": "00"}
        }

    def status(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        scenario = request_data.get("scenario", "success")
        attempt_id = request_data.get("attempt_id", "")

        count = self.scenario_state.get(attempt_id, 0) + 1
        self.scenario_state[attempt_id] = count

        if scenario == "pending_then_success" and count < 2:
            return {"status": "pending", "approved_amount": 0.0}

        return {
            "status": "success",
            "provider": "mock",
            "provider_transaction_id": request_data.get("transaction_id", f"MOCK-TX-{uuid.uuid4().hex[:8]}"),
            "approved_amount": float(request_data.get("amount", 100.0)),
            "rrn": "123456789012",
            "approval_code": "887766",
            "raw_response": {"poll_count": count, "status": "SUCCESS"}
        }

    def cancel(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {"status": "cancelled", "provider_transaction_id": request_data.get("transaction_id")}

    def void(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {"status": "voided", "provider_transaction_id": request_data.get("transaction_id")}

    def refund(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {"status": "refunded", "provider_transaction_id": request_data.get("transaction_id"), "refund_amount": request_data.get("refund_amount")}

    def health(self) -> Dict[str, Any]:
        return {"is_healthy": True, "provider": "mock", "battery": 99, "online": True}
