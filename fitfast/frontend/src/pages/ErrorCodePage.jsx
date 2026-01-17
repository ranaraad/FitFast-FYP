import { useLocation, useNavigate } from "react-router-dom";

export default function ErrorCodePage() {
  const navigate = useNavigate();
  const location = useLocation();
  const statusCode = location.state?.statusCode || 401;

  return (
    <div className="error-code-page">
      <div className="error-code-card">
       
        <p className="error-code-status">{statusCode}</p>
        <h1>Oops,we ran into a problem</h1>
        <p className="error-code-body">Log in to continue, and we will bring you right back.</p>
        <button
          type="button"
          className="error-code-button"
          onClick={() => navigate("/login", { replace: true })}
        >
          Back to Login
        </button>
      </div>
    </div>
  );
}
