# outfit_api.py - NO DEBUG PRINTS, PURE JSON ONLY
import sys
import json
import pickle
import os

def load_outfit_model():
    """Load outfit builder model - UPDATED VERSION"""
    ai_dir = os.path.dirname(os.path.abspath(__file__))
    artifacts_dir = os.path.join(ai_dir, "artifacts")

    # FIRST: Try the NEW fixed model
    fixed_model_path = os.path.join(artifacts_dir, "intelligent_outfit_builder_fixed_names.pkl")
    if os.path.exists(fixed_model_path):
        try:
            with open(fixed_model_path, "rb") as f:
                print(f"Loading fixed model: {fixed_model_path}", file=sys.stderr)
                return pickle.load(f), "intelligent_outfit_builder_fixed_names.pkl"
        except Exception as e:
            print(f"Error loading fixed model: {e}", file=sys.stderr)
            # Continue to try other models

    # SECOND: Try the original model (fallback)
    model_path = os.path.join(artifacts_dir, "intelligent_outfit_builder.pkl")
    if os.path.exists(model_path):
        try:
            with open(model_path, "rb") as f:
                return pickle.load(f), "intelligent_outfit_builder.pkl"
        except:
            pass

    # THIRD: Try complete system
    complete_path = os.path.join(artifacts_dir, "complete_outfit_system.pkl")
    if os.path.exists(complete_path):
        try:
            with open(complete_path, "rb") as f:
                system = pickle.load(f)
                if isinstance(system, dict) and 'outfit_builder' in system:
                    return system['outfit_builder'], "complete_outfit_system.pkl"
                elif hasattr(system, 'build_outfit'):
                    return system, "complete_outfit_system.pkl"
        except:
            pass

    return None, None

def main():
    try:
        # Load request data
        with open(sys.argv[1], "r") as f:
            request_data = json.load(f)

        # Load model
        outfit_builder, model_file = load_outfit_model()

        if not outfit_builder:
            result = {
                "success": False,
                "message": "Outfit builder not found",
                "outfit": {}
            }
        else:
            # Build outfit
            outfit = outfit_builder.build_outfit(
                str(request_data["starting_item_id"]),
                request_data.get("user_measurements", {}),
                request_data["style_theme"],
                max_items=request_data["max_items"]
            )

            if not outfit:
                result = {
                    "success": True,
                    "message": "No outfit built",
                    "outfit": {},
                    "model_used": model_file
                }
            else:
                # Ensure outfit is JSON serializable
                serializable_outfit = {
                    "starting_item": {
                        "id": str(outfit.get("starting_item", {}).get("id", "")),
                        "name": str(outfit.get("starting_item", {}).get("name", "")),
                        "garment_type": str(outfit.get("starting_item", {}).get("garment_type", "")),
                        "price": float(outfit.get("starting_item", {}).get("price", 0)),
                    },
                    "outfit_items": [],
                    "total_price": float(outfit.get("total_price", 0)),
                    "item_count": int(outfit.get("item_count", 0)),
                    "compatibility_score": float(outfit.get("compatibility_score", 0)),
                    "style_coherence": float(outfit.get("style_coherence", 0)),
                    "style_theme": str(outfit.get("style_theme", "")),
                    "description": str(outfit.get("description", ""))
                }

                # Add outfit items
                for item in outfit.get("outfit_items", []):
                    serializable_outfit["outfit_items"].append({
                        "id": str(item.get("id", "")),
                        "name": str(item.get("name", "")),
                        "garment_type": str(item.get("garment_type", "")),
                        "garment_category": str(item.get("garment_category", "")),
                        "price": float(item.get("price", 0)),
                    })

                result = {
                    "success": True,
                    "outfit": serializable_outfit,
                    "model_used": model_file,
                    "items_count": serializable_outfit["item_count"]
                }

    except Exception as e:
        result = {
            "success": False,
            "message": str(e),
            "outfit": {}
        }

    # OUTPUT PURE JSON ONLY (no prints, no stderr)
    sys.stdout.write(json.dumps(result))
    sys.stdout.flush()

if __name__ == "__main__":
    main()