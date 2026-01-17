import { Routes, Route, useLocation } from "react-router-dom";
import { useEffect } from "react";
import Navbar from "./components/layout/NavBar.jsx";
import ProtectedRoute from "./components/auth/ProtectedRoute.jsx";
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
import ErrorCodePage from "./pages/ErrorCodePage.jsx";

export default function App() {
  const location = useLocation();

  // These paths will NOT show the navbar
  const hideNavbarOn = ["/login", "/register", "/error/401"];
  const shouldHideNavbar = hideNavbarOn.includes(location.pathname);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, [location.pathname]);

  return (
    <>
      {!shouldHideNavbar && <Navbar />}

      <div style={{ marginTop: shouldHideNavbar ? "0" : "3.25rem" }}>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/profile" element={<ProtectedRoute><ProfilePage /></ProtectedRoute>} />
          <Route path="/measurements" element={<ProtectedRoute><MeasurementsPage /></ProtectedRoute>} />
          <Route path="/browse" element={<BrowseStoresPage />} />
          <Route path="/stores/:storeId" element={<StorePage />} />
          <Route path="/stores/:storeId/product/:productId" element={<ProductDetailPage />} />
          <Route path="/support" element={<ProtectedRoute><SupportPage /></ProtectedRoute>} />
          <Route path="/cart" element={<ProtectedRoute><CartPage /></ProtectedRoute>} />
          <Route path="/checkout" element={<ProtectedRoute><CheckoutPage /></ProtectedRoute>} />
          <Route path="/order-status" element={<ProtectedRoute><OrderStatusPage /></ProtectedRoute>} />
          <Route path="/orders" element={<ProtectedRoute><OrderStatusPage /></ProtectedRoute>} />
          <Route path="/error/401" element={<ErrorCodePage />} />
        </Routes>
      </div>
    </>
  );
}
