import { Link, useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import api from "../../api";
import { getCartCount } from "../../cartStorage";

export default function Navbar() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [cartCount, setCartCount] = useState(0);
  const navigate = useNavigate();

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

  const handleLogout = async () => {
    try {
      await api.post("/logout");
    } catch (err) {
      console.error(err);
    } finally {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");
      navigate("/login");
      window.location.reload();
    }
  };

  return (
    <nav className="navbar">
      <div className="logo">
        Fit<span>Fast</span>
      </div>

      {isLoggedIn && (
        <ul className="nav-links">
          <li>
            <Link to="/">HomePage</Link>
          </li>

          <li>
            <Link to="/support">Support and help</Link>
          </li>

          <li>
            <Link to="/profile">Account</Link>
          </li>

          <li>
            <Link to="/cart" className="cart-icon">
              🛒
              {cartCount > 0 && (
                <span className="cart-count-badge">{cartCount}</span>
              )}
            </Link>
          </li>

          <li>
            <button onClick={handleLogout} className="logout-btn">
              Logout
            </button>
          </li>
        </ul>
      )}
    </nav>
  );
}
