import os
import pickle
import re
from pathlib import Path
from typing import Any, Dict, List, Optional

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field, RootModel

APP_TITLE = "FitFast Fashion AI Service"
APP_DESCRIPTION = (
    "Lightweight FastAPI wrapper that exposes the notebook-generated "
    "fashion recommendation engine over HTTP."
)
_SERVICE_ROOT = Path(__file__).resolve().parent
DEFAULT_MODEL_PATH = _SERVICE_ROOT / "models" / "fashion_api.pkl"
_FALLBACK_MODEL_PATHS = [
    DEFAULT_MODEL_PATH,
    _SERVICE_ROOT.parent / "frontend" / "src" / "ai" / "artifacts" / "fashion_api.pkl",
]


class UserMeasurements(RootModel[Any]):
    root: Any = Field(default_factory=dict)

    def as_dict(self) -> Dict[str, float]:
        measurements: Dict[str, float] = {}
        data = self.root or {}
        if isinstance(data, list):
            data = {str(index): value for index, value in enumerate(data)}
        if not isinstance(data, dict):
            return measurements

        for key, value in data.items():
            if value is None:
                continue
            if isinstance(value, (int, float)):
                measurements[key] = float(value)
                continue
            if isinstance(value, str):
                match = re.search(r"[-+]?\d*\.?\d+", value)
                if match:
                    measurements[key] = float(match.group())
        return measurements


class UserPreferences(RootModel[Any]):
    root: Any = Field(default_factory=dict)

    def as_dict(self) -> Dict[str, Any]:
        if isinstance(self.root, dict):
            return dict(self.root)
        if isinstance(self.root, list):
            return {str(index): value for index, value in enumerate(self.root)}
        return {}


class PurchaseRecord(BaseModel):
    item_id: Optional[str] = None
    item_name: Optional[str] = None
    price: Optional[float] = None
    rating: Optional[float] = None
    purchased_at: Optional[str] = None


class UserSyncPayload(BaseModel):
    user_id: str
    name: Optional[str] = None
    email: Optional[str] = None
    measurements: Optional[UserMeasurements] = None
    preferences: Optional[UserPreferences] = None
    purchase_history: Optional[List[PurchaseRecord]] = None
    wishlist: Optional[List[Dict[str, Any]]] = None
    view_history: Optional[List[Dict[str, Any]]] = None

    def to_engine_payload(self) -> Dict[str, Any]:
        return {
            "user_id": self.user_id,
            "name": self.name or "",
            "email": self.email or "",
            "measurements": self.measurements.as_dict() if self.measurements else None,
            "preferences": self.preferences.as_dict() if self.preferences else None,
            "purchase_history": [record.model_dump(exclude_none=True) for record in (self.purchase_history or [])],
            "wishlist": self.wishlist or [],
            "view_history": self.view_history or [],
        }


class SizeRequest(BaseModel):
    garment_type: str = Field(..., alias="garmentType")
    item_id: Optional[str] = Field(None, alias="itemId")

    class Config:
        populate_by_name = True


class OutfitRequest(BaseModel):
    starting_item_id: Optional[str] = Field(None, alias="startingItemId")
    style: Optional[str] = None
    max_items: int = Field(4, ge=2, le=6)

    class Config:
        populate_by_name = True


class RecommendationRequest(BaseModel):
    limit: int = Field(6, ge=1, le=20)


class PurchasePayload(BaseModel):
    item_id: str
    item_name: Optional[str] = None
    price: Optional[float] = None


app = FastAPI(title=APP_TITLE, description=APP_DESCRIPTION)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

_api_instance: Any = None


def _resolve_model_path() -> Path:
    candidate = os.getenv("FASHION_AI_MODEL")
    if candidate:
        path = Path(candidate)
        if path.exists():
            return path
        raise FileNotFoundError(
            f"fashion_api.pkl not found at {path}. "
            "Set FASHION_AI_MODEL to the correct location."
        )

    for path in _FALLBACK_MODEL_PATHS:
        if path.exists():
            return path

    fallback_list = "\n".join(str(p) for p in _FALLBACK_MODEL_PATHS)
    raise FileNotFoundError(
        "fashion_api.pkl not found in any expected location. "
        "Export the pickle from the notebook or set FASHION_AI_MODEL to point to it. "
        f"Checked:\n{fallback_list}"
    )


def get_api() -> Any:
    global _api_instance
    if _api_instance is None:
        model_path = _resolve_model_path()
        try:
            with model_path.open("rb") as handle:
                _api_instance = pickle.load(handle)
        except Exception as exc:  # pragma: no cover - defensive only
            raise RuntimeError(f"Failed to load Fashion API from {model_path}: {exc}")
    return _api_instance


@app.post("/api/users")
def register_user(payload: UserSyncPayload) -> Dict[str, Any]:
    api = get_api()
    try:
        profile = api.register(payload.to_engine_payload())
        return {"data": profile}
    except Exception as exc:  # pragma: no cover - delegated to notebook code
        raise HTTPException(status_code=500, detail=f"Registration failed: {exc}")


@app.post("/api/users/{user_id}/size")
def size_recommendation(user_id: str, request: SizeRequest) -> Dict[str, Any]:
    api = get_api()
    try:
        result = api.get_size(user_id, request.garment_type, request.item_id)
        return {"data": result}
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Size recommendation failed: {exc}")


@app.post("/api/users/{user_id}/outfit")
def outfit_recommendation(user_id: str, request: OutfitRequest) -> Dict[str, Any]:
    api = get_api()
    try:
        result = api.build_outfit(
            user_id=user_id,
            starting_item_id=request.starting_item_id,
            style=request.style,
        )
        if isinstance(result, dict):
            result.setdefault("max_items", request.max_items)
        return {"data": result}
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Outfit recommendation failed: {exc}")


@app.post("/api/users/{user_id}/recommendations")
def personalized_recommendations(user_id: str, request: RecommendationRequest) -> Dict[str, Any]:
    api = get_api()
    try:
        result = api.recommend(user_id, n=request.limit)
        return {"data": result}
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Recommendation failed: {exc}")


@app.post("/api/users/{user_id}/insights")
def user_insights(user_id: str) -> Dict[str, Any]:
    api = get_api()
    try:
        result = api.get_insights(user_id)
        return {"data": result}
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Insights failed: {exc}")


@app.post("/api/users/{user_id}/purchases")
def add_purchase(user_id: str, purchase: PurchasePayload) -> Dict[str, Any]:
    api = get_api()
    try:
        result = api.add_purchase(
            user_id=user_id,
            item_id=purchase.item_id,
            item_name=purchase.item_name or "",
            price=purchase.price or 0.0,
        )
        return {"data": result}
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Purchase logging failed: {exc}")


@app.get("/health")
def healthcheck() -> Dict[str, Any]:
    try:
        get_api()
        return {"status": "ok"}
    except Exception as exc:  # pragma: no cover - sanity endpoint
        raise HTTPException(status_code=500, detail=str(exc))


if __name__ == "__main__":  # pragma: no cover - manual launch helper
    import uvicorn

    uvicorn.run(
        "main:app",
        host=os.getenv("HOST", "0.0.0.0"),
        port=int(os.getenv("PORT", "8001")),
        reload=True,
    )
