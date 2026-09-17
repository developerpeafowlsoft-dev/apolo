from abc import ABC, abstractmethod
from typing import Dict, Any

class BaseTerminalProvider(ABC):
    @abstractmethod
    def sale(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        pass

    @abstractmethod
    def status(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        pass

    @abstractmethod
    def cancel(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        pass

    @abstractmethod
    def void(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        pass

    @abstractmethod
    def refund(self, request_data: Dict[str, Any]) -> Dict[str, Any]:
        pass

    @abstractmethod
    def health(self) -> Dict[str, Any]:
        pass
