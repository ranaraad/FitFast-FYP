import { useState, useEffect } from "react";
import api from "./api";

/**
 * Use the SAME schema as MeasurementsPage
 * so values show correctly everywhere.
 */
const DEFAULT_MEASUREMENTS = {
  height_cm: "",
  weight_kg: "",
  bust_cm: "",
  waist_cm: "",
  hips_cm: "",
  shoulder_width_cm: "",
  arm_length_cm: "",
  inseam_cm: "",
  body_shape: "",
  fit_preference: "",
};

export default function ProfilePage() {
  const [user, setUser] = useState(null);
  const [editing, setEditing] = useState(false);
  const [measurements, setMeasurements] = useState(DEFAULT_MEASUREMENTS);
  const [message, setMessage] = useState("");
  const [messageType, setMessageType] = useState("success"); // "success" | "error"
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchUser() {
      try {
        const res = await api.get("/user");
        setUser(res.data);
        setMeasurements({
          ...DEFAULT_MEASUREMENTS,
          ...(res.data?.measurements || {}),
        });
      } catch (error) {
        console.error(error);
        setMessageType("error");
        setMessage("Failed to load profile ❌");
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
      // Match MeasurementsPage update style
      await api.put("/user", { measurements });
      setMessageType("success");
      setMessage("Measurements saved successfully ✅");
      setEditing(false);
    } catch (e) {
      console.error(e);
      setMessageType("error");
      setMessage("Failed to save measurements ❌");
    }
    setTimeout(() => setMessage(""), 3000);
  };

  const handleCancel = () => {
    setMeasurements({
      ...DEFAULT_MEASUREMENTS,
      ...(user?.measurements || {}),
    });
    setEditing(false);
  };

  const formatLabel = (key) =>
    key
      .replace("_cm", "")
      .replace("_kg", "")
      .replace(/_/g, " ")
      .replace(/\b\w/g, (l) => l.toUpperCase());

  const initials = user?.name
    ? user.name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((p) => p[0].toUpperCase())
        .join("")
    : "U";

  const hasMeasurements = Object.values(measurements).some(Boolean);

  if (loading) return <p style={{ textAlign: "center" }}>Loading profile...</p>;
  if (!user) return <p style={{ textAlign: "center" }}>No user data.</p>;

  return (
    <div className="profile-page">
      <div className="profile-card">
        {/* Header */}
        <div className="profile-header">
          <div className="avatar-circle">{initials}</div>
          <div className="profile-header-text">
            <h2 className="profile-title">Welcome, {user.name}</h2>
            <p className="profile-email">{user.email}</p>
          </div>
        </div>

        {message && (
          <div className={messageType === "error" ? "error" : "success"}>
            {message}
          </div>
        )}

        {/* Measurements */}
        <div className="profile-section">
          <div className="section-header">
            <h3>Body Measurements</h3>

            {!editing && (
              <button
                className="edit-icon-btn"
                onClick={() => setEditing(true)}
                aria-label="Edit measurements"
                title="Edit measurements"
              >
                ✏️
              </button>
            )}
          </div>

          {!editing ? (
            <div className="measurements-display">
              {!hasMeasurements ? (
                <div className="empty-state">
                  <div className="empty-icon">📏</div>
                  <div>No measurements yet.</div>
                  <div className="empty-hint">
                    Add them to get better size recommendations.
                  </div>
                </div>
              ) : (
                <ul className="measurements-list">
                  {Object.entries(measurements).map(([key, value]) => (
                    <li className="measurement-item" key={key}>
                      <span className="measurement-label">{formatLabel(key)}</span>
                      <span className="measurement-value">{value ? value : "—"}</span>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          ) : (
            <div className="measurements-form">
              <div className="form-grid">
                {Object.entries(measurements).map(([key, value]) => {
                  const isSelect = key === "body_shape" || key === "fit_preference";
                  return (
                    <div className="form-group" key={key}>
                      <label htmlFor={key}>{formatLabel(key)}</label>

                      {isSelect ? (
                        <select
                          id={key}
                          name={key}
                          value={value}
                          onChange={handleChange}
                        >
                          <option value="">—</option>
                          {key === "body_shape" && (
                            <>
                              <option value="hourglass">Hourglass</option>
                              <option value="pear">Pear</option>
                              <option value="apple">Apple</option>
                              <option value="rectangle">Rectangle</option>
                              <option value="inverted_triangle">
                                Inverted Triangle
                              </option>
                            </>
                          )}
                          {key === "fit_preference" && (
                            <>
                              <option value="tight">Tight</option>
                              <option value="regular">Regular</option>
                              <option value="loose">Loose</option>
                            </>
                          )}
                        </select>
                      ) : (
                        <div className="input-with-unit">
                          <input
                            id={key}
                            type="number"
                            step="0.1"
                            name={key}
                            value={value}
                            onChange={handleChange}
                            placeholder="—"
                          />
                          {(key.endsWith("_cm") || key.endsWith("_kg")) && (
                            <span className="unit-label">
                              {key.endsWith("_cm") ? "cm" : "kg"}
                            </span>
                          )}
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>

              <div className="form-actions">
                <button className="save-btn" onClick={handleSave}>
                  💾 Save
                </button>
                <button className="secondary-btn" onClick={handleCancel}>
                  Cancel
                </button>
              </div>
            </div>
          )}
        </div>

     
      </div>


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