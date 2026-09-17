import os

class Settings:
    HOST: str = os.getenv("BRIDGE_HOST", "127.0.0.1")
    PORT: int = int(os.getenv("BRIDGE_PORT", "8089"))
    SECRET_KEY: str = os.getenv("BRIDGE_SECRET_KEY", "default_secret")
    POS_ORIGINS: list = [
        "http://localhost",
        "http://127.0.0.1",
        "http://localhost:8000",
        "http://127.0.0.1:8000",
        "https://localhost",
        "https://127.0.0.1",
    ]
    MAX_TIMESTAMP_DIFF_SECONDS: int = 30
    CACHE_FILE_PATH: str = os.getenv("CACHE_FILE_PATH", "attempt_store.json")

settings = Settings()
