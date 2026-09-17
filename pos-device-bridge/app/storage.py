import json
import os
from typing import Dict, Any, Optional
from app.config import settings

class AttemptStorage:
    def __init__(self, file_path: str = None):
        self.file_path = file_path or settings.CACHE_FILE_PATH
        self._data: Dict[str, Any] = {}
        self._load()

    def _load(self):
        if os.path.exists(self.file_path):
            try:
                with open(self.file_path, "r", encoding="utf-8") as f:
                    self._data = json.load(f)
            except Exception:
                self._data = {}
        else:
            self._data = {}

    def _save(self):
        try:
            with open(self.file_path, "w", encoding="utf-8") as f:
                json.dump(self._data, f, indent=2)
        except Exception:
            pass

    def get(self, attempt_id: str) -> Optional[Dict[str, Any]]:
        return self._data.get(attempt_id)

    def set(self, attempt_id: str, payload: Dict[str, Any]):
        self._data[attempt_id] = payload
        self._save()

    def get_last(self) -> Optional[Dict[str, Any]]:
        if not self._data:
            return None
        last_key = list(self._data.keys())[-1]
        return self._data[last_key]

attempt_storage = AttemptStorage()
