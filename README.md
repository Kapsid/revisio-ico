# Registry Service

A microservice for fetching company data from Czech (CZ), Slovak (SK), and Polish (PL) business registries.

## Technology Stack

- **PHP 8.4** - Latest PHP version with readonly classes, enums, named arguments
- **Laravel 12** - Modern Laravel with streamlined configuration
- **MariaDB** - Primary database for caching registry data
- **Docker** - PHP-FPM + NGINX + MariaDB + Redis

## Quick Start

### 1. Start Docker containers

```bash
docker-compose up -d --build
```

### 2. Install dependencies

```bash
docker-compose exec php composer install
```

### 3. Setup environment

```bash
cp .env.example .env
docker-compose exec php php artisan key:generate
```

### 4. Run migrations and seed

```bash
docker-compose exec php php artisan migrate
docker-compose exec php php artisan db:seed
```

### 5. Access the API

The service is available at: `http://localhost:8080`

## Test User

- **Email:** test@example.com
- **Password:** test20252026

## API Endpoints

### Get Company Information

```
GET /api/company/info/{countryCode}/{companyId}
```

**Parameters:**
- `countryCode`: cz, sk, or pl
- `companyId`: Company identification number (IČO, REGON, NIP)

**Example:**
```bash
curl http://localhost:8080/api/company/info/cz/25596641
```

**Response:**
```json
{
  "status": "OK",
  "data": {
    "name": "Company a.s.",
    "id": "25596641",
    "vatId": "CZ25596641",
    "vatPayer": true,
    "countryCode": "cz",
    "address": {
      "street": "Pražská",
      "houseNumber": "123",
      "orientationNumber": "12",
      "zip": 11000,
      "city": "Praha"
    }
  }
}
```

### Force Refresh

```
POST /api/company/refresh/{countryCode}/{companyId}
```

Bypasses cache and fetches fresh data from the registry.

### Health Check

```
GET /api/health
```

## Caching Strategy

- Data is cached in MariaDB
- Cache TTL: 24 hours (configurable via `REGISTRY_CACHE_TTL`)
- Data is **versioned**, not overwritten
- Old versions are preserved for audit/history
- `is_current` flag marks the latest version

## Configuration

Key settings in `.env`:

```env
# Cache TTL in hours
REGISTRY_CACHE_TTL=24

# Polish GUS API (required for PL registry)
GUS_API_KEY=your_api_key
GUS_API_ENV=dev  # or 'prod'
```

## Registry Libraries

| Country | Library | Registry |
|---------|---------|----------|
| CZ | h4kuna/ares | ARES (Administrative Register of Economic Subjects) |
| SK | Custom HTTP | RPO (Register právnických osôb) |
| PL | gusapi/gusapi | GUS/REGON (Central Statistical Office) |
