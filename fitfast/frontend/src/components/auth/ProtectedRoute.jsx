import { Navigate, useLocation } from "react-router-dom";

const isAuthenticated = () => Boolean(window.localStorage.getItem("auth_token"));

export default function ProtectedRoute({ children }) {
  const location = useLocation();

  if (!isAuthenticated()) {
    return <Navigate to="/error/401" replace state={{ from: location.pathname }} />;
  }

  return children;
}
