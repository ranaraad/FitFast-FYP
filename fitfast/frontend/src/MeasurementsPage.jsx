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

  const handleChange = (e) => {
    const { name, value } = e.target;
    setMeasurements((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
    console.log("Sending measurements:", measurements);

      await api.put("/user", { measurements });
      setMessage("Measurements saved successfully!");
      setTimeout(() => (window.location.href = "/profile"), 1000);
    } catch (err) {
      console.error(err);
      setError("Failed to save measurements.");
    }
  };

  const handleSkip = () => {
    window.location.href = "/profile";
  };

  return (
    <div className="auth-wrapper">
      <h2>
        Add Your <span>Measurements</span>
      </h2>

      {message && <p className="success">{message}</p>}
      {error && <p className="error">{error}</p>}

      <form onSubmit={handleSubmit}>
        <div className="grid">
          <div>
            <label>Height (cm)</label>
            <input name="height_cm" value={measurements.height_cm} onChange={handleChange} />
          </div>
          <div>
            <label>Weight (kg)</label>
            <input name="weight_kg" value={measurements.weight_kg} onChange={handleChange} />
          </div>
          <div>
            <label>Bust (cm)</label>
            <input name="bust_cm" value={measurements.bust_cm} onChange={handleChange} />
          </div>
          <div>
            <label>Waist (cm)</label>
            <input name="waist_cm" value={measurements.waist_cm} onChange={handleChange} />
          </div>
          <div>
            <label>Hips (cm)</label>
            <input name="hips_cm" value={measurements.hips_cm} onChange={handleChange} />
          </div>
          <div>
            <label>Shoulder Width (cm)</label>
            <input name="shoulder_width_cm" value={measurements.shoulder_width_cm} onChange={handleChange} />
          </div>
          <div>
            <label>Arm Length (cm)</label>
            <input name="arm_length_cm" value={measurements.arm_length_cm} onChange={handleChange} />
          </div>
          <div>
            <label>Inseam (cm)</label>
            <input name="inseam_cm" value={measurements.inseam_cm} onChange={handleChange} />
          </div>
        </div>

        <div>
          <label>Body Shape</label>
          <select name="body_shape" value={measurements.body_shape} onChange={handleChange}>
            <option value="">Select shape</option>
            <option value="hourglass">Hourglass</option>
            <option value="pear">Pear</option>
            <option value="apple">Apple</option>
            <option value="rectangle">Rectangle</option>
            <option value="inverted triangle">Inverted Triangle</option>
          </select>
        </div>

        <div>
          <label>Fit Preference</label>
          <select name="fit_preference" value={measurements.fit_preference} onChange={handleChange}>
            <option value="">Select fit</option>
            <option value="tight">Tight</option>
            <option value="regular">Regular</option>
            <option value="loose">Loose</option>
          </select>
        </div>

        <button type="submit">Save Measurements</button>
        <button type="button" onClick={handleSkip} className="secondary-btn">
          Skip for now
        </button>
      </form>
    </div>
  );
}
