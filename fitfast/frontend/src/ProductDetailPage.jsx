import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "./api";

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
            item.id ||
            item.productId ||
            item.product_id ||
            item.slug ||
            item.name;

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
          setSelectedSize(sizes[0] || "Standard");
          setSelectedColor(colors[0] || "Default");
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
    if (!cartFeedback) return;
    const timeout = setTimeout(() => setCartFeedback(""), 3200);
    return () => clearTimeout(timeout);
  }, [cartFeedback]);

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

  const getSizes = (item) =>
    normalizeOptions(
      item.sizes || item.available_sizes || item.size_options,
      ["XS", "S", "M", "L", "XL"]
    );

  const getColors = (item) =>
    normalizeOptions(
      item.colors || item.available_colors || item.color_options,
      ["Charcoal", "Sand", "Rose"]
    );

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
  

const buildFeatureList = () => {
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
    setCartFeedback(
      `${product.name} added to cart (${selectedColor}${
        selectedSize ? ` / ${selectedSize}` : ""

      }) x${selectedQuantity}`

  );
  };

  const handleToggleWishlist = () => {
    setIsWishlisted(!isWishlisted);
    setCartFeedback(isWishlisted ? "Removed from wishlist" : "Added to wishlist");
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
  const features = buildFeatureList();
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
              className={`wishlist-btn-large ${isWishlisted ? "active" : ""}`}
              onClick={handleToggleWishlist}
              aria-label="Add to wishlist"
            >
              <svg
                width="28"
                height="28"
                viewBox="0 0 24 24"
                fill={isWishlisted ? "currentColor" : "none"}
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
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
                <span className="option-selected">{selectedColor}</span>
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
                <span className="option-selected">{selectedQuantity}</span>
              </div>
              <div className="quantity-control">
                <button
                  className="quantity-btn"
                  onClick={() =>
                    setSelectedQuantity((qty) => Math.max(1, qty - 1))
                  }
                  aria-label="Decrease quantity"
                >
                  −
                </button>
                <span className="quantity-value">{selectedQuantity}</span>
                <button
                  className="quantity-btn"
                  onClick={() => setSelectedQuantity((qty) => qty + 1)}
                  aria-label="Increase quantity"
                >
                  +
                </button>
              </div>
            </div>

            <div className="option-section">
              <div className="option-header">
                <span className="option-label">Size</span>
                <span className="option-selected">{selectedSize}</span>
              </div>
              <div className="size-options">
                {sizes.map((size) => (
                  <button
                    key={size}
                    className={`size-option ${
                      selectedSize === size ? "selected" : ""
                    }`}
                    onClick={() => setSelectedSize(size)}
                  >
                    {size}
                  </button>
                ))}
              </div>
            </div>
          </div>

          <div className="product-actions-detail">
            <button
              type="button"
              className="add-to-cart-btn-large"
              onClick={handleAddToCart}
            >
              Add to Cart
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