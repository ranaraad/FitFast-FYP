import { Routes, Route, Link } from "react-router-dom";
import RegisterPage from "./RegisterPage.jsx";
import LoginPage from "./LoginPage.jsx";
import ProfilePage from "./ProfilePage.jsx";
import MeasurementsPage from "./MeasurementsPage.jsx";

export default function App() {
  return (
    <div style={{ fontFamily: "sans-serif", padding: "1rem" }}>
      <nav style={{ display: "flex", gap: "1rem", marginBottom: "1rem" }}>
        <Link to="/register">Register</Link>
        <Link to="/login">Login</Link>
        <Link to="/profile">Profile</Link>
      </nav>

      <Routes>
        <Route path="/" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/profile" element={<ProfilePage />} />
        <Route path="/measurements" element={<MeasurementsPage />} />
      </Routes>
    </div>
  );
}
