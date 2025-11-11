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
      window.location.reload(); // ensures UI updates immediately
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
            <Link to="/profile">Profile</Link>
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
