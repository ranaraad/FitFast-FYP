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

const pickFirstMeaningfulText = (...candidates) => {
  for (const candidate of candidates) {
    if (candidate === null || candidate === undefined) {
      continue;
    }

    if (Array.isArray(candidate)) {
      const joined = candidate.filter(Boolean).join(", ");
      if (joined.trim()) {
        return joined.trim();
      }
      continue;
    }

    if (typeof candidate === "object") {
      const potential =
        candidate.label ||
        candidate.name ||
        candidate.title ||
        candidate.description ||
        candidate.value ||
        candidate.text;
      if (typeof potential === "string" && potential.trim()) {
        return potential.trim();
      }
      if (typeof potential === "number" && Number.isFinite(potential)) {
        return String(potential);
      }
      continue;
    }

    if (typeof candidate === "number") {
      if (Number.isFinite(candidate)) {
        return String(candidate);
      }
      continue;
    }

    if (typeof candidate === "string" && candidate.trim()) {
      return candidate.trim();
    }
  }

  return "";
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

// Helper function to format confidence score
const formatFitScore = (score) => {
  if (score === null || score === undefined) return '';
  if (typeof score === 'number') {
    if (score >= 1) return '100%';
    return `${Math.round(score * 100)}%`;
  }
  if (typeof score === 'string') {
    const num = parseFloat(score);
    if (!isNaN(num)) {
      if (num >= 1) return '100%';
      return `${Math.round(num * 100)}%`;
    }
    return score;
  }
  return '';
};

// Helper function to determine if response is using fallback
const isFallbackResponse = (response) => {
  if (!response) return false;

  // Check if it's a fallback from backend
  if (response.is_fallback === true) return true;

  // Check for fallback indicators in data structure
  if (response.data?.is_fallback === true) return true;
  if (response.data?.method?.includes('fallback') || response.data?.method?.includes('size_chart')) return true;

  return false;
};

// Helper function to get AI source information
const getAISourceInfo = (response) => {
  if (!response) return { source: 'unknown', isRealAI: false, modelUsed: null };

  const data = response.data || response;

  // Check if real AI was used
  const isRealAI = data.model_used &&
                   !data.model_used.includes('fallback') &&
                   !data.is_fallback &&
                   data.model_used.includes('.pkl');

  const source = isRealAI ? 'ai_system' : 'fallback';
  const modelUsed = data.model_used || data.method || null;

  return { source, isRealAI, modelUsed };
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

  // Debug logging
  useEffect(() => {
    if (sizeRecommendation) {
      console.log('📏 Size Recommendation Data:', {
        raw: sizeRecommendation,
        isFallback: isFallbackResponse(sizeRecommendation),
        sourceInfo: getAISourceInfo(sizeRecommendation),
        summary: sizeSummary
      });
    }
  }, [sizeRecommendation]);

  useEffect(() => {
    if (outfitSuggestion) {
      console.log('👗 Outfit Suggestion Data:', {
        raw: outfitSuggestion,
        isFallback: isFallbackResponse(outfitSuggestion),
        sourceInfo: getAISourceInfo(outfitSuggestion),
        items: outfitItems
      });
    }
  }, [outfitSuggestion]);

  const sizeSummary = useMemo(() => {
    if (!sizeRecommendation) return null;

    // Get data from response
    const data = sizeRecommendation.data || sizeRecommendation;

    // Check if it's a fallback
    const isFallback = isFallbackResponse(sizeRecommendation);
    const sourceInfo = getAISourceInfo(sizeRecommendation);

    // Extract size recommendation
    let recommendedSize = null;
    let fitScore = null;
    let method = null;
    let recommendations = [];

    if (data.recommendations && Array.isArray(data.recommendations) && data.recommendations.length > 0) {
      // Real AI response with recommendations array
      recommendations = data.recommendations;
      const firstRec = recommendations[0];
      recommendedSize = firstRec.recommended_size || firstRec.size;
      fitScore = firstRec.overall_fit_score || firstRec.fit_score;
      method = data.model_used || 'AI Recommendation';
    } else if (data.recommended_size || data.size) {
      // Direct size recommendation
      recommendedSize = data.recommended_size || data.size;
      fitScore = data.fit_score || data.confidence;
      method = data.method || sourceInfo.modelUsed || (isFallback ? 'Size Chart' : 'AI Analysis');
    } else if (isFallback) {
      // Fallback response
      recommendedSize = data.recommended_size || data.size || 'M';
      fitScore = data.confidence || 0.7;
      method = 'Advanced Size Chart';
    }

    return {
      size: recommendedSize,
      fitScore,
      method,
      isFallback,
      sourceInfo,
      recommendations,
      rawData: data
    };
  }, [sizeRecommendation]);

  const outfitItems = useMemo(() => {
    if (!outfitSuggestion) return [];

    const data = outfitSuggestion.data || outfitSuggestion;

    if (data.outfit?.outfit_items && Array.isArray(data.outfit.outfit_items)) {
      return data.outfit.outfit_items;
    }
    if (data.outfit_items && Array.isArray(data.outfit_items)) {
      return data.outfit_items;
    }
    if (data.items && Array.isArray(data.items)) {
      return data.items;
    }
    if (data.outfit?.items && Array.isArray(data.outfit.items)) {
      return data.outfit.items;
    }

    return [];
  }, [outfitSuggestion]);

  const outfitSizeMap = useMemo(() => {
    if (!outfitSuggestion) return {};

    const data = outfitSuggestion.data || outfitSuggestion;
    const outfit = data.outfit || data;

    return outfit.size_recommendations || outfit.sizeRecommendations || {};
  }, [outfitSuggestion]);

  const outfitSourceInfo = useMemo(() => {
    return getAISourceInfo(outfitSuggestion);
  }, [outfitSuggestion]);

  const outfitTotalPrice = useMemo(() => {
    if (!outfitItems.length) return 0;
    
    // Calculate total from outfit items
    const itemsTotal = outfitItems.reduce((sum, item) => {
      const price = parseFloat(item.price || 0);
      return sum + price;
    }, 0);
    
    // Use API total if available, otherwise use calculated total
    return outfitSuggestion?.data?.outfit?.total_price || itemsTotal;
  }, [outfitItems, outfitSuggestion]);

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

  const GARMENT_DETAIL_PROFILES = [
    {
      types: [
        "t_shirt",
        "v_neck_tee",
        "henley_shirt",
        "polo_shirt",
        "fitted_shirt",
        "dress_shirt",
        "crewneck_sweater",
        "v_neck_sweater",
        "cardigan",
        "turtleneck",
      ],
      fabric: "Soft combed knit with breathable stretch for everyday wear.",
      fit: "Regular cut through the chest and shoulders; size up for an oversized drape.",
      care: "Machine wash cold inside out; tumble dry low or lay flat to preserve shape.",
    },
    {
      types: [
        "pullover_hoodie",
        "zip_hoodie",
        "bomber_jacket",
        "denim_jacket",
        "trench_coat",
        "wool_coat",
        "windbreaker",
        "puffer_jacket",
      ],
      fabric: "Durable woven shell with smooth lining designed for easy layering.",
      fit: "Structured regular fit built to layer over light knits; consider sizing up for bulkier sweaters.",
      care: "Spot clean as needed; dry clean or professional clean to preserve the finish.",
    },
    {
      types: [
        "slim_pants",
        "regular_pants",
        "cargo_pants",
        "slim_jeans",
        "regular_jeans",
        "leggings",
        "yoga_pants",
      ],
      fabric: "Stretch-infused twill that holds its shape while moving with you.",
      fit: "Sits at the natural waist with a tailored leg; size up for a more relaxed silhouette.",
      care: "Machine wash cold inside out; hang dry to preserve color and fabric recovery.",
    },
    {
      types: [
        "casual_shorts",
        "cargo_shorts",
        "training_shorts",
        "shorts",
      ],
      fabric: "Lightweight woven blend with breathable stretch and quick-dry comfort.",
      fit: "Athletic rise with room through the thigh; stays easy through the hip.",
      care: "Machine wash cold; tumble dry low or hang dry to maintain shape.",
    },
    {
      types: [
        "a_line_dress",
        "bodycon_dress",
        "maxi_dress",
        "midi_dress",
        "wrap_dress",
        "sun_dress",
      ],
      fabric: "Fluid woven fabric with gentle drape and subtle stretch for comfort.",
      fit: "Designed to skim the body; use bust and waist measurements for best size selection.",
      care: "Hand wash cold or use a delicate cycle; hang to dry to protect the fabric.",
    },
    {
      types: [
        "a_line_skirt",
        "pencil_skirt",
        "tennis_skirt",
      ],
      fabric: "Structured woven fabric with a smooth handfeel and dependable stretch.",
      fit: "Sits at the waist with a clean drape; true to size for a polished silhouette.",
      care: "Machine wash cold on delicate; press on low heat if needed.",
    },
    {
      types: [
        "bikini_top",
        "swim_trunks",
        "board_shorts",
        "one_piece_swimsuit",
      ],
      fabric: "Chlorine-resistant swim knit with a supportive, fully lined finish.",
      fit: "Secure, stay-put feel in and out of the water; stay true to your usual swim size.",
      care: "Rinse after wear and hand wash cold; lay flat to dry out of direct sun.",
    },
    {
      types: [
        "briefs",
        "boxer_briefs",
        "boxers",
      ],
      fabric: "Breathable microfiber with smooth, no-show stretch for daily comfort.",
      fit: "Close fit that flexes with movement; true to size for a stay-put feel.",
      care: "Machine wash cold in a mesh bag; hang dry to extend elasticity.",
    },
    {
      types: [
        "crew_socks",
        "ankle_socks",
      ],
      fabric: "Combed cotton knit with arch support and soft cushioning.",
      fit: "Snug through the arch and cuff so they stay in place all day.",
      care: "Machine wash warm; tumble dry low to preserve stretch.",
    },
    {
      types: [
        "sneakers",
        "dress_shoes",
        "loafers",
        "boots",
      ],
      fabric: "Supple leather upper with breathable lining and cushioned insole.",
      fit: "Runs true to size; go up half a size if you have a wider instep.",
      care: "Brush off dirt after wear; wipe clean with a damp cloth and condition leather regularly.",
    },
    {
      types: [
        "backpack",
        "tote_bag",
        "crossbody_bag",
        "clutch",
      ],
      fabric: "Pebbled faux leather exterior with durable cotton lining.",
      fit: "Sized to carry daily essentials with a structured silhouette that keeps its shape.",
      care: "Wipe clean with a soft cloth; keep away from prolonged moisture and direct heat.",
    },
    {
      types: [
        "necklace",
        "bracelet",
        "earrings",
        "ring",
      ],
      fabric: "Hypoallergenic plated metal with a polished, tarnish-resistant finish.",
      fit: "Adjustable closure for a personalized, comfortable fit against the skin.",
      care: "Store in the provided pouch; keep away from water, lotions, and perfumes.",
    },
    {
      types: [
        "baseball_cap",
        "beanie",
        "sun_hat",
        "bucket_hat",
      ],
      fabric: "Soft felt and twill blends with breathable comfort lining.",
      fit: "Standard crown height with an interior band that adjusts for a custom feel.",
      care: "Spot clean with a lint brush or damp cloth; reshape and air dry away from heat.",
    },
  ];

  const DEFAULT_DETAIL_PROFILE = {
    fabric: "Premium fabric with a smooth handfeel.",
    fit: "True to size with a relaxed drape. Size down for a closer fit.",
    care: "Machine wash cold with like colors. Tumble dry low.",
  };

  const resolveGarmentDetailProfile = (garmentType) => {
    const normalized = normalizeGarmentKey(garmentType);
    if (!normalized) {
      return DEFAULT_DETAIL_PROFILE;
    }

    for (const profile of GARMENT_DETAIL_PROFILES) {
      if (profile.types.some((type) => normalizeGarmentKey(type) === normalized)) {
        return profile;
      }
    }

    return DEFAULT_DETAIL_PROFILE;
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

  const garmentDetailProfile = useMemo(
    () => resolveGarmentDetailProfile(inferGarmentType),
    [inferGarmentType]
  );

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

  // Automatically fetch size recommendation when product is loaded
  useEffect(() => {
    if (product && resolvedItemId) {
      handleSizeAssist();
    }
  }, [product, resolvedItemId]);


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

    console.log('Size API Response:', { data, error }); // Debug log

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

    // Debug: log the raw response and check for store_id
    console.log('🔍 DEBUG Outfit Response:', {
      rawResponse: data,
      firstItem: data?.data?.outfit?.outfit_items?.[0],
      allItems: data?.data?.outfit?.outfit_items,
      // Check for store_id
      firstItemHasStoreId: data?.data?.outfit?.outfit_items?.[0]?.store_id,
      firstItemHasStoreIdAlt: data?.data?.outfit?.outfit_items?.[0]?.storeId,
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
    pickFirstMeaningfulText(
      product.fabric,
      product.material,
      product.materials,
      product.textile,
      product.textiles,
      garmentDetailProfile.fabric
    ) || garmentDetailProfile.fabric;
  const care =
    pickFirstMeaningfulText(
      product.care_instructions,
      product.care,
      product.careInstructions,
      garmentDetailProfile.care
    ) || garmentDetailProfile.care;
  const deliveryNote =
    product.shipping_note ||
    "Free standard delivery over $75. Easy 30-day returns.";
  const fitNote =
    pickFirstMeaningfulText(
      product.fit,
      product.fit_note,
      product.fitNotes,
      garmentDetailProfile.fit
    ) || garmentDetailProfile.fit;
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
        <div>
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
          
          {/* Style Outfit Section under image */}
          <div className="ai-action" style={{ marginTop: '20px' }}>
            <button
              type="button"
              className={`ai-button secondary ${outfitLoading ? 'loading' : ''}`}
              onClick={handleOutfitAssist}
              disabled={outfitLoading}
            >
              {outfitLoading ? (
                <>
                  <span className="spinner"></span>
                  Curating outfit...
                </>
              ) : (
                "Style this outfit"
              )}
            </button>

            {outfitError && <p className="ai-message error">{outfitError}</p>}
          </div>

          {/* Outfit Suggestions under image */}
          {outfitItems.length > 0 && (
            <div className={`ai-outfit ${outfitSourceInfo.isRealAI ? 'real-ai-outfit' : 'fallback-outfit'}`} style={{ marginTop: '15px' }}>
              <div className="ai-outfit-header">
                <div className="ai-result-heading">
                  <h3>Complete Outfit Suggestion</h3>
                  <div className="outfit-source-indicator">
                    <span className={`ai-source-badge ${outfitSourceInfo.isRealAI ? 'ai-badge' : 'fallback-badge'}`}>
                      {outfitSourceInfo.isRealAI ? '🤖 AI Generated' : '📊 Basic Match'}
                    </span>
                    {outfitSuggestion.data?.outfit?.compatibility_score !== undefined && (
                      <span className="ai-chip">
                        {Math.round(outfitSuggestion.data.outfit.compatibility_score)}% match
                      </span>
                    )}
                    {outfitSuggestion.data?.outfit?.style_theme && (
                      <span className="ai-chip secondary">
                        {outfitSuggestion.data.outfit.style_theme.replace(/_/g, ' ')}
                      </span>
                    )}
                  </div>
                </div>

                {outfitSuggestion.data?.outfit?.description && (
                  <p className="outfit-description">
                    {outfitSuggestion.data.outfit.description}
                  </p>
                )}

                <div className="outfit-meta-info">
                  <span className="outfit-item-count">
                    {outfitItems.length} items · ${outfitTotalPrice.toFixed(2)}
                  </span>
                </div>
              </div>

              <div className="ai-outfit-grid">
                {outfitItems.map((item, index) => {
                  const itemId = item.id || item.item_id || index;
                  const recommendedSize = outfitSizeMap[item.id] || outfitSizeMap[itemId] || 'Standard';
                  const isRealItem = item.name && !item.name.startsWith('Item ');

                  return (
                    <div
                      key={itemId}
                      className="ai-outfit-card clickable"
                      style={{ cursor: isRealItem ? 'pointer' : 'default' }}
                      onClick={() => {
                        if (!isRealItem) return;

                        // Try multiple ways to get store ID
                        const navStoreId =
                          item.store_id ||
                          item.storeId ||
                          item.store_id_alt ||
                          storeId; // fallback to current store

                        console.log('🖱️ Clicking outfit item:', {
                          itemId,
                          itemName: item.name,
                          hasStoreId: Boolean(item.store_id || item.storeId),
                          navStoreId,
                          itemData: item
                        });

                        if (itemId && navStoreId) {
                          navigate(`/stores/${navStoreId}/product/${itemId}`);
                        } else {
                          console.warn('Cannot navigate: missing store ID', {
                            itemId,
                            itemName: item.name,
                            hasStoreId: Boolean(item.store_id || item.storeId),
                            item
                          });
                          setCartFeedback(`Sorry, "${item.name}" doesn't have store information.`);
                        }
                      }}
                    >
                      <div className="outfit-card-image">
                        {item.image_url || item.image ? (
                          <img
                            src={item.image_url || item.image}
                            alt={item.name || item.item_name || "Outfit item"}
                            className="outfit-item-image"
                          />
                        ) : (
                          <div className="ai-outfit-placeholder">
                            {(item.name || item.item_name || "?").slice(0, 1)}
                          </div>
                        )}
                        {!isRealItem && (
                          <div className="item-source-indicator">
                            <span className="item-source-badge">Generic</span>
                          </div>
                        )}
                      </div>
                      <div className="ai-outfit-info">
                        <p className="ai-outfit-name">
                          {item.name || item.item_name || "Curated piece"}
                          {!isRealItem && <span className="item-generic-indicator"> (example)</span>}
                        </p>
                        {item.price && (
                          <p className="ai-outfit-price">
                            ${parseFloat(item.price).toFixed(2)}
                          </p>
                        )}
                        <p className="ai-outfit-meta">
                          {item.garment_type || item.garment_category || ''}
                        </p>
                        {isRealItem && (
                          <button
                            className="outfit-item-add-btn"
                            onClick={(e) => {
                              e.stopPropagation();
                              addToCart({
                                id: item.id,
                                name: item.name || item.item_name,
                                price: item.price,
                                image: item.image_url || item.image,
                                size: outfitSizeMap[item.id] || 'Standard',
                                storeId: item.store_id || item.storeId || storeId,
                                quantity: 1
                              });
                              setCartFeedback(`Added ${item.name || item.item_name} to cart`);
                            }}
                          >
                            Add to Cart
                          </button>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>

              <div className="outfit-actions">
                <button
                  className="outfit-add-all"
                  onClick={() => {
                    const validItems = outfitItems.filter(i => i.id && i.name && !i.name.startsWith('Item '));
                    const totalOriginalPrice = validItems.reduce((sum, item) => sum + parseFloat(item.price || 0), 0);
                    const savings = totalOriginalPrice * 0.20;
                    
                    validItems.forEach(item => {
                      addToCart({
                        id: item.id,
                        name: item.name || item.item_name,
                        price: item.price,
                        image: item.image_url || item.image,
                        size: outfitSizeMap[item.id] || 'Standard',
                        storeId: storeId,
                        quantity: 1,
                        bundleDiscount: 20
                      });
                    });
                    setCartFeedback(`Added ${validItems.length} items to cart with 20% off! You saved $${savings.toFixed(2)}`);
                  }}
                  disabled={outfitItems.every(item => !item.id || item.name?.startsWith('Item '))}
                >
                  <span className="outfit-add-all-text">Add all to cart</span>
                  <span className="outfit-add-all-prices">
                    <span className="outfit-original-price">${outfitTotalPrice.toFixed(2)}</span>
                    <span className="outfit-discounted-price">${(outfitTotalPrice * 0.8).toFixed(2)}</span>
                  </span>
                </button>
              </div>
            </div>
          )}
        </div>

        <div className="product-detail-info">
          <div className="product-detail-header">
            <div>
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

          {/* AI Assist Section with Source Indicators */}
          <div className="ai-assist">
            <div className="ai-action">
              {sizeLoading && (
                <p className="ai-message loading">
                  <span className="spinner"></span>
                  Finding your best size...
                </p>
              )}

              {sizeError && <p className="ai-message error">{sizeError}</p>}

              {sizeSummary && (
                <div className={`ai-result ${sizeSummary.isFallback ? 'fallback' : 'real-ai'}`}>
                  <div className="ai-result-header">
                    <p className="ai-result-title">
                      {sizeSummary.size ? `Recommended size: ${sizeSummary.size}` : "Size recommendation"}
                    </p>
                    <div className="ai-source-indicator">
                      <span className={`ai-source-badge ${sizeSummary.isFallback ? 'fallback-badge' : 'ai-badge'}`}>
                        {sizeSummary.isFallback ? '📊 Fallback' : '🤖 AI'}
                      </span>
                    </div>
                  </div>

                  {sizeSummary.fitScore !== null && (
                    <div className="ai-confidence">
                      <div className="confidence-meter">
                        <div
                          className={`confidence-fill ${sizeSummary.isFallback ? 'fallback-fill' : 'ai-fill'}`}
                          style={{
                            width: typeof sizeSummary.fitScore === 'number'
                              ? `${Math.min(sizeSummary.fitScore * 100, 100)}%`
                              : '0%'
                          }}
                        ></div>
                      </div>
                      <div className="confidence-info">
                        <span className="confidence-score">
                          Confidence: <strong>{formatFitScore(sizeSummary.fitScore)}</strong>
                        </span>
                      </div>
                    </div>
                  )}

                  {/* Show top recommendations if available */}
                  {sizeSummary.recommendations && sizeSummary.recommendations.length > 0 && (
                    <div className="ai-recommendations">
                      <p className="recommendations-label">Top matches:</p>
                      <div className="recommendations-list">
                        {sizeSummary.recommendations.slice(0, 3).map((rec, idx) => (
                          <div key={idx} className="recommendation-item">
                            <span className="rec-name">{rec.item_name}</span>
                            <span className="rec-size">Size {rec.recommended_size}</span>
                            <span className="rec-score">{formatFitScore(rec.overall_fit_score)}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* Debug info (visible in development) */}
                  {process.env.NODE_ENV === 'development' && (
                    <div className="debug-info">
                      <small>
                        Source: {sizeSummary.sourceInfo.source} |
                        Fallback: {sizeSummary.isFallback ? 'Yes' : 'No'}
                      </small>
                    </div>
                  )}
                </div>
              )}
            </div>
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

      {/* CSS Styles (Add to your CSS file) */}
      <style jsx>{`
        .ai-source-indicator {
          display: flex;
          align-items: center;
          gap: 8px;
          margin-left: auto;
        }

        .ai-source-badge {
          padding: 4px 8px;
          border-radius: 12px;
          font-size: 0.8em;
          font-weight: 600;
          display: inline-flex;
          align-items: center;
          gap: 4px;
        }

        .ai-badge {
          background: #e3f2fd;
          color: #1976d2;
          border: 1px solid #90caf9;
        }

        .fallback-badge {
          background: #fff3cd;
          color: #856404;
          border: 1px solid #ffeaa7;
        }

        .ai-model-info {
          font-size: 0.8em;
          color: #666;
          background: #f5f5f5;
          padding: 2px 6px;
          border-radius: 4px;
        }

        .ai-result-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          margin-bottom: 12px;
        }

        .confidence-fill.ai-fill {
          background: linear-gradient(90deg, #4CAF50, #8BC34A);
        }

        .confidence-fill.fallback-fill {
          background: linear-gradient(90deg, #FF9800, #FFC107);
        }

        .confidence-info {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-top: 8px;
        }

        .ai-recommendations {
          margin-top: 16px;
          padding-top: 16px;
          border-top: 1px solid #eee;
        }

        .recommendations-list {
          display: flex;
          flex-direction: column;
          gap: 8px;
          margin-top: 8px;
        }

        .recommendation-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 8px;
          background: #f9f9f9;
          border-radius: 4px;
          font-size: 0.9em;
        }

        .rec-name {
          flex: 2;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }

        .rec-size {
          flex: 1;
          text-align: center;
          font-weight: 600;
          color: #1976d2;
        }

        .rec-score {
          flex: 1;
          text-align: right;
          font-weight: 600;
          color: #4CAF50;
        }

        .ai-button.loading {
          opacity: 0.8;
          cursor: not-allowed;
        }

        .spinner {
          display: inline-block;
          width: 16px;
          height: 16px;
          border: 2px solid rgba(255,255,255,0.3);
          border-radius: 50%;
          border-top-color: white;
          animation: spin 1s ease-in-out infinite;
          margin-right: 8px;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        .outfit-source-indicator {
          display: flex;
          align-items: center;
          gap: 8px;
        }

        .outfit-meta-info {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-top: 12px;
          font-size: 0.9em;
          color: #666;
        }

        .outfit-model {
          background: #f0f0f0;
          padding: 4px 8px;
          border-radius: 4px;
        }

        .item-source-indicator {
          position: absolute;
          top: 8px;
          right: 8px;
          z-index: 2;
        }

        .item-source-badge {
          background: rgba(255,152,0,0.9);
          color: white;
          padding: 2px 6px;
          border-radius: 10px;
          font-size: 0.7em;
          font-weight: 600;
        }

        .item-generic-indicator {
          color: #FF9800;
          font-size: 0.8em;
          margin-left: 4px;
        }

        .outfit-card-image {
          position: relative;
        }

        .debug-info {
          margin-top: 8px;
          padding: 8px;
          background: #f5f5f5;
          border-radius: 4px;
          font-size: 0.8em;
          color: #666;
        }

        .ai-result.fallback {
          border-left-color: #FF9800;
        }

        .ai-result.real-ai {
          border-left-color: #4CAF50;
        }

        .real-ai-outfit {
          border: 2px solid #4CAF50;
        }

        .fallback-outfit {
          border: 2px solid #FF9800;
        }
      `}</style>
    </div>
  );
}
