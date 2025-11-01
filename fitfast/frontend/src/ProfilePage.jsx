import { useState, useEffect } from "react";
import api from "./api";

export default function ProfilePage() {
  const [user, setUser] = useState(null);
  const [error, setError] = useState("");

  useEffect(() => {
    async function fetchUser() {
      try {
        const res = await api.get("/user");
        setUser(res.data);
      } catch (err) {
        console.log(err);
        setError("Not authenticated.");
      }
    }

    fetchUser();
  }, []);

  async function handleLogout() {
    try {
      await api.post("/logout");
    } catch (err) {
      console.log(err);
    } finally {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");
      window.location.href = "/login";
    }
  }

  if (error) {
    return <p style={{ color: "red" }}>{error}</p>;
  }

  if (!user) {
    return <p>Loading...</p>;
  }

  return (
    <div style={{ maxWidth: 400, margin: "2rem auto", fontFamily: "sans-serif" }}>
      <h2>Your Profile</h2>
      <p><strong>ID:</strong> {user.id}</p>
      <p><strong>Name:</strong> {user.name}</p>
      <p><strong>Email:</strong> {user.email}</p>
      <p><strong>Role ID:</strong> {user.role_id}</p>

      <button onClick={handleLogout} style={{ marginTop: "1rem" }}>
        Logout
      </button>
    </div>
  );
}
