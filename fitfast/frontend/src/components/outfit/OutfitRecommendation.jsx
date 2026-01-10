import { useState, useEffect, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import {
  buildOutfitRecommendation,
  syncUserProfile,
} from "../../services/aiClient";
import { addToCart } from "../../cartStorage";
import { getItemImage, getItemId } from "../../utils/item";

const OUTFIT_DISCOUNT_PERCENT = 15;

export default function OutfitRecommendation({ className = "" }) {
  const navigate = useNavigate();
  const [outfitData, setOutfitData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [feedback, setFeedback] = useState("");

  const outfitItems = useMemo(() => {
    if (!outfitData) return [];
    if (Array.isArray(outfitData.outfit_items)) return outfitData.outfit_items;
    if (Array.isArray(outfitData.items)) return outfitData.items;
    return [];
  }, [outfitData]);

  const outfitSizeMap = useMemo(() => {
    if (!outfitData) return {};
    return (
      outfitData.size_recommendations ||
      outfitData.sizeRecommendations ||
      {}
    );
  }, [outfitData]);

  const totalPrice = useMemo(() => {
    return outfitItems.reduce((sum, item) => {
      const price = Number(item.price) || 0;
      return sum + price;
    }, 0);
  }, [outfitItems]);

  const discountedPrice = useMemo(() => {
    return totalPrice * (1 - OUTFIT_DISCOUNT_PERCENT / 100);
  }, [totalPrice]);

  const savings = useMemo(() => {
    return totalPrice - discountedPrice;
  }, [totalPrice, discountedPrice]);

  const formatPrice = (price) => {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  };

  const handleLoadOutfit = async () => {
    const token = localStorage.getItem("auth_token");
    if (!token) {
      setError("Sign in to get personalized outfit recommendations.");
      return;
    }

    setLoading(true);
    setError("");

    const { error: syncError } = await syncUserProfile();
    if (syncError) {
      setError(syncError);
      setLoading(false);
      return;
    }

    const { data, error: outfitError } = await buildOutfitRecommendation("me", {
      startingItemId: null,
      style: null,
      maxItems: 4,
    });

    if (outfitError) {
      setError(outfitError);
      setOutfitData(null);
    } else {
      setOutfitData(data);
      setError("");
    }

    setLoading(false);
  };

  const handleAddAllToCart = () => {
    if (outfitItems.length === 0) return;

    outfitItems.forEach((item) => {
      const itemId = getItemId(item) || item.name;
      const recommendedSize = outfitSizeMap[itemId]?.size || null;

      addToCart({
        id: itemId,
        storeId: item.store_id || item.storeId,
        name: item.name || "Outfit item",
        price: item.price,
        image: getItemImage(item),
        size: recommendedSize,
        storeName: item.store_name || item.storeName,
        quantity: 1,
        bundleDiscount: OUTFIT_DISCOUNT_PERCENT,
      });
    });

    setFeedback(
      `Complete outfit added to cart! You saved ${formatPrice(savings)} (${OUTFIT_DISCOUNT_PERCENT}% off)`
    );
  };

  const handleItemClick = (item) => {
    const itemId = getItemId(item) || item.name;
    const storeId = item.store_id || item.storeId;

    if (storeId && itemId) {
      navigate(`/stores/${storeId}/product/${itemId}`);
    }
  };

  useEffect(() => {
    if (!feedback) return;
    const timeout = setTimeout(() => setFeedback(""), 4000);
    return () => clearTimeout(timeout);
  }, [feedback]);

  return (
    <div className={`outfit-recommendation ${className}`.trim()}>
      {feedback && <div className="outfit-feedback">{feedback}</div>}

      <div className="outfit-header">
        <div className="outfit-header-content">
          <h2 className="outfit-title">Style Your Perfect Outfit</h2>
          <p className="outfit-subtitle">
            Let AI curate a complete look tailored just for you
          </p>
        </div>
        <button
          type="button"
          className="outfit-generate-btn"
          onClick={handleLoadOutfit}
          disabled={loading}
        >
          {loading ? "Curating..." : outfitData ? "Refresh Outfit" : "Get My Outfit"}
        </button>
      </div>

      {error && (
        <div className="outfit-error">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
            />
          </svg>
          <p>{error}</p>
        </div>
      )}

      {!loading && !error && outfitItems.length === 0 && (
        <div className="outfit-empty">
          <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
            <path
              d="M16 6L18.29 8.29L13.41 13.17L9.41 9.17L2 16.59L3.41 18L9.41 12L13.41 16L19.71 9.71L22 12V6H16Z"
              fill="currentColor"
            />
          </svg>
          <h3>Discover Your Style</h3>
          <p>Click the button above to let AI create a personalized outfit for you</p>
        </div>
      )}

      {outfitItems.length > 0 && (
        <>
          <div className="outfit-grid">
            {outfitItems.map((item) => {
              const itemId = getItemId(item) || item.name;
              const recommendedSize = outfitSizeMap[itemId]?.size || null;

              return (
                <div
                  key={itemId}
                  className="outfit-card"
                  onClick={() => handleItemClick(item)}
                >
                  <div className="outfit-card-image">
                    {getItemImage(item) ? (
                      <img src={getItemImage(item)} alt={item.name || "Outfit item"} />
                    ) : (
                      <div className="outfit-card-placeholder">
                        {(item.name || "?").slice(0, 1)}
                      </div>
                    )}
                  </div>
                  <div className="outfit-card-info">
                    <h4 className="outfit-card-name">{item.name || "Curated piece"}</h4>
                    <p className="outfit-card-price">{formatPrice(item.price || 0)}</p>
                    {recommendedSize && (
                      <p className="outfit-card-size">Size: {recommendedSize}</p>
                    )}
                  </div>
                </div>
              );
            })}
          </div>

          <div className="outfit-summary">
            <div className="outfit-summary-details">
              <div className="outfit-summary-row">
                <span className="outfit-summary-label">Original Price:</span>
                <span className="outfit-summary-value outfit-summary-original">
                  {formatPrice(totalPrice)}
                </span>
              </div>
              <div className="outfit-summary-row">
                <span className="outfit-summary-label">Bundle Discount ({OUTFIT_DISCOUNT_PERCENT}%):</span>
                <span className="outfit-summary-value outfit-summary-discount">
                  -{formatPrice(savings)}
                </span>
              </div>
              <div className="outfit-summary-row outfit-summary-total-row">
                <span className="outfit-summary-label">Your Price:</span>
                <span className="outfit-summary-value outfit-summary-total">
                  {formatPrice(discountedPrice)}
                </span>
              </div>
              {outfitData?.compatibility_score !== undefined && (
                <div className="outfit-compatibility">
                  <span className="outfit-compatibility-label">Style Match:</span>
                  <span className="outfit-compatibility-value">
                    {Math.round(outfitData.compatibility_score)}%
                  </span>
                </div>
              )}
            </div>
            <button
              type="button"
              className="outfit-add-all-btn"
              onClick={handleAddAllToCart}
            >
              Add Complete Outfit to Cart
            </button>
          </div>
        </>
      )}
    </div>
  );
}
