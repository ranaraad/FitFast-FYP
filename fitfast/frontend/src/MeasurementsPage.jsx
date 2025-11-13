import { useState } from "react";
import api from "./api";

export default function MeasurementsPage() {
  const [measurements, setMeasurements] = useState({
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
  });
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

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
      console.log("Sending measurements:", measurements);
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

  return (
    <div className="auth-wrapper measurements-page">
      <div className="page-header">
        <div className="header-icon">📏</div>
        <h2>
          Complete Your <span>Measurements</span>
        </h2>
        <p className="subtitle">Help us find the perfect fit for you</p>
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="error">{error}</div>}

      <div className="measurements-container">
        {/* Body Measurements Section */}
        <div className="section">
          <h3 className="section-title">
            <span className="section-icon">👤</span>
            Body Measurements
          </h3>
          <div className="grid">
            <div className="input-group">
              <label htmlFor="height_cm">Height</label>
              <div className="input-with-unit">
                <input
                  id="height_cm"
                  name="height_cm"
                  type="number"
                  step="0.1"
                  value={measurements.height_cm}
                  onChange={handleChange}
                  placeholder="170"
                />
                <span className="unit">cm</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="weight_kg">Weight</label>
              <div className="input-with-unit">
                <input
                  id="weight_kg"
                  name="weight_kg"
                  type="number"
                  step="0.1"
                  value={measurements.weight_kg}
                  onChange={handleChange}
                  placeholder="65"
                />
                <span className="unit">kg</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="bust_cm">Bust</label>
              <div className="input-with-unit">
                <input
                  id="bust_cm"
                  name="bust_cm"
                  type="number"
                  step="0.1"
                  value={measurements.bust_cm}
                  onChange={handleChange}
                  placeholder="90"
                />
                <span className="unit">cm</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="waist_cm">Waist</label>
              <div className="input-with-unit">
                <input
                  id="waist_cm"
                  name="waist_cm"
                  type="number"
                  step="0.1"
                  value={measurements.waist_cm}
                  onChange={handleChange}
                  placeholder="70"
                />
                <span className="unit">cm</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="hips_cm">Hips</label>
              <div className="input-with-unit">
                <input
                  id="hips_cm"
                  name="hips_cm"
                  type="number"
                  step="0.1"
                  value={measurements.hips_cm}
                  onChange={handleChange}
                  placeholder="95"
                />
                <span className="unit">cm</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="shoulder_width_cm">Shoulder Width</label>
              <div className="input-with-unit">
                <input
                  id="shoulder_width_cm"
                  name="shoulder_width_cm"
                  type="number"
                  step="0.1"
                  value={measurements.shoulder_width_cm}
                  onChange={handleChange}
                  placeholder="38"
                />
                <span className="unit">cm</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="arm_length_cm">Arm Length</label>
              <div className="input-with-unit">
                <input
                  id="arm_length_cm"
                  name="arm_length_cm"
                  type="number"
                  step="0.1"
                  value={measurements.arm_length_cm}
                  onChange={handleChange}
                  placeholder="58"
                />
                <span className="unit">cm</span>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="inseam_cm">Inseam</label>
              <div className="input-with-unit">
                <input
                  id="inseam_cm"
                  name="inseam_cm"
                  type="number"
                  step="0.1"
                  value={measurements.inseam_cm}
                  onChange={handleChange}
                  placeholder="75"
                />
                <span className="unit">cm</span>
              </div>
            </div>
          </div>
        </div>

        {/* Preferences Section */}
        <div className="section">
          <h3 className="section-title">
            <span className="section-icon">⚙️</span>
            Fit Preferences
          </h3>
          <div className="preferences-grid">
            <div className="input-group">
              <label htmlFor="body_shape">Body Shape</label>
              <div className="select-wrapper">
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
                  <option value="inverted triangle">▽ Inverted Triangle</option>
                </select>
              </div>
            </div>

            <div className="input-group">
              <label htmlFor="fit_preference">Fit Preference</label>
              <div className="select-wrapper">
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
        </div>

        {/* Action Buttons */}
        <div className="action-buttons">
          <button 
            type="button" 
            onClick={handleSubmit} 
            disabled={loading}
            className="save-btn"
          >
            {loading ? (
              <>
                <span className="spinner-small"></span> Saving...
              </>
            ) : (
              <>💾 Save Measurements</>
            )}
          </button>
          <button 
            type="button" 
            onClick={handleSkip} 
            className="secondary-btn"
            disabled={loading}
          >
            Skip for now →
          </button>
        </div>
      </div>

      <style jsx>{`
        .measurements-page {
          max-width: 700px;
        }

        .page-header {
          text-align: center;
          margin-bottom: 2rem;
          padding-bottom: 1.5rem;
          border-bottom: 2px solid rgba(190, 91, 80, 0.15);
        }

        .header-icon {
          font-size: 3rem;
          margin-bottom: 1rem;
          opacity: 0.8;
        }

        .subtitle {
          color: #888;
          font-size: 0.95rem;
          margin-top: 0.5rem;
        }

        .measurements-container {
          display: flex;
          flex-direction: column;
          gap: 2rem;
        }

        .section {
          background: white;
          border-radius: 12px;
          padding: 1.8rem;
          border: 1px solid rgba(100, 27, 46, 0.1);
          box-shadow: 0 2px 8px rgba(100, 27, 46, 0.05);
        }

        .section-title {
          font-size: 1.2rem;
          font-weight: 600;
          color: #641b2e;
          margin: 0 0 1.5rem 0;
          padding: 0;
          border: none;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }

        .section-icon {
          font-size: 1.3rem;
        }

        .grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
          gap: 1.2rem;
        }

        .preferences-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 1.2rem;
        }

        .input-group {
          display: flex;
          flex-direction: column;
        }

        .input-group label {
          margin-bottom: 0.4rem;
          font-size: 0.9rem;
          color: #641b2e;
          font-weight: 600;
        }

        .input-with-unit {
          position: relative;
          display: flex;
          align-items: center;
        }

        .input-with-unit input {
          padding-right: 3rem;
        }

        .unit {
          position: absolute;
          right: 1rem;
          color: #888;
          font-size: 0.9rem;
          font-weight: 500;
          pointer-events: none;
        }

        .select-wrapper {
          position: relative;
        }

        select {
          width: 100%;
          padding: 0.85rem 2.5rem 0.85rem 1.2rem;
          border-radius: 12px;
          border: 2px solid rgba(100, 27, 46, 0.12);
          background-color: white;
          font-size: 0.95rem;
          color: #333;
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          cursor: pointer;
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23641b2e' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 1rem center;
        }

        select:focus {
          outline: none;
          border-color: #be5b50;
          background-color: #fefefe;
          box-shadow: 
            0 0 0 4px rgba(190, 91, 80, 0.12),
            0 2px 8px rgba(190, 91, 80, 0.1);
        }

        select:hover:not(:focus) {
          border-color: rgba(100, 27, 46, 0.25);
        }

        .action-buttons {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1rem;
          margin-top: 1rem;
        }

        .save-btn {
          background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 0.5rem;
        }

        .save-btn:hover {
          background: linear-gradient(135deg, #43a047 0%, #66bb6a 100%);
        }

        .save-btn:disabled,
        .secondary-btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
          transform: none !important;
        }

        .spinner-small {
          width: 16px;
          height: 16px;
          border: 2px solid rgba(255, 255, 255, 0.3);
          border-top-color: white;
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
          display: inline-block;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
          .grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
          }

          .preferences-grid {
            grid-template-columns: 1fr;
          }
        }

        @media (max-width: 480px) {
          .measurements-page {
            padding: 1.8rem 1.6rem;
          }

          .section {
            padding: 1.5rem;
          }

          .grid {
            grid-template-columns: 1fr;
          }

          .action-buttons {
            grid-template-columns: 1fr;
          }

          .header-icon {
            font-size: 2.5rem;
          }
        }
      `}</style>
    </div>
  );
}