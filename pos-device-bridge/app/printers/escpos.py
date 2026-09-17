from typing import Dict, Any
from app.printers.base import BasePrinter

class EscPosPrinter(BasePrinter):
    def __init__(self, port: str = "USB"):
        self.port = port

    def print_invoice(self, invoice_data: Dict[str, Any]) -> Dict[str, Any]:
        order_number = invoice_data.get("order_number", "UNKNOWN")
        return {
            "status": "printed",
            "printer_port": self.port,
            "order_number": order_number,
            "bytes_sent": len(str(invoice_data)),
        }
