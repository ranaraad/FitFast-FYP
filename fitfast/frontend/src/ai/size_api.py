# size_api.py - NO DEBUG PRINTS, PURE JSON ONLY
import sys
import json
import pickle
import os

def load_size_model():
    """Load size recommender model"""
    ai_dir = os.path.dirname(os.path.abspath(__file__))
    artifacts_dir = os.path.join(ai_dir, "artifacts")

    # Try to load size recommender
    model_path = os.path.join(artifacts_dir, "size_recommender_v2.pkl")
    if os.path.exists(model_path):
        try:
            with open(model_path, "rb") as f:
                return pickle.load(f), "size_recommender_v2.pkl"
        except:
            pass

    # Try complete system
    complete_path = os.path.join(artifacts_dir, "complete_size_system.pkl")
    if os.path.exists(complete_path):
        try:
            with open(complete_path, "rb") as f:
                system = pickle.load(f)
                if isinstance(system, dict) and 'size_recommender' in system:
                    return system['size_recommender'], "complete_size_system.pkl"
        except:
            pass

    return None, None

def main():
    try:
        # Load request data
        with open(sys.argv[1], "r") as f:
            request_data = json.load(f)

        # Load model
        recommender, model_file = load_size_model()

        if not recommender:
            result = {
                "success": False,
                "message": "AI model not found",
                "recommendations": []
            }
        else:
            # Get recommendations
            recommendations = recommender.find_best_fitting_items(
                request_data["user_measurements"],
                request_data["garment_type"],
                top_k=request_data.get("top_k", 5),
                min_fit_score=request_data.get("min_fit_score", 0.3)
            )

            # Ensure recommendations are JSON serializable
            serializable_recs = []
            for rec in recommendations:
                serializable_recs.append({
                    "item_id": int(rec.get("item_id", 0)),
                    "item_name": str(rec.get("item_name", "")),
                    "recommended_size": str(rec.get("recommended_size", "")),
                    "overall_fit_score": float(rec.get("overall_fit_score", 0)),
                    "fit_assessment": str(rec.get("fit_assessment", "")),
                    "price": float(rec.get("price", 0)),
                    "category": str(rec.get("category", "")),
                    "store": str(rec.get("store", "")),
                    "garment_type": str(rec.get("garment_type", "")),
                    "available_sizes": list(rec.get("available_sizes", [])),
                })

            result = {
                "success": True,
                "recommendations": serializable_recs,
                "model_used": model_file,
                "garment_type": request_data["garment_type"],
                "items_found": len(serializable_recs)
            }

    except Exception as e:
        result = {
            "success": False,
            "message": str(e),
            "recommendations": []
        }

    # OUTPUT PURE JSON ONLY (no prints, no stderr)
    sys.stdout.write(json.dumps(result))
    sys.stdout.flush()

if __name__ == "__main__":
    main()