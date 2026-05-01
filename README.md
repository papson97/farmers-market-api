# 🌾 Farmers Market API

Backend API for the Farmers Market Platform - Agricultural products marketplace in Côte d'Ivoire.

## 📋 Description

This API powers a marketplace platform where farmers can purchase agricultural products (pesticides, fertilizers, seeds) from points of sale (POS). Farmers can pay in cash (FCFA) or on credit, with debts repaid using agricultural commodities (e.g., cacao).

## 🛠️ Tech Stack

- **PHP** 8.3+
- **Laravel** 13
- **MySQL** 8.4
- **Laravel Sanctum** (token-based authentication)

## ✅ Features

- 🔐 Role-based authentication (Admin / Supervisor / Operator)
- 📦 Product catalog with nested categories
- 👨‍🌾 Farmer management with credit limits
- 💳 Cash and credit transactions with configurable interest rates
- 📉 Debt tracking with FIFO repayment system
- 🌾 Commodity-based repayment (kg → FCFA conversion)

## ⚙️ Requirements

- PHP 8.1+
- Composer
- MySQL 8+
- Laravel 13

## 🚀 Installation

### 1. Clone the repository
```bash
git clone https://github.com/yourusername/farmers-market-api.git
cd farmers-market-api
```

### 2. Install dependencies
```bash
composer install
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=farmers_market
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Create database
```bash
mysql -u root -e "CREATE DATABASE farmers_market;"
```

### 5. Run migrations and seeders
```bash
php artisan migrate:fresh --seed
```

### 6. Start the server
```bash
php artisan serve
```

API will be available at `http://127.0.0.1:8000`

## 👥 Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@test.com | 123456 |
| Supervisor | supervisor@test.com | 123456 |
| Operator | operator@test.com | 123456 |

## 🌱 Demo Data

The seeder creates:
- 3 users (admin, supervisor, operator)
- 3 parent categories (Pesticides, Engrais, Semences)
- 3 subcategories (Herbicides, Insecticides, Engrais Organiques)
- 4 products (Roundup, Décis, Compost Bio, Urée)
- 3 farmers (Kouame Koffi, Adjoua Bamba, Konan Yao)
- Settings: interest rate 30%, commodity rate 1000 FCFA/kg

## 📡 API Endpoints

### Authentication
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | /api/login | Login and get token | ❌ |
| POST | /api/logout | Logout | ✅ |
| GET | /api/me | Get current user | ✅ |

### Users
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| POST | /api/supervisors | Create supervisor | Admin |
| POST | /api/operators | Create operator | Supervisor |
| GET | /api/users | List users | Admin/Supervisor |
| DELETE | /api/users/{id} | Delete user | Admin/Supervisor |

### Categories
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | /api/categories | List all categories | All |
| GET | /api/categories/{id} | Get category | All |
| POST | /api/categories | Create category | Admin/Supervisor |
| PUT | /api/categories/{id} | Update category | Admin/Supervisor |
| DELETE | /api/categories/{id} | Delete category | Admin/Supervisor |

### Products
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | /api/products | List all products | All |
| GET | /api/products/{id} | Get product | All |
| POST | /api/products | Create product | Admin/Supervisor |
| PUT | /api/products/{id} | Update product | Admin/Supervisor |
| DELETE | /api/products/{id} | Delete product | Admin/Supervisor |

### Farmers
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | /api/farmers | List all farmers | All |
| GET | /api/farmers/{id} | Get farmer details | All |
| GET | /api/farmers/search?q= | Search farmer | All |
| POST | /api/farmers | Create farmer | All |
| PUT | /api/farmers/{id} | Update farmer | All |

### Transactions
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | /api/transactions | List transactions | All |
| GET | /api/transactions/{id} | Get transaction | All |
| POST | /api/transactions | Create transaction | All |

### Debts & Repayments
| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | /api/farmers/{id}/debts | Get farmer debts | All |
| GET | /api/farmers/{id}/repayments | Get farmer repayments | All |
| POST | /api/repayments | Record repayment | All |

## 💡 Business Rules

| Rule | Description |
|------|-------------|
| Credit Interest | Cash price × (1 + interest rate). Default: 30% |
| Credit Limit | System blocks transactions exceeding farmer's credit limit |
| FIFO Repayment | Oldest debt is settled first |
| Partial Repayment | Remaining balance stays open if repayment is insufficient |
| Commodity Rate | Configurable rate (default: 1 kg = 1,000 FCFA) |

## 🔧 Configuration

Configurable settings stored in `settings` table:

| Key | Default | Description |
|-----|---------|-------------|
| interest_rate | 30 | Credit interest rate (%) |
| commodity_rate | 1000 | FCFA per kg of commodity |