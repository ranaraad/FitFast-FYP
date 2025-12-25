import { useEffect, useState } from "react";
import api from "./api";

export default function HomePage() {
  const [stores, setStores] = useState([]);
  const [filteredStores, setFilteredStores] = useState([]);
  const [search, setSearch] = useState("");
  const [, setLoading] = useState(true);
  const [, setError] = useState("");

  function scrollCarousel(amount) {
  const carousel = document.getElementById("storeCarousel");
  if (carousel) {
    carousel.scrollBy({ left: amount, behavior: "smooth" });
  }
}


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
      {/* ================= HERO BANNER ================= */}
<div className="hero-banner">
  <div className="hero-left">
    <h1>
      Perfect Fit,<br />
      <span>Faster Delivery</span>
    </h1>
    <button>Browse Now</button>
  </div>
</div>

      {/* Hero / header section */}
      <section className="home-header">
        <div>

    

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
    <h2>Your Next Favorite Pieces!</h2>
    <button className="shop-btn browse-all-btn">Browse all →</button>

  </div>

  {/* Carousel container with arrows */}
  <div className="carousel-wrapper">
    {/* LEFT ARROW */}
    <button className="carousel-arrow left" onClick={() => scrollCarousel(-300)}>
      ❮
    </button>

    {/* CARDS */}
    <div className="store-carousel" id="storeCarousel">
      {filteredStores.map((store) => (
        <article key={store.id} className="store-card">
          <div className="store-banner">
            <img src={store.banner_url || "/placeholder-banner.png"} alt={store.name} />

            <div className="store-logo-circle">
              {store.logo_url ? (
                <img src={store.logo_url} alt="logo" />
              ) : (
                <span>{store.name?.[0]?.toUpperCase()}</span>
              )}
            </div>
          </div>

          <div className="store-info">
            <h3>{store.name}</h3>

            {store.description && (
              <p className="store-description">{store.description}</p>
            )}

            {store.categories && (
              <div className="store-categories">
                {store.categories.slice(0, 2).map((cat) => (
                  <span key={cat} className="category-chip">{cat}</span>
                ))}
                {store.categories.length > 2 && (
                  <span className="category-chip more">+{store.categories.length - 2}</span>
                )}
              </div>
            )}

            <button className="shop-btn">Shop Now</button>
          </div>
        </article>
      ))}
    </div>

    {/* RIGHT ARROW */}
    <button className="carousel-arrow right" onClick={() => scrollCarousel(300)}>
      ❯
    </button>
  </div>
</section>


    </div>
  );
}
