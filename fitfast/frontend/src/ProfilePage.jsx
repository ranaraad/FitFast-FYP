import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import api from "./api";
import {
  getWishlist,
  toggleWishlistEntry,
} from "./wishlistStorage";

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
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [editing, setEditing] = useState(false);
  const [measurements, setMeasurements] = useState(DEFAULT_MEASUREMENTS);
  const [message, setMessage] = useState("");
  const [messageType, setMessageType] = useState("success"); // "success" | "error"
  const [loading, setLoading] = useState(true);
  const [wishlistItems, setWishlistItems] = useState([]);

  useEffect(() => {
    async function fetchUser() {
      try {
        const res = await api.get("/user");
        setUser(res.data);
        setMeasurements({
          ...DEFAULT_MEASUREMENTS,
          ...(res.data?.measurements || {}),
        });
        setWishlistItems(getWishlist());
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

  useEffect(() => {
    const syncWishlist = () => setWishlistItems(getWishlist());
    window.addEventListener("storage", syncWishlist);
    return () => window.removeEventListener("storage", syncWishlist);
  }, []);


  const handleChange = (e) => {
    const { name, value } = e.target;
    setMeasurements((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = async () => {
    try {
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

  const formatPrice = (price) => {
    if (price === null || price === undefined || price === "") return null;
    const value = Number(price);
    if (Number.isNaN(value)) return price;
    return `$${value.toFixed(2)}`;
  };

  const statusClassName = (status = "") =>
    `status-badge status-${status.toLowerCase().replace(/[^a-z]+/g, "-")}`;

  const defaultOrders = [
    {
      id: "FF-10294",
      date: "Nov 11, 2025",
      total: 148.9,
      items: 3,
      status: "Processing",
      trackable: true,
    },
    {
      id: "FF-10172",
      date: "Oct 26, 2025",
      total: 212.4,
      items: 2,
      status: "Shipped",
      trackable: true,
    },
    {
      id: "FF-09788",
      date: "Sep 14, 2025",
      total: 98.5,
      items: 1,
      status: "Delivered",
      trackable: false,
    },
  ];

  const orders = Array.isArray(user?.orders) && user.orders.length ? user.orders : defaultOrders;
  const hasOrders = orders.length > 0;

  const handleLogout = () => navigate("/logout");
  const handleChangePassword = () => navigate("/account/security");
  const handleDeleteAccount = () => navigate("/support/delete-account");
  const handleManageBilling = () => navigate("/account/billing");


  const initials = user?.name
    ? user.name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((p) => p[0].toUpperCase())
        .join("")
    : "U";

  const hasMeasurements = Object.values(measurements).some(Boolean);
  const hasWishlist = wishlistItems.length > 0;

  const handleToggleWishlist = (item) => {
    const { items } = toggleWishlistEntry({
      id: item.id,
      storeId: item.storeId,
      name: item.name,
      price: item.price,
      image: item.image,
      storeName: item.storeName,
    });
    setWishlistItems(items);
    setMessageType("success");
    setMessage(`${item.name || "Item"} removed from wishlist`);
    setTimeout(() => setMessage(""), 2000);
  };

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
                type="button"
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
                        <div className="input-with-unit profile-input-unit">
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
                <button type="button" className="save-btn" onClick={handleSave}>
                  💾 Save
                </button>
                <button type="button" className="secondary-btn" onClick={handleCancel}>
                  Cancel
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      <div className="profile-section">
        <div className="section-header">
          <h3>Order History</h3>
          {hasOrders && <span className="pill-count">{orders.length} orders</span>}
        </div>

        {!hasOrders ? (
          <div className="measurements-display">
            <div className="empty-state">
              <div className="empty-icon">🛍️</div>
              <div>No orders yet.</div>
              <div className="empty-hint">When you shop, your order timeline will appear here.</div>
            </div>
          </div>
        ) : (
          <div className="orders-timeline">
            {orders.map((order) => (
              <div className="order-card" key={order.id}>
                <div className="order-row">
                  <div>
                    <p className="order-id">{order.id}</p>
                    <p className="order-details">{order.items} items • {order.date}</p>
                  </div>
                  <div className="order-status-stack">
                    <span className={statusClassName(order.status)}>{order.status}</span>
                    <span className="order-total">{formatPrice(order.total)}</span>
                  </div>
                </div>

                <div className="order-actions">
                  {order.trackable && (
                    <button
                      type="button"
                      className="secondary-btn small"
                      onClick={() => navigate(`/orders/${order.id}/track`)}
                    >
                      Track order
                    </button>
                  )}
                  <button
                    type="button"
                    className="ghost-btn small"
                    onClick={() => navigate(`/orders/${order.id}`)}
                  >
                    View details
                  </button>
                  <button
                    type="button"
                    className="ghost-btn small"
                    onClick={() => navigate(`/support`) }
                  >
                    Get support
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="profile-section">
        <div className="section-header">
          <h3>Account Settings</h3>
        </div>

        <div className="settings-grid">
          <div className="setting-card">
            <div>
              <p className="setting-title">Security</p>
              <p className="setting-description">Update your password to keep your account protected.</p>
            </div>
            <button type="button" className="secondary-btn" onClick={handleChangePassword}>
              Change password
            </button>
          </div>
          <div className="setting-card">
            <div>
              <p className="setting-title">Billing</p>
              <p className="setting-description">Manage your saved cards and billing contacts.</p>
            </div>
            <button type="button" className="ghost-btn" onClick={handleManageBilling}>
              Manage billing
            </button>
          </div>
          <div className="setting-card critical">
            <div>
              <p className="setting-title">Delete account</p>
              <p className="setting-description">Remove your profile permanently, including orders and measurements.</p>
            </div>
            <button type="button" className="danger-btn" onClick={handleDeleteAccount}>
              Delete account
            </button>
          </div>
        </div>

        <div className="logout-card">
          <div className="logout-copy">
            <p className="logout-title">Ready to head out?</p>
            <p className="logout-description">Sign out safely — we’ll be here when you return.</p>
          </div>
          <button type="button" className="primary-btn" onClick={handleLogout}>
            Log out
          </button>
        </div>
      </div>

      <div className="profile-section">
        <div className="section-header wishlist-header">
          <h3>Wishlist</h3>
          {hasWishlist && (
            <span className="pill-count">{wishlistItems.length} saved</span>
          )}
        </div>

        {!hasWishlist ? (
          <div className="measurements-display">
            <div className="empty-state">
              <div className="empty-icon">💖</div>
              <div>No wishlist items yet.</div>
              <div className="empty-hint">
                Add favorites while browsing stores to see them here.
              </div>
            </div>
          </div>
        ) : (
          <div className="wishlist-grid">
            {wishlistItems.map((item) => {
              const key = `${item.storeId}-${item.id}`;
              const displayPrice = formatPrice(item.price);

              return (
                <div className="wishlist-card" key={key}>
                  <div 
                    className="wishlist-clickable"
                    onClick={() => navigate(`/product/${item.storeId}/${item.id}`)}
                    style={{ cursor: 'pointer' }}
                  >
                    <div className="wishlist-media">
                      {item.image ? (
                        <img src={item.image} alt={item.name || "Wishlist item"} />
                      ) : (
                        <div className="image-placeholder">{item.name?.[0] || ""}</div>
                      )}
                    </div>
                    <div className="wishlist-info">
                      <p className="wishlist-name">{item.name || "Saved item"}</p>
                      <p className="wishlist-meta">
                        {item.storeName ? `${item.storeName} • ` : ""}
                        {displayPrice || "Price pending"}
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    className="wishlist-heart-btn filled"
                    aria-label="Remove from wishlist"
                    title="Remove from wishlist"
                    onClick={() => handleToggleWishlist(item)}
                  >
                    <svg 
                      width="20" 
                      height="20" 
                      viewBox="0 0 24 24" 
                      fill="currentColor"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                  </button>
                </div>
              );
            })}
          </div>
        )}
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

        .orders-timeline {
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }

        .order-card {
          background: white;
          border-radius: 12px;
          border: 1px solid rgba(100, 27, 46, 0.12);
          padding: 1.25rem 1.5rem;
          box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }

        .order-row {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 1rem;
        }

        .order-id {
          margin: 0;
          font-weight: 700;
          color: #1d1d1f;
          font-size: 1.05rem;
        }

        .order-details {
          margin: 0.35rem 0 0;
          color: #666;
          font-size: 0.9rem;
        }

        .order-status-stack {
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          gap: 0.45rem;
        }

        .status-badge {
          padding: 0.35rem 0.8rem;
          border-radius: 999px;
          font-weight: 600;
          font-size: 0.78rem;
          text-transform: uppercase;
          letter-spacing: 0.05em;
        }

        .status-processing {
          background: rgba(255, 189, 67, 0.2);
          color: #8c540a;
        }

        .status-shipped {
          background: rgba(89, 126, 247, 0.18);
          color: #2941a8;
        }

        .status-delivered {
          background: rgba(76, 175, 80, 0.18);
          color: #2e7d32;
        }

        .status-cancelled {
          background: rgba(229, 57, 53, 0.18);
          color: #c62828;
        }

        .order-total {
          font-weight: 700;
          color: #1d1d1f;
        }

        .order-actions {
          display: flex;
          gap: 0.6rem;
          flex-wrap: wrap;
        }

        .secondary-btn.small,
        .ghost-btn.small {
          padding: 0.45rem 0.85rem;
          font-size: 0.85rem;
        }

        .settings-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
          gap: 1rem;
          margin-bottom: 1.5rem;
        }

        .setting-card {
          background: white;
          border-radius: 12px;
          border: 1px solid rgba(100, 27, 46, 0.12);
          padding: 1.5rem;
          display: flex;
          flex-direction: column;
          gap: 1rem;
          box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
        }

        .setting-card.critical {
          border-color: rgba(198, 40, 40, 0.32);
        }

        .setting-title {
          margin: 0;
          font-weight: 700;
          color: #1d1d1f;
          font-size: 1rem;
        }

        .setting-description {
          margin: 0.35rem 0 0;
          color: #666;
          font-size: 0.9rem;
          line-height: 1.5;
        }

        .danger-btn {
          background: linear-gradient(135deg, #c62828, #e53935);
          color: #fff;
          border: none;
          padding: 0.65rem 1.2rem;
          border-radius: 999px;
          font-weight: 600;
          cursor: pointer;
          transition: opacity 0.2s ease;
        }

        .danger-btn:hover {
          opacity: 0.9;
        }

        .logout-card {
          background: white;
          border-radius: 12px;
          border: 1px solid rgba(100, 27, 46, 0.12);
          padding: 1.5rem;
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 1rem;
          box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        }

        .logout-title {
          margin: 0;
          font-weight: 700;
          font-size: 1.05rem;
        }

        .logout-description {
          margin: 0.3rem 0 0;
          color: #666;
          font-size: 0.88rem;
        }

        .logout-copy {
          flex: 1;
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

        /* Wishlist styling - compact mini cards similar to cart items */
        .wishlist-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
          gap: 1rem;
        }

        .wishlist-card {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 0.9rem 1rem;
          background: white;
          border: 1px solid rgba(100, 27, 46, 0.12);
          border-radius: 14px;
          box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
          transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .wishlist-card:hover {
          transform: translateY(-2px);
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
          border-color: rgba(100, 27, 46, 0.18);
        }

        .wishlist-clickable {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          flex: 1;
          min-width: 0;
        }

        .wishlist-media {
          width: 72px;
          height: 72px;
          border-radius: 12px;
          overflow: hidden;
          background: linear-gradient(135deg, #f8e8e5, #f1d9d5);
          flex-shrink: 0;
          display: grid;
          place-items: center;
        }

        .wishlist-media img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .wishlist-info {
          flex: 1;
          min-width: 0;
          display: flex;
          flex-direction: column;
          gap: 0.2rem;
        }

        .wishlist-name {
          font-weight: 700;
          font-size: 0.95rem;
          color: #1a1a1a;
          margin: 0;
          line-height: 1.3;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }

        .wishlist-meta {
          font-size: 0.85rem;
          color: #777;
          margin: 0;
        }

        .wishlist-heart-btn {
          align-self: flex-start;
          border: none;
          background: transparent;
          color: #641b2e;
          display: grid;
          place-items: center;
          transition: all 0.2s ease;
          cursor: pointer;
          padding: 0;
          flex-shrink: 0;
          line-height: 1;
        }

        .wishlist-heart-btn:hover {
          transform: scale(1.1);
        }

        .wishlist-heart-btn:active {
          transform: scale(0.95);
        }

        .wishlist-heart-btn svg {
          display: block;
          filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }

        .wishlist-heart-btn svg path {
          fill: #ffffff;
          stroke: currentColor;
          transition: fill 0.2s ease, stroke 0.2s ease;
        }

        .wishlist-heart-btn.filled svg path {
          fill: currentColor;
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
