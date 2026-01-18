# Registry Service

A microservice for fetching company data from Czech (CZ), Slovak (SK), and Polish (PL) business registries.

## Technology Stack

- **PHP 8.4** - Latest PHP version with readonly classes, enums, named arguments
- **Laravel 12** - Modern Laravel with streamlined configuration
- **MariaDB** - Primary database for caching registry data
- **Docker** - PHP-FPM + NGINX + MariaDB

## Quick Start

### 1. Setup environment

```bash
cp .env.example .env
```

### 2. Run full setup

```bash
make setup
```

This builds containers, installs dependencies, generates app key, and runs migrations.

### 3. Access the API

The service is available at: `http://localhost:8080`

## Available Make Commands

```bash
make help      # Show all available commands
make build     # Build Docker containers
make up        # Start all containers
make down      # Stop all containers
make restart   # Restart all containers
make logs      # Show container logs
make shell     # Open PHP container shell
make install   # Install composer dependencies
make migrate   # Run database migrations
make seed      # Seed the database
make fresh     # Fresh install (migrate:fresh --seed)
make test      # Run tests
make key       # Generate application key
make setup     # Full setup (build + up + install + key + migrate + seed)
```

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

### Health Check

```
GET /api/health
```

## Caching Strategy

- Data is cached in MariaDB
- Cache TTL: 24 hours (configurable via `REGISTRY_CACHE_TTL`)
- One record per company

## Configuration

Key settings in `.env`:

```env
# Cache TTL in hours
REGISTRY_CACHE_TTL=24

# Polish GUS API (required for PL registry)
# Public test key - safe for development
GUS_API_KEY=abcde12345abcde12345
GUS_API_ENV=dev  # or 'prod'
```

## Registry Libraries

| Country | Library | Registry |
|---------|---------|----------|
| CZ | h4kuna/ares | ARES (Administrative Register of Economic Subjects) |
| SK | lubosdz/parser-orsr | ORSR (Obchodný register Slovenskej republiky) |
| PL | gusapi/gusapi | GUS/REGON (Central Statistical Office) |
