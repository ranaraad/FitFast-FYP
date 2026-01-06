import { useState } from "react";
import { Link } from "react-router-dom";
import api from "../api";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");

  async function handleLogin(event) {
    event.preventDefault();
    setError("");
    setMessage("");

    try {
      const res = await api.post("/login", { email, password });
      localStorage.setItem("auth_token", res.data.token);
      localStorage.setItem("auth_user", JSON.stringify(res.data.user));
      setMessage("Welcome back to FitFast!");
      setTimeout(() => {
        window.location.href = "/";
      }, 1200);
    } catch {
      setError("Invalid credentials. Please try again.");
    }
  }

  return (
    <>
      <div className="logo">
        Fit<span>Fast</span>
      </div>

      <div className="auth-wrapper">
        <h2>
          Log<span>In</span>
        </h2>

        {message && <p className="success">{message}</p>}
        {error && <p className="error">{error}</p>}

        <form onSubmit={handleLogin}>
          <div>
            <label>Email</label>
            <input
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="johndoe@email.com"
              required
            />
          </div>

          <div>
            <label>Password</label>
            <input
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="********"
              required
            />
          </div>

          <button type="submit">Login</button>
        </form>

        <p>
          Don’t have an account? <Link to="/register">Sign Up</Link>
        </p>
      </div>
    </>
  );
}
