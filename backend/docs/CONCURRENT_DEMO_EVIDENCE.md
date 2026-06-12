# Concurrent Demo Evidence — Race Condition (Optimistic Locking)

Bukti hasil WP10 Langkah 10.2 — concurrent stock-out check.
Dijalankan: 12 Juni 2026, terhadap stack lokal (`docker compose up`).

Tujuan: membuktikan optimistic locking mencegah **double deduction** saat dua
request stock-out tiba bersamaan dengan `version` yang sama.

---

## Skenario

- Produk: id=20 (USB-C Hub 7-Port), stok awal **85**, version **0**
- Dua request stock-out `quantity=1`, **keduanya pakai `version=0`**, dikirim paralel
- Ekspektasi: satu menang (200), satu kalah race (409), stok turun **tepat 1**

## Script Reproduksi

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"admin@stockflow.com","password":"password"}' \
  | sed -n 's/.*"token":"\([^"]*\)".*/\1/p')

req() {
  curl -s -o "/tmp/resp_$1.json" -w "%{http_code}" \
    -X POST http://localhost:8000/api/products/20/stock-out \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" -H "Accept: application/json" \
    -d '{"quantity":1,"version":0}' > "/tmp/code_$1.txt"
}

req A & req B & wait
echo "A: $(cat /tmp/code_A.txt)"; cat /tmp/resp_A.json
echo "B: $(cat /tmp/code_B.txt)"; cat /tmp/resp_B.json
```

> Catatan: endpoint API wajib mengirim header `Accept: application/json`.
> Tanpa header itu, Laravel membalas redirect HTML, bukan JSON.

## Hasil Aktual

```
Request A: HTTP 200
{"message":"Stock updated successfully","stock_before":85,"stock_after":84}

Request B: HTTP 409
{"message":"Data telah berubah, silakan refresh dan coba lagi.","code":"STOCK_CONFLICT"}
```

## Verifikasi Side-Effect (inti pembuktian)

Post-state produk 20:

```
stock=84  version=1  out-transactions=1
```

- Stok turun **tepat 1** (85 → 84), **bukan 2** → tidak ada double deduction
- Version naik **tepat 1** (0 → 1) → hanya satu update yang berhasil
- Hanya **1** `stock_transaction` type=out → request yang kalah tidak menulis apa pun

Worker memproses job dari pemenang (side-effect async terpisah dari response):

```
App\Jobs\StockUpdatedJob ......... RUNNING
App\Jobs\StockUpdatedJob ......... 78.70ms DONE
```

Structured log mencatat kedua sisi:

```
INFO  stock.updated  {"product_id":20,"type":"out","quantity":1,"stock_before":85,"stock_after":84,"user_id":34}
WARN  stock.conflict {"product_id":20,"requested_version":0,"quantity":1,"user_id":34}
```

## Demo 2-Browser (untuk Live Defense)

1. Login 2 tab (boleh `admin@stockflow.com` dan `staff@stockflow.com`, password `password`).
2. Kedua tab buka halaman `/stock-out`, pilih produk yang sama — keduanya membaca `version` yang sama.
3. Tab 1 submit stock-out → sukses, stok berkurang.
4. Tab 2 submit stock-out dengan version lama → UI menampilkan pesan
   "Data telah berubah, silakan refresh dan coba lagi." (HTTP 409).

Jawaban inti untuk juri: optimistic locking memakai kolom `version`.
`UPDATE ... WHERE id=? AND version=? AND stock>=?` — kalau version sudah berubah,
`affected=0`, sistem balas 409 tanpa menyentuh stok. Inilah yang mencegah double deduction
tanpa mengunci row di database (lebih ringan dari pessimistic locking).
