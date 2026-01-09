import { useState, useEffect, useMemo } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "../api";
import {
  getWishlist,
  isItemWishlisted,
  toggleWishlistEntry,
} from "../wishlistStorage";
import { addToCart } from "../cartStorage";
import {
  buildOutfitRecommendation,
  getSizeRecommendation,
  syncUserProfile,
} from "../services/aiClient";

// Measurement metadata used to render the dynamic size guide.
const STANDARD_SIZE_PRIORITY = [
  "XXS",
  "XS",
  "S",
  "M",
  "L",
  "XL",
  "XXL",
  "XXXL",
  "3XL",
  "4XL",
  "5XL",
  "ONE SIZE",
  "OS",
  "STANDARD",
];

const MEASUREMENT_PRIORITY = [
  "chest_circumference",
  "waist_circumference",
  "hips_circumference",
  "garment_length",
  "dress_length",
  "shoulder_to_hem",
  "sleeve_length",
  "shoulder_width",
  "inseam_length",
  "short_length",
  "thigh_circumference",
  "leg_opening",
  "rise",
  "foot_length",
  "foot_width",
  "hood_height",
  "bicep_circumference",
  "collar_size",
  "head_circumference",
  "calf_circumference",
  "length",
  "width",
  "depth",
  "circumference",
  "chain_length",
  "bracelet_circumference",
];

const MEASUREMENT_LABELS = {
  chest_circumference: "Chest",
  waist_circumference: "Waist",
  hips_circumference: "Hips",
  garment_length: "Length",
  dress_length: "Dress Length",
  shoulder_to_hem: "Shoulder to Hem",
  sleeve_length: "Sleeve",
  shoulder_width: "Shoulder",
  inseam_length: "Inseam",
  short_length: "Short Length",
  thigh_circumference: "Thigh",
  leg_opening: "Leg Opening",
  rise: "Rise",
  foot_length: "Foot Length",
  foot_width: "Foot Width",
  hood_height: "Hood Height",
  bicep_circumference: "Bicep",
  collar_size: "Collar",
  head_circumference: "Head",
  calf_circumference: "Calf",
  length: "Length",
  width: "Width",
  depth: "Depth",
  circumference: "Circumference",
  chain_length: "Chain Length",
  bracelet_circumference: "Bracelet",
};

const MEASUREMENT_UNITS = {
  foot_width: "cm",
  foot_length: "cm",
  brim_width: "cm",
};

const MEASUREMENT_DIVISORS = {
  foot_width: 10,
  brim_width: 10,
};

const normalizeSizeValue = (value) => {
  if (value === null || value === undefined) {
    return "";
  }

  return value.toString().trim();
};

const getSizeOrderIndex = (size) => {
  const normalized = normalizeSizeValue(size);
  if (!normalized) {
    return Number.MAX_SAFE_INTEGER;
  }

  const upper = normalized.toUpperCase();
  const presetIndex = STANDARD_SIZE_PRIORITY.indexOf(upper);
  if (presetIndex !== -1) {
    return presetIndex;
  }

  const numericValue = Number(normalized);
  if (Number.isFinite(numericValue)) {
    return STANDARD_SIZE_PRIORITY.length + numericValue;
  }

  return STANDARD_SIZE_PRIORITY.length * 2 + upper.charCodeAt(0);
};

const sortSizesByPriority = (sizes) =>
  [...sizes].sort((a, b) => {
    const diff = getSizeOrderIndex(a) - getSizeOrderIndex(b);
    if (diff !== 0) {
      return diff;
    }
    return normalizeSizeValue(a).localeCompare(normalizeSizeValue(b));
  });

const getMeasurementOrderIndex = (key) => {
  const index = MEASUREMENT_PRIORITY.indexOf(key);
  if (index !== -1) {
    return index;
  }
  return MEASUREMENT_PRIORITY.length * 2 + key.charCodeAt(0);
};

const sortMeasurementsByPriority = (keys) =>
  [...keys].sort((a, b) => {
    const diff = getMeasurementOrderIndex(a) - getMeasurementOrderIndex(b);
    if (diff !== 0) {
      return diff;
    }
    return a.localeCompare(b);
  });

