import { useState, useEffect, useMemo, useRef } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api";
import {
  getWishlist,
  toggleWishlistEntry,
} from "../wishlistStorage";

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

const isBrowser = typeof window !== "undefined";
const ORDER_HISTORY_STORAGE = "fitfast_account_orders";
const ORDER_STATUS_STORAGE = "fitfast_recent_order";
const PAYMENT_METHODS_STORAGE = "fitfast_payment_methods";

const DEFAULT_ADDRESS = {
  line1: "342 Fashion Avenue",
  line2: "Suite 18B",
  city: "New York",
  state: "NY",
  postalCode: "10001",
  country: "USA",
  phone: "(212) 555-0113",
};

const readStoredJson = (key, fallback) => {
  if (!isBrowser) return fallback;
  try {
    const raw = window.localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch (err) {
    console.error(`Failed to read storage for ${key}`, err);
    return fallback;
  }
};

const writeStoredJson = (key, value) => {
  if (!isBrowser) return;
  try {
    if (value === null || value === undefined) {
      window.localStorage.removeItem(key);
    } else {
      window.localStorage.setItem(key, JSON.stringify(value));
    }
  } catch (err) {
    console.error(`Failed to persist storage for ${key}`, err);
  }
};

const mapApiPaymentMethod = (method) => ({
  id: method.id || `pm-${method.last4 || Date.now()}`,
  brand: method.brand || method.card_brand || "Card",
  nickname: method.nickname || method.label || method.brand || "Saved card",
  last4: method.last4 || method.card_last_four || "0000",
  expMonth: method.exp_month || method.expMonth || "01",
  expYear: method.exp_year || method.expYear || "30",
  isDefault: Boolean(method.is_default ?? method.isDefault ?? false),
});

const normalizeEmail = (value) =>
  typeof value === "string" ? value.trim().toLowerCase() : "";

const ensureDefaultPayment = (methods) => {
  if (!Array.isArray(methods) || !methods.length) return [];
  if (methods.some((method) => method.isDefault)) {
    return methods.map((method) => ({ ...method }));
  }
  return methods.map((method, index) => ({ ...method, isDefault: index === 0 }));
};

const getPaymentMethodsKey = (user) => {
  if (!user) return null;
  if (user.id) return `${PAYMENT_METHODS_STORAGE}_${user.id}`;
  const emailKey = normalizeEmail(user.email);
  return emailKey ? `${PAYMENT_METHODS_STORAGE}_${emailKey}` : null;
};

const computeTrackable = (status, explicit) => {
  if (typeof explicit === "boolean") return explicit;
  const normalized = (status || "").toString().toLowerCase();
  const terminal = ["delivered", "cancelled", "canceled", "refunded", "returned"];
  return !terminal.some((term) => normalized.includes(term));
};

const getOrderHistoryKey = (user) => {
  if (!user) return null;
  if (user.id) return `${ORDER_HISTORY_STORAGE}_${user.id}`;
  const emailKey = normalizeEmail(user.email);
  return emailKey ? `${ORDER_HISTORY_STORAGE}_${emailKey}` : null;
};

const mapApiOrder = (order) => {
  if (!order || typeof order !== "object") return null;

  const orderCode = order.code || order.reference || `ORDER-${order.id}`;
  const statusLabel = (order.status?.label || order.status || "Processing").toString();

  const items = Array.isArray(order.items)
    ? order.items.map((item) => ({
        id: item.id || item.code || `${orderCode}-item`,
        name: item.name || item.title || "Ordered item",
        quantity: item.quantity || item.qty || 1,
        price: Number(item.price || item.unit_price || 0),
        image: item.image || item.thumbnail || null,
        size: item.size || null,
        color: item.color || null,
      }))
    : [];

  const totalsSource = order.totals || {};
  const totals = {
    subtotal: Number(totalsSource.subtotal ?? order.subtotal ?? 0),
    shipping: Number(totalsSource.shipping ?? order.shipping_total ?? 0),
    tax: Number(totalsSource.tax ?? order.tax_total ?? 0),
    discount: Number(totalsSource.discount ?? order.discount_total ?? 0),
    total: Number(totalsSource.total ?? order.total ?? order.grand_total ?? 0),
  };

  const contact = order.contact || {
    fullName: order.contact_name || "",
    email: order.contact_email || "",
    phone: order.contact_phone || "",
  };

  const normalizedEmail = normalizeEmail(contact.email || order.email);

  return {
    id: orderCode,
    code: orderCode,
    placedAt: order.placed_at || order.created_at || order.createdAt || new Date().toISOString(),
    eta:
      order.eta ||
      order.estimated_delivery ||
      order.estimatedArrival ||
      new Date(Date.now() + 72 * 60 * 60 * 1000).toISOString(),
    status: statusLabel,
    total: Number(totals.total || 0),
    items,
    delivery: order.delivery || {
      id: order.delivery_method || "standard",
      label: order.delivery_label || "Standard delivery",
      description: order.delivery_description || "Arrives in 3-5 business days",
    },
    shippingAddress:
      order.shipping_address || order.shippingAddress || { ...DEFAULT_ADDRESS },
    payment:
      order.payment || order.payment_method || {
        label: order.payment_label || "Card",
        cardLast4: order.payment_last4 || "0000",
      },
    contact,
    totals,
    trackable: computeTrackable(statusLabel, order.trackable),
    userId: order.user_id ?? order.userId ?? null,
    userEmail: normalizedEmail,
  };
};

const mapRecentOrder = (order) => {
  if (!order || typeof order !== "object") return null;

  const orderCode = order.code || order.id || order.reference || `ORDER-${Date.now()}`;
  const statusLabel = (order.status || "Processing").toString();
  const contact = order.contact || {
    fullName: order.fullName || "",
    email: order.email || "",
    phone: order.phone || "",
  };

  const items = Array.isArray(order.items)
    ? order.items.map((item, index) => ({
        id: item.id || item.code || `${orderCode}-item-${index + 1}`,
        name: item.name || "Ordered item",
        quantity: item.quantity || 1,
        price: Number(item.price || 0),
        image: item.image || null,
        size: item.size || null,
        color: item.color || null,
      }))
    : [];

  const totalsSource = order.totals || {};
  const totals = {
    subtotal: Number(totalsSource.subtotal ?? order.subtotal ?? 0),
    shipping: Number(totalsSource.shipping ?? order.shipping ?? 0),
    tax: Number(totalsSource.tax ?? order.tax ?? 0),
    discount: Number(totalsSource.discount ?? order.discount ?? 0),
    total: Number(totalsSource.total ?? order.total ?? 0),
  };

  return {
    id: orderCode,
    code: orderCode,
    placedAt: order.placedAt || order.created_at || order.createdAt || new Date().toISOString(),
    eta:
      order.eta ||
      order.estimatedArrival ||
      order.estimated_delivery ||
      new Date(Date.now() + 72 * 60 * 60 * 1000).toISOString(),
    status: statusLabel,
    total: Number(totals.total || 0),
    items,
    delivery:
      order.delivery || {
        id: order.deliveryId || "standard",
        label: order.deliveryLabel || "Standard delivery",
        description: order.delivery?.description || "Arrives in 3-5 business days",
      },
    shippingAddress: order.shippingAddress || { ...DEFAULT_ADDRESS },
    payment:
      order.payment || {
        label: order.paymentLabel || "Card",
        cardLast4: order.payment?.cardLast4 || order.cardLast4 || "0000",
      },
    contact,
    totals,
    trackable: computeTrackable(statusLabel, order.trackable),
    userId: order.userId ?? order.user_id ?? null,
    userEmail: normalizeEmail(order.userEmail || order.user_email || contact.email),
  };
};

const mergeOrders = (...sources) => {
  const flattened = [];
  sources.forEach((source) => {
    if (!source) return;
    if (Array.isArray(source)) {
      source.forEach((item) => flattened.push(item));
    } else {
      flattened.push(source);
    }
  });

  const merged = new Map();

  flattened.forEach((order) => {
    if (!order || !order.id) return;

    const normalized = mapRecentOrder(order) || order;
    const existing = merged.get(normalized.id);

    if (!existing) {
      merged.set(normalized.id, normalized);
      return;
    }

    const currentPlacedAt = Date.parse(existing.placedAt || 0);
    const incomingPlacedAt = Date.parse(normalized.placedAt || 0);
    const latest = incomingPlacedAt > currentPlacedAt ? normalized : existing;
    const mergedOrder = { ...existing, ...normalized, ...latest };
    mergedOrder.trackable = computeTrackable(mergedOrder.status, mergedOrder.trackable);
    merged.set(normalized.id, mergedOrder);
  });

  return Array.from(merged.values()).sort(
    (a, b) => Date.parse(b.placedAt || 0) - Date.parse(a.placedAt || 0)
  );
};

const orderBelongsToUser = (order, user) => {
  if (!order || !user) return false;
  if (order.userId && user.id && order.userId === user.id) return true;
  const orderEmail = normalizeEmail(order.userEmail || order.contact?.email);
  const userEmail = normalizeEmail(user.email);
  return Boolean(orderEmail && userEmail && orderEmail === userEmail);
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
  const [orders, setOrders] = useState([]);
  const [orderStorageKey, setOrderStorageKey] = useState(null);
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [paymentStorageKey, setPaymentStorageKey] = useState(null);
  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [passwordForm, setPasswordForm] = useState({
    current: "",
    next: "",
    confirm: "",
  });
  const [passwordStatus, setPasswordStatus] = useState({ type: "", message: "" });
  const [passwordLoading, setPasswordLoading] = useState(false);
  const [showBillingModal, setShowBillingModal] = useState(false);
  const [billingForm, setBillingForm] = useState({
    nickname: "",
    brand: "Visa",
    number: "",
    expMonth: "",
    expYear: "",
    setDefault: true,
  });
  const [billingError, setBillingError] = useState("");
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [deleteError, setDeleteError] = useState("");
  const [activeOrder, setActiveOrder] = useState(null);
  const [showOrderModal, setShowOrderModal] = useState(false);
  const measurementsRef = useRef(null);
  const ordersRef = useRef(null);
  const settingsRef = useRef(null);
  const wishlistRef = useRef(null);

  const sectionLinks = useMemo(
    () => [
      { id: "measurements", label: "Measurements", ref: measurementsRef },
      { id: "orders", label: "Orders", ref: ordersRef },
      { id: "settings", label: "Settings", ref: settingsRef },
      { id: "wishlist", label: "Wishlist", ref: wishlistRef },
    ],
    [measurementsRef, ordersRef, settingsRef, wishlistRef]
  );

  const [activeSection, setActiveSection] = useState(sectionLinks[0]?.id ?? "measurements");

  useEffect(() => {
    async function fetchUser() {
      try {
        const res = await api.get("/user");
        const fetchedUser = res.data;
        setUser(fetchedUser);
        setMeasurements({
          ...DEFAULT_MEASUREMENTS,
          ...(fetchedUser?.measurements || {}),
        });
        setWishlistItems(getWishlist());

        const paymentKey = getPaymentMethodsKey(fetchedUser);
        setPaymentStorageKey(paymentKey);

        const storedPaymentRaw = paymentKey ? readStoredJson(paymentKey, []) : [];
        let storedPaymentMethods = Array.isArray(storedPaymentRaw)
          ? storedPaymentRaw
              .filter((entry) => entry && typeof entry === "object")
              .map(mapApiPaymentMethod)
          : [];

        if (!storedPaymentMethods.length) {
          const legacyPaymentRaw = readStoredJson(PAYMENT_METHODS_STORAGE, []);
          if (paymentKey && Array.isArray(legacyPaymentRaw) && legacyPaymentRaw.length) {
            storedPaymentMethods = legacyPaymentRaw
              .filter((entry) => entry && typeof entry === "object")
              .map(mapApiPaymentMethod);
            writeStoredJson(PAYMENT_METHODS_STORAGE, null);
          }
        }

        let resolvedPaymentMethods = storedPaymentMethods;

        if (Array.isArray(fetchedUser?.payment_methods) && fetchedUser.payment_methods.length) {
          const apiPaymentMethods = fetchedUser.payment_methods
            .filter((entry) => entry && typeof entry === "object")
            .map(mapApiPaymentMethod);

          const merged = new Map();
          [...apiPaymentMethods, ...storedPaymentMethods].forEach((method) => {
            if (!method) return;
            const dedupeKey = method.id || `${method.brand}-${method.last4}`;
            const existing = merged.get(dedupeKey);
            merged.set(dedupeKey, existing ? { ...existing, ...method } : method);
          });

          resolvedPaymentMethods = Array.from(merged.values());
        }

        setPaymentMethods(ensureDefaultPayment(resolvedPaymentMethods));

        const key = getOrderHistoryKey(fetchedUser);
        setOrderStorageKey(key);

        const storedOrdersRaw = key ? readStoredJson(key, []) : [];
        const storedOrders = Array.isArray(storedOrdersRaw)
          ? storedOrdersRaw.map(mapRecentOrder).filter(Boolean)
          : [];

        const apiOrders = Array.isArray(fetchedUser?.orders)
          ? fetchedUser.orders.map(mapApiOrder).filter(Boolean)
          : [];

        const recentSnapshot = mapRecentOrder(readStoredJson(ORDER_STATUS_STORAGE, null));
        const hydratedRecent =
          recentSnapshot && orderBelongsToUser(recentSnapshot, fetchedUser)
            ? [recentSnapshot]
            : [];

        setOrders(mergeOrders(apiOrders, storedOrders, hydratedRecent));
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

  useEffect(() => {
    if (!orderStorageKey) return;
    writeStoredJson(orderStorageKey, orders);
  }, [orderStorageKey, orders]);

  useEffect(() => {
    if (!isBrowser || !user) return undefined;

    const syncRecentOrder = () => {
      const snapshot = mapRecentOrder(readStoredJson(ORDER_STATUS_STORAGE, null));
      if (!snapshot || !orderBelongsToUser(snapshot, user)) return;
      setOrders((prev) => mergeOrders(prev, [snapshot]));
    };

    window.addEventListener("fitfast-order-updated", syncRecentOrder);
    window.addEventListener("storage", syncRecentOrder);

    return () => {
      window.removeEventListener("fitfast-order-updated", syncRecentOrder);
      window.removeEventListener("storage", syncRecentOrder);
    };
  }, [user]);

  useEffect(() => {
    if (!paymentStorageKey) return;
    writeStoredJson(paymentStorageKey, paymentMethods);
  }, [paymentStorageKey, paymentMethods]);

  useEffect(() => {
    if (!isBrowser) return undefined;

    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

        if (visible.length) {
          const topSection = visible[0].target.getAttribute("data-section-id");
          if (topSection) {
            setActiveSection((prev) => (prev === topSection ? prev : topSection));
          }
        }
      },
      {
        rootMargin: "-45% 0px -45% 0px",
        threshold: [0.25, 0.5, 0.75],
      }
    );

    sectionLinks.forEach(({ ref }) => {
      if (ref.current) {
        observer.observe(ref.current);
      }
    });

    return () => observer.disconnect();
  }, [sectionLinks]);


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

  const resolvedOrders = useMemo(
    () => (Array.isArray(orders) ? orders.filter((entry) => entry && entry.id) : []),
    [orders]
  );

  const hasOrders = resolvedOrders.length > 0;

  const formatCardLabel = (method) => {
    if (!method) return "Saved card";
    return `${method.brand || "Card"} •••• ${method.last4}`;
  };

  const computeItemCount = (list = []) =>
    Array.isArray(list)
      ? list.reduce((total, item) => total + (item.quantity ? Number(item.quantity) : 1), 0)
      : 0;

  const formatOrderSummary = (order = {}) => {
    const itemCount = computeItemCount(order.items);
    const datePart = (() => {
      if (!order?.placedAt) return "";
      const parsed = new Date(order.placedAt);
      if (Number.isNaN(parsed.getTime())) return order.placedAt;
      return parsed.toLocaleDateString(undefined, {
        month: "short",
        day: "numeric",
        year: "numeric",
      });
    })();

    const itemsLabel = `${itemCount} ${itemCount === 1 ? "item" : "items"}`;
    return datePart ? `${itemsLabel} • ${datePart}` : itemsLabel;
  };

  const handleSidebarNavigate = (sectionId) => {
    const target = sectionLinks.find((entry) => entry.id === sectionId);
    if (target?.ref.current) {
      target.ref.current.scrollIntoView({ behavior: "smooth", block: "start" });
      if (typeof target.ref.current.focus === "function") {
        if (typeof window !== "undefined" && typeof window.requestAnimationFrame === "function") {
          window.requestAnimationFrame(() => {
            target.ref.current?.focus({ preventScroll: true });
          });
        } else {
          target.ref.current.focus();
        }
      }
    }
    setActiveSection(sectionId);
  };

  const handleTogglePasswordModal = () => {
    setShowPasswordModal((prev) => !prev);
    setPasswordStatus({ type: "", message: "" });
    setPasswordForm({ current: "", next: "", confirm: "" });
    setPasswordLoading(false);
  };

  const handlePasswordFieldChange = (event) => {
    const { name, value } = event.target;
    setPasswordForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmitPassword = async (event) => {
    event.preventDefault();
    setPasswordStatus({ type: "", message: "" });

    if (!passwordForm.current || !passwordForm.next || !passwordForm.confirm) {
      setPasswordStatus({ type: "error", message: "Please complete all password fields." });
      return;
    }

    if (passwordForm.next.length < 6) {
      setPasswordStatus({ type: "error", message: "New password should be at least 6 characters long." });
      return;
    }

    if (passwordForm.next !== passwordForm.confirm) {
      setPasswordStatus({ type: "error", message: "New password confirmation does not match." });
      return;
    }

    setPasswordLoading(true);

    try {
      await api.post("/user/password", {
        current_password: passwordForm.current,
        new_password: passwordForm.next,
        new_password_confirmation: passwordForm.confirm,
      });
      setPasswordStatus({ type: "success", message: "Password updated successfully." });
      setPasswordForm({ current: "", next: "", confirm: "" });
      setTimeout(() => {
        setPasswordLoading(false);
        setShowPasswordModal(false);
      }, 1200);
    } catch (error) {
      console.error(error);
      const fallback = error.response?.data?.message || "Unable to update password. Please check your current password.";
      setPasswordStatus({ type: "error", message: fallback });
      setPasswordLoading(false);
    }
  };

  const handleToggleBillingModal = () => {
    setShowBillingModal((prev) => !prev);
    setBillingError("");
    setBillingForm({ nickname: "", brand: "Visa", number: "", expMonth: "", expYear: "", setDefault: !paymentMethods.length });
  };

  const handleBillingFieldChange = (event) => {
    const { name, value, type, checked } = event.target;
    const nextValue = type === "checkbox" ? checked : value;
    setBillingForm((prev) => ({ ...prev, [name]: nextValue }));
  };

  const handleAddPaymentMethod = (event) => {
    event.preventDefault();
    setBillingError("");

    const sanitizedNumber = billingForm.number.replace(/\D/g, "");
    if (sanitizedNumber.length < 12) {
      setBillingError("Enter a valid card number (minimum 12 digits).");
      return;
    }

    if (!/^(0[1-9]|1[0-2])$/.test(billingForm.expMonth)) {
      setBillingError("Provide expiry month as MM.");
      return;
    }

    if (!/^\d{2}$/.test(billingForm.expYear)) {
      setBillingError("Provide expiry year as YY.");
      return;
    }

    const last4 = sanitizedNumber.slice(-4);
    const newMethod = {
      id: `pm-${Date.now()}`,
      brand: billingForm.brand,
      nickname: billingForm.nickname || `${billingForm.brand} ending ${last4}`,
      last4,
      expMonth: billingForm.expMonth,
      expYear: billingForm.expYear,
      isDefault: Boolean(billingForm.setDefault),
    };

    setPaymentMethods((prev) => {
      const normalized = Array.isArray(prev)
        ? prev.map((method) => ({ ...method }))
        : [];
      const next = billingForm.setDefault
        ? normalized.map((method) => ({ ...method, isDefault: false }))
        : normalized;
      next.push(newMethod);
      return ensureDefaultPayment(next);
    });

    setBillingForm({ nickname: "", brand: "Visa", number: "", expMonth: "", expYear: "", setDefault: false });
    setShowBillingModal(false);
  };

  const handleSetDefaultPayment = (id) => {
    setPaymentMethods((prev) => prev.map((method) => ({ ...method, isDefault: method.id === id })));
  };

  const handleRemovePaymentMethod = (id) => {
    setPaymentMethods((prev) => {
      const remaining = prev.filter((method) => method.id !== id);
      if (!remaining.length) {
        return [];
      }
      return ensureDefaultPayment(remaining);
    });
  };

  const handleLogout = async () => {
    try {
      await api.post("/logout");
    } catch (error) {
      console.error("Logout failed", error);
    } finally {
      if (isBrowser) {
        window.localStorage.removeItem("auth_token");
        window.localStorage.removeItem("auth_user");
      }
      setOrders([]);
      setOrderStorageKey(null);
      setPaymentMethods([]);
      setPaymentStorageKey(null);
      navigate("/login");
    }
  };

  const handleTrackOrder = (order) => {
    if (!order) return;
    if (!isBrowser) {
      navigate("/order-status");
      return;
    }

    const payload = {
      code: order.id,
      placedAt: order.placedAt,
      eta: order.eta,
      status: order.status,
      delivery: order.delivery,
      payment: order.payment,
      contact: {
        fullName: user?.name || order.contact?.fullName || "",
        email: user?.email || order.contact?.email || "",
        phone: order.contact?.phone || (user?.phone ?? ""),
      },
      shippingAddress: order.shippingAddress || { ...DEFAULT_ADDRESS },
      items: order.items || [],
      totals: order.totals || {
        subtotal: order.total || 0,
        shipping: 0,
        tax: 0,
        discount: 0,
        total: order.total || 0,
      },
      trackable: order.trackable,
      userId: user?.id ?? order.userId ?? null,
      userEmail: normalizeEmail(user?.email || order.contact?.email || order.userEmail || ""),
    };

    // Persist a snapshot so the order status page has context even without a fresh API call.
    try {
      window.localStorage.setItem(ORDER_STATUS_STORAGE, JSON.stringify(payload));
      window.dispatchEvent(new Event("fitfast-order-updated"));
    } catch (err) {
      console.error("Failed to persist order snapshot", err);
    }

    navigate("/order-status");
  };

  const handleViewOrderDetails = (order) => {
    setActiveOrder(order);
    setShowOrderModal(true);
  };

  const handleOrderSupport = (order) => {
    const template = `Order ${order.id} (${order.status}) — I need assistance with this order.`;
    if (isBrowser) {
      sessionStorage.setItem(
        "fitfast_support_prefill",
        JSON.stringify({ message: template, type: "question" })
      );
    }
    navigate("/support", { state: { prefillMessage: template, topic: "question" } });
  };

  const handleCloseOrderModal = () => {
    setActiveOrder(null);
    setShowOrderModal(false);
  };

  const openDeleteModal = () => {
    setDeleteError("");
    setDeleteLoading(false);
    setShowDeleteModal(true);
  };

  const cancelDeleteAccount = () => {
    setShowDeleteModal(false);
    setDeleteLoading(false);
    setDeleteError("");
  };

  const handleDeleteAccount = async () => {
    setDeleteLoading(true);
    setDeleteError("");
    try {
      await api.delete("/user");
      if (isBrowser) {
        window.localStorage.removeItem("auth_token");
        window.localStorage.removeItem("auth_user");
      }
      setShowDeleteModal(false);
      setDeleteLoading(false);
      navigate("/register");
    } catch (error) {
      console.error(error);
      const fallback = error.response?.data?.message || "Unable to delete account. Please try again.";
      setDeleteError(fallback);
      setDeleteLoading(false);
    }
  };

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
      <div className="profile-layout">
        <aside className="profile-sidebar" aria-label="Account quick links">
          <p className="sidebar-title">Quick access</p>
          <nav className="sidebar-nav" aria-label="Account sections">
            {sectionLinks.map((section) => (
              <button
                type="button"
                key={section.id}
                className={`sidebar-link${activeSection === section.id ? " active" : ""}`}
                onClick={() => handleSidebarNavigate(section.id)}
                aria-current={activeSection === section.id ? "true" : undefined}
              >
                {section.label}
              </button>
            ))}
          </nav>
        </aside>
        <div className="profile-content">
          <div
            ref={measurementsRef}
            id="measurements"
            data-section-id="measurements"
            className="profile-card"
            tabIndex={-1}
          >
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

          <div
            ref={ordersRef}
            id="orders"
            data-section-id="orders"
            className="profile-section"
            tabIndex={-1}
          >
            <div className="section-header">
              <h3>Order History</h3>
              {hasOrders && <span className="pill-count">{resolvedOrders.length} orders</span>}
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
                {resolvedOrders.map((order) => (
                  <div className="order-card" key={order.id}>
                    <div className="order-row">
                      <div>
                        <p className="order-id">{order.id}</p>
                        <p className="order-details">{formatOrderSummary(order)}</p>
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
                          onClick={() => handleTrackOrder(order)}
                        >
                          Track order
                        </button>
                      )}
                      <button
                        type="button"
                        className="ghost-btn small"
                        onClick={() => handleViewOrderDetails(order)}
                      >
                        View details
                      </button>
                      <button
                        type="button"
                        className="ghost-btn small"
                        onClick={() => handleOrderSupport(order)}
                      >
                        Get support
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          <div
            ref={settingsRef}
            id="settings"
            data-section-id="settings"
            className="profile-section"
            tabIndex={-1}
          >
            <div className="section-header">
              <h3>Account Settings</h3>
            </div>

            <div className="settings-grid">
              <div className="setting-card">
                <div>
                  <p className="setting-title">Security</p>
                  <p className="setting-description">Update your password to keep your account protected.</p>
                </div>
                <button type="button" className="secondary-btn" onClick={handleTogglePasswordModal}>
                  Change password
                </button>
              </div>
              <div className="setting-card">
                <div>
                  <p className="setting-title">Billing</p>
                  <p className="setting-description">Manage your saved cards and billing contacts.</p>
                </div>
                <button type="button" className="ghost-btn" onClick={handleToggleBillingModal}>
                  Manage billing
                </button>
              </div>
              <div className="setting-card critical">
                <div>
                  <p className="setting-title">Delete account</p>
                  <p className="setting-description">Remove your profile permanently, including orders and measurements.</p>
                </div>
                <button type="button" className="danger-btn" onClick={openDeleteModal}>
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

          <div
            ref={wishlistRef}
            id="wishlist"
            data-section-id="wishlist"
            className="profile-section"
            tabIndex={-1}
          >
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
                        style={{ cursor: "pointer" }}
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
                          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                      </button>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </div>
      </div>


      {showPasswordModal && (
        <div className="modal-backdrop" role="dialog" aria-modal="true">
          <div className="modal-card">
            <div className="modal-header">
              <h4>Change password</h4>
              <button type="button" className="modal-close" onClick={handleTogglePasswordModal} aria-label="Close password modal">
                ✕
              </button>
            </div>
            <form className="modal-body" onSubmit={handleSubmitPassword}>
              <label className="modal-field">
                <span>Current password</span>
                <input
                  type="password"
                  name="current"
                  value={passwordForm.current}
                  onChange={handlePasswordFieldChange}
                  autoComplete="current-password"
                  disabled={passwordLoading}
                  required
                />
              </label>
              <label className="modal-field">
                <span>New password</span>
                <input
                  type="password"
                  name="next"
                  value={passwordForm.next}
                  onChange={handlePasswordFieldChange}
                  autoComplete="new-password"
                  disabled={passwordLoading}
                  required
                />
              </label>
              <label className="modal-field">
                <span>Confirm new password</span>
                <input
                  type="password"
                  name="confirm"
                  value={passwordForm.confirm}
                  onChange={handlePasswordFieldChange}
                  autoComplete="new-password"
                  disabled={passwordLoading}
                  required
                />
              </label>
              {passwordStatus.message && (
                <div className={`modal-status ${passwordStatus.type}`}>
                  {passwordStatus.message}
                </div>
              )}
              <div className="modal-actions">
                <button type="button" className="ghost-btn" onClick={handleTogglePasswordModal} disabled={passwordLoading}>
                  Cancel
                </button>
                <button type="submit" className="primary-btn" disabled={passwordLoading}>
                  {passwordLoading ? "Updating…" : "Save password"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {showBillingModal && (
        <div className="modal-backdrop" role="dialog" aria-modal="true">
          <div className="modal-card wide">
            <div className="modal-header">
              <h4>Manage billing</h4>
              <button type="button" className="modal-close" onClick={handleToggleBillingModal} aria-label="Close billing modal">
                ✕
              </button>
            </div>
            <div className="modal-body">
              <div className="billing-columns">
                <div className="billing-list">
                  <p className="billing-subtitle">Saved payment methods</p>
                  {paymentMethods.length ? (
                    paymentMethods.map((method) => (
                      <div className="billing-card" key={method.id}>
                        <div>
                          <p className="billing-card-name">
                            {formatCardLabel(method)}
                            {method.isDefault && <span className="badge">Default</span>}
                          </p>
                          <p className="billing-card-meta">Expiry {method.expMonth}/{method.expYear}</p>
                          {method.nickname && <p className="billing-card-note">{method.nickname}</p>}
                        </div>
                        <div className="billing-card-actions">
                          {!method.isDefault && (
                            <button type="button" className="ghost-btn small" onClick={() => handleSetDefaultPayment(method.id)}>
                              Make default
                            </button>
                          )}
                          <button type="button" className="ghost-btn small" onClick={() => handleRemovePaymentMethod(method.id)}>
                            Remove
                          </button>
                        </div>
                      </div>
                    ))
                  ) : (
                    <div className="empty-state">
                      <div className="empty-icon">💳</div>
                      <div>No saved payment methods yet.</div>
                      <div className="empty-hint">Add a card to speed up checkout next time.</div>
                    </div>
                  )}
                </div>
                <form className="billing-form" onSubmit={handleAddPaymentMethod}>
                  <p className="billing-subtitle">Add a new card</p>
                  <label className="modal-field">
                    <span>Card nickname</span>
                    <input
                      name="nickname"
                      value={billingForm.nickname}
                      onChange={handleBillingFieldChange}
                      placeholder="e.g. Travel card"
                    />
                  </label>
                  <label className="modal-field">
                    <span>Card brand</span>
                    <select name="brand" value={billingForm.brand} onChange={handleBillingFieldChange}>
                      <option value="Visa">Visa</option>
                      <option value="Mastercard">Mastercard</option>
                      <option value="Amex">American Express</option>
                      <option value="Discover">Discover</option>
                    </select>
                  </label>
                  <label className="modal-field">
                    <span>Card number</span>
                    <input
                      name="number"
                      value={billingForm.number}
                      onChange={handleBillingFieldChange}
                      placeholder="1234 5678 9012 3456"
                      inputMode="numeric"
                      autoComplete="cc-number"
                      required
                    />
                  </label>
                  <div className="billing-row">
                    <label className="modal-field">
                      <span>Expiry month</span>
                      <input
                        name="expMonth"
                        value={billingForm.expMonth}
                        onChange={handleBillingFieldChange}
                        placeholder="MM"
                        maxLength={2}
                        inputMode="numeric"
                        autoComplete="cc-exp-month"
                        required
                      />
                    </label>
                    <label className="modal-field">
                      <span>Expiry year</span>
                      <input
                        name="expYear"
                        value={billingForm.expYear}
                        onChange={handleBillingFieldChange}
                        placeholder="YY"
                        maxLength={2}
                        inputMode="numeric"
                        autoComplete="cc-exp-year"
                        required
                      />
                    </label>
                  </div>
                  <label className="checkbox-field">
                    <input
                      type="checkbox"
                      name="setDefault"
                      checked={billingForm.setDefault}
                      onChange={handleBillingFieldChange}
                    />
                    <span>Set as default payment method</span>
                  </label>
                  {billingError && <div className="modal-status error">{billingError}</div>}
                  <div className="modal-actions">
                    <button type="button" className="ghost-btn" onClick={handleToggleBillingModal}>
                      Close
                    </button>
                    <button type="submit" className="primary-btn">
                      Save card
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      )}

      {showDeleteModal && (
        <div className="modal-backdrop" role="dialog" aria-modal="true">
          <div className="modal-card danger">
            <div className="modal-header">
              <h4>Delete account</h4>
              <button type="button" className="modal-close" onClick={cancelDeleteAccount} aria-label="Close delete modal">
                ✕
              </button>
            </div>
            <div className="modal-body">
              <p>
                This will remove your profile, measurements, and saved preferences. You will no longer have access to order history.
              </p>
              <p>Please confirm you wish to continue.</p>
              {deleteError && <div className="modal-status error">{deleteError}</div>}
            </div>
            <div className="modal-actions">
              <button type="button" className="ghost-btn" onClick={cancelDeleteAccount} disabled={deleteLoading}>
                Keep my account
              </button>
              <button
                type="button"
                className="danger-btn"
                onClick={handleDeleteAccount}
                disabled={deleteLoading}
              >
                {deleteLoading ? "Deleting…" : "Delete account"}
              </button>
            </div>
          </div>
        </div>
      )}

      {showOrderModal && activeOrder && (
        <div className="modal-backdrop" role="dialog" aria-modal="true">
          <div className="modal-card wide">
            <div className="modal-header">
              <h4>Order {activeOrder.id}</h4>
              <button type="button" className="modal-close" onClick={handleCloseOrderModal} aria-label="Close order modal">
                ✕
              </button>
            </div>
            <div className="modal-body order-modal">
              <div className="order-meta-block">
                <span className={statusClassName(activeOrder.status)}>{activeOrder.status}</span>
                <p className="order-meta-line">{formatOrderSummary(activeOrder)}</p>
                <p className="order-meta-line">Total {formatPrice(activeOrder.total)}</p>
                {activeOrder.delivery?.label && (
                  <p className="order-meta-line">Delivery: {activeOrder.delivery.label}</p>
                )}
                {activeOrder.payment?.label && (
                  <p className="order-meta-line">
                    Paid with {activeOrder.payment.label}
                    {activeOrder.payment.cardLast4 ? ` •••• ${activeOrder.payment.cardLast4}` : ""}
                  </p>
                )}
              </div>
              <div className="order-items">
                {Array.isArray(activeOrder.items) && activeOrder.items.length ? (
                  activeOrder.items.map((item) => (
                    <div className="order-item-row" key={`${activeOrder.id}-${item.id}`}>
                      <div>
                        <p className="order-item-name">{item.name}</p>
                        <p className="order-item-meta">Qty {item.quantity || 1}{item.size ? ` • Size ${item.size}` : ""}{item.color ? ` • ${item.color}` : ""}</p>
                      </div>
                      <div className="order-item-price">{formatPrice((item.price || 0) * (item.quantity || 1))}</div>
                    </div>
                  ))
                ) : (
                  <p className="order-meta-line">No line items recorded.</p>
                )}
              </div>
            </div>
            <div className="modal-actions">
              {activeOrder.trackable && (
                <button type="button" className="secondary-btn" onClick={() => handleTrackOrder(activeOrder)}>
                  Track order
                </button>
              )}
              <button type="button" className="ghost-btn" onClick={() => handleOrderSupport(activeOrder)}>
                Get support
              </button>
              <button type="button" className="ghost-btn" onClick={handleCloseOrderModal}>
                Close
              </button>
            </div>
          </div>
        </div>
      )}


      <style jsx>{`
        .profile-page {
          display: flex;
          flex-direction: column;
          gap: 2rem;
        }

        .profile-layout {
          display: flex;
          flex-direction: column;
          gap: 2rem;
        }

        .profile-content {
          display: flex;
          flex-direction: column;
          gap: 2rem;
          flex: 1;
        }

        .profile-card,
        .profile-section {
          background: #fff;
          border-radius: 16px;
          border: 1px solid rgba(100, 27, 46, 0.12);
          padding: 1.75rem 1.5rem;
          box-shadow: 0 18px 32px rgba(0, 0, 0, 0.06);
        }

        .profile-card {
          display: flex;
          flex-direction: column;
          gap: 1.5rem;
        }

        .profile-card .profile-section {
          border: none;
          padding: 0;
          box-shadow: none;
        }

        .profile-sidebar {
          background: #fff6f5;
          border-radius: 16px;
          border: 1px solid rgba(100, 27, 46, 0.12);
          box-shadow: 0 14px 28px rgba(0, 0, 0, 0.05);
          padding: 1.5rem;
          display: flex;
          flex-direction: column;
          gap: 1.25rem;
        }

        .sidebar-title {
          margin: 0;
          font-weight: 700;
          color: #641b2e;
          letter-spacing: 0.03em;
          text-transform: uppercase;
          font-size: 0.85rem;
        }

        .sidebar-nav {
          display: flex;
          flex-wrap: wrap;
          gap: 0.75rem;
        }

        .sidebar-link {
          border: 1px solid rgba(100, 27, 46, 0.2);
          border-radius: 999px;
          padding: 0.55rem 1.1rem;
          background: #fff;
          color: #641b2e;
          font-weight: 600;
          cursor: pointer;
          transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .sidebar-link:hover {
          background: rgba(100, 27, 46, 0.08);
          transform: translateY(-1px);
        }

        .sidebar-link.active {
          background: linear-gradient(135deg, #641b2e, #be5b50);
          color: #fff;
          border-color: transparent;
          box-shadow: 0 12px 20px rgba(100, 27, 46, 0.2);
        }

        [data-section-id]:focus-visible {
          outline: 2px solid rgba(100, 27, 46, 0.45);
          outline-offset: 6px;
        }

        @media (min-width: 960px) {
          .profile-layout {
            flex-direction: row;
            align-items: flex-start;
          }

          .profile-sidebar {
            position: sticky;
            top: 96px;
            min-width: 220px;
            max-width: 240px;
          }

          .sidebar-nav {
            flex-direction: column;
          }
        }

        .modal-backdrop {
          position: fixed;
          inset: 0;
          background: rgba(26, 26, 26, 0.55);
          display: grid;
          place-items: center;
          padding: 1.5rem;
          z-index: 1000;
        }

        .modal-card {
          width: min(520px, 100%);
          background: #fff;
          border-radius: 16px;
          box-shadow: 0 24px 48px rgba(0, 0, 0, 0.18);
          overflow: hidden;
          display: flex;
          flex-direction: column;
          gap: 1.25rem;
          max-height: calc(100vh - 3rem);
        }

        .modal-card.wide {
          width: min(720px, 100%);
        }

        .modal-card.danger {
          border: 2px solid rgba(198, 40, 40, 0.25);
        }

        .modal-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1.25rem 1.5rem 0 1.5rem;
        }

        .modal-header h4 {
          margin: 0;
          font-size: 1.2rem;
        }

        .modal-close {
          border: none;
          background: transparent;
          font-size: 1.25rem;
          cursor: pointer;
          line-height: 1;
          color: #1f1f1f;
          transition: color 0.2s ease;
        }

        .modal-close:focus-visible {
          outline: 2px solid rgba(100, 27, 46, 0.5);
          outline-offset: 4px;
        }

        .modal-close:hover {
          color: #641b2e;
        }

        .modal-body {
          padding: 0 1.5rem 1.5rem 1.5rem;
          display: flex;
          flex-direction: column;
          gap: 1rem;
          overflow-y: auto;
        }

        .modal-field {
          display: flex;
          flex-direction: column;
          gap: 0.4rem;
          font-size: 0.9rem;
        }

        .modal-field span {
          font-weight: 600;
        }

        .modal-field input,
        .modal-field select {
          border: 1px solid rgba(100, 27, 46, 0.2);
          border-radius: 10px;
          padding: 0.7rem 0.8rem;
          font-size: 0.95rem;
        }

        .modal-actions {
          padding: 0 1.5rem 1.5rem 1.5rem;
          display: flex;
          justify-content: flex-end;
          gap: 0.75rem;
          flex-wrap: wrap;
        }

        .modal-status {
          padding: 0.65rem 0.9rem;
          border-radius: 12px;
          font-size: 0.9rem;
          font-weight: 600;
        }

        .modal-status.success {
          background: rgba(67, 160, 71, 0.12);
          color: #2e7d32;
        }

        .modal-status.error {
          background: rgba(198, 40, 40, 0.12);
          color: #c62828;
        }

        .billing-columns {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
          gap: 1.5rem;
        }

        .billing-list {
          display: flex;
          flex-direction: column;
          gap: 1rem;
        }

        .billing-form {
          display: flex;
          flex-direction: column;
          gap: 1rem;
          padding: 1rem;
          border: 1px solid rgba(100, 27, 46, 0.12);
          border-radius: 12px;
          background: rgba(248, 238, 238, 0.35);
        }

        .billing-subtitle {
          margin: 0;
          font-weight: 700;
          color: #1d1d1f;
        }

        .billing-card {
          padding: 1rem;
          border: 1px solid rgba(100, 27, 46, 0.12);
          border-radius: 12px;
          display: flex;
          justify-content: space-between;
          gap: 0.75rem;
          background: #fff7f6;
        }

        .billing-card-name {
          margin: 0;
          font-weight: 600;
          display: flex;
          gap: 0.5rem;
          align-items: center;
        }

        .billing-card-meta,
        .billing-card-note {
          margin: 0.25rem 0 0;
          color: #666;
          font-size: 0.85rem;
        }

        .billing-card-actions {
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
        }

        .badge {
          background: rgba(67, 160, 71, 0.12);
          color: #2e7d32;
          font-size: 0.75rem;
          padding: 0.2rem 0.6rem;
          border-radius: 999px;
        }

        .billing-row {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 0.75rem;
        }

        .checkbox-field {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          font-size: 0.9rem;
        }

        .checkbox-field input {
          width: 18px;
          height: 18px;
        }

        .order-modal {
          gap: 1.5rem;
        }

        .order-meta-block {
          display: flex;
          flex-direction: column;
          gap: 0.35rem;
        }

        .order-meta-line {
          margin: 0;
          color: #555;
        }

        .order-items {
          display: flex;
          flex-direction: column;
          gap: 0.85rem;
          padding-top: 0.5rem;
          border-top: 1px dashed rgba(100, 27, 46, 0.25);
        }

        .order-item-row {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 0.75rem;
        }

        .order-item-name {
          margin: 0;
          font-weight: 600;
        }

        .order-item-meta {
          margin: 0.2rem 0 0;
          color: #666;
          font-size: 0.85rem;
        }

        .order-item-price {
          font-weight: 600;
        }

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
          .modal-card,
          .modal-card.wide {
            width: 100%;
            max-height: calc(100vh - 2rem);
          }

          .modal-actions {
            justify-content: center;
          }

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
