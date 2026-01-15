# Copilot Coding Agent Instructions for FitFast FYP

## Project Overview
FitFast FYP is a full-stack application for fashion e-commerce, combining a Laravel (PHP) backend API, a React (Vite) frontend, and a Python FastAPI microservice for AI-powered recommendations.

## Architecture & Key Components
- **Backend (Laravel)**: Main business logic, user management, orders, payments, etc. (see `app/`, `routes/`, `database/`)
- **Frontend (React + Vite)**: User-facing web/mobile app (see `frontend/src/`)
- **AI Service (FastAPI)**: Exposes ML model endpoints for recommendations (see `ai-service/`)
- **Data Flow**: Frontend ↔ Laravel API ↔ AI Service (via HTTP)

## Developer Workflows
- **Backend**
  - Start: `php artisan serve` (Laravel dev server)
  - Migrate DB: `php artisan migrate`
  - Run tests: `php artisan test`
  - Seed data: `php artisan db:seed`
- **Frontend**
  - Start: `cd frontend && npm install && npm run dev`
  - Build: `npm run build`
- **AI Service**
  - Setup: `cd ai-service && python -m venv .venv && .venv\Scripts\activate && pip install -r requirements.txt`
  - Run: `uvicorn main:app --host 0.0.0.0 --port 8001`
  - Model: Place `fashion_api.pkl` in `ai-service/models/` (see ai-service/README.md)

## Project-Specific Conventions
- **API responses**: AI service always returns `{ "data": ... }` for consistency with Laravel API.
- **Model files**: ML pickles must be rebuilt after retraining and placed in the correct folder.
- **Frontend**: Uses Vite, React, and Capacitor for mobile. See `frontend/README.md` for plugin details.
- **Backend**: Follows Laravel conventions for controllers, models, migrations, and observers.
- **Cross-service communication**: Laravel calls AI service via HTTP (see ai-service endpoints table).

## Integration & Patterns
- **AI endpoints**: Documented in `ai-service/README.md` (POST to `/api/users/{userId}/...`)
- **Frontend/Backend contract**: Keep API shape and error handling consistent between Laravel and FastAPI.
- **Environment variables**: Use `.env` for Laravel, and `FASHION_AI_MODEL` for AI service model path.

## Key Files & Directories
- `app/Models/`, `app/Http/Controllers/`: Laravel business logic
- `frontend/src/`: React app source
- `ai-service/main.py`, `ai-service/models/`: AI service code and model
- `routes/api.php`: Laravel API routes
- `database/migrations/`: DB schema

## Example: Adding a New AI Endpoint
1. Update `ai-service/main.py` with a new FastAPI route.
2. Document the endpoint in `ai-service/README.md`.
3. Update Laravel to call the new endpoint (see `app/Http/Controllers/`).
4. Update frontend to consume the new data.

---
For more, see each component's README. When in doubt, follow Laravel and Vite best practices, but prefer the conventions above when they differ.
