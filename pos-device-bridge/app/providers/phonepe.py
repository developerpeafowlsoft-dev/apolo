from typing import Dict, Any
from app.providers.base import BaseTerminalProvider

class PhonePeTerminalProvider(BaseTerminalProvider):
    def __init__(self, config: Dict[str, Any] = None):
        self.config = config or {}

    def sale(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "status": "pending",
            "provider": "phonepe",
            "message": "PhonePe EDC transaction initialized. Integration SDK pending.",
            "approved_amount": 0.0,
        }

    def status(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "status": "unknown",
            "provider": "phonepe",
            "message": "PhonePe status enquiry placeholder.",
        }

    def cancel(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {"status": "cancelled", "provider": "phonepe"}

    def void(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {"status": "voided", "provider": "phonepe"}

    def refund(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {"status": "refunded", "provider": "phonepe"}

    def health(self) -> Dict[str, Any]:
        return {"is_healthy": True, "provider": "phonepe", "online": True}
