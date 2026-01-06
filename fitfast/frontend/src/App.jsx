import { Routes, Route, useLocation } from "react-router-dom";
import Navbar from "./components/layout/NavBar.jsx";
import RegisterPage from "./pages/RegisterPage.jsx";
import LoginPage from "./pages/LoginPage.jsx";
import ProfilePage from "./pages/ProfilePage.jsx";
import MeasurementsPage from "./pages/MeasurementsPage.jsx";
import HomePage from "./pages/HomePage.jsx";
import BrowseStoresPage from "./pages/BrowseStoresPage.jsx";
import StorePage from "./pages/StorePage.jsx";
import ProductDetailPage from "./pages/ProductDetailPage.jsx";
import SupportPage from "./pages/SupportPage.jsx";
import CartPage from "./pages/CartPage.jsx";
import CheckoutPage from "./pages/CheckoutPage.jsx";
import OrderStatusPage from "./pages/OrderStatusPage.jsx";

export default function App() {
  const location = useLocation();

  // These paths will NOT show the navbar
  const hideNavbarOn = ["/login", "/register"];
  const shouldHideNavbar = hideNavbarOn.includes(location.pathname);

  return (
    <>
      {!shouldHideNavbar && <Navbar />} {/* ✅ Conditionally show navbar */}

      <div style={{ marginTop: shouldHideNavbar ? "0" : "3.25rem" }}>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/profile" element={<ProfilePage />} />
          <Route path="/measurements" element={<MeasurementsPage />} />
          <Route path="/browse" element={<BrowseStoresPage />} />
          <Route path="/stores/:storeId" element={<StorePage />} />
          <Route path="/stores/:storeId/product/:productId" element={<ProductDetailPage />} />
          <Route path="/support" element={<SupportPage />} />
          <Route path="/cart" element={<CartPage />} />
          <Route path="/checkout" element={<CheckoutPage />} />
          <Route path="/order-status" element={<OrderStatusPage />} />
        </Routes>
      </div>
    </>
  );
}
