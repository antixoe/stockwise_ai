# Explainable AI dan Inventory Decision Support

## SHAP

`ai-service/stockwise_ml/explainability.py` memakai `shap.TreeExplainer` untuk Random Forest/XGBoost yang dipilih pada training. Feature row dibentuk memakai fungsi yang sama dengan inference recursive: kalender, promo/harga default sesuai metodologi, lag, dan rolling yang tidak membaca target masa depan.

Explanation menyimpan baseline/expected value, local contribution setiap fitur, global feature importance model tree, predicted/reconstructed output, model version, explainer version, dan limitation note. Pipeline menolak hasil bila `baseline + sum(SHAP values)` tidak konsisten dengan output model dalam toleransi numerik.

SHAP adalah atribusi perilaku model pada observasi tertentu, bukan bukti sebab-akibat. Fitur berkorelasi dapat membagi kontribusi dan perubahan distribusi data dapat mengubah interpretasi.

## Aturan restock

Keputusan inventory dihitung Laravel melalui `RestockDecisionService`, terpisah dari model ML:

```text
demand_during_lead_time = sum(forecast harian selama ceil(lead_time_days))
reorder_point = demand_during_lead_time + safety_stock
inventory_position = on_hand - reserved + incoming
target_inventory_level = reorder_point
raw_order_quantity = max(0, target_inventory_level - inventory_position)
```

Jika jumlah positif, minimum supplier/product diterapkan lalu quantity dibulatkan ke kelipatan order. Lead time nol/negatif, order multiple nol/negatif, atau forecast sukses yang tidak tersedia ditolak. Bila horizon forecast lebih pendek daripada lead time, sisa demand diekstrapolasi memakai rata-rata forecast yang tersedia dan disimpan dalam snapshot input.

Risk classification: `critical` bila inventory position <= 0, `high` bila di bawah reorder point, dan `normal` selain itu. Recommendation menyimpan supplier, lead time, snapshot inventory, forecast model version, rule version, rationale, dan status.

## Approval

Hanya Manajer yang dapat approve/reject. `RecommendationDecisionService` mengunci recommendation dengan row lock, memeriksa status `pending` dan unique decision, menyimpan original/final quantity, user, comment, model/rule version, timestamp, lalu mengubah status dalam satu transaction. Approval tidak mengirim purchase order dan tidak mengubah stok.
