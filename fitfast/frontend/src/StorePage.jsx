import { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import api from "./api";

export default function StorePage() {
  const { storeId } = useParams();
  const [store, setStore] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedCategoryId, setSelectedCategoryId] = useState(null);

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

  return (
    <div className="store-page">
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
                {selectedCategory.items.map((item) => (
                  <article
                    key={item.id || item.name}
                    className="product-card"
                  >
                    <div className="product-image">
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
                    </div>

                    <div className="product-info">
                      <div className="product-top">
                        <h3>{item.name}</h3>
                        {item.price && (
                          <span className="price">${item.price}</span>
                        )}
                      </div>
                      {item.description && (
                        <p className="muted small">{item.description}</p>
                      )}
                    </div>
                  </article>
                ))}
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
