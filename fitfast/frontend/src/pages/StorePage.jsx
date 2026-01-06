import { useState, useEffect } from "react";
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

  const handleItemClick = (item) => {
    const itemId = getItemId(item) ?? item.name;

    navigate(`/stores/${storeId}/product/${itemId}`);
  };

  const handleAddToCart = (item) => {
    const itemId = getItemId(item) ?? item.name;

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
              <div className="empty-state card">No items in this category yet.</div>
            )}
          </section>
        </>
      )}
    </div>
  );
}
