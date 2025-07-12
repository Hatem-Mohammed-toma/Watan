
# 🗺️ Watan – Country, Event & Product Management System (Laravel API)

A Laravel-based RESTful **web application** designed to manage countries, cities, events, media files, and product-related boycotts through structured APIs and dynamic data handling.

🔗 **Live Repo:** [Watan on GitHub](https://github.com/Hatem-Mohammed-toma/Watan)

---

## 🚀 Features

- **Country & Event Management**
  - Store countries, cities, events with description, date, and location
  - Event APIs with filtering and search

- **Media Handling**
  - Upload multiple images/videos for events or posts
  - Secure cloud/local storage with auto deletion on record removal

- **Post & Comment System**
  - Users can submit posts and comments
  - Admin approval flow for both posts and comments (statuses: pending, accepted, rejected)

- **Boycott Product Detection**
  - Each product has a unique code
  - API checks if product is boycotted and suggests alternatives from the same category

- **Authentication & Recovery**
  - JWT Authentication (register, login, token refresh)
  - Forgot and reset password system using email APIs

- **Admin Dashboard**
  - View/manage all submitted posts, comments, events, and product status
  - Accept or reject post/comment requests

---

## 🛠️ Tech Stack

| Layer        | Tools Used                                   |
|--------------|----------------------------------------------|
| Backend      | Laravel 10 (RESTful API), MySQL              |
| Auth         | JWT Authentication                           |
| Tools        | Postman, Cloud Storage (media handling)      |
| Features     | File Uploads, API Filtering, Role Management |

---

## 📁 Project Structure (API-Only Laravel)

```
/watan-api
├── app/
│   ├── Http/Controllers/          # API endpoints for auth, posts, products, etc.
│   ├── Models/                    # Models (Country, City, Event, Post, Product)
│   └── Services/Helpers/          # Optional logic helpers
│
├── routes/
│   └── api.php                    # All API route definitions
│
├── database/
│   └── migrations/               # Table schemas (countries, events, posts, etc.)
│
├── storage/app/public/           # Media files (images/videos)
├── config/                       # JWT config, filesystems, auth
└── README.md                     # Project overview
```

---

## 🌐 Localization (English & Arabic)

This project can support **multi-language localization** via Laravel’s built-in `lang` directory (optional enhancement for labels, messages, etc.).

---

## ⚙️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/Hatem-Mohammed-toma/Watan
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   - Copy `.env.example` to `.env`
   - Set DB, JWT, and mail credentials

4. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

5. **Generate keys and JWT**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

6. **Serve the API**
   ```bash
   php artisan serve
   ```

---

## 📌 Developer

- **Name:** Hatem Mohammed Toma  
- **GitHub:** [@Hatem-Mohammed-toma](https://github.com/Hatem-Mohammed-toma)
