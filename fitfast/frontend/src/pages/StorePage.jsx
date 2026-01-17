import { useState, useEffect, useMemo, useRef } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "../api";
import {
  getWishlist,
  isItemWishlisted,
  toggleWishlistEntry,
} from "../wishlistStorage";
import { addToCart } from "../cartStorage";
import ItemCard from "../components/cards/ItemCard";
import { getItemId, getItemImage, getBestFitCopy } from "../utils/item";

const PRIMARY_COLOR = "#641b2e";

const CATEGORY_THEME_PRESETS = [
  {
    keywords: ["dress", "gown", "skirt"],
    badge: "Occasion ready",
    blurb: "Fluid silhouettes and statement drapes for night-out plans.",
    tint: 0.96,
    gradientIntensity: 0.18,
  },
  {
    keywords: ["coat", "jacket", "outer"],
    badge: "Layering edit",
    blurb: "Structured outerwear to top off every fit in style.",
    tint: 0.95,
    gradientIntensity: 0.17,
  },
  {
    keywords: ["denim", "jean"],
    badge: "Everyday denim",
    blurb: "Your go-to washes and fits for off-duty ease.",
    tint: 0.95,
    gradientIntensity: 0.16,
  },
  {
    keywords: ["suit", "tailor", "blazer"],
    badge: "Sharp tailoring",
    blurb: "Clean lines and confident cuts for elevated dressing.",
    tint: 0.94,
    gradientIntensity: 0.18,
  },
  {
    keywords: ["active", "sport", "athleisure", "gym"],
    badge: "Move-ready",
    blurb: "Breathable fabrics built to flex with your routine.",
    tint: 0.97,
    gradientIntensity: 0.15,
  },
];

const CATEGORY_THEME_FALLBACKS = [
  {
    badge: "Trending now",
    blurb: "Curated pieces styled to mix and match with ease.",
    tint: 0.96,
    gradientIntensity: 0.18,
  },
  {
    badge: "Fresh drop",
    blurb: "Seasonal heroes refreshed with a modern twist.",
    tint: 0.97,
    gradientIntensity: 0.16,
  },
  {
    badge: "Editor pick",
    blurb: "Elevated staples with effortlessly polished vibes.",
    tint: 0.96,
    gradientIntensity: 0.18,
  },
];

function clamp01(value) {
  if (value < 0) return 0;
  if (value > 1) return 1;
  return value;
}

function hexToRgb(hex) {
  const normalized = hex.replace("#", "").trim();
  const expanded =
    normalized.length === 3
      ? normalized
          .split("")
          .map((char) => char + char)
          .join("")
      : normalized;

  const value = parseInt(expanded, 16);

  return {
    r: (value >> 16) & 255,
    g: (value >> 8) & 255,
    b: value & 255,
  };
}

function rgbChannelToHex(channel) {
  const bounded = Math.max(0, Math.min(255, Math.round(channel)));
  return bounded.toString(16).padStart(2, "0");
}

function rgbToHex(r, g, b) {
  return `#${rgbChannelToHex(r)}${rgbChannelToHex(g)}${rgbChannelToHex(b)}`;
}

function mixWithWhite(hex, weight = 0.85) {
  const ratio = clamp01(weight);
  const { r, g, b } = hexToRgb(hex);
  const mixedR = r + (255 - r) * ratio;
  const mixedG = g + (255 - g) * ratio;
  const mixedB = b + (255 - b) * ratio;

  return rgbToHex(mixedR, mixedG, mixedB);
}

function hexToRgba(hex, alpha = 1) {
  const { r, g, b } = hexToRgb(hex);
  return `rgba(${r}, ${g}, ${b}, ${clamp01(alpha)})`;
}

function createThemeFromConfig(config = {}) {
  const {
    tint = 0.96,
    gradientIntensity = 0.18,
    keywords: _unusedKeywords,
    ...rest
  } = config;
  const accent = PRIMARY_COLOR;
  const surface = mixWithWhite(PRIMARY_COLOR, tint);
  const startAlpha = clamp01(gradientIntensity);
  const endAlpha = clamp01(Math.max(gradientIntensity - 0.1, 0.04));
  const gradient = `linear-gradient(135deg, ${hexToRgba(
    PRIMARY_COLOR,
    startAlpha
  )}, ${hexToRgba(PRIMARY_COLOR, endAlpha)})`;

  return {
    accent,
    surface,
    gradient,
    ...rest,
  };
}

