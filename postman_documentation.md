# Panduan Postman: Integrasi Game Store Microservices

Gunakan payload request dan expected response di bawah ini untuk dimasukkan ke dalam Postman Collection kalian.

---

## 1. Player Service (Port 8001)

### A. Get Player Profile (Provider)
- **Method:** `GET`
- **URL:** `http://localhost:8001/api/players/1/profile`
- **Expected Response (200 OK):**
```json
{
    "data": {
        "id": 1,
        "name": "Noval Saputra",
        "email": "noval@example.com",
        "wallet_balance": "500000.00",
        "created_at": "2026-04-19T10:00:00.000000Z",
        "updated_at": "2026-04-19T10:00:00.000000Z"
    }
}
```

### B. Check Player Balance (Provider)
- **Method:** `GET`
- **URL:** `http://localhost:8001/api/players/1/balance`
- **Expected Response (200 OK):**
```json
{
    "wallet_balance": "500000.00"
}
```

### C. Get Player Transaction History (Consumer)
*Catatan: Ini akan mengambil data dari Order Service*
- **Method:** `GET`
- **URL:** `http://localhost:8001/api/players/1/transactions`
- **Expected Response (200 OK):**
```json
{
    "player_id": "1",
    "transactions": [
        {
            "id": 1,
            "player_id": 1,
            "game_item_id": 2,
            "quantity": 1,
            "total_price": "50000.00",
            "status": "success",
            "created_at": "2026-04-19T10:05:00.000000Z",
            "updated_at": "2026-04-19T10:05:00.000000Z"
        }
    ]
}
```

---

## 2. Game Item Service (Port 8002)

### A. Get Item Details (Provider)
- **Method:** `GET`
- **URL:** `http://localhost:8002/api/items/1`
- **Expected Response (200 OK):**
```json
{
    "data": {
        "id": 1,
        "name": "1000 Diamond Mobile Legends",
        "price": "150000.00",
        "stock": 50,
        "category": "Currency",
        "created_at": "2026-04-19T10:00:00.000000Z",
        "updated_at": "2026-04-19T10:00:00.000000Z"
    }
}
```

### B. Validate Stock (Provider)
- **Method:** `GET`
- **URL:** `http://localhost:8002/api/items/1/validate-stock?quantity=2`
- **Expected Response (200 OK):**
```json
{
    "is_available": true,
    "stock": 50
}
```

### C. Get Trending Items (Consumer)
*Catatan: Ini mengambil rekap data penjualan dari Order Service*
- **Method:** `GET`
- **URL:** `http://localhost:8002/api/items/trending`
- **Expected Response (200 OK):**
```json
{
    "data": [
        {
            "item": {
                "id": 1,
                "name": "1000 Diamond Mobile Legends",
                "price": "150000.00",
                "stock": 50,
                "category": "Currency",
                "created_at": "...",
                "updated_at": "..."
            },
            "total_sold": 15
        }
    ]
}
```

---

## 3. Order Service (Port 8003)

### A. Create Order (Consumer)
*Catatan: Proses ini akan memanggil Player Service untuk cek saldo dan Game Item Service untuk cek stok*
- **Method:** `POST`
- **URL:** `http://localhost:8003/api/orders`
- **Request Body (JSON):**
```json
{
    "player_id": 1,
    "game_item_id": 1,
    "quantity": 2
}
```
- **Expected Response (201 Created):**
```json
{
    "message": "Order created successfully",
    "data": {
        "player_id": 1,
        "game_item_id": 1,
        "quantity": 2,
        "total_price": 300000,
        "status": "success",
        "updated_at": "2026-04-19T10:10:00.000000Z",
        "created_at": "2026-04-19T10:10:00.000000Z",
        "id": 1
    }
}
```

### B. Get Player Orders (Provider)
- **Method:** `GET`
- **URL:** `http://localhost:8003/api/orders/player/1`
- **Expected Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "player_id": 1,
            "game_item_id": 1,
            "quantity": 2,
            "total_price": "300000.00",
            "status": "success",
            "created_at": "2026-04-19T10:10:00.000000Z",
            "updated_at": "2026-04-19T10:10:00.000000Z"
        }
    ]
}
```

### C. Get Sales Recap (Provider)
- **Method:** `GET`
- **URL:** `http://localhost:8003/api/orders/recap`
- **Expected Response (200 OK):**
```json
{
    "data": [
        {
            "game_item_id": 1,
            "total_sold": 15
        },
        {
            "game_item_id": 2,
            "total_sold": 5
        }
    ]
}
```
