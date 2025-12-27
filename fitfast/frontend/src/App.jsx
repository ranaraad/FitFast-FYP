import { Routes, Route, useLocation } from "react-router-dom";
import Navbar from "./NavBar.jsx";
import RegisterPage from "./RegisterPage.jsx";
import LoginPage from "./LoginPage.jsx";
import ProfilePage from "./ProfilePage.jsx";
import MeasurementsPage from "./MeasurementsPage.jsx";
import HomePage from "./HomePage.jsx";
import StorePage from "./StorePage.jsx";

export default function App() {
  const location = useLocation();

  // These paths will NOT show the navbar
  const hideNavbarOn = ["/login", "/register"];
  const shouldHideNavbar = hideNavbarOn.includes(location.pathname);

  return (
    <>
      {!shouldHideNavbar && <Navbar />} {/* ✅ Conditionally show navbar */}

      <div style={{ marginTop: shouldHideNavbar ? "0" : "5rem" }}>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/profile" element={<ProfilePage />} />
          <Route path="/measurements" element={<MeasurementsPage />} />
          <Route path="/stores/:storeId" element={<StorePage />} />
          <Route path="/stores/:storeId/product/:productId" element={<ProductDetailPage />} />
        </Routes>
      </div>
    </>
  );
}
