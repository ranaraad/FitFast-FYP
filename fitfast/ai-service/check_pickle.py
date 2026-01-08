import pickle
from pathlib import Path

path = Path(r"C:/Users/Rana/OneDrive/Desktop/FitFast FYP/fitfast/frontend/src/ai/artifacts/fashion_recommendation_engine.pkl")
obj = pickle.load(path.open("rb"))
print(type(obj))
for name in ("register", "get_size", "build_outfit", "recommend"):
    print(f"{name}: {hasattr(obj, name)}")
