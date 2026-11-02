from http.server import BaseHTTPRequestHandler, HTTPServer
import argparse, hashlib, hmac, json, os, time
from stockwise_ml.pipeline import infer
from stockwise_ml.explainability import explain

class Handler(BaseHTTPRequestHandler):
    artifact = None; secret = None; nonces = set()
    def send_json(self, status, payload):
        body = json.dumps(payload, separators=(',', ':')).encode(); self.send_response(status); self.send_header('Content-Type','application/json'); self.send_header('Content-Length', str(len(body))); self.end_headers(); self.wfile.write(body)
    def do_GET(self):
        if self.path == '/health': return self.send_json(200, {'status':'ok','service':'stockwise-ml'})
        self.send_json(404, {'status':'not_found'})
    def do_POST(self):
        if self.path not in ('/v1/forecast', '/v1/explain'): return self.send_json(404, {'status':'not_found'})
        length = int(self.headers.get('Content-Length','0')); body = self.rfile.read(length); timestamp = self.headers.get('X-StockWise-Timestamp',''); nonce = self.headers.get('X-StockWise-Nonce',''); signature = self.headers.get('X-StockWise-Signature','')
        try: valid_time = abs(time.time() - int(timestamp)) <= 300
        except ValueError: valid_time = False
        expected = hmac.new(self.secret.encode(), f'{timestamp}.{nonce}.'.encode()+body, hashlib.sha256).hexdigest()
        if not valid_time or nonce in self.nonces or not hmac.compare_digest(expected, signature): return self.send_json(401, {'status':'unauthorized'})
        self.nonces.add(nonce)
        try:
            request = json.loads(body)
            if self.path == '/v1/explain':
                result = explain(self.artifact, str(request['product_id']), request['forecast_date'])
            else:
                result = infer(self.artifact, str(request['product_id']), int(request['horizon']), request.get('data_as_of'))
            result['request_id'] = request.get('request_id'); return self.send_json(200, result)
        except (KeyError, ValueError, TypeError) as exc: return self.send_json(422, {'status':'invalid_input','error':str(exc)})
        except Exception: return self.send_json(500, {'status':'model_error'})

def main():
    parser = argparse.ArgumentParser(); parser.add_argument('--artifacts', default='artifacts'); parser.add_argument('--host', default='127.0.0.1'); parser.add_argument('--port', type=int, default=8091); args = parser.parse_args()
    Handler.artifact = os.path.join(args.artifacts, 'forecast-model.joblib'); Handler.secret = os.environ.get('STOCKWISE_AI_SECRET')
    if not Handler.secret: raise SystemExit('STOCKWISE_AI_SECRET wajib diisi melalui environment')
    HTTPServer((args.host, args.port), Handler).serve_forever()

if __name__ == '__main__':
    main()
