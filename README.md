# Order Management System

## Running the Application

Install dependencies:

```bash
composer install          
```
Start the DB, application, and other services using Docker:

```bash
docker-compose up -d --build 
```
Access the application at `http://localhost:8000`.

### Run all API tests:

```bash
php vendor/bin/codecept run Api
```
