# ai_module.py - REAL IMPLEMENTATION (with actual logic)
import pandas as pd
import numpy as np
import json
import pickle
from sklearn.metrics.pairwise import cosine_similarity

# ========== SIZE RECOMMENDER V2 (REAL LOGIC) ==========
class SizeRecommenderV2:
    def __init__(self):
        self.measurement_db = None
        self.item_info = {}
        self.garment_stats = {}

    def load_data(self, measurement_db, original_df):
        """Load measurement database and item information"""
        self.measurement_db = measurement_db

        # Store item information
        for idx, row in original_df.iterrows():
            item_id = row.get('ID', idx + 1)
            self.item_info[item_id] = {
                'name': row.get('Name', ''),
                'price': row.get('Price', 0),
                'category': row.get('Category', ''),
                'store': row.get('Store', ''),
                'total_stock': row.get('Total Stock', 0),
                'description': row.get('Description', ''),
                'garment_type_db': row.get('Garment Type', '')
            }

        # Calculate statistics
        self._calculate_statistics()

        # Removed debug prints

    def _calculate_statistics(self):
        """Calculate statistics for each garment type"""
        if self.measurement_db.empty:
            return

        for garment_type in self.measurement_db['garment_type'].unique():
            type_data = self.measurement_db[self.measurement_db['garment_type'] == garment_type]

            stats = {
                'total_items': type_data['item_id'].nunique(),
                'available_sizes': sorted(type_data['size'].unique()),
                'common_measurements': [],
                'size_stats': {}
            }

            # Find common measurements (appear in > 50% of records)
            measurement_cols = [col for col in type_data.columns
                              if col not in ['item_id', 'item_name', 'garment_type', 'size',
                                           'fit_type', 'ease', 'stretch', 'size_system']]

            for col in measurement_cols:
                if type_data[col].notna().sum() > len(type_data) * 0.5:
                    stats['common_measurements'].append(col)

                    # Calculate stats per size
                    size_stats = {}
                    for size in stats['available_sizes']:
                        size_values = type_data[type_data['size'] == size][col]
                        if size_values.notna().any():
                            size_stats[size] = {
                                'min': float(size_values.min()),
                                'max': float(size_values.max()),
                                'mean': float(size_values.mean()),
                                'std': float(size_values.std())
                            }
                    stats['size_stats'][col] = size_stats

            self.garment_stats[garment_type] = stats

    def get_garment_types(self):
        """Get list of available garment types"""
        if self.measurement_db is not None:
            return sorted(self.measurement_db['garment_type'].unique())
        return []

    def get_garment_stats(self, garment_type):
        """Get statistics for a garment type"""
        return self.garment_stats.get(garment_type, {})

    def find_best_fitting_items(self, user_measurements, garment_type, top_k=5, min_fit_score=0.3):
        """
        Find items with the best fitting size for the user

        Args:
            user_measurements: dict of user measurements in cm
            garment_type: type of garment to search for
            top_k: number of recommendations to return
            min_fit_score: minimum fit score to include

        Returns:
            List of recommended items with fit details
        """
        # Removed debug prints

        # Get items of this garment type
        garment_items = self.measurement_db[self.measurement_db['garment_type'] == garment_type]

        if garment_items.empty:
            return []

        recommendations = []
        processed_items = set()

        # For each unique item
        for item_id in garment_items['item_id'].unique():
            if item_id in processed_items:
                continue

            item_sizes = garment_items[garment_items['item_id'] == item_id]
            item_info = self.item_info.get(item_id, {})

            # Find best size for this user in this item
            best_fit = self._find_best_size_for_item(user_measurements, item_sizes, garment_type)

            if best_fit and best_fit['overall_fit_score'] >= min_fit_score:
                recommendation = {
                    'item_id': item_id,
                    'item_name': item_info.get('name', f'Item {item_id}'),
                    'price': item_info.get('price', 0),
                    'category': item_info.get('category', ''),
                    'store': item_info.get('store', ''),
                    'garment_type': garment_type,
                    'recommended_size': best_fit['size'],
                    'overall_fit_score': best_fit['overall_fit_score'],
                    'fit_assessment': best_fit['fit_assessment'],
                    'key_measurements': best_fit.get('key_measurements', {}),
                    'available_sizes': list(item_sizes['size'].unique()),
                    'size_comparison': best_fit.get('size_comparison', {})
                }

                recommendations.append(recommendation)
                processed_items.add(item_id)

        # Sort by fit score
        recommendations.sort(key=lambda x: x['overall_fit_score'], reverse=True)

        return recommendations[:top_k]

    def _find_best_size_for_item(self, user_measurements, item_sizes_df, garment_type):
        """Find the best fitting size for a specific item"""
        best_size = None
        best_score = -1
        best_details = {}

        # Get key measurements for this garment type
        key_measurements = self._get_key_measurements(garment_type)

        # For each available size in this item
        for _, size_row in item_sizes_df.iterrows():
            size_name = size_row['size']

            # Calculate fit for this size
            fit_result = self._calculate_fit_score(user_measurements, size_row, key_measurements)

            if fit_result['overall_score'] > best_score:
                best_score = fit_result['overall_score']
                best_size = size_name
                best_details = fit_result

        if best_size is None:
            return None

        # Determine fit assessment
        if best_score >= 0.8:
            assessment = "Excellent Fit"
        elif best_score >= 0.6:
            assessment = "Good Fit"
        elif best_score >= 0.4:
            assessment = "Fair Fit"
        else:
            assessment = "Poor Fit"

        return {
            'size': best_size,
            'overall_fit_score': best_score,
            'fit_assessment': assessment,
            'key_measurements': best_details.get('measurement_scores', {}),
            'size_comparison': best_details.get('comparison', {})
        }

    def _get_key_measurements(self, garment_type):
        """Get key measurements for a garment type based on your database"""
        # Based on your garment type mapping from Step 2
        measurement_mapping = {
            't_shirt': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'v_neck_tee': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'fitted_shirt': ['chest_circumference', 'waist_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'dress_shirt': ['chest_circumference', 'waist_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'collar_size'],
            'slim_pants': ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
            'regular_pants': ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening'],
            'regular_jeans': ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening', 'rise'],
            'slim_jeans': ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference', 'leg_opening', 'rise'],
            'casual_shorts': ['waist_circumference', 'hips_circumference', 'short_length', 'thigh_circumference', 'leg_opening'],
            'a_line_dress': ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length', 'shoulder_to_hem'],
            'bodycon_dress': ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length'],
            'maxi_dress': ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length', 'shoulder_to_hem'],
            'sun_dress': ['chest_circumference', 'waist_circumference', 'hips_circumference', 'dress_length', 'shoulder_to_hem'],
            'pencil_skirt': ['waist_circumference', 'hips_circumference', 'skirt_length'],
            'a_line_skirt': ['waist_circumference', 'hips_circumference', 'skirt_length'],
            'bomber_jacket': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'bicep_circumference'],
            'denim_jacket': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'trench_coat': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'wool_coat': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'crewneck_sweater': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'v_neck_sweater': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width'],
            'pullover_hoodie': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'hood_height'],
            'zip_hoodie': ['chest_circumference', 'garment_length', 'sleeve_length', 'shoulder_width', 'hood_height'],
            'yoga_pants': ['waist_circumference', 'hips_circumference', 'inseam_length', 'thigh_circumference'],
            'training_shorts': ['waist_circumference', 'hips_circumference', 'short_length', 'thigh_circumference'],
            'bikini_top': ['chest_circumference', 'underbust_circumference', 'cup_size'],
            'swim_trunks': ['waist_circumference', 'hips_circumference', 'short_length', 'thigh_circumference'],
            'briefs': ['waist_circumference', 'hips_circumference'],
            'boxers': ['waist_circumference', 'hips_circumference', 'short_length'],
            'ankle_socks': ['foot_length'],
            'crew_socks': ['foot_length'],
            'sneakers': ['foot_length', 'foot_width'],
            'dress_shoes': ['foot_length', 'foot_width'],
        }

        # Try exact match first
        if garment_type in measurement_mapping:
            return measurement_mapping[garment_type]

        # Try partial match
        for key, measurements in measurement_mapping.items():
            if key in garment_type or garment_type in key:
                return measurements

        # Default based on common patterns
        if 'shirt' in garment_type or 'tee' in garment_type or 'top' in garment_type:
            return ['chest_circumference', 'garment_length', 'sleeve_length']
        elif 'pant' in garment_type or 'jean' in garment_type or 'short' in garment_type:
            return ['waist_circumference', 'hips_circumference', 'inseam_length']
        elif 'dress' in garment_type:
            return ['chest_circumference', 'waist_circumference', 'hips_circumference', 'garment_length']
        elif 'jacket' in garment_type or 'coat' in garment_type:
            return ['chest_circumference', 'sleeve_length', 'garment_length']
        elif 'skirt' in garment_type:
            return ['waist_circumference', 'hips_circumference', 'garment_length']
        else:
            return ['chest_circumference', 'waist_circumference']  # Default fallback

    def _calculate_fit_score(self, user_measurements, size_row, key_measurements):
        """Calculate detailed fit score"""
        measurement_scores = {}
        comparison = {}
        total_score = 0
        matches = 0

        # Score key measurements
        for measurement in key_measurements:
            if measurement in user_measurements and measurement in size_row:
                user_val = user_measurements[measurement]
                size_val = size_row[measurement]

                if pd.notna(size_val):
                    try:
                        size_val = float(size_val)
                        difference = user_val - size_val
                        abs_difference = abs(difference)

                        # Calculate score
                        score = self._measurement_score(abs_difference, measurement)

                        measurement_scores[measurement] = {
                            'score': score,
                            'difference': difference,
                            'user': user_val,
                            'size': size_val,
                            'assessment': self._fit_assessment(difference, measurement)
                        }

                        comparison[measurement] = {
                            'user': user_val,
                            'size': size_val,
                            'difference': difference
                        }

                        total_score += score
                        matches += 1
                    except:
                        continue

        if matches == 0:
            return {'overall_score': 0, 'measurement_scores': {}, 'comparison': {}}

        overall_score = total_score / matches

        return {
            'overall_score': overall_score,
            'measurement_scores': measurement_scores,
            'comparison': comparison
        }

    def _measurement_score(self, difference, measurement_type):
        """Calculate score based on difference and measurement type"""
        # Different tolerances for different measurements
        if 'chest' in measurement_type:
            if difference <= 2: return 1.0
            elif difference <= 5: return 0.8
            elif difference <= 8: return 0.5
            else: return 0.2
        elif 'waist' in measurement_type:
            if difference <= 1: return 1.0
            elif difference <= 3: return 0.8
            elif difference <= 6: return 0.4
            else: return 0.1
        elif 'length' in measurement_type or 'sleeve' in measurement_type:
            if difference <= 3: return 1.0
            elif difference <= 6: return 0.7
            elif difference <= 10: return 0.4
            else: return 0.1
        elif 'hips' in measurement_type:
            if difference <= 2: return 1.0
            elif difference <= 5: return 0.7
            elif difference <= 8: return 0.4
            else: return 0.1
        else:
            if difference <= 2: return 1.0
            elif difference <= 5: return 0.7
            elif difference <= 8: return 0.4
            else: return 0.1

    def _fit_assessment(self, difference, measurement_type):
        """Provide human-readable fit assessment"""
        abs_diff = abs(difference)

        if 'chest' in measurement_type:
            if abs_diff <= 2: return "Perfect"
            elif abs_diff <= 5: return "Good"
            elif abs_diff <= 8: return "Slightly loose/tight"
            else: return "Too loose/tight"
        elif 'waist' in measurement_type:
            if abs_diff <= 1: return "Perfect"
            elif abs_diff <= 3: return "Good"
            elif abs_diff <= 6: return "Noticeably loose/tight"
            else: return "Too loose/tight"
        else:
            if abs_diff <= 2: return "Perfect"
            elif abs_diff <= 5: return "Good"
            elif abs_diff <= 8: return "Acceptable"
            else: return "Poor"

