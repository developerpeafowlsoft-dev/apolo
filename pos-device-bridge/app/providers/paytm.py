import time
import uuid
import logging
from typing import Dict, Any, List, Optional
from app.providers.base import BaseTerminalProvider

logger = logging.getLogger("paytm_provider")

class PaytmTerminalProvider(BaseTerminalProvider):
    def __init__(self, config: Optional[Dict[str, Any]] = None):
        self.config = config or {}
        self.merchant_id = self.config.get("merchant_id", "PAYTM_MOCK_MERCHANT")
        self.terminal_id = self.config.get("terminal_id", "PAYTM_MOCK_TERM_01")
        self.port_name = self.config.get("port_name", "COM3")
        self.baud_rate = self.config.get("baud_rate", 115200)
        self.parity = self.config.get("parity", "N")
        self.data_bits = self.config.get("data_bits", 8)
        self.stop_bits = self.config.get("stop_bits", 1)
        self.debug_mode = self.config.get("debug_mode", False)
        self.environment = self.config.get("environment", "uat")

    def _convert_rupees_to_paise(self, amount_rupees: float) -> int:
        return int(round(float(amount_rupees) * 100))

    def _map_payment_mode(self, method: str) -> str:
        method_lower = (method or "card").lower()
        if method_lower == "upi":
            return "QR"
        elif method_lower in ["all", "customer_choice"]:
            return "ALL"
        return "CARD"

    def discover_ports(self) -> List[str]:
        return ["COM1", "COM3", "COM4", "/dev/ttyUSB0", "/dev/ttyUSB1"]

    def recover_connection(self) -> bool:
        logger.info(f"Attempting Paytm ECR connection recovery on port {self.port_name}...")
        return True

    def sale(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        amount_rupees = float(request_data.get("amount", 0.0))
        amount_paise = self._convert_rupees_to_paise(amount_rupees)
        payment_mode = self._map_payment_mode(request_data.get("payment_method", "card"))
        merchant_order_id = request_data.get("bill_reference", f"ORD-{uuid.uuid4().hex[:8]}")
        request_id = request_data.get("provider_request_id", f"REQ-{uuid.uuid4().hex[:10]}")
        scenario = request_data.get("scenario", "success")

        if scenario == "cable_removed":
            return {
                "status": "failed",
                "provider": "paytm",
                "failure_code": "CABLE_DISCONNECTED",
                "failure_message": "Paytm ECR serial cable disconnected.",
                "approved_amount": 0.0,
            }

        if scenario == "printer_no_paper":
            return {
                "status": "failed",
                "provider": "paytm",
                "failure_code": "PRINTER_NO_PAPER",
                "failure_message": "Paytm EDC printer out of paper.",
                "approved_amount": 0.0,
            }

        if scenario == "printer_malfunction":
            return {
                "status": "failed",
                "provider": "paytm",
                "failure_code": "PRINTER_MALFUNCTION",
                "failure_message": "Paytm EDC printer hardware error.",
                "approved_amount": 0.0,
            }

        if scenario == "timeout":
            return {
                "status": "unknown",
                "provider": "paytm",
                "provider_request_id": request_id,
                "failure_code": "RESPONSE_TIMEOUT",
                "failure_message": "Paytm SDK response timeout. Verification pending via status query.",
                "approved_amount": 0.0,
            }

        if scenario == "user_cancelled":
            return {
                "status": "cancelled",
                "provider": "paytm",
                "failure_code": "USER_CANCELLED",
                "failure_message": "Customer pressed cancel button on Paytm EDC.",
                "approved_amount": 0.0,
            }

        if scenario == "declined":
            return {
                "status": "failed",
                "provider": "paytm",
                "failure_code": "DECLINED_BY_BANK",
                "failure_message": "Transaction declined by card issuing bank.",
                "approved_amount": 0.0,
            }

        tx_id = f"PAYTM-TX-{uuid.uuid4().hex[:10].upper()}"
        rrn = f"77{uuid.uuid4().int % 10000000000:010d}"
        approval_code = f"{uuid.uuid4().int % 1000000:06d}"

        return {
            "status": "success",
            "provider": "paytm",
            "provider_request_id": request_id,
            "provider_transaction_id": tx_id,
            "payment_method": payment_mode.lower(),
            "approved_amount": amount_rupees,
            "currency": "INR",
            "terminal_id": self.terminal_id,
            "merchant_id": self.merchant_id,
            "rrn": rrn,
            "approval_code": approval_code,
            "masked_pan": "4532XXXXXX9012" if payment_mode == "CARD" else None,
            "card_network": request_data.get("card_network", "VISA") if payment_mode == "CARD" else None,
            "card_type": request_data.get("card_type", "DEBIT") if payment_mode == "CARD" else None,
            "upi_reference": f"paytm@upi-{rrn}" if payment_mode == "QR" else None,
            "safe_raw_response": {
                "responseCode": "00",
                "responseMessage": "SUCCESS",
                "merchantOrderId": merchant_order_id,
                "amountInPaise": amount_paise,
                "paymentMode": payment_mode,
                "environment": self.environment,
            }
        }

    def status(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        scenario = request_data.get("scenario", "success")
        tx_id = request_data.get("transaction_id", f"PAYTM-TX-{uuid.uuid4().hex[:8].upper()}")

        if scenario == "status_recovery_success":
            return {
                "status": "success",
                "provider": "paytm",
                "provider_transaction_id": tx_id,
                "approved_amount": float(request_data.get("amount", 500.0)),
                "rrn": "771234567890",
                "approval_code": "654321",
                "safe_raw_response": {"status": "SUCCESS", "recovered": True}
            }

        if scenario == "status_recovery_failed":
            return {
                "status": "failed",
                "provider": "paytm",
                "failure_code": "TRANSACTION_NOT_FOUND",
                "failure_message": "Transaction failed or not recorded on Paytm host.",
                "approved_amount": 0.0,
            }

        return {
            "status": "success",
            "provider": "paytm",
            "provider_transaction_id": tx_id,
            "approved_amount": float(request_data.get("amount", 100.0)),
            "safe_raw_response": {"status": "SUCCESS"}
        }

    def cancel(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "status": "cancelled",
            "provider": "paytm",
            "provider_transaction_id": request_data.get("transaction_id"),
            "safe_raw_response": {"status": "CANCELLED_BY_POS"}
        }

    def void(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "status": "voided",
            "provider": "paytm",
            "provider_transaction_id": request_data.get("transaction_id"),
            "approved_amount": float(request_data.get("original_amount", 0.0)),
            "safe_raw_response": {"status": "VOID_SUCCESS"}
        }

    def refund(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        refund_amount = float(request_data.get("refund_amount", 0.0))
        return {
            "status": "refunded",
            "provider": "paytm",
            "provider_transaction_id": request_data.get("transaction_id"),
            "approved_amount": refund_amount,
            "safe_raw_response": {"status": "REFUND_SUCCESS", "refund_amount_paise": self._convert_rupees_to_paise(refund_amount)}
        }

    def health(self) -> Dict[str, Any]:
        return {
            "is_healthy": True,
            "provider": "paytm",
            "terminal_id": self.terminal_id,
            "port_name": self.port_name,
            "environment": self.environment,
            "is_online": True,
        }
