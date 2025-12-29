import { Link, useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import api from "./api";

export default function Navbar() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem("auth_token");
    setIsLoggedIn(!!token);
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
      {/* Logo */}
      <div className="logo">
        Fit<span>Fast</span>
      </div>

      {/* Links only if logged in */}
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