# ========== INTELLIGENT OUTFIT BUILDER (REAL LOGIC) ==========
class IntelligentOutfitBuilder:
    def __init__(self, items_df, item_embeddings_dict, size_recommender=None):
        """
        Intelligent outfit builder with compatibility rules
        """
        self.items_df = items_df.copy()
        self.item_embeddings_dict = item_embeddings_dict
        self.size_recommender = size_recommender

        # Build item metadata
        self.item_metadata = self._build_item_metadata()

        # Define outfit compatibility rules
        self.compatibility_rules = self._define_compatibility_rules()

        # Define style themes
        self.style_themes = self._define_style_themes()

    def _build_item_metadata(self):
        """Build comprehensive item metadata"""
        metadata = {}

        for idx, row in self.items_df.iterrows():
            item_id = str(int(row['item_id']))

            # Determine category
            garment_type = str(row.get('garment_type', 'unknown')).lower()
            garment_category = self._determine_category(garment_type)

            # Determine formality
            formality = self._determine_formality(
                garment_type,
                row.get('garment_formality', 'casual'),
                row.get('name', '')
            )

            metadata[item_id] = {
                'id': item_id,
                'name': str(row.get('name', f'Item {item_id}')),
                'garment_type': garment_type,
                'garment_category': garment_category,
                'formality': formality,
                'price': float(row.get('price', 0)),
                'description': str(row.get('description', '')),
                'store': str(row.get('store', 'unknown')),
                'main_category': str(row.get('category', 'unknown')),
                'has_embeddings': item_id in self.item_embeddings_dict
            }

        return metadata

    def _determine_category(self, garment_type):
        """Determine outfit category from garment type"""
        garment_type = str(garment_type).lower()

        # Top items
        if any(word in garment_type for word in ['tee', 't_shirt', 'shirt', 'blouse', 'top',
                                                'sweater', 'hoodie', 'cardigan', 'polo']):
            return 'top'

        # Bottom items
        elif any(word in garment_type for word in ['pant', 'jean', 'trouser', 'chino',
                                                  'legging', 'sweatpant']):
            return 'bottom'

        # Shorts
        elif any(word in garment_type for word in ['short', 'bermuda']):
            return 'shorts'

        # Skirts
        elif any(word in garment_type for word in ['skirt']):
            return 'skirt'

        # Dresses
        elif any(word in garment_type for word in ['dress', 'jumpsuit', 'romper']):
            return 'dress'

        # Outerwear
        elif any(word in garment_type for word in ['jacket', 'coat', 'blazer',
                                                  'vest', 'gilet']):
            return 'outerwear'

        # Footwear
        elif any(word in garment_type for word in ['shoe', 'sneaker', 'boot',
                                                  'sandal', 'loafer']):
            return 'footwear'

        # Accessories
        elif any(word in garment_type for word in ['bag', 'hat', 'belt', 'scarf']):
            return 'accessory'

        else:
            return 'other'

    def _determine_formality(self, garment_type, existing_formality, item_name):
        """Determine formality level"""
        # Use existing if available
        if existing_formality and existing_formality != 'unknown':
            return existing_formality

        # Determine from garment type and name
        item_text = f"{garment_type} {item_name}".lower()

        if any(word in item_text for word in ['formal', 'dress', 'suit', 'blazer', 'oxford']):
            return 'formal'
        elif any(word in item_text for word in ['casual', 'tee', 'hoodie', 'sweat']):
            return 'casual'
        elif any(word in item_text for word in ['business', 'work', 'office', 'chino']):
            return 'business_casual'
        elif any(word in item_text for word in ['athletic', 'training', 'gym', 'sport']):
            return 'athletic'
        else:
            return 'casual'  # Default

    def _define_compatibility_rules(self):
        """Define sophisticated compatibility rules"""
        return {
            # Category compatibility matrix
            'category_compatibility': {
                'top': ['bottom', 'shorts', 'skirt'],
                'bottom': ['top', 'sweater', 'outerwear'],
                'shorts': ['top', 'sweater', 'outerwear'],
                'skirt': ['top', 'sweater', 'outerwear'],
                'dress': ['outerwear', 'footwear', 'accessory'],
                'outerwear': ['top', 'sweater', 'bottom', 'shorts', 'skirt', 'dress'],
                'sweater': ['bottom', 'shorts', 'skirt'],
                'footwear': ['bottom', 'shorts', 'skirt', 'dress'],
                'accessory': ['top', 'bottom', 'dress', 'outerwear']
            },

            # Formality compatibility
            'formality_compatibility': {
                'athletic': ['athletic'],
                'casual': ['casual', 'athletic'],
                'business_casual': ['business_casual', 'casual'],
                'formal': ['formal', 'business_casual']
            },

            # Seasonal considerations
            'seasonal_weights': {
                'summer': {'shorts': 2.0, 'dress': 2.0, 'outerwear': 0.5},
                'winter': {'outerwear': 2.0, 'sweater': 2.0, 'shorts': 0.2},
                'spring': {'top': 1.5, 'dress': 1.5, 'outerwear': 1.0},
                'fall': ['sweater', 'jacket', 'bottom']
            }
        }

    def _define_style_themes(self):
        """Define style themes for outfit generation"""
        return {
            'casual_everyday': {
                'categories': ['top', 'bottom', 'footwear'],
                'formality': ['casual'],
                'description': 'Comfortable everyday wear'
            },
            'smart_casual': {
                'categories': ['top', 'bottom', 'outerwear', 'footwear'],
                'formality': ['business_casual', 'casual'],
                'description': 'Polished yet comfortable'
            },
            'athletic_performance': {
                'categories': ['top', 'bottom', 'footwear'],
                'formality': ['athletic'],
                'description': 'Activewear for performance'
            },
            'evening_out': {
                'categories': ['dress', 'outerwear', 'footwear', 'accessory'],
                'formality': ['formal', 'business_casual'],
                'description': 'Night out or special occasion'
            },
            'beach_vacation': {
                'categories': ['top', 'shorts', 'dress', 'footwear'],
                'formality': ['casual'],
                'description': 'Relaxed vacation wear'
            }
        }

    def find_similar_items(self, item_id, n=5, same_category=True, min_similarity=0.0):
        """Find similar items using embeddings"""
        if item_id not in self.item_embeddings_dict:
            return []

        target_embedding = self.item_embeddings_dict[item_id]
        similarities = []

        for other_id, other_embedding in self.item_embeddings_dict.items():
            if other_id == item_id:
                continue

            # Filter by category if requested
            if same_category:
                target_category = self.item_metadata[item_id]['garment_category']
                other_category = self.item_metadata[other_id]['garment_category']
                if target_category != other_category:
                    continue

            # Calculate cosine similarity
            cos_sim = np.dot(target_embedding, other_embedding) / (
                np.linalg.norm(target_embedding) * np.linalg.norm(other_embedding) + 1e-8
            )

            if cos_sim >= min_similarity:
                similarities.append({
                    'item_id': other_id,
                    'similarity': float(cos_sim),
                    'name': self.item_metadata[other_id]['name'],
                    'garment_type': self.item_metadata[other_id]['garment_type'],
                    'garment_category': self.item_metadata[other_id]['garment_category'],
                    'price': self.item_metadata[other_id]['price']
                })

        # Sort by similarity
        similarities.sort(key=lambda x: x['similarity'], reverse=True)
        return similarities[:n]

    def build_outfit(self, starting_item_id, user_measurements=None,
                    style_theme='casual_everyday', max_items=4,
                    max_price=None, require_size_fit=True):
        """Build a complete outfit"""
        if starting_item_id not in self.item_metadata:
            return None

        starting_item = self.item_metadata[starting_item_id]

        # Get style theme configuration
        theme_config = self.style_themes.get(style_theme, self.style_themes['casual_everyday'])

        # Initialize outfit
        outfit_items = [starting_item]
        total_price = starting_item['price']
        size_recommendations = {}

        # Get size recommendation for starting item
        if user_measurements and self.size_recommender and require_size_fit:
            size_rec = self._get_size_recommendation(starting_item, user_measurements)
            if size_rec:
                size_recommendations[starting_item_id] = size_rec

        # Find compatible items for other categories
        target_categories = theme_config['categories'].copy()
        target_categories.remove(starting_item['garment_category'])  # Remove starting category

        for category in target_categories[:max_items-1]:
            if len(outfit_items) >= max_items:
                break

            # Find compatible items in this category
            compatible_items = self._find_compatible_items(
                outfit_items,
                category,
                theme_config['formality'],
                max_price_per_item=max_price/len(target_categories) if max_price else None
            )

            if compatible_items:
                best_item = compatible_items[0]
                outfit_items.append(best_item)
                total_price += best_item['price']

                # Get size recommendation if needed
                if user_measurements and self.size_recommender and require_size_fit:
                    size_rec = self._get_size_recommendation(best_item, user_measurements)
                    if size_rec:
                        size_recommendations[best_item['id']] = size_rec

        # Calculate outfit metrics
        compatibility_score = self._calculate_outfit_compatibility(outfit_items)
        style_coherence = self._calculate_style_coherence(outfit_items, style_theme)

        return {
            'starting_item': starting_item,
            'outfit_items': outfit_items,
            'total_price': total_price,
            'item_count': len(outfit_items),
            'size_recommendations': size_recommendations,
            'compatibility_score': compatibility_score,
            'style_coherence': style_coherence,
            'style_theme': style_theme,
            'description': theme_config['description']
        }

    def _find_compatible_items(self, current_outfit, target_category,
                              allowed_formalities, max_price_per_item=None):
        """Find items compatible with current outfit"""
        compatible_items = []

        for item_id, metadata in self.item_metadata.items():
            # Skip if already in outfit
            if any(existing['id'] == item_id for existing in current_outfit):
                continue

            # Check category match
            if metadata['garment_category'] != target_category:
                continue

            # Check formality compatibility
            if metadata['formality'] not in allowed_formalities:
                continue

            # Check price limit
            if max_price_per_item and metadata['price'] > max_price_per_item:
                continue

            # Calculate compatibility with all items in current outfit
            item_compatibility = self._calculate_item_compatibility(metadata, current_outfit)

            if item_compatibility > 0:
                compatible_items.append((item_id, metadata, item_compatibility))

        # Sort by compatibility score
        compatible_items.sort(key=lambda x: x[2], reverse=True)

        # Return just the metadata
        return [item[1] for item in compatible_items[:5]]  # Top 5

    def _calculate_item_compatibility(self, new_item, existing_items):
        """Calculate compatibility score for a new item with existing outfit"""
        if not existing_items:
            return 0

        compatibility_score = 0

        for existing_item in existing_items:
            # Category compatibility
            if existing_item['garment_category'] in self.compatibility_rules['category_compatibility'].get(
                new_item['garment_category'], []
            ):
                compatibility_score += 30

            # Formality compatibility
            if new_item['formality'] in self.compatibility_rules['formality_compatibility'].get(
                existing_item['formality'], []
            ):
                compatibility_score += 20

            # Price harmony (items within 3x price range)
            price_ratio = max(new_item['price'], existing_item['price']) / (
                min(new_item['price'], existing_item['price']) + 1e-8
            )
            if price_ratio < 3:
                compatibility_score += 10

        return compatibility_score / len(existing_items)

    def _get_size_recommendation(self, item, user_measurements):
        """Get size recommendation for an item"""
        if not self.size_recommender or not user_measurements:
            return None

        try:
            garment_type = item['garment_type']
            if garment_type == 'unknown':
                return None

            # Use size recommender
            recommendations = self.size_recommender.find_best_fitting_items(
                user_measurements, garment_type, top_k=1
            )

            if recommendations:
                return recommendations[0]['recommended_size']
        except Exception as e:
            return None

        return None

    def _calculate_outfit_compatibility(self, outfit_items):
        """Calculate overall outfit compatibility score"""
        if len(outfit_items) < 2:
            return 0

        total_score = 0
        comparisons = 0

        for i in range(len(outfit_items)):
            for j in range(i + 1, len(outfit_items)):
                item1 = outfit_items[i]
                item2 = outfit_items[j]

                # Category compatibility
                if item1['garment_category'] in self.compatibility_rules['category_compatibility'].get(
                    item2['garment_category'], []
                ):
                    total_score += 30

                # Formality compatibility
                if item1['formality'] in self.compatibility_rules['formality_compatibility'].get(
                    item2['formality'], []
                ):
                    total_score += 20

                comparisons += 1

        if comparisons == 0:
            return 0

        return min(100, total_score / comparisons)

    def _calculate_style_coherence(self, outfit_items, style_theme):
        """Calculate how well the outfit matches the style theme"""
        theme_config = self.style_themes.get(style_theme, {})
        if not theme_config:
            return 0

        score = 0

        # Check category alignment
        theme_categories = set(theme_config.get('categories', []))
        outfit_categories = set(item['garment_category'] for item in outfit_items)
        category_overlap = len(theme_categories.intersection(outfit_categories))
        score += (category_overlap / len(theme_categories)) * 40

        # Check formality alignment
        theme_formalities = set(theme_config.get('formality', []))
        outfit_formalities = set(item['formality'] for item in outfit_items)
        formality_overlap = len(theme_formalities.intersection(outfit_formalities))
        score += (formality_overlap / len(theme_formalities)) * 40

        # Price consistency (within reasonable range)
        prices = [item['price'] for item in outfit_items if item['price'] > 0]
        if len(prices) > 1:
            price_range = max(prices) - min(prices)
            price_avg = np.mean(prices)
            if price_avg > 0:
                price_variation = price_range / price_avg
                if price_variation < 2:  # Prices within 2x range
                    score += 20

        return min(100, score)

    def generate_multiple_outfits(self, starting_item_id, user_measurements=None,
                                 n_outfits=3, max_price_per_outfit=None):
        """Generate multiple outfit options"""
        outfits = []
        style_themes = list(self.style_themes.keys())

        for i in range(min(n_outfits, len(style_themes))):
            theme = style_themes[i]
            outfit = self.build_outfit(
                starting_item_id,
                user_measurements,
                style_theme=theme,
                max_price=max_price_per_outfit
            )

            if outfit:
                outfits.append(outfit)

        # Sort by compatibility score
        outfits.sort(key=lambda x: x['compatibility_score'], reverse=True)
        return outfits

    def save_model(self, filepath):
        """Save the outfit builder model"""
        with open(filepath, 'wb') as f:
            pickle.dump(self, f)

    @staticmethod
    def load_model(filepath):
        """Load a saved outfit builder model"""
        try:
            with open(filepath, 'rb') as f:
                builder = pickle.load(f)
            return builder
        except Exception as e:
            return None

# Export classes
__all__ = ['SizeRecommenderV2', 'IntelligentOutfitBuilder']