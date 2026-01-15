import { useCallback, useEffect, useMemo, useState } from "react";
import WishlistButton from "../buttons/WishlistButton";
import styles from "./ItemCard.module.css";
import {
  getItemImage,
  getItemName,
  formatPrice,
  getItemId,
  inferGarmentType,
} from "../../utils/item";
import { getSizeRecommendation, syncUserProfile } from "../../services/aiClient";

export default function ItemCard({
  item,
  badgeContent,
  wishlisted = false,
  onWishlistToggle,
  onAddToCart,
  onClick,
  className = "",
  addToCartLabel = "Add to Cart",
  showAddToCart = true,
  showPrice = true,
}) {
  if (!item) {
    return null;
  }

  const [showBestFit, setShowBestFit] = useState(false);
  const [bestFitStatus, setBestFitStatus] = useState("idle");
  const [bestFitMessage, setBestFitMessage] = useState(badgeContent || "");
  const [bestFitError, setBestFitError] = useState("");
  const [showQuickAdd, setShowQuickAdd] = useState(false);
  const [selectedSize, setSelectedSize] = useState("");
  const [selectedColor, setSelectedColor] = useState("");

  const name = getItemName(item);
  const image = getItemImage(item);
  const priceLabel = showPrice ? formatPrice(item.price) : "";

  const articleClassName = [
    "product-card-modern",
    styles.card,
    className,
  ]
    .filter(Boolean)
    .join(" ");

  const handleCardClick = () => {
    if (typeof onClick === "function") {
      onClick(item);
    }
  };

  const handleWishlistToggle = () => {
    if (typeof onWishlistToggle === "function") {
      onWishlistToggle(item);
    }
  };

  const handleAddToCart = () => {
    if (typeof onAddToCart !== "function") {
      return;
    }
    setShowQuickAdd((prev) => !prev);
  };

  const resolvedItemId = getItemId(item);
  const garmentType = inferGarmentType(item);

  const normalizeFitScore = useCallback((value) => {
    const numeric = Number(value);
    if (!Number.isFinite(numeric)) {
      return null;
    }
    if (numeric <= 1 && numeric >= 0) {
      return Math.round(numeric * 100);
    }
    return Math.round(numeric);
  }, []);

  const formatBestFitCopy = useCallback(
    (payload) => {
      if (!payload) {
        return "We need more data.";
      }

      const recommendations = Array.isArray(payload.recommendations)
        ? payload.recommendations
        : [];

      const fallback = recommendations.find((entry) => entry?.recommended_size || entry?.size) || recommendations[0] || null;

      const recommendedSize =
        payload.recommended_size ||
        payload.recommendedSize ||
        payload.size ||
        fallback?.recommended_size ||
        fallback?.size ||
        null;

      const rawScore =
        payload.fit_score ??
        payload.fitScore ??
        payload.confidence ??
        fallback?.fit_score ??
        fallback?.confidence ??
        null;

      const match = normalizeFitScore(rawScore);

      if (recommendedSize && match !== null) {
        return `Best Fit: ${recommendedSize}`;
      }

      if (recommendedSize) {
        return `Best Fit: ${recommendedSize}`;
      }

      if (match !== null) {
        return `Best Fit Confidence`;
      }

      return "We need more data.";
    },
    [normalizeFitScore]
  );

  const fetchBestFit = useCallback(async () => {
    if (bestFitStatus === "loading" || bestFitStatus === "ready") {
      return;
    }

    const token = window.localStorage.getItem("auth_token");
    if (!token) {
      setBestFitStatus("error");
      setBestFitError("Sign in to see your best size.");
      return;
    }

    setBestFitStatus("loading");
    setBestFitError("");

    try {
      const { error: syncError } = await syncUserProfile();
      if (syncError) {
        setBestFitStatus("error");
        setBestFitError(syncError);
        return;
      }

      // Request a personalized fit recommendation from the AI service.
      const { data, error } = await getSizeRecommendation("me", {
        garmentType,
        itemId: resolvedItemId,
      });

      if (error) {
        setBestFitStatus("error");
        setBestFitError(error);
        return;
      }

      const message = formatBestFitCopy(data);
      setBestFitMessage(message);
      setBestFitStatus("ready");
    } catch (fetchError) {
      console.error("Failed to fetch best fit recommendation", fetchError);
      setBestFitStatus("error");
      setBestFitError("Unable to fetch your size right now.");
    }
  }, [bestFitStatus, formatBestFitCopy, garmentType, resolvedItemId]);

  const handleBestFitToggle = (event) => {
    event.stopPropagation();
    const nextState = !showBestFit;
    setShowBestFit(nextState);
    if (nextState && bestFitStatus === "idle") {
      fetchBestFit();
    }
  };

  const normalizeOptions = useCallback((value, fallback = []) => {
    if (Array.isArray(value)) return value.filter(Boolean);
    if (typeof value === "string") {
      return value
        .split(/[,|]/)
        .map((entry) => entry.trim())
        .filter(Boolean);
    }
    return fallback;
  }, []);

  const normalizeSizeStock = useCallback((target) => {
    const rawStock = target?.size_stock || target?.sizeStock || {};
    if (Array.isArray(rawStock)) return {};

    return Object.entries(rawStock).reduce((acc, [size, quantity]) => {
      acc[size] = Number(quantity) || 0;
      return acc;
    }, {});
  }, []);

  const sizeStock = useMemo(() => normalizeSizeStock(item), [item, normalizeSizeStock]);

  const sizes = useMemo(() => {
    const stockSizes = Object.keys(sizeStock);
    if (stockSizes.length > 0) return stockSizes;

    return normalizeOptions(
      item?.sizes || item?.available_sizes || item?.size_options,
      ["XS", "S", "M", "L", "XL"]
    );
  }, [item, normalizeOptions, sizeStock]);

  const colors = useMemo(() => {
    const variants =
      item?.color_variants || item?.available_colors || item?.color_options || [];

    if (Array.isArray(variants)) {
      const mapped = variants
        .map((variant) => variant?.name || variant?.color || variant)
        .filter(Boolean);
      if (mapped.length) return mapped;
    }

    if (typeof variants === "object" && variants !== null) {
      const mapped = Object.values(variants)
        .map((variant) => variant?.name || variant)
        .filter(Boolean);
      if (mapped.length) return mapped;
    }

    return ["Charcoal", "Sand", "Rose"];
  }, [item]);

  const handleQuickAddConfirm = () => {
    if (typeof onAddToCart !== "function") {
      return;
    }
    if (!selectedSize || !selectedColor) {
      return;
    }
    onAddToCart(item, { size: selectedSize, color: selectedColor });
    setShowQuickAdd(false);
    setSelectedSize("");
    setSelectedColor("");
  };

  useEffect(() => {
    setShowBestFit(false);
    setBestFitStatus("idle");
    setBestFitMessage(badgeContent || "");
    setBestFitError("");
    setShowQuickAdd(false);
    setSelectedSize("");
    setSelectedColor("");
  }, [item, badgeContent]);

  return (
    <article
      className={articleClassName}
      onClick={handleCardClick}
      style={{ cursor: "pointer" }}
      aria-label={`View details for ${name}`}
      tabIndex={0}
      onKeyDown={e => {
        if (e.key === "Enter" || e.key === " ") {
          handleCardClick();
        }
      }}
    >
      <div className={`product-image-container ${styles.imageWrapper}`}>
        {image ? (
          <img src={image} alt={name} loading="lazy" />
        ) : (
          <div className={`image-placeholder ${styles.placeholder}`}>
            {name.slice(0, 1)}
          </div>
        )}

        {typeof onWishlistToggle === "function" && (
          <WishlistButton active={wishlisted} onClick={handleWishlistToggle} />
        )}

        {badgeContent && (
          <button
            type="button"
            className={`best-fit-badge ${showBestFit ? "best-fit-badge--active" : ""}`.trim()}
            onClick={handleBestFitToggle}
            aria-pressed={showBestFit}
            aria-label={
              showBestFit
                ? "Hide best fit recommendation"
                : "Show best fit recommendation"
            }
          >
            {showBestFit
              ? bestFitStatus === "loading"
                ? "Finding your best size..."
                : bestFitStatus === "error"
                ? bestFitError || "We could not load your size."
                : bestFitMessage || "We need more data."
              : "Find my best size"}
          </button>
        )}
      </div>

      <div className={`product-info-modern ${styles.content}`}>
        <h3>{name}</h3>

        <div className={`product-footer ${styles.footer}`}>
          {showPrice && priceLabel && (
            <span className="price-modern">{priceLabel}</span>
          )}

          {showAddToCart && typeof onAddToCart === "function" && (
            <button
              type="button"
              className="add-to-cart-btn"
              onClick={(event) => {
                event.stopPropagation();
                handleAddToCart();
              }}
            >
              {addToCartLabel}
            </button>
          )}
        </div>

        {showQuickAdd && (
          <div
            className={styles.quickAddPanel}
            onClick={(event) => event.stopPropagation()}
            role="dialog"
            aria-label={`Choose size and color for ${name}`}
          >
            <div className={styles.quickAddHeader}>
              <div>
                <p className={styles.quickAddTitle}>Quick add</p>
                <p className={styles.quickAddSubtitle}>Pick a size and color.</p>
              </div>
              <button
                type="button"
                className={styles.quickAddClose}
                onClick={() => setShowQuickAdd(false)}
                aria-label="Close quick add"
              >
                x
              </button>
            </div>

            <div className={styles.quickAddGroup}>
              <span className={styles.quickAddLabel}>Size</span>
              <div className={styles.quickAddChips}>
                {sizes.map((size) => {
                  const isUnavailable = sizeStock[size] === 0;
                  return (
                    <button
                      key={size}
                      type="button"
                      className={`${styles.quickAddChip} ${
                        selectedSize === size ? styles.quickAddChipActive : ""
                      }`}
                      onClick={() => setSelectedSize(size)}
                      disabled={isUnavailable}
                    >
                      {size}
                    </button>
                  );
                })}
              </div>
            </div>

            <div className={styles.quickAddGroup}>
              <span className={styles.quickAddLabel}>Color</span>
              <div className={styles.quickAddChips}>
                {colors.map((color) => (
                  <button
                    key={color}
                    type="button"
                    className={`${styles.quickAddChip} ${
                      selectedColor === color ? styles.quickAddChipActive : ""
                    }`}
                    onClick={() => setSelectedColor(color)}
                  >
                    {color}
                  </button>
                ))}
              </div>
            </div>

            <div className={styles.quickAddActions}>
              <button
                type="button"
                className={styles.quickAddGhost}
                onClick={() => setShowQuickAdd(false)}
              >
                Keep browsing
              </button>
              <button
                type="button"
                className={styles.quickAddConfirm}
                onClick={handleQuickAddConfirm}
                disabled={!selectedSize || !selectedColor}
              >
                Add to cart
              </button>
            </div>
          </div>
        )}
      </div>
    </article>
  );
}
