import { useParams,useState,useEffect } from "react-router-dom";
import api from "./api";

export default function StorePage() {
  const { storeId } = useParams();
  const [store, setStore] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

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

  return (
    <div className="store-page">
      <header className="store-header">
        <h1>{store.name}</h1>
        {store.description && <p>{store.description}</p>}
      </header>

      {categories.length === 0 && <p>No categories available.</p>}

      {categories.map((category) => (
        <section
          key={category.id || category.name}
          className="store-category-section"
        >
          <h2>{category.name || "Category"}</h2>
          {category.items && category.items.length > 0 ? (
            <ul className="category-items">
              {category.items.map((item) => (
                <li key={item.id || item.name} className="category-item">
                  <div className="item-details">
                    <h3>{item.name}</h3>
                    {item.description && <p>{item.description}</p>}
                  </div>
                  {item.price && <span className="item-price">${item.price}</span>}
                </li>
              ))}
            </ul>
          ) : (
            <p className="empty-state">No items in this category yet.</p>
          )}
        </section>
      ))}
    </div>
  );
}
