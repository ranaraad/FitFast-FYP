import { useState } from "react";
import api from "./api";
import { Link } from "react-router-dom";

export default function RegisterPage() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");

  async function handleRegister(e) {
    e.preventDefault();
    setError("");
    setMessage("");

    try {
      const res = await api.post("/register", {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      localStorage.setItem("auth_token", res.data.token);
      localStorage.setItem("auth_user", JSON.stringify(res.data.user));
      setMessage("Welcome to FitFast, " + res.data.user.name + "!");
      setTimeout(() => (window.location.href = "/measurements"), 1200);
    } catch {
      setError("Registration failed. Please check your details.");
    }
  }

  return (
    <>
      <div className="logo">
        Fit<span>Fast</span>
      </div>

      <div className="auth-wrapper">
        <h2>
          Sign<span>Up</span>
        </h2>

        {message && <p className="success">{message}</p>}
        {error && <p className="error">{error}</p>}

        <form onSubmit={handleRegister}>
          <div>
            <label>Full Name</label>
            <input
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="John Doe"
              required
            />
          </div>

          <div>
            <label>Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="johndoe@email.com"
              required
            />
          </div>

          <div>
            <label>Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="********"
              required
            />
          </div>

          <div>
            <label>Confirm Password</label>
            <input
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              placeholder="********"
              required
            />
          </div>

          <button type="submit">Register</button>
        </form>

        <p>
          Already have an account? <Link to="/login">Log In</Link>
        </p>
      </div>
    </>
  );
}
