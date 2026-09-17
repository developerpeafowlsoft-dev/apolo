# POS Device Bridge - Service Deployment Guide

This guide details the deployment of `pos-device-bridge` as a background service on Windows (NSSM / Windows Service) and Linux (systemd).

---

## Service Overview
- **Binary / Interpreter**: Python 3.9+
- **Binding Host**: `127.0.0.1` (localhost only)
- **Port**: `8089`
- **Application Server**: Uvicorn ASGI Server

---

## 1. Windows Deployment (NSSM - Non-Sucking Service Manager)

### Step 1: Install NSSM
Download `nssm.exe` from [nssm.cc](https://nssm.cc) and copy it to `C:\Windows\System32`.

### Step 2: Configure Virtual Environment
Open PowerShell as Administrator:
```powershell
cd C:\pos-device-bridge
python -m venv venv
.\venv\Scripts\activate
pip install -r requirements.txt
```

### Step 3: Register Service
```powershell
nssm install PosDeviceBridge "C:\pos-device-bridge\venv\Scripts\python.exe" "-m uvicorn app.main:app --host 127.0.0.1 --port 8089"
nssm set PosDeviceBridge AppDirectory "C:\pos-device-bridge"
nssm set PosDeviceBridge DisplayName "POS Local Device Bridge"
nssm set PosDeviceBridge Description "Manages Paytm/PhonePe EDC payment terminals and ESC/POS thermal receipt printers."
nssm set PosDeviceBridge Start SERVICE_AUTO_START
nssm start PosDeviceBridge
```

---

## 2. Linux Deployment (systemd Service)

### Step 1: Virtual Environment Setup
```bash
cd /opt/pos-device-bridge
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### Step 2: Create systemd Unit File
Create file `/etc/systemd/system/pos-device-bridge.service`:
```ini
[Unit]
Description=POS Local Device Bridge Service
After=network.target

[Service]
Type=simple
User=posuser
WorkingDirectory=/opt/pos-device-bridge
Environment="PATH=/opt/pos-device-bridge/venv/bin"
Environment="BRIDGE_HOST=127.0.0.1"
Environment="BRIDGE_PORT=8089"
ExecStart=/opt/pos-device-bridge/venv/bin/uvicorn app.main:app --host 127.0.0.1 --port 8089 --workers 2
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

### Step 3: Enable & Start Service
```bash
sudo systemctl daemon-reload
sudo systemctl enable pos-device-bridge
sudo systemctl start pos-device-bridge
sudo systemctl status pos-device-bridge
```
