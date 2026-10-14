# PT Janur Tangguh Abadi — Sea Freight Booking System

A Laravel-based web application to manage sea freight container bookings: from customer booking requests, staff/admin pricing offers, invoice issuance, payment verification, to shipment progress tracking.

This project supports the operational flow between customers, staff, and administrators in a single integrated system.

## Project Summary

This application provides features to:

- Manage customer sea freight bookings
- Allow staff/admin to provide final price offers
- Process customer confirmations of offers
- Automatically generate invoices
- Manage payment proof uploads and verification
- Store operational shipment data such as containers, vessels, ETD/ETA, and delivery notes
- Present clear shipment status and history

## Key Features

### Customer
- Register and log in
- Create new booking requests
- Select routes and enter cargo details
- View realtime price estimates on the booking form
- Track booking status using a booking code
- View personal booking history
- Accept or reject final price offers
- Upload payment proof (receipt)
- Download invoice as PDF

### Staff / Admin
- Dashboard with overview of all bookings
- Manage route master data and base pricing
- Send final price offers to customers
- View and issue invoices
- Verify uploaded payment proof
- Manage operational shipment data
- Update shipment progress and statuses

## Technology Stack

- Backend: Laravel 10 (PHP 8.1+)
- Frontend: Blade templates, Bootstrap 5, Vite
- CSS: Sass
- Authentication: Laravel UI
- PDF generation: Dompdf
- Database: MySQL
- API/auth tokens (optional): Laravel Sanctum

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL
- Git

## Installation

1. Clone the repository and enter the project directory:

```bash
git clone https://github.com/Kirigaya1145/project-kp-janur.git
cd project-kp-janur
```

2. Install PHP and JavaScript dependencies:

```bash
composer install
npm install
```

3. Configure environment and app key:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials, for example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

4. Run migrations and seeders (creates demo accounts and example routes):

```bash
php artisan migrate --seed
```

5. Create storage symlink (required for uploaded payment proof and company logo):

```bash
php artisan storage:link
```

6. Build frontend assets:

```bash
npm run build
```

For development mode with live rebuilds:

```bash
npm run dev
```

7. Run the application server:

```bash
php artisan serve
```

Open the app at:

http://localhost:8000

## Default Demo Accounts

The database seeder creates sample accounts for testing:

| Role | Email | Password |
|---|---|---|
| Admin | admin@janurtangguhabadi.com | password |
| Staff | staff@janurtangguhabadi.com | password |
| Customer | customer@example.com | password |

Please change these passwords before using the application in production.

## Booking Workflow

Typical booking flow:

1. Customer submits a booking request (status: waiting for offer).
2. Staff/Admin provides a final price offer (status: awaiting customer confirmation).
3. Customer accepts the offer → invoice is generated (status: awaiting payment). Customer may reject the offer.
4. Customer uploads payment proof (status: awaiting payment verification).
5. Admin verifies payment (status: operational-ready) or rejects (customer must re-upload proof).
6. Admin fills operational details and updates shipment progress until completion.

Standard status sequence used in the system:

- Waiting for Offer
- Waiting for Confirmation
- Waiting for Payment
- Payment Verification
- In Transit / Processing
- Completed

Additional states like Offer Rejected or Cancelled are also supported.

## Main Routes (Overview)

Public:
- GET / — Landing page
- GET /tentang — About the company
- GET /booking/cek, POST /booking/cek — Check booking status by code

Customer (requires login):
- GET/POST /booking — Booking form and submission
- GET /booking/riwayat — Customer booking history
- POST /booking/{booking}/konfirmasi-penawaran — Accept/reject offer
- POST /invoice/{invoice}/bukti-pembayaran — Upload payment proof
- GET /invoice/{invoice}/pdf — Download invoice PDF

Admin / Staff (prefix /admin, middleware role:admin,staff):
- GET /admin/dashboard — All bookings overview
- GET/POST /admin/rute — Manage routes & base prices
- GET /admin/booking/{booking} — Booking details & management
- POST /admin/booking/{booking}/penawaran — Send price offer
- POST /admin/booking/{booking}/invoice — Issue manual invoice
- POST /admin/booking/{booking}/operasional — Save operational data
- POST /admin/booking/{booking}/progress — Update shipment progress
- POST /admin/bukti-pembayaran/{bukti}/verifikasi — Verify/reject payment proof

## Database (core tables)

- users
- rute_harga
- bookings
- booking_barang
- invoice
- bukti_pembayaran
- booking_container
- surat_jalan
- status_bookings
- company_profiles

## Project Structure (important folders)

project-kp-janur/
├── app/ (controllers, middleware, models)
├── bootstrap/
├── config/
├── database/ (migrations, seeders)
├── public/
├── resources/ (views, js, sass)
├── routes/
├── storage/
├── tests/
└── ...

## Development Notes & TODOs

- Consider consolidating duplicated status fields (e.g., `status_harga` vs. `status_booking`).
- Add automated notifications (email / WhatsApp) for status updates.
- Improve validation and UX for forms, especially payment uploads and admin operations.
- Add UI for issuing manual invoices if required by business flow.

## License & Usage

This project is provided for internal or educational purposes and can be adapted further according to organizational needs.

---

© PT Janur Tangguh Abadi
