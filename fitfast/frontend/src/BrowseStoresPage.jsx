import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "./api";
import {
  getWishlist,
  isItemWishlisted,
  toggleWishlistEntry,
} from "./wishlistStorage";
import { addToCart } from "./cartStorage";
import ItemCard from "./components/cards/ItemCard";
import {
  getItemId,
  getItemImage,
  getBestFitCopy,
} from "./utils/item";

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

  const handleAddToCart = (store, item) => {
    const itemId = getItemId(item) ?? item.name;

    addToCart({
      id: itemId,
      storeId: store.id,
      name: item.name,
      price: item.price,
      image: getItemImage(item),
      storeName: store.name,
      quantity: 1,
    });

    setCartFeedback(`${item.name || "Item"} added to cart`);
  };

  const handleToggleWishlist = (store, item) => {
    const itemId = getItemId(item) ?? item.name;

    const { items, added } = toggleWishlistEntry({
      id: itemId,
      storeId: store.id,
      name: item.name,
      price: item.price,
      image: getItemImage(item),
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
                      const itemId = getItemId(item) ?? item.name;
                      const wishlisted = isItemWishlisted(
                        wishlistItems,
                        itemId,
                        store.id
                      );

                      return (
                        <ItemCard
                          key={itemId}
                          item={item}
                          badgeContent={getBestFitCopy(item)}
                          wishlisted={wishlisted}
                          className="browse-carousel-card"
                          onClick={(cardItem) =>
                            navigate(`/stores/${store.id}/product/${getItemId(cardItem) ?? cardItem.name}`)
                          }
                          onAddToCart={(cardItem) => handleAddToCart(store, cardItem)}
                          onWishlistToggle={(cardItem) => handleToggleWishlist(store, cardItem)}
                        />
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