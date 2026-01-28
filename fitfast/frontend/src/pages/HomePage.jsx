import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api";
import heroBannerImage from "../imgs/courier-delivery-man-holding-parcel-box-with-mobile-phone-fast-online-delivery-service-online-ordering-internet-e-commerce-ideas-for-websites-or-banners-3d-perspecti.webp";

export default function HomePage() {
  const navigate = useNavigate();
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

  function handleSearch(event) {
    const value = event.target.value.toLowerCase();
    setSearch(value);

    if (!value) {
      setFilteredStores(stores);
      return;
    }

    const filtered = stores.filter((store) => {
      const name = store.name?.toLowerCase() || "";
      const desc = store.description?.toLowerCase() || "";
      const categoryMatch = Array.isArray(store.categories)
        ? store.categories.some((category) =>
            typeof category === "string"
              ? category.toLowerCase().includes(value)
              : (category?.name ?? "").toLowerCase().includes(value)
          )
        : false;

      return name.includes(value) || desc.includes(value) || categoryMatch;
    });

    setFilteredStores(filtered);
  }

  return (
    <div className="home-page">
      <div className="hero-banner">
        <div className="hero-left">
          <h1>
            Perfect Fit,
            <br />
            <span>Faster Delivery</span>
          </h1>

          <div className="hero-actions">
            <button
              className="shop-btn hero-primary-btn"
              onClick={() => navigate("/browse")}
            >
              View Curated Stores
            </button>

          </div>
          <div className="hero-stats">
            <div>
              <strong>150+</strong>
              <span>independent boutiques</span>
            </div>
            <div>
              <strong>8K+</strong>
              <span>orders fulfilled</span>
            </div>
          </div>
        </div>
        <div className="hero-right">
          <img
            src={heroBannerImage}
            alt="Courier delivering a package"
            className="hero-banner-image"
          />
        </div>
      </div>

      {/* Redesigned search experience */}
      <div className="home-search-row">
        <div className="home-search-card">
          <div className="home-search-heading">
            <p>Discover the perfect store for your next look</p>
          </div>
          <div className="home-search-input-wrapper">
            <span className="search-icon" role="img" aria-label="Search icon">
              🔍
            </span>
            <input
              type="text"
              value={search}
              onChange={handleSearch}
              placeholder="Search for a store..."
              className="home-search-input"
            />

          </div>

        </div>
      </div>

      <section className="home-stores-section">
        <div className="home-stores-header">
          <h2>Your Next Favorite Piece Is One Click Away!</h2>
          <button
            className="shop-btn browse-all-btn"
            onClick={() => navigate("/browse")}
          >
            Browse all →
          </button>
        </div>

        <div className="carousel-wrapper">
          <button
            className="carousel-arrow left"
            onClick={() => scrollCarousel(-300)}
          >
            ❮
          </button>

          <div className="store-carousel" id="storeCarousel">
            {filteredStores.map((store) => (
              <article key={store.id} className="store-card">
                <div className="store-banner">
                  <img
                    src={store.banner_url || "/placeholder-banner.png"}
                    alt={store.name}
                  />

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
                      {store.categories.slice(0, 2).map((category) => (
                        <span key={category} className="category-chip">
                          {category}
                        </span>
                      ))}
                      {store.categories.length > 2 && (
                        <span className="category-chip more">
                          +{store.categories.length - 2}
                        </span>
                      )}
                    </div>
                  )}

                  <button
                    className="shop-btn"
                    onClick={() => store.id && navigate(`/stores/${store.id}`)}
                  >
                    Shop Now
                  </button>
                </div>
              </article>
            ))}
          </div>

          <button
            className="carousel-arrow right"
            onClick={() => scrollCarousel(300)}
          >
            ❯
          </button>
        </div>
      </section>
    </div>
  );
}
