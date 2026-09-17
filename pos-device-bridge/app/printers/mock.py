from typing import Dict, Any
from app.printers.base import BasePrinter

class MockPrinter(BasePrinter):
    def __init__(self):
        self.printed_jobs = set()

    def print_invoice(self, invoice_data: Dict[str, Any]) -> Dict[str, Any]:
        scenario = invoice_data.get("scenario", "success")
        job_id = invoice_data.get("job_id") or invoice_data.get("order_number")

        if scenario == "printer_offline":
            return {
                "status": "failed",
                "failure_code": "PRINTER_OFFLINE",
                "failure_message": "Thermal printer is powered off or disconnected.",
            }

        if scenario == "no_paper":
            return {
                "status": "failed",
                "failure_code": "NO_PAPER",
                "failure_message": "Thermal printer roll is out of paper.",
            }

        if scenario == "paper_misaligned":
            return {
                "status": "failed",
                "failure_code": "PAPER_MISALIGNED",
                "failure_message": "Paper cover open or misaligned.",
            }

        if job_id and job_id in self.printed_jobs and scenario != "allow_reprint":
            return {
                "status": "printed",
                "already_printed": True,
                "message": "Job previously printed successfully.",
            }

        if job_id:
            self.printed_jobs.add(job_id)

        pulse_pulsed = invoice_data.get("pulse_cash_drawer", True)

        return {
            "status": "printed",
            "mock": True,
            "order_number": invoice_data.get("order_number", "MOCK-BILL-001"),
            "paper_width_mm": invoice_data.get("paper_width_mm", 80),
            "line_items_count": len(invoice_data.get("items", [])),
            "cash_drawer_pulsed": pulse_pulsed,
        }
