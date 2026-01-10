import { Link } from "react-router-dom";
import { useEffect, useState } from "react";
import { getCartCount } from "../../cartStorage";

export default function Navbar() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [cartCount, setCartCount] = useState(0);
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem("auth_token");
    setIsLoggedIn(Boolean(token));
  }, []);

  useEffect(() => {
    const syncCartCount = () => setCartCount(getCartCount());

    syncCartCount();
    window.addEventListener("cart-updated", syncCartCount);
    window.addEventListener("storage", syncCartCount);

    return () => {
      window.removeEventListener("cart-updated", syncCartCount);
      window.removeEventListener("storage", syncCartCount);
    };
  }, []);

  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth > 960) {
        setIsMenuOpen(false);
      }
    };

    window.addEventListener("resize", handleResize);
    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }, []);

  useEffect(() => {
    if (!isLoggedIn) {
      setIsMenuOpen(false);
    }
  }, [isLoggedIn]);

  const closeMenu = () => setIsMenuOpen(false);

  return (
    <nav className="navbar">
      <div className="logo">
        Fit<span>Fast</span>
      </div>

      {isLoggedIn && (
        <div className="nav-actions">
          <button
            type="button"
            className={`menu-toggle ${isMenuOpen ? "active" : ""}`}
            aria-label="Toggle navigation menu"
            aria-controls="primary-navigation"
            aria-expanded={isMenuOpen}
            onClick={() => setIsMenuOpen((prev) => !prev)}
          >
            <span />
          </button>

          <ul
            className={`nav-links ${isMenuOpen ? "open" : ""}`}
            id="primary-navigation"
          >
            <li>
              <Link to="/" onClick={closeMenu}>
                HomePage
              </Link>
            </li>

            <li>
              <Link to="/support" onClick={closeMenu}>
                Support and help
              </Link>
            </li>

            <li>
              <Link to="/profile" onClick={closeMenu}>
                Account
              </Link>
            </li>

            <li>
              <Link to="/cart" className="cart-icon" onClick={closeMenu}>
                🛒
                {cartCount > 0 && (
                  <span className="cart-count-badge">{cartCount}</span>
                )}
              </Link>
            </li>
          </ul>
        </div>
      )}
    </nav>
  );
}
