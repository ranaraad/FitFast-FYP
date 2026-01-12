import os
import pickle
import pandas as pd
import sys

# Add current directory to path
current_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, current_dir)

from ai_module import IntelligentOutfitBuilder

def fix_outfit_builder_names():
    ai_dir = r"C:\Users\Rana\OneDrive\Desktop\FitFast FYP\fitfast\frontend\src\ai"
    artifacts_dir = os.path.join(ai_dir, "artifacts")

    print("=== FIXING OUTFIT BUILDER NAMES ===")

    # 1. Load the ORIGINAL data (with real names)
    print("\n1. Loading original data with real names...")
    original_path = os.path.join(ai_dir, "original_items.pkl")
    original_df = pd.read_pickle(original_path)

    print(f"   Original data: {original_df.shape}")
    print(f"   First item: ID={original_df.iloc[0]['ID']}, Name='{original_df.iloc[0]['Name']}'")

    # 2. Load embeddings (for similarity)
    print("\n2. Loading embeddings...")
    embeddings_dir = os.path.join(artifacts_dir, "embeddings")
    embeddings_path = os.path.join(embeddings_dir, "item_embeddings.pkl")

    item_embeddings_dict = {}
    if os.path.exists(embeddings_path):
        embeddings_df = pd.read_pickle(embeddings_path)
        print(f"   Embeddings loaded: {embeddings_df.shape}")

        # Extract embeddings
        embed_cols = [col for col in embeddings_df.columns if col.startswith('embedding_')]
        for idx, row in embeddings_df.iterrows():
            item_id = str(int(row['item_id']))
            if embed_cols:
                embeddings = row[embed_cols].values.astype('float32')
                item_embeddings_dict[item_id] = embeddings

        print(f"   Created embeddings for {len(item_embeddings_dict)} items")
    else:
        print("   ⚠️ No embeddings found")

    # 3. Load features from Step 2 (for garment info)
    print("\n3. Loading garment information...")
    features_path = os.path.join(artifacts_dir, "features_df.pkl")

    if os.path.exists(features_path):
        features_df = pd.read_pickle(features_path)
        print(f"   Features loaded: {features_df.shape}")

        # Extract garment info
        garment_info = features_df[['item_id', 'garment_type', 'garment_category', 'garment_formality']].copy()

        # Merge with original data
        merged_df = original_df.copy()
        merged_df.rename(columns={'ID': 'item_id', 'Name': 'name', 'Price': 'price'}, inplace=True)
        merged_df = pd.merge(merged_df, garment_info, on='item_id', how='left')

        print(f"   Merged data: {merged_df.shape}")
        print(f"   Columns: {list(merged_df.columns)}")

    else:
        print("   ⚠️ No features found, using original data only")
        merged_df = original_df.copy()
        merged_df.rename(columns={'ID': 'item_id', 'Name': 'name', 'Price': 'price'}, inplace=True)
        merged_df['garment_type'] = merged_df.get('Garment Type', 'unknown')
        merged_df['garment_category'] = 'other'
        merged_df['garment_formality'] = 'casual'

    # 4. Load size recommender (optional)
    print("\n4. Loading size recommender...")
    size_recommender = None
    size_rec_path = os.path.join(artifacts_dir, "size_recommender_v2.pkl")

    if os.path.exists(size_rec_path):
        try:
            with open(size_rec_path, 'rb') as f:
                size_recommender = pickle.load(f)
            print("   ✅ Size recommender loaded")
        except Exception as e:
            print(f"   ⚠️ Could not load size recommender: {e}")

    # 5. Create NEW outfit builder with REAL names
    print("\n5. Creating new outfit builder...")
    outfit_builder = IntelligentOutfitBuilder(
        items_df=merged_df,
        item_embeddings_dict=item_embeddings_dict,
        size_recommender=size_recommender
    )

    # 6. Verify names are correct
    print("\n✅ VERIFICATION:")
    print(f"   Total items in metadata: {len(outfit_builder.item_metadata)}")

    # Check first 5 items
    for item_id in ['1', '2', '3', '4', '5']:
        if item_id in outfit_builder.item_metadata:
            item = outfit_builder.item_metadata[item_id]
            print(f"   Item {item_id}: '{item['name']}' (Type: {item['garment_type']}, Price: ${item['price']})")
        else:
            print(f"   Item {item_id}: NOT FOUND")

    # 7. Test outfit building
    print("\n🧪 TEST OUTFIT BUILD:")
    test_measurements = {
        "chest_circumference": 95,
        "waist_circumference": 82,
    }

    outfit = outfit_builder.build_outfit(
        starting_item_id="1",
        user_measurements=test_measurements,
        style_theme="casual_everyday",
        max_items=3
    )

    if outfit:
        print(f"   ✅ Outfit built successfully!")
        print(f"   Starting item: {outfit['starting_item']['name']}")
        print(f"   Outfit items: {len(outfit['outfit_items'])}")

        for i, item in enumerate(outfit['outfit_items'], 1):
            print(f"   {i}. {item['name']} (${item['price']:.2f}, {item['garment_category']})")
    else:
        print("   ❌ Could not build outfit")

    # 8. Save the fixed outfit builder
    print("\n💾 Saving fixed outfit builder...")
    save_path = os.path.join(artifacts_dir, "intelligent_outfit_builder_fixed_names.pkl")

    with open(save_path, 'wb') as f:
        pickle.dump(outfit_builder, f)

    print(f"   Saved to: {save_path}")
    print(f"   File size: {os.path.getsize(save_path) / 1024:.1f} KB")

    return save_path

if __name__ == "__main__":
    fix_outfit_builder_names()