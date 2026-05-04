# 📞 Phone Numbers — Axon 

A single-page application that lists, categorizes, and validates country phone numbers from a database.

---
## 📚 Documentation

- **[Docker Setup](docs/docker.md)** — Container management & commands
- **[Husky Git Hooks](docs/husky/husky.md)** — Automated code quality checks on commit

---

## ⚙️ Setup & Installation

### 📋 Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Make](https://www.gnu.org/software/make/)

### 🚀 Quick Start

```bash
make rebuild-container
```

This single command:
- Starts all Docker containers (PHP, Web, Redis)
- Installs Composer dependencies
- Generates the application key
- Runs database migrations & seeds

---

## ✨ Features

- List all phone numbers stored in the database
- Filter by **country** (Cameroon, Ethiopia, etc) Check CountryEnum For the reset
- Filter by **state** (Valid / Invalid) — determined by per-country regex rules
- Server-side **pagination** with configurable page size (5 / 10 / 15 / 20)
- Smart **caching layer** (Redis, Database, or file — auto-detected)
- Cache bypass via `?no-cache` query parameter
- Clean architecture: DTOs · Enums · Form Requests · API Resources

---

## 🎥 Video Demonstrations

- **[Project Planning & Architecture-1](https://www.loom.com/share/e6ec749737104fb5844fe74f02e38f1b)** 
- **[Frontend Interface-2](https://www.loom.com/share/46c4d870630f450a95f466dcb673e5eb)** 
- **[Filtering Logic-3](https://www.loom.com/share/8151bdb07e5846379879c002e3894c38)** 
- **[System Cache Strategy-4](https://www.loom.com/share/3bc692878ce34d7180e17253d68757e7)** 

---

## 🛠️ Tech Stack

| Layer                | Technologies                           |
|----------------------|----------------------------------------|
| **Backend**          | PHP 8.3 · Laravel 13                   |
| **Frontend**         | Vue 3 (Composition API)                |
| **Database/Caching** | Sqlite · Redis                         |
| **Testing**          | Pest · PHPStan · Pint                  |
| **Dev Tools**        | Docker · Husky · Debugbar              |

---

## 🌐 API Endpoints

| Method | Endpoint              | Description                                      |
|--------|-----------------------|--------------------------------------------------|
| `GET`  | `/api/phone-numbers`  | Paginated list of phone numbers with filters     |
| `GET`  | `/api/dropdowns`      | Country and state options for the filter selects |

### Query Parameters — `GET /api/phone-numbers`

| Parameter  | Type      | Default | Description                       |
|------------|-----------|---------|-----------------------------------|
| `country`  | `string`  | `null`  | Country dial code (e.g. `237`)    |
| `state`    | `string`  | `null`  | `valid` or `invalid`              |
| `page`     | `integer` | `1`     | Current page number               |
| `per_page` | `integer` | `5`     | Results per page (5/10/15/20)     |

---

## 🧪 Testing & Code Quality

```bash
# Static analysis
make test-phpstan

# Code style
make test-pint
```

---

## 📐 Architecture Overview

```
app/
├── DTOs/
│   ├── BaseDto.php                  # Reflection-based hydration + cache key generation
│   └── PhoneNumberSearchDto.php     # Search filters DTO
├── Enums/
│   ├── CountryEnum.php              # Country codes + validation regex
│   └── PhoneStateEnum.php           # Valid / Invalid state
├── Facades/
│   └── ToggleCache.php              # Facade for the cache support class
├── Http/
│   ├── Controllers/API/
│   │   └── PhoneNumberController.php
│   ├── Requests/
│   │   └── PhoneNumberIndexRequest.php
│   └── Resources/
│       └── PhoneNumberResource.php  # Formats + validates each phone number
└── Support/
    └── ToggleCacheSupport.php       # Smart cache layer (Redis / DB / file)
```

---

## ⚡ Caching Strategy

The `ToggleCacheSupport` class provides a unified cache interface that works across drivers:

- **Redis** — uses `SCAN` with prefix pattern matching for targeted invalidation
- **Database** — uses `LIKE` pattern on the cache key column
- **File/other** — falls back to full `Cache::flush()`

Cache keys are generated dynamically from DTO properties, e.g.:

```
layout:dashboard|table:customers|country:237|state:valid|page:1|per_page:5
```

Additional controls:

```bash
# Enable or Disable viewing cache key name
TOGGLE_CACHE_DEBUG=true
# Enable or Disable caching globally
TOGGLE_CACHE_ENABLED=true


# Bypass cache for a single request
GET /api/phone-numbers?no-cache
```
---

