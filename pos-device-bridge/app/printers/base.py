from abc import ABC, abstractmethod
from typing import Dict, Any

class BasePrinter(ABC):
    @abstractmethod
    def print_invoice(self, invoice_data: Dict[str, Any]) -> Dict[str, Any]:
        pass
