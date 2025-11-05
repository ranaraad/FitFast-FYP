import { useState, useEffect } from "react";
import api from "./api";

export default function ProfilePage() {
  const [user, setUser] = useState(null);
  const [editing, setEditing] = useState(false);
  const [measurements, setMeasurements] = useState({});
  const [message, setMessage] = useState("");

  useEffect(() => {
    async function fetchUser() {
      const res = await api.get("/user");
      setUser(res.data);
      setMeasurements(res.data.measurements || {});
    }
    fetchUser();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setMeasurements((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = async () => {
    await api.put("/user", { measurements });
    setMessage("Measurements updated!");
    setEditing(false);
  };

  async function handleLogout() {
    await api.post("/logout");
    localStorage.clear();
    window.location.href = "/login";
  }

  if (!user) return <p>Loading...</p>;

  return (
    <div className="auth-wrapper">
      <h2>
        Welcome, <span>{user.name}</span>
      </h2>

      <p>Email: {user.email}</p>

      <h3>Measurements</h3>

      {message && <p className="success">{message}</p>}

      {!editing ? (
        <>
          <ul>
            {Object.entries(measurements).length > 0 ? (
              Object.entries(measurements).map(([key, value]) => (
                <li key={key}>
                  <strong>{key.replace("_cm", "").replace("_", " ")}:</strong> {value || "-"}
                </li>
              ))
            ) : (
              <p>No measurements added yet.</p>
            )}
          </ul>
          <button onClick={() => setEditing(true)}>Edit Measurements</button>
        </>
      ) : (
        <form onSubmit={(e) => e.preventDefault()}>
          {Object.keys(measurements).map((key) => (
            <div key={key}>
              <label>{key.replace("_cm", "").replace("_", " ")}</label>
              <input name={key} value={measurements[key] || ""} onChange={handleChange} />
            </div>
          ))}
          <button onClick={handleSave}>Save</button>
          <button type="button" className="secondary-btn" onClick={() => setEditing(false)}>
            Cancel
          </button>
        </form>
      )}

      <button onClick={handleLogout} className="logout-btn">
        Logout
      </button>
    </div>
  );
}
