import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "./api";
import {
  getWishlist,
  isItemWishlisted,
  toggleWishlistEntry,
} from "./wishlistStorage";
import { addToCart } from "./cartStorage";

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
                <div className="size-guide-row header">
                  <span>Size</span>
                  <span>Chest</span>
                  <span>Waist</span>
                  <span>Length</span>
                </div>
                {sizes.map((size) => (
                  <div className="size-guide-row" key={size}>
                    <span>{size}</span>
                    <span>34" - 38"</span>
                    <span>28" - 32"</span>
                    <span>26" - 30"</span>
                  </div>
                ))}
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
