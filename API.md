# Mobile API (Laravel Sanctum)

REST API for the Hattrick Ply React Native mobile app. Uses MySQL via the existing Laravel models.

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

All protected routes require a Bearer token from login/register:

```
Authorization: Bearer {token}
```

## Endpoints

### Auth
- `POST /login` — email, password
- `POST /register` — name, email, password, password_confirmation, account_type (customer|distributor)
- `POST /logout` — revoke token
- `GET /me` — current user
- `PATCH /profile` — update profile fields

### Customer (`role:customer`)
- `GET /customer/catalog` — product list (search, category filter)
- `GET /customer/catalog/{id}` — product detail
- `GET /customer/categories` — available categories
- `GET /customer/cart` — cart items
- `POST /customer/cart/{product}` — add to cart (quantity)
- `PUT /customer/cart/{product}` — update quantity/notes
- `DELETE /customer/cart/{product}` — remove item
- `POST /customer/cart/place-order` — place order from cart
- `GET /customer/orders` — order history
- `GET /customer/orders/{id}` — order detail

### Distributor (`role:distributor`)
- `GET /distributor/dashboard` — stats + recent processing orders
- `GET /distributor/products` — product catalog with stock
- `POST /distributor/products/{id}/restock` — request restock
- `GET /distributor/orders` — order list (status filter)
- `GET /distributor/orders/{id}` — order detail
- `PATCH /distributor/orders/{id}/status` — update fulfillment_status

## Run

```bash
php artisan migrate
php artisan serve
```

Mobile app lives in `../mobile/`.
