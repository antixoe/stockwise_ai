import hashlib
import hmac
import json
import threading
import time
from http.server import HTTPServer
from urllib.request import Request, urlopen

from service import Handler


def test_internal_service_requires_hmac_and_returns_forecast():
    Handler.artifact = "artifacts/forecast-model.joblib"
    Handler.secret = "test-secret"
    server = HTTPServer(("127.0.0.1", 0), Handler)
    thread = threading.Thread(target=server.serve_forever, daemon=True); thread.start()
    try:
        body = json.dumps({"product_id": "SKU-001", "horizon": 7, "data_as_of": "2025-08-28"}, separators=(",", ":")).encode()
        timestamp = str(int(time.time())); nonce = "pytest-nonce"
        signature = hmac.new(Handler.secret.encode(), f"{timestamp}.{nonce}.".encode() + body, hashlib.sha256).hexdigest()
        request = Request(f"http://127.0.0.1:{server.server_port}/v1/forecast", data=body, method="POST", headers={"Content-Type": "application/json", "X-StockWise-Timestamp": timestamp, "X-StockWise-Nonce": nonce, "X-StockWise-Signature": signature})
        result = json.loads(urlopen(request, timeout=5).read())
        assert result["status"] == "success"
        assert len(result["predictions"]) == 7
    finally:
        server.shutdown(); thread.join(timeout=2); server.server_close()
