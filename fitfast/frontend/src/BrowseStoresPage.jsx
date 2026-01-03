import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "./api";
import {
  getWishlist,
  isItemWishlisted,
  toggleWishlistEntry,
} from "./wishlistStorage";
import { addToCart } from "./cartStorage";

export default function BrowseStoresPage() {
  const navigate = useNavigate();
  const [storesWithItems, setStoresWithItems] = useState([]);
  const [search, setSearch] = useState("");
  const [filteredStores, setFilteredStores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [cartFeedback, setCartFeedback] = useState("");
  const [wishlistItems, setWishlistItems] = useState(() => getWishlist());

  useEffect(() => {
    async function fetchStoresAndItems() {
      try {
        const storesRes = await api.get("/stores");
        const stores = storesRes.data.data || [];
        
        // Fetch full store details with items for each store
        const storesWithItemsData = await Promise.all(
          stores.map(async (store) => {
            try {
              const storeDetailsRes = await api.get(`/stores/${store.id}`);
              const storeData = storeDetailsRes.data.data;
              
              // Extract all items from all categories
              const allItems = storeData.categories?.reduce((items, category) => {
                return items.concat(category.items || []);
              }, []) || [];
              
              return {
                ...store,
                items: allItems
              };
            } catch (err) {
              console.error(`Failed to fetch details for store ${store.id}`, err);
              return {
                ...store,
                items: []
              };
            }
          })
        );

        setStoresWithItems(storesWithItemsData);
        setFilteredStores(storesWithItemsData);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }

    fetchStoresAndItems();
  }, []);

  useEffect(() => {
    if (!cartFeedback) return;

    const timeout = setTimeout(() => setCartFeedback(""), 3200);
    return () => clearTimeout(timeout);
  }, [cartFeedback]);

  useEffect(() => {
    if (!search) {
      setFilteredStores(storesWithItems);
      return;
    }

    const searchLower = search.toLowerCase();
    const filtered = storesWithItems.filter((store) => {
      const name = store.name?.toLowerCase() || "";
      const desc = store.description?.toLowerCase() || "";
      return name.includes(searchLower) || desc.includes(searchLower);
    });

    setFilteredStores(filtered);
  }, [search, storesWithItems]);

  const getItemImage = (item) =>
    item.image_url ||
    item.image ||
    item.imagePath ||
    item.image_path ||
    item.primary_image_url ||
    item.primary_image?.image_path;

  const formatPrice = (price) => {
    if (!price && price !== 0) return "";
    const amount = Number(price);
    if (Number.isNaN(amount)) return price;
    return `$${amount.toFixed(2)}`;
  };

  const getBestFitCopy = (item) => {
    if (item.best_fit_match || item.best_fit_match === 0) {
      const value = Number(item.best_fit_match);
      if (!Number.isNaN(value)) {
        return `Best Fit: ${Math.round(value)}% Match!`;
      }
      return `Best Fit: ${item.best_fit_match}`;
    }
    if (item.best_fit_label) return item.best_fit_label;
    if (item.best_fit_description) return item.best_fit_description;
    return "Best Fit: Medium - 90% Match!";
  };

  const handleAddToCart = (e, store, item, itemImage) => {
    e.stopPropagation();
    const itemId = item.id || item.name;

    addToCart({
      id: itemId,
      storeId: store.id,
      name: item.name,
      price: item.price,
      image: itemImage ?? getItemImage(item),
      storeName: store.name,
      quantity: 1,
    });

    setCartFeedback(`${item.name || "Item"} added to cart`);
  };

  const handleToggleWishlist = (e, store, item, itemImage) => {
    e.stopPropagation();
    const itemId = item.id || item.name;

    const { items, added } = toggleWishlistEntry({
      id: itemId,
      storeId: store.id,
      name: item.name,
      price: item.price,
      image: itemImage ?? getItemImage(item),
      storeName: store.name,
    });

    setWishlistItems(items);
    setCartFeedback(added ? "Added to wishlist" : "Removed from wishlist");
  };

  return (
    <div className="browse-stores-page">
      {cartFeedback && <div className="cart-feedback">{cartFeedback}</div>}
      {/* Hero Section */}
      <section className="browse-hero">
        <div className="browse-hero-content">
          <h1>Discover Your Style</h1>
          <p className="browse-hero-subtitle">
            Explore our curated collection of premium fashion stores
          </p>
        </div>
      </section>

      {/* Search Bar */}
      <section className="browse-controls">
        <div className="browse-controls-container">
          <div className="browse-search-wrapper">
            <svg className="browse-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
            </svg>
            <input
              type="text"
              placeholder="Search stores..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="browse-search-input"
            />
          </div>

          <div className="browse-count">
            <span>{filteredStores.length} {filteredStores.length === 1 ? 'Store' : 'Stores'}</span>
          </div>
        </div>
      </section>

      {/* Stores Sections */}
      <section className="browse-stores-sections">
        {loading ? (
          <div className="browse-loading">
            <div className="loading-spinner"></div>
            <p>Loading stores...</p>
          </div>
        ) : filteredStores.length === 0 ? (
          <div className="browse-empty">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
              <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
            </svg>
            <h3>No stores found</h3>
            <p>Try adjusting your search</p>
          </div>
        ) : (
          filteredStores.map((store) => (
            <div key={store.id} className="browse-store-section">
              <div className="browse-section-header">
                <h2>Top {store.name} Picks For You!</h2>
                <button 
                  className="browse-view-all-btn"
                  onClick={() => navigate(`/stores/${store.id}`)}
                >
                  View All →
                </button>
              </div>

              {store.items.length > 0 ? (
                <div className="browse-carousel-wrapper carousel-wrapper">
                  <div className="browse-items-carousel" id={`store-carousel-${store.id}`}>
                    {store.items.slice(0, 10).map((item) => {
                      const itemId = item.id || item.name;
                      const isWishlisted = isItemWishlisted(
                        wishlistItems,
                        itemId,
                        store.id
                      );
                      const itemImage = getItemImage(item);
                      const bestFitCopy = getBestFitCopy(item);

                      return (
                        <article
                          key={itemId}
                          className="product-card-modern browse-carousel-card"
                          onClick={() => navigate(`/stores/${store.id}/product/${itemId}`)}
                        >
                          <div className="product-image-container">
                            {itemImage ? (
                              <img src={itemImage} alt={item.name} loading="lazy" />
                            ) : (
                              <div className="image-placeholder">
                                {item.name?.slice(0, 1) || ""}
                              </div>
                            )}

                            <button
                              type="button"
                              aria-label="Toggle wishlist"
                              onClick={(e) => handleToggleWishlist(e, store, item, itemImage)}
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

                            <div className="best-fit-badge">{bestFitCopy}</div>
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
                                onClick={(e) => handleAddToCart(e, store, item, itemImage)}
                              >
                                Add to Cart
                              </button>
                            </div>
                          </div>
                        </article>
                      );
                    })}
                  </div>
                </div>
              ) : (
                <div className="browse-no-items">
                  <p>No items available yet</p>
                </div>
              )}
            </div>
          ))
        )}
      </section>
    </div>
  );
}