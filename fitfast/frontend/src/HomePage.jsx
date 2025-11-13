import { useEffect, useState } from "react";
import api from "./api";

export default function HomePage() {
  const [stores, setStores] = useState([]);
  const [filteredStores, setFilteredStores] = useState([]);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    async function fetchStores() {
      try {
        const res = await api.get("/stores");
        setStores(res.data.data || []);
        setFilteredStores(res.data.data || []);
      } catch (err) {
        console.error(err);
        setError("Failed to load stores. Please try again.");
      } finally {
        setLoading(false);
      }
    }

    fetchStores();
  }, []);

  function handleSearch(e) {
    const value = e.target.value.toLowerCase();
    setSearch(value);

    if (!value) {
      setFilteredStores(stores);
      return;
    }

    const filtered = stores.filter((store) => {
      const name = store.name?.toLowerCase() || "";
      const desc = store.description?.toLowerCase() || "";
      return name.includes(value) || desc.includes(value);
    });

    setFilteredStores(filtered);
  }

  return (
    <div className="home-page">
      {/* Hero / header section */}
      <section className="home-header">
        <div>
          <h1>
            Discover your <span>perfect fit</span>,
            <br />
            from curated multi-brand stores.
          </h1>
          <p>
            Browse hand-picked clothing stores, compare fits, and let FitFast
            help you shop smarter based on your style and measurements.
          </p>

          <div className="home-search-row">
            <input
              type="text"
              value={search}
              onChange={handleSearch}
              placeholder="Search for a store, style, or brand..."
              className="home-search-input"
            />
          </div>

          <div className="chip-row">
            <button
              type="button"
              className="chip"
              onClick={() => setFilteredStores(stores)}
            >
              All
            </button>
            <button
              type="button"
              className="chip"
              onClick={() => {
                const topRated = [...stores].sort(
                  (a, b) => (b.rating || 0) - (a.rating || 0)
                );
                setFilteredStores(topRated);
              }}
            >
              Top Rated
            </button>
            <button
              type="button"
              className="chip"
              onClick={() => {
                const fast = stores.filter(
                  (s) => s.eta_minutes && s.eta_minutes <= 30
                );
                setFilteredStores(fast);
              }}
            >
              Fast Delivery
            </button>
          </div>
        </div>
      </section>

      {/* Stores section */}
      <section className="home-stores-section">
        <div className="home-stores-header">
          <h2>Stores</h2>
          <span>
            {filteredStores.length} store{filteredStores.length !== 1 && "s"}{" "}
            available
          </span>
        </div>

        {loading && <p className="home-muted">Loading stores...</p>}
        {error && <p className="error">{error}</p>}

        {!loading && !error && filteredStores.length === 0 && (
          <p className="home-muted">
            No stores match your search. Try a different keyword.
          </p>
        )}

        <div className="store-grid">
          {filteredStores.map((store) => (
            <article key={store.id} className="store-card">
              <div className="store-banner">
                {store.banner_url ? (
                  <img src={store.banner_url} alt={store.name} />
                ) : (
                  <div className="store-banner-placeholder" />
                )}
                <div className="store-logo-circle">
                  {store.logo_url ? (
                    <img src={store.logo_url} alt={`${store.name} logo`} />
                  ) : (
                    <span className="store-logo-letter">
                      {store.name?.[0]?.toUpperCase() || "F"}
                    </span>
                  )}
                </div>
              </div>

              <div className="store-content">
                <h3>{store.name}</h3>
                {store.description && (
                  <p className="store-description">{store.description}</p>
                )}

                <div className="store-meta-row">
                  {store.rating && (
                    <span className="store-pill">
                      ⭐ {store.rating.toFixed ? store.rating.toFixed(1) : store.rating}
                    </span>
                  )}
                  {store.eta_minutes && (
                    <span className="store-pill">{store.eta_minutes} min</span>
                  )}
                  {store.delivery_fee !== null &&
                    store.delivery_fee !== undefined && (
                      <span className="store-pill">
                        {store.delivery_fee === 0
                          ? "Free delivery"
                          : `${store.delivery_fee}$ delivery`}
                      </span>
                    )}
                </div>

                {store.categories && store.categories.length > 0 && (
                  <div className="store-categories">
                    {store.categories.slice(0, 3).map((cat) => (
                      <span key={cat} className="store-category-chip">
                        {cat}
                      </span>
                    ))}
                    {store.categories.length > 3 && (
                      <span className="store-category-chip more">
                        +{store.categories.length - 3} more
                      </span>
                    )}
                  </div>
                )}

                <button
                  type="button"
                  className="store-cta"
                  // later this will go to /stores/:id
                  onClick={() => alert("Open store details coming soon ✨")}
                >
                  View store
                </button>
              </div>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
