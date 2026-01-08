import { useEffect, useState } from "react";
import api from "../api";

/* ================= DEFAULT MEASUREMENTS ================= */
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

export default function MeasurementsPage() {
  const [measurements, setMeasurements] = useState(DEFAULT_MEASUREMENTS);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  /* ================= LOAD USER MEASUREMENTS ================= */
  useEffect(() => {
    async function fetchUserMeasurements() {
      try {
        const res = await api.get("/user");

        setMeasurements({
          ...DEFAULT_MEASUREMENTS,
          ...(res.data?.measurements || {}),
        });
      } catch (err) {
        console.error("Failed to load measurements", err);
      }
    }

    fetchUserMeasurements();
  }, []);

  /* ================= HANDLERS ================= */
  const handleChange = (e) => {
    const { name, value } = e.target;
    setMeasurements((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    setMessage("");

    try {
      await api.put("/user", { measurements });
      setMessage("Measurements saved successfully!");
      setTimeout(() => (window.location.href = "/"), 1500);
    } catch (err) {
      console.error(err);
      setError("Failed to save measurements. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handleSkip = () => {
    window.location.href = "/";
  };

  const hasMeasurements = Object.values(measurements).some(Boolean);

  /* ================= RENDER ================= */
  return (
    <div className="measurements-page">
      <div className="page-header">
        <div className="header-icon">📏</div>
        <h2>
          Complete Your <span>Measurements</span>
        </h2>
        <p className="subtitle">Help us find the perfect fit for you</p>
      </div>

      {!hasMeasurements && (
        <div className="info-box">
          You haven’t added your measurements yet — fill them in to get better size
          recommendations 👗
        </div>
      )}

      {message && <div className="success">{message}</div>}
      {error && <div className="error">{error}</div>}

      <form className="measurements-container" onSubmit={handleSubmit}>
        {/* ================= BODY MEASUREMENTS ================= */}
        <div className="section">
          <h3 className="section-title">👤 Body Measurements</h3>

          <div className="grid">
            {[
              ["height_cm", "Height", "cm"],
              ["weight_kg", "Weight", "kg"],
              ["bust_cm", "Bust", "cm"],
              ["waist_cm", "Waist", "cm"],
              ["hips_cm", "Hips", "cm"],
              ["shoulder_width_cm", "Shoulder Width", "cm"],
              ["arm_length_cm", "Arm Length", "cm"],
              ["inseam_cm", "Inseam", "cm"],
            ].map(([key, label, unit]) => (
              <div className="input-group" key={key}>
                <label htmlFor={key}>{label}</label>
                <div className="input-with-unit">
                  <input
                    id={key}
                    name={key}
                    type="number"
                    step="0.1"
                    min="0"
                    value={measurements[key]}
                    onChange={handleChange}
                    placeholder="—"
                  />
                  <span className="unit">{unit}</span>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* ================= PREFERENCES ================= */}
        <div className="section">
          <h3 className="section-title">⚙️ Fit Preferences</h3>

          <div className="preferences-grid">
            <div className="input-group">
              <label htmlFor="body_shape">Body Shape</label>
              <select
                id="body_shape"
                name="body_shape"
                value={measurements.body_shape}
                onChange={handleChange}
              >
                <option value="">Select your body shape</option>
                <option value="hourglass">⏳ Hourglass</option>
                <option value="pear">🍐 Pear</option>
                <option value="apple">🍎 Apple</option>
                <option value="rectangle">▭ Rectangle</option>
                <option value="inverted_triangle">▽ Inverted Triangle</option>
              </select>
            </div>

            <div className="input-group">
              <label htmlFor="fit_preference">Fit Preference</label>
              <select
                id="fit_preference"
                name="fit_preference"
                value={measurements.fit_preference}
                onChange={handleChange}
              >
                <option value="">Select your fit preference</option>
                <option value="tight">✨ Tight Fit</option>
                <option value="regular">👕 Regular Fit</option>
                <option value="loose">🧥 Loose Fit</option>
              </select>
            </div>
          </div>
        </div>

        {/* ================= ACTIONS ================= */}
        <div className="action-buttons">
          <button type="submit" className="save-btn" disabled={loading}>
            {loading ? "Saving..." : "💾 Save Measurements"}
          </button>

          <button
            type="button"
            className="secondary-btn"
            onClick={handleSkip}
            disabled={loading}
          >
            Skip for now →
          </button>
        </div>
      </form>
    </div>
  );
}
