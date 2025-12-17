# Diabetes Care System (Laravel API)

A backend API built with **Laravel** for managing diabetes patients and doctors. The system uses **Laravel Sanctum** for authentication and supports background jobs, email verification, and Google API integration.

---

## 📦 Tech Stack

* PHP 8.2+
* Laravel
* Laravel Sanctum (API Authentication)
* MySQL / PostgreSQL
* Laravel Queue (background jobs)
* Google API Client

---

## 🗄️ Database

**Database name:**

```
doc_sugar_db
```

### Migrate & Seed

This command will:

* Drop all tables
* Recreate the schema
* Seed initial data (including Doctor seeder)

```bash
php artisan migrate:fresh --seed
```

---

## 🔐 Authentication

* API authentication is handled using **Laravel Sanctum**
* Supported roles:

  * `patient`
  * `doctor`

Tokens are generated on login and must be sent via:

```
Authorization: sanctum {token}
```

---

## 📧 Email & Queue

The system uses **queues** for:

* Email verification
* Background notifications

Start the queue worker:

```bash
php artisan queue:work
php artisan schedule:work

```

> ⚠️ Make sure your queue connection is correctly configured in `.env`.

---

## ☁️ Google API Integration

The project uses the Google API client library.

### Installation

```bash
composer require google/apiclient 
```

### Environment Variables

Add the following to your `.env` file:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=your_redirect_uri

GOOGLE_ACCESS_TOKEN=your_access_token
GOOGLE_REFRESH_TOKEN=your_refresh_token
```

> 🔒 **Do not commit `.env` to GitHub**. These credentials must remain private.

---

## ⚙️ Environment Setup (`.env`)

Example database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=diabetes_care
DB_USERNAME=root
DB_PASSWORD=
```

Queue configuration example:

```env
QUEUE_CONNECTION=database
```

---

## ▶️ Run the Project

```bash
php artisan serve
```

API will be available at:

```
http://127.0.0.1:8000/api
```

---

## 🧪 Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan queue:work
php artisan tinker
```

---

## 🚀 Current Features

* Sanctum authentication (Patient / Doctor)
* Doctor seeder
* Email verification template
* Secure medical data handling
* Google API integration ready

---



## 📄 License

This project is for educational / graduation purposes.