const isMeaningfulValue = (value) => {
  if (value === null || value === undefined) {
    return false;
  }

  if (typeof value === "string") {
    return value.trim().length > 0;
  }

  return true;
};

const parseSizingPayload = (raw) => {
  if (!raw) {
    return null;
  }

  if (typeof raw === "string") {
    try {
      return JSON.parse(raw);
    } catch (err) {
      console.warn("Failed to parse sizing_data string", err);
      return null;
    }
  }

  if (typeof raw === "object") {
    return raw;
  }

  return null;
};

const pickMeasurementMap = (payload) => {
  if (!payload || typeof payload !== "object") {
    return null;
  }

  const candidates = [
    payload.measurements_cm,
    payload.measurements,
    payload.metric,
    payload.chart,
  ];

  for (const candidate of candidates) {
    if (
      candidate &&
      typeof candidate === "object" &&
      !Array.isArray(candidate) &&
      Object.keys(candidate).length
    ) {
      return candidate;
    }
  }

  return null;
};

const formatMeasurementLabel = (key) => {
  if (MEASUREMENT_LABELS[key]) {
    return MEASUREMENT_LABELS[key];
  }

  return key
    .replace(/_/g, " ")
    .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

const formatMeasurementValue = (raw, key) => {
  if (!isMeaningfulValue(raw)) {
    return "—";
  }

  const numeric = Number(raw);
  if (Number.isFinite(numeric)) {
    const divisor = MEASUREMENT_DIVISORS[key] || 1;
    const adjusted = numeric / divisor;
    const decimals = adjusted % 1 === 0 ? 0 : 1;
    const unit = MEASUREMENT_UNITS[key] || "cm";
    const value = adjusted.toFixed(decimals);
    return `${value} ${unit}`.trim();
  }

  return String(raw).trim();
};

const buildSizeGuide = (item) => {
  if (!item) {
    return null;
  }

  const payload =
    item.sizing_data ||
    item.sizingData ||
    item.size_chart ||
    item.sizeChart ||
    null;

  const parsed = parseSizingPayload(payload);
  if (!parsed) {
    return null;
  }

  const measurementMap = pickMeasurementMap(parsed);
  if (!measurementMap) {
    return null;
  }

  const sizeKeys = sortSizesByPriority(Object.keys(measurementMap));
  if (!sizeKeys.length) {
    return null;
  }

  const measurementKeys = new Set();
  for (const size of sizeKeys) {
    const sizeData = measurementMap[size];
    if (!sizeData || typeof sizeData !== "object") {
      continue;
    }
    Object.entries(sizeData).forEach(([measurementKey, measurementValue]) => {
      if (isMeaningfulValue(measurementValue)) {
        measurementKeys.add(measurementKey);
      }
    });
  }

  if (!measurementKeys.size) {
    return null;
  }

  const sortedMeasurements = sortMeasurementsByPriority(Array.from(measurementKeys));

  const formattedValues = {};
  for (const size of sizeKeys) {
    formattedValues[size] = {};
    for (const measurementKey of sortedMeasurements) {
      const rawValue = measurementMap[size]?.[measurementKey];
      formattedValues[size][measurementKey] = formatMeasurementValue(
        rawValue,
        measurementKey
      );
    }
  }

  return {
    sizes: sizeKeys,
    measurements: sortedMeasurements,
    labels: sortedMeasurements.reduce((acc, key) => {
      acc[key] = formatMeasurementLabel(key);
      return acc;
    }, {}),
    values: formattedValues,
  };
};

export default function ProductDetailPage() {
  const { storeId, productId } = useParams();
  const navigate = useNavigate();
  const [store, setStore] = useState(null);
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedSize, setSelectedSize] = useState("");
  const [selectedColor, setSelectedColor] = useState("");
  const [cartFeedback, setCartFeedback] = useState("");
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [selectedQuantity, setSelectedQuantity] = useState(1);
  const [sizeRecommendation, setSizeRecommendation] = useState(null);
  const [sizeLoading, setSizeLoading] = useState(false);
  const [sizeError, setSizeError] = useState("");
  const [outfitSuggestion, setOutfitSuggestion] = useState(null);
  const [outfitLoading, setOutfitLoading] = useState(false);
  const [outfitError, setOutfitError] = useState("");
  const sizeSummary = useMemo(() => {
    if (!sizeRecommendation) return null;

    const primary =
      sizeRecommendation.recommended_size ||
      sizeRecommendation.recommendedSize ||
      sizeRecommendation.size;

    const recommendationList = Array.isArray(sizeRecommendation.recommendations)
      ? sizeRecommendation.recommendations
      : [];

    const fallbackEntry = recommendationList.length ? recommendationList[0] : null;

    const pickedSize = primary || fallbackEntry?.recommended_size || fallbackEntry?.size || null;
    const fitScore =
      sizeRecommendation.fit_score ??
      fallbackEntry?.fit_score ??
      sizeRecommendation.confidence ??
      null;

    return {
      size: pickedSize,
      fitScore,
      method: sizeRecommendation.method || fallbackEntry?.method || null,
    };
  }, [sizeRecommendation]);

  const outfitItems = useMemo(() => {
    if (!outfitSuggestion) return [];
    if (Array.isArray(outfitSuggestion.outfit_items)) return outfitSuggestion.outfit_items;
    if (Array.isArray(outfitSuggestion.items)) return outfitSuggestion.items;
    return [];
  }, [outfitSuggestion]);

  const outfitSizeMap = useMemo(() => {
    if (!outfitSuggestion) return {};
    const map = outfitSuggestion.size_recommendations || outfitSuggestion.sizeRecommendations || {};
    return map;
  }, [outfitSuggestion]);

  const sizeGuide = useMemo(() => buildSizeGuide(product), [product]);

  const normalizeOptions = (value, fallback = []) => {
    if (Array.isArray(value)) return value.filter(Boolean);
    if (typeof value === "string") {
      return value
        .split(/[,|]/)
        .map((entry) => entry.trim())
        .filter(Boolean);
    }
    return fallback;
  };

  const normalizeSizeStock = (item) => {
    const rawStock = item?.size_stock || item?.sizeStock || {};

    if (Array.isArray(rawStock)) return {};

    return Object.entries(rawStock).reduce((acc, [size, quantity]) => {
      acc[size] = Number(quantity) || 0;
      return acc;
    }, {});
  };

  const getTotalStock = (item) => {
    const sizeStock = normalizeSizeStock(item);
    const sizeTotal = Object.values(sizeStock).reduce((sum, qty) => sum + qty, 0);

    if (sizeTotal > 0) return sizeTotal;
    if (item?.stock_quantity !== undefined) return Number(item.stock_quantity) || 0;

    return 0;
  };

  const getSizeStock = (item, size) => normalizeSizeStock(item)[size] || 0;

  const getSizes = (item) => {
    const sizeStock = normalizeSizeStock(item);
    const stockSizes = Object.keys(sizeStock);

    if (stockSizes.length > 0) return stockSizes;

    return normalizeOptions(
      item?.sizes || item?.available_sizes || item?.size_options,
      ["XS", "S", "M", "L", "XL"]
    );
  };

  const getColors = (item) => {
    const variants =
      item?.color_variants || item?.available_colors || item?.color_options || [];

    if (Array.isArray(variants)) {
      return variants
        .map((variant) => variant?.name || variant?.color || variant)
        .filter(Boolean);
    }

    if (typeof variants === "object" && variants !== null) {
      return Object.values(variants)
        .map((variant) => variant?.name || variant)
        .filter(Boolean);
    }

    return ["Charcoal", "Sand", "Rose"];
  };

  const getAvailableQuantity = () => {
    if (!product) return 0;

    const sizeQuantity = selectedSize ? getSizeStock(product, selectedSize) : 0;
    const totalStock = getTotalStock(product);

    if (sizeQuantity > 0) return sizeQuantity;
    return totalStock;
  };

  const normalizeGarmentTypeValue = (value) => {
    if (!value && value !== 0) return "";
    if (typeof value === "string") return value.trim();
    if (typeof value === "number") return String(value);
    if (typeof value === "object") {
      const candidate =
        value.garment_type ||
        value.garmentType ||
        value.garmentTypeKey ||
        value.key ||
        value.slug ||
        value.name ||
        value.label ||
        value.title;
      if (candidate) {
        return normalizeGarmentTypeValue(candidate);
      }
    }
    return "";
  };

  const normalizeGarmentKey = (raw) => {
    if (!raw) return "";
    return raw
      .toString()
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, "_")
      .replace(/^_+|_+$/g, "");
  };

  const normalizeStyleValue = (value) => {
    if (!value && value !== 0) return "";
    if (typeof value === "string") return value.trim();
    if (typeof value === "number") return String(value);
    if (Array.isArray(value)) {
      return value
        .map((entry) => normalizeStyleValue(entry))
        .filter(Boolean)
        .join(" ");
    }
    if (typeof value === "object") {
      const candidate =
        value.style ||
        value.name ||
        value.label ||
        value.title ||
        value.description ||
        value.slug;
      if (candidate) {
        return normalizeStyleValue(candidate);
      }
    }
    return "";
  };

  const resolvedItemId = useMemo(() => {
    if (!product) return productId;
    return (
      product.id ??
      product.item_id ??
      product.productId ??
      product.slug ??
      product.name ??
      productId
    );
  }, [product, productId]);

  const inferGarmentType = useMemo(() => {
    if (!product) return "t_shirt";

    const explicit = normalizeGarmentTypeValue(
      product.garment_type ||
        product.garmentType ||
        product.garmentTypeKey ||
        product.garmentCategory
    );
    if (explicit) return normalizeGarmentKey(explicit) || "t_shirt";

    const rawCategory = (() => {
      const category = product.category || product.category_name || product.category_slug;
      if (typeof category === "string") return category;
      if (category && typeof category === "object") {
        return category.slug || category.name || "";
      }
      return "";
    })()
      .toString()
      .toLowerCase();

    if (rawCategory.includes("dress")) return "a_line_dress";
    if (rawCategory.includes("jean")) return "regular_jeans";
    if (rawCategory.includes("pant")) return "regular_pants";
    if (rawCategory.includes("skirt")) return "a_line_skirt";
    if (rawCategory.includes("coat") || rawCategory.includes("jacket")) return "bomber_jacket";
    if (rawCategory.includes("hoodie")) return "pullover_hoodie";
    if (rawCategory.includes("sweater")) return "crewneck_sweater";
    if (rawCategory.includes("short")) return "casual_shorts";

    return "t_shirt";
  }, [product]);

  const resolvedStyle = useMemo(() => {
    if (!product) return "";

    const directStyle = normalizeStyleValue(
      product.style ||
        product.style_name ||
        product.styleName ||
        product.styleLabel ||
        product.occasion ||
        product.occasion_name ||
        product.occasionName
    );
    if (directStyle) return directStyle;

    const fallback = normalizeStyleValue(product.category_style || product.category);
    if (fallback) return fallback;

    return "";
  }, [product]);


  useEffect(() => {
    async function fetchData() {
      setLoading(true);
      setError("");

      try {
        const res = await api.get(`/stores/${storeId}`);
        const storeData = res.data.data || res.data;
        setStore(storeData);

        const normalizedProductId = productId?.toString();
        const matchesProduct = (item) => {
          const candidate =
             item.id || item.productId || item.product_id || item.slug || item.name;

          return candidate?.toString() === normalizedProductId;
        };

        const categories = storeData.categories || [];
        const categoryProduct = categories
          .flatMap((category) => category.items || [])
          .find(matchesProduct);

        const fallbackProduct = (storeData.items || []).find(matchesProduct);
        const foundProduct = categoryProduct || fallbackProduct;

        if (foundProduct) {
          setProduct(foundProduct);
          
          // Set default selections
          const sizes = getSizes(foundProduct);
          const colors = getColors(foundProduct);
          const sizeStock = normalizeSizeStock(foundProduct);

          const firstAvailableSize =
            sizes.find((size) => (sizeStock[size] ?? 0) > 0) ||
            sizes[0] ||
            "Standard";

          setSelectedSize(firstAvailableSize);
          setSelectedColor(colors[0] || "Default");
          setSelectedQuantity(1);
        } else {
          setError("Product not found");
        }
      } catch (err) {
        console.error(err);
        setError("Failed to load product details. Please try again.");
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [storeId, productId]);

  useEffect(() => {
    if (!product) return;
    const wishlist = getWishlist();
    setIsWishlisted(isItemWishlisted(wishlist, productId, storeId));
  }, [product, productId, storeId]);

  useEffect(() => {
    setSizeRecommendation(null);
    setSizeError("");
    setOutfitSuggestion(null);
    setOutfitError("");
  }, [resolvedItemId]);


  useEffect(() => {
    if (!cartFeedback) return;
    const timeout = setTimeout(() => setCartFeedback(""), 3200);
    return () => clearTimeout(timeout);
  }, [cartFeedback]);

 useEffect(() => {
    if (!product) return;
    const available = getAvailableQuantity();
    setSelectedQuantity((qty) => {
      if (available <= 0) return Math.max(1, qty);
      return Math.max(1, Math.min(qty, available));
    });
  }, [product, selectedSize]); 
 
  const getItemImage = (item) =>
    item?.image_url ||
    item?.image ||
    item?.imagePath ||
    item?.image_path ||
    item?.primary_image_url ||
    item?.primary_image?.image_path;

  const formatPrice = (price) => {
    if (!price && price !== 0) return "";
    const amount = Number(price);
    if (Number.isNaN(amount)) return price;
    return `$${amount.toFixed(2)}`;
  };
  

  const buildFeatureList = (sizes, colors) => {
    const baseFeatures = [
      "Breathable, all-day comfort fabric",
      "Tailored silhouette with clean finishing",
      "Machine-washable and travel-friendly",
    ];

    const hasColorRange = colors.length > 0;
    const hasSizeRange = sizes.length > 1;

    if (hasColorRange) {
      baseFeatures.push(`Available in ${colors.join(", ")}`);
    }

    if (hasSizeRange) {
      baseFeatures.push(
        `Inclusive sizing from ${sizes[0]}${
          sizes.length > 1 ? ` to ${sizes[sizes.length - 1]}` : ""
        }`
      );
    }

    return baseFeatures;
  };

  const handleAddToCart = () => {
    const maxQuantity = getAvailableQuantity();

    if (maxQuantity <= 0) {
      setCartFeedback("This item is currently out of stock.");
      return;
    }

    if (selectedQuantity > maxQuantity) {
      setSelectedQuantity(maxQuantity);
      setCartFeedback(
        `Only ${maxQuantity} available${
          selectedSize ? ` for size ${selectedSize}` : ""
        }`
      );
      return;
    }
    addToCart({
      id: productId,
      storeId,
      name: product.name,
      price: product.price,
      image: getItemImage(product),
      size: selectedSize,
      color: selectedColor,
      storeName: store?.name,
      quantity: selectedQuantity,
    });
    setCartFeedback(
      `${product.name} added to cart (${selectedColor}${
        selectedSize ? ` / ${selectedSize}` : ""

      }) x${selectedQuantity}`

  );
  };

  const handleToggleWishlist = () => {
    if (!product) return;

    const { added } = toggleWishlistEntry({
      id: productId,
      storeId,
      name: product.name,
      price: product.price,
      image: getItemImage(product),
      storeName: store?.name,
    });

    setIsWishlisted(added);
    setCartFeedback(added ? "Added to wishlist" : "Removed from wishlist");
  };

  const handleSizeAssist = async () => {
    if (!product) return;

    const token = localStorage.getItem("auth_token");
    if (!token) {
      setSizeError("Sign in to get a personalized fit recommendation.");
      return;
    }

    setSizeLoading(true);
    setSizeError("");

    const { error: syncError } = await syncUserProfile();
    if (syncError) {
      setSizeError(syncError);
      setSizeLoading(false);
      return;
    }

    const { data, error } = await getSizeRecommendation("me", {
      garmentType: inferGarmentType,
      itemId: resolvedItemId,
    });

    if (error) {
      setSizeError(error);
      setSizeRecommendation(null);
    } else {
      setSizeRecommendation(data);
    }

    setSizeLoading(false);
  };

  const handleOutfitAssist = async () => {
    if (!product) return;

    const token = localStorage.getItem("auth_token");
    if (!token) {
      setOutfitError("Sign in to let FitFast curate a full look for you.");
      return;
    }

    setOutfitLoading(true);
    setOutfitError("");

    const { error: syncError } = await syncUserProfile();
    if (syncError) {
      setOutfitError(syncError);
      setOutfitLoading(false);
      return;
    }

    const { data, error } = await buildOutfitRecommendation("me", {
      startingItemId: resolvedItemId,
      style: null,
      maxItems: 4,
    });

    if (error) {
      setOutfitError(error);
      setOutfitSuggestion(null);
    } else {
      setOutfitSuggestion(data);
    }

    setOutfitLoading(false);
  };

  if (loading) {
    return <div className="product-detail-page">Loading product...</div>;
  }

  if (error || !product) {
    return (
      <div className="product-detail-page">
        <div className="error-container">
          <p>{error || "Product not found"}</p>
          <button onClick={() => navigate(`/stores/${storeId}`)} className="back-btn">
            Back to Store
          </button>
        </div>
      </div>
    );
  }

  const sizes = getSizes(product);
  const colors = getColors(product);
  const features = buildFeatureList(sizes, colors);
  const availableQuantity = getAvailableQuantity();
  const isOutOfStock = availableQuantity <= 0;
  const maxQuantity = Math.max(availableQuantity || 0, 1);
  const fabric =
    product.fabric || product.material || product.materials || "Premium cotton blend";
  const care =
    product.care_instructions ||
    product.care ||
    "Machine wash cold with like colors. Tumble dry low."
  const deliveryNote =
    product.shipping_note ||
    "Free standard delivery over $75. Easy 30-day returns.";
  const fitNote =
    product.fit ||
    "True to size with a relaxed drape. Size down for a closer fit.";
  const hasDynamicSizeGuide = Boolean(
    sizeGuide &&
      Array.isArray(sizeGuide.sizes) &&
      sizeGuide.sizes.length &&
      Array.isArray(sizeGuide.measurements) &&
      sizeGuide.measurements.length
  );

  return (
    <div className="product-detail-page">
      {cartFeedback && <div className="cart-feedback">{cartFeedback}</div>}

      <button onClick={() => navigate(`/stores/${storeId}`)} className="back-link">
        ← Back to {store?.name || "Store"}
      </button>

      <div className="product-detail-container">
        <div className="product-detail-image">
          {getItemImage(product) ? (
            <img
              src={getItemImage(product)}
              alt={product.name || "Product"}
            />
          ) : (
            <div className="image-placeholder-large">
              {product.name?.slice(0, 1) || "P"}
            </div>
          )}
        </div>

        <div className="product-detail-info">
          <div className="product-detail-header">
            <div>
              <div className="best-fit-badge-large">Best Fit: Medium - 90% Match!</div>
              <h1>{product.name}</h1>
              {product.price && (
                <p className="price-large">{formatPrice(product.price)}</p>
              )}
            </div>
            
            <button
              onClick={handleToggleWishlist}
              aria-label="Add to wishlist"
              style={{
                width: '44px',
                height: '44px',
                borderRadius: '50%',
                border: 'none',
                background: 'transparent',
                color: '#942341',
                display: 'grid',
                placeItems: 'center',
                transition: 'all 0.2s ease',
                cursor: 'pointer',
                padding: 0,
                flexShrink: 0,
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.transform = 'scale(1.1)';
                e.currentTarget.style.background = 'rgba(233, 30, 99, 0.08)';
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.transform = 'scale(1)';
                e.currentTarget.style.background = 'transparent';
              }}
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill={isWishlisted ? "currentColor" : "#ffffff"}
                stroke="currentColor"
                strokeWidth="1.8"
                xmlns="http://www.w3.org/2000/svg"
                style={{
                  display: 'block',
                  filter: 'drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1))'
                }}
              >
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
              </svg>
            </button>
          </div>

          {product.description && (
            <p className="product-description">{product.description}</p>
          )}

          <div className="product-meta-grid">
            <div>
              <p className="meta-label">Fabric</p>
              <p className="meta-value">{fabric}</p>
            </div>
            <div>
              <p className="meta-label">Fit</p>
              <p className="meta-value">{fitNote}</p>
            </div>
            <div>
              <p className="meta-label">Care</p>
              <p className="meta-value">{care}</p>
            </div>
            <div>
              <p className="meta-label">Delivery</p>
              <p className="meta-value">{deliveryNote}</p>
            </div>
          </div>

          <div className="product-options">
            <div className="option-section">
              <div className="option-header">
                <span className="option-label">Color</span>
              </div>
              <div className="color-options">
                {colors.map((color) => (
                  <button
                    key={color}
                    className={`color-option ${
                      selectedColor === color ? "selected" : ""
                    }`}
                    onClick={() => setSelectedColor(color)}
                    title={color}
                  >
                    {color}
                  </button>
                ))}
              </div>
            </div>

            <div className="option-section">
              <div className="option-header">
                <span className="option-label">Quantity</span>
              </div>
              <div className="quantity-control">
                <button
                  className="quantity-btn"
                  onClick={() =>
                    setSelectedQuantity((qty) => Math.max(1, qty - 1))
                  }
                  aria-label="Decrease quantity"
                  disabled={selectedQuantity <= 1 || isOutOfStock}
                >
                  −
                </button>
                <span className="quantity-value">{selectedQuantity}</span>
                <button
                  className="quantity-btn"
                  onClick={() =>
                    setSelectedQuantity((qty) => Math.min(maxQuantity, qty + 1))
                  }
                  aria-label="Increase quantity"
                  disabled={
                    isOutOfStock ||
                    (availableQuantity > 0 && selectedQuantity >= availableQuantity)
                  }
                >
                  +
                </button>
              </div>
             
            </div>

            <div className="option-section">
              <div className="option-header">
                <span className="option-label">Size</span>
              </div>
              <div className="size-options">
                {sizes.map((size) => {
                  const sizeQuantity = getSizeStock(product, size);
                  const isSizeUnavailable = sizeQuantity <= 0;

                  return (
                    <button
                      key={size}
                      className={`size-option ${
                        selectedSize === size ? "selected" : ""
                      } ${isSizeUnavailable ? "disabled" : ""}`}
                      onClick={() => setSelectedSize(size)}
                      disabled={isSizeUnavailable}
                    >
                      {size}
                    </button>
                  );
                })}
              </div>
            </div>
          </div>

          <div className="ai-assist">
            <div className="ai-action">
              <button
                type="button"
                className="ai-button"
                onClick={handleSizeAssist}
                disabled={sizeLoading}
              >
                {sizeLoading ? "Finding your size..." : "Find my best size"}
              </button>
              {sizeError && <p className="ai-message error">{sizeError}</p>}
              {sizeSummary && (
                <div className="ai-result">
                  <p className="ai-result-title">Recommended size</p>
                  <p className="ai-result-value">{sizeSummary.size || "We need more data."}</p>
                  <p className="ai-result-meta">
                    {sizeSummary.method ? `Method · ${sizeSummary.method}` : "AI confidence"}
                    {typeof sizeSummary.fitScore === "number"
                      ? ` · ${(sizeSummary.fitScore * 100).toFixed(0)}%`
                      : ""}
                  </p>
                </div>
              )}
            </div>

            <div className="ai-action">
              <button
                type="button"
                className="ai-button secondary"
                onClick={handleOutfitAssist}
                disabled={outfitLoading}
              >
                {outfitLoading ? "Curating outfit..." : "Style this outfit"}
              </button>
              {outfitError && <p className="ai-message error">{outfitError}</p>}
            </div>

            {outfitItems.length > 0 && (
              <div className="ai-outfit">
                <div className="ai-result-heading">
                  <h3>Suggested outfit</h3>
                  {outfitSuggestion?.compatibility_score !== undefined && (
                    <span className="ai-chip">
                      {Math.round(outfitSuggestion.compatibility_score)}% match
                    </span>
                  )}
                </div>
                <div className="ai-outfit-grid">
                  {outfitItems.map((item) => {
                    const mapKey = item.id || item.item_id || item.slug || item.name;
                    const recommended = outfitSizeMap?.[mapKey] || {};

                    return (
                      <div key={mapKey} className="ai-outfit-card">
                        {item.image_url || item.image ? (
                          <img
                            src={item.image_url || item.image}
                            alt={item.name || "Outfit item"}
                          />
                        ) : (
                          <div className="ai-outfit-placeholder">
                            {(item.name || "?").slice(0, 1)}
                          </div>
                        )}
                        <div className="ai-outfit-info">
                          <p className="ai-outfit-name">{item.name || "Curated piece"}</p>
                          <p className="ai-outfit-meta">
                            {recommended.size ? `Suggested size ${recommended.size}` : "Best available size"}
                          </p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}
          </div>

          <div className="product-actions-detail">
            <button
              type="button"
              className="add-to-cart-btn-large"
              onClick={handleAddToCart}
              disabled={isOutOfStock}
            >
               {isOutOfStock ? "Out of Stock" : "Add to Cart"}
            </button>
          </div>
          <div className="detail-sections">
            <div className="detail-card">
              <h3>Highlights</h3>
              <ul className="feature-list">
                {features.map((feature) => (
                  <li key={feature}>{feature}</li>
                ))}
              </ul>
            </div>

            <div className="detail-card">
              <h3>Size & Fit</h3>
              <div className="fit-grid">
                <div>
                  <p className="meta-label">Model details</p>
                  <p className="meta-value">5'9" · Wearing size M</p>
                </div>
                <div>
                  <p className="meta-label">Fit notes</p>
                  <p className="meta-value">{fitNote}</p>
                </div>
              </div>

              <div className="size-guide">
                {hasDynamicSizeGuide ? (
                  <>
                    <div
                      className="size-guide-row header"
                      style={{
                        gridTemplateColumns: `repeat(${sizeGuide.measurements.length + 1}, minmax(0, 1fr))`,
                      }}
                    >
                      <span>Size</span>
                      {sizeGuide.measurements.map((measurementKey) => (
                        <span key={measurementKey}>
                          {sizeGuide.labels[measurementKey]}
                        </span>
                      ))}
                    </div>
                    {sizeGuide.sizes.map((size) => (
                      <div
                        className="size-guide-row"
                        key={size}
                        style={{
                          gridTemplateColumns: `repeat(${sizeGuide.measurements.length + 1}, minmax(0, 1fr))`,
                        }}
                      >
                        <span>{size}</span>
                        {sizeGuide.measurements.map((measurementKey) => (
                          <span key={measurementKey}>
                            {sizeGuide.values[size]?.[measurementKey] || "—"}
                          </span>
                        ))}
                      </div>
                    ))}
                  </>
                ) : (
                  <>
                    <div className="size-guide-row header">
                      <span>Size</span>
                    </div>
                    {sizes.map((size) => (
                      <div className="size-guide-row" key={size}>
                        <span>{size}</span>
                      </div>
                    ))}
                    <p className="muted small">
                      Detailed measurements are not available for this item yet.
                    </p>
                  </>
                )}
              </div>
            </div>

            <div className="detail-card">
              <h3>Delivery & Returns</h3>
              <p className="meta-value">{deliveryNote}</p>
              <p className="muted small">Express delivery available at checkout.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
