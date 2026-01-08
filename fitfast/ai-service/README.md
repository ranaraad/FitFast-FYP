# FitFast Fashion AI Service

FastAPI wrapper around the notebook-generated **FashionRecommendationEngine**. The service exposes HTTP endpoints so the Laravel API and React frontend can request personalized size and outfit recommendations.

## 1. Prepare the pickle

1. Open `frontend/src/ai/Untitled1 (3).ipynb` in Jupyter or VS Code.
2. Run all cells until the pickle files (`fashion_api.pkl`, `fashion_recommendation_engine.pkl`) are produced.
3. Move the generated `fashion_api.pkl` into `ai-service/models/` (create the folder if it does not exist).

```
ai-service/
  models/
    fashion_api.pkl
```

If you store the pickle elsewhere, set the `FASHION_AI_MODEL` environment variable to the absolute path before starting the service.

## 2. Install dependencies

```bash
cd ai-service
python -m venv .venv
.venv\Scripts\activate  # Windows
pip install -r requirements.txt
```

## 3. Run the service locally

```bash
uvicorn main:app --host 0.0.0.0 --port 8001
```

The health-check is available at `http://localhost:8001/health`.

## 4. Available endpoints

| Method | Path | Description |
| ------ | ---- | ----------- |
| POST | `/api/users` | Register or refresh a user profile inside the engine |
| POST | `/api/users/{userId}/size` | Size recommendation for a garment type and optional item |
| POST | `/api/users/{userId}/outfit` | Build a cohesive outfit |
| POST | `/api/users/{userId}/recommendations` | Personalized product list |
| POST | `/api/users/{userId}/insights` | Retrieve user insights |
| POST | `/api/users/{userId}/purchases` | Submit a new purchase so the engine can learn |

All responses follow `{ "data": ... }` to simplify consumption from Laravel.

## 5. Deployment tips

- Run the service behind HTTPS in production (nginx/Traefik).
- Configure `FASHION_AI_MODEL` to point at a persistent model location.
- Add observability with `/health` and FastAPI logs.
- Rebuild the pickle after retraining in the notebook, then restart the service.
