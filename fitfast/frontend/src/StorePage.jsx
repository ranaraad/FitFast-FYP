import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import api from "./api";
import {
  getWishlist,
  isItemWishlisted,
  toggleWishlistEntry,
} from "./wishlistStorage";
import { addToCart } from "./cartStorage";

export default function StorePage() {
  const { storeId } = useParams();
  const navigate = useNavigate();
  const [store, setStore] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedCategoryId, setSelectedCategoryId] = useState(null);
  const [cartFeedback, setCartFeedback] = useState("");
  const [wishlistItems, setWishlistItems] = useState(() => getWishlist());

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
    const itemId = item.id || item.name;

    addToCart({
      id: itemId,
      storeId,
      name: item.name,
      price: item.price,
      image: getItemImage(item),
      storeName: store?.name,
      quantity: 1,
    });
    setCartFeedback(`${item.name || "Item"} added to cart`);
  };

  const handleToggleWishlist = (e, item) => {
    e.stopPropagation();
    const itemId = item.id || item.name;

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

  const formatPrice = (price) => {
    if (!price && price !== 0) return "";
    const amount = Number(price);
    if (Number.isNaN(amount)) return price;
    return `$${amount.toFixed(2)}`;
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
                  const isWishlisted = isItemWishlisted(
                    wishlistItems,
                    itemId,
                    storeId
                  );

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
                          onClick={(e) => handleToggleWishlist(e, item)}
                          aria-label="Add to wishlist"
                          style={{
                            position: 'absolute',
                            top: '12px',
                            right: '12px',
                            width: '40px',
                            height: '40px',
                            borderRadius: '50%',
                            border: 'none',
                            background: 'transparent',
                            color: '#942341',
                            display: 'grid',
                            placeItems: 'center',
                            transition: 'all 0.2s ease',
                            cursor: 'pointer',
                            padding: 0,
                            zIndex: 10,
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
                              filter: 'drop-shadow(0 2px 4px rgba(0,0,0,0.2))'
                            }}
                          >
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
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
