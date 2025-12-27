import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "./api";

export default function StorePage() {
  const { storeId } = useParams();
  const navigate = useNavigate();
  const [store, setStore] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedCategoryId, setSelectedCategoryId] = useState(null);
  const [cartFeedback, setCartFeedback] = useState("");
  const [wishlistItems, setWishlistItems] = useState(new Set());

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
      setSelectedCategoryId(firstCategory.id ?? firstCategory.name);
    }
  }, [store]);

  if (loading) {
    return <div className="store-page">Loading store...</div>;
  }

  if (error) {
    return <div className="store-page error">{error}</div>;
  }

  if (!store) {
    return <div className="store-page">Store not found.</div>;
  }

  const categories = store.categories || [];
  const selectedCategory = categories.find(
    (category) => (category.id ?? category.name) === selectedCategoryId
  );

  const getItemImage = (item) =>
    item.image_url ||
    item.image ||
    item.imagePath ||
    item.image_path ||
    item.primary_image_url ||
    item.primary_image?.image_path;

  const handleItemClick = (item) => {
    navigate(`/stores/${storeId}/product/${item.id || item.name}`);
  };

  const handleAddToCart = (e, item) => {
    e.stopPropagation();
    setCartFeedback(`${item.name || "Item"} added to cart`);
  };

  const handleToggleWishlist = (e, itemId) => {
    e.stopPropagation();
    setWishlistItems((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(itemId)) {
        newSet.delete(itemId);
        setCartFeedback("Removed from wishlist");
      } else {
        newSet.add(itemId);
        setCartFeedback("Added to wishlist");
      }
      return newSet;
    });
  };

  const formatPrice = (price) => {
    if (!price && price !== 0) return "";
    const amount = Number(price);
    if (Number.isNaN(amount)) return price;
    return `$${amount.toFixed(2)}`;
  };

  return (
    <div className="store-page">
      {cartFeedback && <div className="cart-feedback">{cartFeedback}</div>}
      
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
          <div className="category-pills">
            {categories.map((category) => {
              const categoryKey = category.id ?? category.name;
              const isActive = categoryKey === selectedCategoryId;

              return (
                <button
                  key={categoryKey}
                  className={`category-pill ${isActive ? "active" : ""}`}
                  onClick={() => setSelectedCategoryId(categoryKey)}
                >
                  <span>{category.name || "Category"}</span>
                  <span className="pill-count">
                    {(category.items?.length ?? 0) + " items"}
                  </span>
                </button>
              );
            })}
          </div>

          <section className="category-detail">
            <div className="category-heading">
              <div>
                <p className="eyebrow">Browse by style</p>
                <h2>{selectedCategory?.name || "Category"}</h2>
                {selectedCategory?.description && (
                  <p className="muted">{selectedCategory.description}</p>
                )}
              </div>
              <div className="category-meta">
                <span className="pill-count">
                  {selectedCategory?.items?.length ?? 0} pieces
                </span>
              </div>
            </div>

            {selectedCategory?.items?.length ? (
              <div className="product-grid">
                {selectedCategory.items.map((item) => {
                  const itemId = item.id || item.name;
                  const isWishlisted = wishlistItems.has(itemId);

                  return (
                    <article
                      key={itemId}
                      className="product-card-modern"
                      onClick={() => handleItemClick(item)}
                    >
                      <div className="product-image-container">
                        {getItemImage(item) ? (
                          <img
                            src={getItemImage(item)}
                            alt={item.name || "Item"}
                            loading="lazy"
                          />
                        ) : (
                          <div className="image-placeholder">
                            {item.name?.slice(0, 1) || ""}
                          </div>
                        )}
                        
                        <button
                          className={`wishlist-btn ${isWishlisted ? "active" : ""}`}
                          onClick={(e) => handleToggleWishlist(e, itemId)}
                          aria-label="Add to wishlist"
                        >
                          <svg
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill={isWishlisted ? "currentColor" : "none"}
                            stroke="currentColor"
                            strokeWidth="2"
                          >
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                          </svg>
                        </button>

                        <div className="best-fit-badge">Best Fit: Medium - 90% Match!</div>
                      </div>

                      <div className="product-info-modern">
                        <h3>{item.name}</h3>
                        <div className="product-footer">
                          {item.price && (
                            <span className="price-modern">{formatPrice(item.price)}</span>
                          )}
                          <button
                            type="button"
                            className="add-to-cart-btn"
                            onClick={(e) => handleAddToCart(e, item)}
                          >
                            Add to Cart
                          </button>
                        </div>
                      </div>
                    </article>
                  );
                })}
              </div>
            ) : (
              <div className="empty-state card">No items in this category yet.</div>
            )}
          </section>
        </>
      )}
    </div>
  );
}