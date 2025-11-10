import { useState, useEffect } from "react";
import api from "./api";

export default function ProfilePage() {
  const [user, setUser] = useState(null);
  const [editing, setEditing] = useState(false);
  const [measurements, setMeasurements] = useState({});
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchUser() {
      try {
        const res = await api.get("/user");
        setUser(res.data);
        setMeasurements(res.data.measurements || {});
      } catch (error) {
        console.error("Failed to fetch user:", error);
      } finally {
        setLoading(false);
      }
    }
    fetchUser();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setMeasurements((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = async () => {
    try {
      await api.put("/user", { measurements });
      setMessage("Measurements updated successfully!");
      setEditing(false);
      setTimeout(() => setMessage(""), 3000);
    } catch {
      setMessage("Failed to update measurements. Please try again.");
    }
  };

  const handleCancel = () => {
    setMeasurements(user.measurements || {});
    setEditing(false);
    setMessage("");
  };

  async function handleLogout() {
    try {
      await api.post("/logout");
      localStorage.clear();
      window.location.href = "/login";
    } catch (error) {
      console.error("Logout failed:", error);
    }
  }

  const formatLabel = (key) => {
    return key
      .replace(/_cm/g, "")
      .replace(/_/g, " ")
      .split(" ")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ");
  };

  if (loading) {
    return (
      <div className="auth-wrapper">
        <div style={{ textAlign: "center", padding: "2rem" }}>
          <div className="spinner"></div>
          <p style={{ marginTop: "1rem", color: "#641b2e" }}>Loading your profile...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="auth-wrapper">
        <p className="error">Unable to load profile. Please try again.</p>
      </div>
    );
  }

  return (
    <div className="auth-wrapper profile-container">
      {/* Header Section */}
      <div className="profile-header">
        <div className="avatar-circle">
          {user.name.charAt(0).toUpperCase()}
        </div>
        <h2>
          Welcome back, <span>{user.name}</span>!
        </h2>
        <p className="profile-email">{user.email}</p>
      </div>

      {/* Message Display */}
      {message && (
        <div className={message.includes("success") ? "success" : "error"}>
          {message}
        </div>
      )}

      {/* Measurements Section */}
      <div className="measurements-section">
        <div className="section-header">
          <h3>Body Measurements</h3>
          {!editing && (
            <button 
              onClick={() => setEditing(true)} 
              className="edit-icon-btn"
              title="Edit measurements"
            >
              ✏️
            </button>
          )}
        </div>

        {!editing ? (
          <div className="measurements-display">
            {Object.entries(measurements).length > 0 ? (
              <ul className="measurements-list">
                {Object.entries(measurements).map(([key, value]) => (
                  <li key={key} className="measurement-item">
                    <span className="measurement-label">{formatLabel(key)}</span>
                    <span className="measurement-value">
                      {value ? `${value} cm` : "—"}
                    </span>
                  </li>
                ))}
              </ul>
            ) : (
              <div className="empty-state">
                <div className="empty-icon">📏</div>
                <p>No measurements added yet.</p>
                <p className="empty-hint">Click edit to add your measurements</p>
              </div>
            )}
          </div>
        ) : (
          <form className="measurements-form" onSubmit={(e) => e.preventDefault()}>
            <div className="form-grid">
              {Object.keys(measurements).length > 0 ? (
                Object.keys(measurements).map((key) => (
                  <div key={key} className="form-group">
                    <label htmlFor={key}>{formatLabel(key)}</label>
                    <div className="input-with-unit">
                      <input
                        id={key}
                        name={key}
                        type="number"
                        step="0.1"
                        value={measurements[key] || ""}
                        onChange={handleChange}
                        placeholder="0.0"
                      />
                      <span className="unit-label">cm</span>
                    </div>
                  </div>
                ))
              ) : (
                <p style={{ gridColumn: "1 / -1", textAlign: "center", color: "#888" }}>
                  No measurement fields available. Contact support to set up your profile.
                </p>
              )}
            </div>

            <div className="form-actions">
              <button type="button" onClick={handleSave} className="save-btn">
                💾 Save Changes
              </button>
              <button type="button" className="secondary-btn" onClick={handleCancel}>
                ✕ Cancel
              </button>
            </div>
          </form>
        )}
      </div>

      {/* Logout Button */}
      <button onClick={handleLogout} className="logout-btn">
        🚪 Logout
      </button>

      <style jsx>{`
        .profile-container {
          max-width: 600px;
        }

        .profile-header {
          text-align: center;
          margin-bottom: 2rem;
          padding-bottom: 1.5rem;
          border-bottom: 2px solid rgba(190, 91, 80, 0.15);
        }

        .avatar-circle {
          width: 80px;
          height: 80px;
          border-radius: 50%;
          background: linear-gradient(135deg, #641b2e 0%, #be5b50 100%);
          color: white;
          font-size: 2rem;
          font-weight: 700;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 auto 1rem;
          box-shadow: 0 4px 16px rgba(100, 27, 46, 0.25);
        }

        .profile-email {
          color: #888;
          font-size: 0.9rem;
          margin-top: 0.3rem;
        }

        .measurements-section {
          margin-top: 1.5rem;
        }

        .section-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 1rem;
        }

        .section-header h3 {
          margin: 0;
          border: none;
          padding: 0;
        }

        .edit-icon-btn {
          background: transparent;
          border: none;
          font-size: 1.2rem;
          cursor: pointer;
          padding: 0.5rem;
          border-radius: 8px;
          transition: all 0.2s ease;
          width: auto;
          margin: 0;
          box-shadow: none;
        }

        .edit-icon-btn:hover {
          background: rgba(190, 91, 80, 0.1);
          transform: scale(1.1);
        }

        .measurements-display {
          background: white;
          border-radius: 12px;
          padding: 1.5rem;
          border: 1px solid rgba(100, 27, 46, 0.1);
        }

        .measurements-list {
          list-style: none;
          padding: 0;
          margin: 0;
        }

        .measurement-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1rem;
          margin-bottom: 0.5rem;
          background: rgba(248, 238, 238, 0.5);
          border-radius: 10px;
          transition: all 0.3s ease;
          border: 1px solid transparent;
        }

        .measurement-item:hover {
          background: rgba(190, 91, 80, 0.08);
          border-color: rgba(190, 91, 80, 0.2);
          transform: translateX(4px);
        }

        .measurement-item:last-child {
          margin-bottom: 0;
        }

        .measurement-label {
          color: #641b2e;
          font-weight: 600;
        }

        .measurement-value {
          color: #be5b50;
          font-weight: 600;
          font-size: 1.1rem;
        }

        .empty-state {
          text-align: center;
          padding: 2rem 1rem;
          color: #888;
        }

        .empty-icon {
          font-size: 3rem;
          margin-bottom: 1rem;
          opacity: 0.6;
        }

        .empty-hint {
          font-size: 0.85rem;
          color: #aaa;
          margin-top: 0.5rem;
        }

        .measurements-form {
          background: white;
          border-radius: 12px;
          padding: 1.5rem;
          border: 1px solid rgba(100, 27, 46, 0.1);
        }

        .form-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 1.2rem;
          margin-bottom: 1.5rem;
        }

        .form-group {
          display: flex;
          flex-direction: column;
        }

        .input-with-unit {
          position: relative;
          display: flex;
          align-items: center;
        }

        .input-with-unit input {
          padding-right: 3rem;
        }

        .unit-label {
          position: absolute;
          right: 1rem;
          color: #888;
          font-size: 0.9rem;
          font-weight: 500;
          pointer-events: none;
        }

        .form-actions {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
          margin-top: 1.5rem;
        }

        .save-btn {
          background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
        }

        .save-btn:hover {
          background: linear-gradient(135deg, #43a047 0%, #66bb6a 100%);
        }

        .spinner {
          width: 40px;
          height: 40px;
          border: 4px solid rgba(190, 91, 80, 0.2);
          border-top-color: #be5b50;
          border-radius: 50%;
          animation: spin 1s linear infinite;
          margin: 0 auto;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
          .avatar-circle {
            width: 70px;
            height: 70px;
            font-size: 1.8rem;
          }

          .form-grid {
            grid-template-columns: 1fr;
          }

          .form-actions {
            grid-template-columns: 1fr;
          }
        }
      `}</style>
    </div>
  );
}