function pickCategoryTheme(name, index) {
  const normalized = (name || "").toLowerCase();

  for (const preset of CATEGORY_THEME_PRESETS) {
    if (preset.keywords.some((keyword) => normalized.includes(keyword))) {
      return createThemeFromConfig(preset);
    }
  }

  return createThemeFromConfig(
    CATEGORY_THEME_FALLBACKS[index % CATEGORY_THEME_FALLBACKS.length]
  );
}

function getCategoryBlurb(category, theme) {
  if (category?.description) {
    const trimmed = category.description.trim();
    if (trimmed.length <= 130) {
      return trimmed;
    }
    return `${trimmed.slice(0, 127).trim()}...`;
  }

  return theme?.blurb || "Curated looks to build outfits you can live in.";
}

function getCategoryKey(category, index) {
  if (!category) {
    return `category-${index}`;
  }

  return category.id ?? category.name ?? `category-${index}`;
}

export default function StorePage() {
  const { storeId } = useParams();
  const navigate = useNavigate();
  const [store, setStore] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedCategoryId, setSelectedCategoryId] = useState(null);
  const [cartFeedback, setCartFeedback] = useState("");
  const [priceSort, setPriceSort] = useState(""); // '', 'low', 'high'
  const [wishlistItems, setWishlistItems] = useState(() => getWishlist());
  const [itemSearch, setItemSearch] = useState("");
  const categoryRailRef = useRef(null);

  useEffect(() => {
    async function fetchStore() {
      setLoading(true);
      setError("");

      try {
        const res = await api.get(`/stores/${storeId}`);
        setStore(res.data.data || res.data);
      } catch (err) {
        console.error(err);
        setError("Failed to load store details. Please try again.");
      } finally {
        setLoading(false);
      }
    }

    fetchStore();
  }, [storeId]);

  useEffect(() => {
    if (!cartFeedback) return;

    const timeout = setTimeout(() => setCartFeedback(""), 3200);
    return () => clearTimeout(timeout);
  }, [cartFeedback]);

  useEffect(() => {
    if (store?.categories?.length) {
      const firstCategory = store.categories[0];
      setSelectedCategoryId(getCategoryKey(firstCategory, 0));
    }
  }, [store]);

  useEffect(() => {
    const scroller = categoryRailRef.current;
    if (!scroller) return;

    const activeCard = scroller.querySelector(".category-card.is-active");
    if (!activeCard) return;

    const scrollerRect = scroller.getBoundingClientRect();
    const activeRect = activeCard.getBoundingClientRect();

    if (
      activeRect.left < scrollerRect.left + 16 ||
      activeRect.right > scrollerRect.right - 16
    ) {
      activeCard.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
        inline: "center",
      });
    }
  }, [selectedCategoryId]);

  const rawCategories = store?.categories;
  const decoratedCategories = useMemo(
    () =>
      (rawCategories ?? []).map((category, index) => ({
        category,
        key: getCategoryKey(category, index),
        theme: pickCategoryTheme(category?.name, index),
      })),
    [rawCategories]
  );

  const selectedCategoryEntry = decoratedCategories.find(
    ({ key }) => key === selectedCategoryId
  );

  const selectedCategory = selectedCategoryEntry?.category;
  const selectedCategoryTheme = selectedCategoryEntry?.theme;

  const categories = useMemo(() => rawCategories ?? [], [rawCategories]);

  const normalizedItemSearch = itemSearch.trim().toLowerCase();
  const matchedCategory = useMemo(() => {
    if (!normalizedItemSearch) return null;
    const exactMatch = categories.find(
      (category) =>
        (category?.name || "").toLowerCase() === normalizedItemSearch
    );
    if (exactMatch) return exactMatch;
    const startsWithMatch = categories.find((category) =>
      (category?.name || "").toLowerCase().startsWith(normalizedItemSearch)
    );
    if (startsWithMatch) return startsWithMatch;
    return (
      categories.find((category) =>
        (category?.name || "").toLowerCase().includes(normalizedItemSearch)
      ) || null
    );
  }, [categories, normalizedItemSearch]);

  const filteredItems = useMemo(() => {
    let items = [];
    if (!normalizedItemSearch) {
      items = selectedCategory?.items ?? [];
    } else {
      const matches = new Map();
      categories.forEach((category, categoryIndex) => {
        const categoryItems = category?.items ?? [];
        const categoryKey = getCategoryKey(category, categoryIndex);
        const categoryName = category?.name?.toLowerCase() ?? "";
        const categoryMatches = categoryName.includes(normalizedItemSearch);
        categoryItems.forEach((item, itemIndex) => {
          const itemName = item?.name?.toLowerCase() ?? "";
          const itemDesc = item?.description?.toLowerCase() ?? "";
          const rawItemCategory = item?.category ?? item?.category_name ?? "";
          let itemCategoryName = "";
          if (typeof rawItemCategory === "string") {
            itemCategoryName = rawItemCategory.toLowerCase();
          } else if (rawItemCategory && typeof rawItemCategory === "object") {
            itemCategoryName = (rawItemCategory.name ?? "").toLowerCase();
          }
          if (
            categoryMatches ||
            itemName.includes(normalizedItemSearch) ||
            itemDesc.includes(normalizedItemSearch) ||
            itemCategoryName.includes(normalizedItemSearch)
          ) {
            const lookupKey = getItemId(item) ?? `${categoryKey}-${itemIndex}`;
            if (!matches.has(lookupKey)) {
              matches.set(lookupKey, item);
            }
          }
        });
      });
      items = Array.from(matches.values());
    }
    // Price sorting
    if (priceSort === 'low') {
      items = [...items].sort((a, b) => (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0));
    } else if (priceSort === 'high') {
      items = [...items].sort((a, b) => (parseFloat(b.price) || 0) - (parseFloat(a.price) || 0));
    }
    return items;
  }, [categories, normalizedItemSearch, selectedCategory, priceSort]);

  const showingSearchResults = normalizedItemSearch.length > 0;
  const displayCategoryEntry =
    showingSearchResults && matchedCategory
      ? decoratedCategories.find(
          ({ category }) => category === matchedCategory
        ) || selectedCategoryEntry
      : selectedCategoryEntry;
  const displayCategory = displayCategoryEntry?.category || selectedCategory;
  const displayCategoryTheme =
    displayCategoryEntry?.theme || selectedCategoryTheme;
  const categoryCountLabel = showingSearchResults
    ? `${filteredItems.length} ${filteredItems.length === 1 ? "match" : "matches"}`
    : `${filteredItems.length} ${filteredItems.length === 1 ? "piece" : "pieces"}`;

  if (loading) {
    return (
      <div className="store-page page-loading">
        <div className="loading-spinner"></div>
        <p>Loading store...</p>
      </div>
    );
  }

  if (error) {
    return <div className="store-page error">{error}</div>;
  }

  if (!store) {
    return <div className="store-page">Store not found.</div>;
  }

  const handleItemClick = (item) => {
    console.log('Clicked item:', item);
    const itemId = getItemId(item) ?? item.name;
    console.log('Navigating to:', `/stores/${storeId}/product/${itemId}`);
    navigate(`/stores/${storeId}/product/${itemId}`);
  };

  const handleAddToCart = (item, options = {}) => {
    const itemId = getItemId(item) ?? item.name;

    const selectionLabel = [options.color, options.size].filter(Boolean).join(" / ");

    addToCart({
      id: itemId,
      storeId,
      name: item.name,
      price: item.price,
      image: getItemImage(item),
      storeName: store?.name,
      quantity: 1,
      size: options.size || null,
      color: options.color || null,
    });
    setCartFeedback(
      `${item.name || "Item"} added to cart${selectionLabel ? ` (${selectionLabel})` : ""}`
    );
  };

  const handleToggleWishlist = (item) => {
    const itemId = getItemId(item) ?? item.name;

    const { items, added } = toggleWishlistEntry({
      id: itemId,
      storeId,
      name: item.name,
      price: item.price,
      image: getItemImage(item),
      storeName: store?.name,
    });
    setWishlistItems(items);
    setCartFeedback(added ? "Added to wishlist" : "Removed from wishlist");
  };

  return (
    <div className="store-page">
      {cartFeedback && <div className="cart-feedback">{cartFeedback}</div>}

      <button
        type="button"
        className="back-link"
        onClick={() => navigate("/")}
      >
        ← Back to Stores
      </button>

      <section className="store-hero">
        <div className="store-hero-content">
          <p className="eyebrow">Curated wardrobe</p>
          <h1>{store.name}</h1>
          {store.description && <p className="muted">{store.description}</p>}
        </div>
        <div className="store-hero-badge">
          {store.logo_url ? (
            <img src={store.logo_url} alt={`${store.name} logo`} />
          ) : (
            <span>{store.name?.slice(0, 1) || "S"}</span>
          )}
          <small>{categories.length} categories</small>
        </div>
      </section>



      {categories.length === 0 ? (
        <p className="empty-state">No categories available.</p>
      ) : (
        <>
          <section className="category-rail">
            <div className="category-rail__header">
              <div>
                <p className="eyebrow">Shop by category</p>
                <h2>Dial in your vibe</h2>
              </div>
            </div>

            <div className="category-rail__viewport">
              <div className="category-rail__scroller" ref={categoryRailRef}>
                {decoratedCategories.map(({ category, key, theme }, index) => {
                  const recordKey = key ?? `category-${index}`;
                  const reactKey =
                    typeof recordKey === "number"
                      ? `category-${recordKey}`
                      : recordKey;
                  const isActive = recordKey === selectedCategoryId;
                  return (
                    <button
                      key={reactKey}
                      type="button"
                      className={`category-card ${isActive ? "is-active" : ""}`}
                      aria-pressed={isActive}
                      onClick={() => setSelectedCategoryId(recordKey)}
                      style={{
                        "--category-accent": theme.accent,
                        "--category-surface": theme.surface,
                        "--category-gradient": theme.gradient,
                      }}
                    >
                      <span className="category-card__badge">{theme.badge}</span>
                      <h3 className="category-card__title">
                        {category?.name || "Category"}
                      </h3>
                    </button>
                  );
                })}
              </div>
            </div>
          </section>

          <section
            className="category-detail"
            style={
              displayCategoryTheme
                ? {
                    "--category-accent": displayCategoryTheme.accent,
                    "--category-surface": displayCategoryTheme.surface,
                  }
                : undefined
            }
          >
            <div className="category-heading">
              <div>
                <p className="eyebrow">
                  {showingSearchResults && matchedCategory
                    ? "Search spotlight"
                    : "Browse by style"}
                </p>
                <h2>{displayCategory?.name || "Category"}</h2>
                <p className="muted">
                  {displayCategory
                    ? getCategoryBlurb(displayCategory, displayCategoryTheme)
                    : "Curated edits tailored to the store's vibe."}
                </p>
              </div>
              <div className="category-meta">
                <div className="price-sort">
                  <select
                    value={priceSort}
                    onChange={e => setPriceSort(e.target.value)}
                  >
                    <option value="">Sort by price</option>
                    <option value="low">Price: Low to High</option>
                    <option value="high">Price: High to Low</option>
                  </select>
                </div>
                <div className="category-search">
                  <svg
                    className="category-search-icon"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                  >
                    <path
                      d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                    />
                  </svg>
                  <input
                    type="text"
                    value={itemSearch}
                    onChange={(event) => setItemSearch(event.target.value)}
                    placeholder="Search items or categories..."
                    className="category-search-input"
                  />
                </div>
                <span className="pill-count">
                  {categoryCountLabel}
                </span>
              </div>
            </div>

            {filteredItems.length ? (
              <div className="product-grid">
                {filteredItems.map((item) => {
                  const itemId = getItemId(item) ?? item.name;
                  const wishlisted = isItemWishlisted(
                    wishlistItems,
                    itemId,
                    storeId
                  );

                  return (
                    <ItemCard
                      key={itemId}
                      item={item}
                      badgeContent={getBestFitCopy(item)}
                      wishlisted={wishlisted}
                      onClick={handleItemClick}
                      onAddToCart={handleAddToCart}
                      onWishlistToggle={handleToggleWishlist}
                    />
                  );
                })}
              </div>
            ) : (
              <div className="empty-state card">
                {showingSearchResults
                  ? `No matches for "${itemSearch.trim()}" in this store.`
                  : "No items in this category yet."}
              </div>
            )}
          </section>
        </>
      )}
    </div>
  );
}
