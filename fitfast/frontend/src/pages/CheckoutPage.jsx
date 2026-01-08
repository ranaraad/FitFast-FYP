import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { clearCart, getCart } from "../cartStorage";
import api from "../api";

const DEFAULT_CONTACT = {
	fullName: "",
	email: "",
	phone: "",
};

const DEFAULT_ADDRESS = {
	line1: "",
	line2: "",
	city: "",
	state: "",
	postalCode: "",
};

const DEFAULT_CARD = {
	nameOnCard: "",
	number: "",
	expiry: "",
	cvc: "",
};

const STORAGE_KEYS = {
	contact: "fitfast_checkout_contact",
	address: "fitfast_checkout_address",
	delivery: "fitfast_checkout_delivery",
	payment: "fitfast_checkout_payment",
	promo: "fitfast_checkout_promo",
};

const SHIPPING_OPTIONS = [
	{
		id: "standard",
		label: "Standard delivery",
		description: "Arrives in 3-5 business days",
		detail: "Free on orders $75+",
		icon: "🚚",
		baseCost: 8,
	},
	{
		id: "express",
		label: "Express courier",
		description: "Guaranteed next-day in major cities",
		detail: "Priority handling",
		icon: "⚡",
		baseCost: 16,
	},
	{
		id: "pickup",
		label: "In-store pickup",
		description: "Ready in under 2 hours",
		detail: "We will notify you when it is ready",
		icon: "🏬",
		baseCost: 0,
	},
];

const PAYMENT_METHODS = [
	{
		id: "card",
		label: "Card payment",
		caption: "Visa, Mastercard, American Express",
		icon: "💳",
	},
	{
		id: "cod",
		label: "Cash on delivery",
		caption: "Settle at the doorstep with cash or card",
		icon: "🤝",
	},
];

const PROMO_CODES = {
	FITFAST10: {
		code: "FITFAST10",
		label: "FitFast Insider 10% off",
		rate: 0.1,
	},
	VIP15: {
		code: "VIP15",
		label: "VIP 15% appreciation",
		rate: 0.15,
	},
};

const isBrowser = typeof window !== "undefined";
const ORDER_STATUS_STORAGE = "fitfast_recent_order";
const ORDER_HISTORY_PREFIX = "fitfast_account_orders";

function readStoredValue(key, fallback) {
	if (!isBrowser || !key) return fallback;

	try {
		const raw = window.localStorage.getItem(key);
		return raw ? JSON.parse(raw) : fallback;
	} catch (err) {
		console.error(`Failed to parse stored checkout data for ${key}`, err);
		return fallback;
	}
}

function writeStoredValue(key, value) {
	if (!isBrowser || !key) return;

	try {
		if (value === null || value === undefined) {
			window.localStorage.removeItem(key);
		} else {
			window.localStorage.setItem(key, JSON.stringify(value));
		}
	} catch (err) {
		console.error(`Failed to persist checkout data for ${key}`, err);
	}
}

function persistLatestOrder(payload) {
	if (!isBrowser) return;

	try {
		window.localStorage.setItem(ORDER_STATUS_STORAGE, JSON.stringify(payload));
		window.dispatchEvent(new Event("fitfast-order-updated"));
	} catch (err) {
		console.error("Failed to persist latest order", err);
	}
}

function readAuthUser() {
	if (!isBrowser) return null;
	try {
		const raw = window.localStorage.getItem("auth_user");
		return raw ? JSON.parse(raw) : null;
	} catch (err) {
		console.error("Failed to parse auth_user", err);
		return null;
	}
}

function resolveOrderHistoryKey(authUser, contact) {
	if (authUser?.id) return `${ORDER_HISTORY_PREFIX}_${authUser.id}`;
	const email = (authUser?.email || contact?.email || "").trim().toLowerCase();
	return email ? `${ORDER_HISTORY_PREFIX}_${email}` : null;
}

function resolveStorageKey(baseKey, authUser) {
	if (!baseKey) return baseKey;
	if (authUser?.id) return `${baseKey}_${authUser.id}`;
	const email = (authUser?.email || "").trim().toLowerCase();
	return email ? `${baseKey}_${email}` : baseKey;
}

function appendOrderToAccountHistory(orderRecord, authUser) {
	const key = resolveOrderHistoryKey(authUser, orderRecord.contact);
	if (!key) return;
	const existing = readStoredValue(key, []);
	const next = Array.isArray(existing)
		? existing.filter((entry) => entry && entry.code !== orderRecord.code)
		: [];
	next.unshift(orderRecord);
	writeStoredValue(key, next.slice(0, 25));
}

function loadInitialContact(storageKey, authUser) {
	const stored = storageKey ? readStoredValue(storageKey, null) : null;
	if (stored) return { ...DEFAULT_CONTACT, ...stored };

	const user = authUser || readAuthUser();
	if (user) {
		return {
			fullName: user.name || "",
			email: user.email || "",
			phone: user.phone || "",
		};
	}

	return DEFAULT_CONTACT;
}

function loadInitialAddress(storageKey) {
	const stored = storageKey ? readStoredValue(storageKey, null) : null;
	if (stored) return { ...DEFAULT_ADDRESS, ...stored };
	return DEFAULT_ADDRESS;
}

function formatPrice(price) {
	const numeric = Number(price);
	if (!Number.isFinite(numeric)) return "$0.00";
	return `$${numeric.toFixed(2)}`;
}

function generateConfirmationCode() {
	const now = new Date();
	const year = now.getFullYear().toString().slice(-2);
	const dayOfYear = Math.floor(
		(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()) -
			Date.UTC(now.getFullYear(), 0, 0)) /
			86400000
	)
		.toString()
		.padStart(3, "0");
	const random = Math.floor(Math.random() * 900 + 100);
	return `FF${year}-${dayOfYear}-${random}`;
}

function estimateDeliveryHours(optionId) {
	switch (optionId) {
		case "express":
			return 24;
		case "pickup":
			return 4;
		default:
			return 60;
	}
}

export default function CheckoutPage() {
	const navigate = useNavigate();
	const [cartItems, setCartItems] = useState(() => getCart());
	const [authUser, setAuthUser] = useState(() => readAuthUser());
	const storageKeys = useMemo(
		() => ({
			contact: resolveStorageKey(STORAGE_KEYS.contact, authUser),
			address: resolveStorageKey(STORAGE_KEYS.address, authUser),
			delivery: resolveStorageKey(STORAGE_KEYS.delivery, authUser),
			payment: resolveStorageKey(STORAGE_KEYS.payment, authUser),
			promo: resolveStorageKey(STORAGE_KEYS.promo, authUser),
		}),
		[authUser]
	);
	const [contactInfo, setContactInfo] = useState(() => loadInitialContact(storageKeys.contact, authUser));
	const [shippingAddress, setShippingAddress] = useState(() => loadInitialAddress(storageKeys.address));
	const [deliveryOption, setDeliveryOption] = useState(() => {
		const stored = readStoredValue(storageKeys.delivery, "standard");
		return stored || "standard";
	});
	const [paymentMethod, setPaymentMethod] = useState(() => {
		const stored = readStoredValue(storageKeys.payment, "card");
		return stored || "card";
	});
	const [promoInput, setPromoInput] = useState(() => {
		const stored = readStoredValue(storageKeys.promo, { input: "", applied: null });
		return stored?.input || "";
	});
	const [appliedPromo, setAppliedPromo] = useState(() => {
		const stored = readStoredValue(storageKeys.promo, { input: "", applied: null });
		return stored?.applied || null;
	});
	const [cardDetails, setCardDetails] = useState(() => ({ ...DEFAULT_CARD }));
	const [promoFeedback, setPromoFeedback] = useState("");
	const [isSubmitting, setIsSubmitting] = useState(false);
	const [orderError, setOrderError] = useState(null);

	useEffect(() => {
		const syncAuth = () => setAuthUser(readAuthUser());
		syncAuth();
		window.addEventListener("storage", syncAuth);
		return () => window.removeEventListener("storage", syncAuth);
	}, []);

	useEffect(() => {
		setContactInfo(loadInitialContact(storageKeys.contact, authUser));
		setShippingAddress(loadInitialAddress(storageKeys.address));
		setDeliveryOption(() => {
			const stored = readStoredValue(storageKeys.delivery, "standard");
			return stored || "standard";
		});
		setPaymentMethod(() => {
			const stored = readStoredValue(storageKeys.payment, "card");
			return stored || "card";
		});
		const storedPromo = readStoredValue(storageKeys.promo, { input: "", applied: null }) || {
			input: "",
			applied: null,
		};
		setPromoInput(storedPromo.input || "");
		setAppliedPromo(storedPromo.applied || null);
	}, [storageKeys.contact, storageKeys.address, storageKeys.delivery, storageKeys.payment, storageKeys.promo, authUser]);

	useEffect(() => {
		const syncCart = () => setCartItems(getCart());

		syncCart();
		window.addEventListener("cart-updated", syncCart);
		window.addEventListener("storage", syncCart);

		return () => {
			window.removeEventListener("cart-updated", syncCart);
			window.removeEventListener("storage", syncCart);
		};
	}, []);

	useEffect(() => {
		writeStoredValue(storageKeys.contact, contactInfo);
	}, [storageKeys.contact, contactInfo]);

	useEffect(() => {
		writeStoredValue(storageKeys.address, shippingAddress);
	}, [storageKeys.address, shippingAddress]);

	useEffect(() => {
		writeStoredValue(storageKeys.delivery, deliveryOption);
	}, [storageKeys.delivery, deliveryOption]);

	useEffect(() => {
		writeStoredValue(storageKeys.payment, paymentMethod);
	}, [storageKeys.payment, paymentMethod]);

	useEffect(() => {
		writeStoredValue(storageKeys.promo, {
			input: promoInput,
			applied: appliedPromo,
		});
	}, [storageKeys.promo, promoInput, appliedPromo]);

	useEffect(() => {
		if (!contactInfo.fullName) return;

		setCardDetails((prev) => {
			if (prev.nameOnCard) return prev;
			return { ...prev, nameOnCard: contactInfo.fullName };
		});
	}, [contactInfo.fullName]);

	const subtotal = useMemo(() => {
		return cartItems.reduce((sum, item) => {
			const price = Number(item.price) || 0;
			const quantity = Number(item.quantity) || 1;
			return sum + price * quantity;
		}, 0);
	}, [cartItems]);

	const shippingCost = useMemo(() => {
		if (!cartItems.length) return 0;
		const option = SHIPPING_OPTIONS.find((entry) => entry.id === deliveryOption);
		if (!option) return 0;

		if (option.id === "standard") {
			return subtotal >= 75 ? 0 : option.baseCost;
		}

		return option.baseCost;
	}, [deliveryOption, cartItems.length, subtotal]);

	const discount = useMemo(() => {
		if (!appliedPromo) return 0;
		const rate = Number(appliedPromo.rate) || 0;
		return subtotal * rate;
	}, [appliedPromo, subtotal]);

	const taxEstimate = useMemo(() => {
		if (!subtotal) return 0;
		const taxableAmount = Math.max(0, subtotal - discount);
		return taxableAmount * 0.08;
	}, [subtotal, discount]);

	const estimatedTotal = useMemo(() => {
		return Math.max(0, subtotal - discount) + shippingCost + taxEstimate;
	}, [subtotal, discount, shippingCost, taxEstimate]);

	const formatCartPrice = (value) => formatPrice(value || 0);

	const handleContactChange = (event) => {
		const { name, value } = event.target;
		setContactInfo((prev) => ({ ...prev, [name]: value }));
	};

	const handleAddressChange = (event) => {
		const { name, value } = event.target;
		setShippingAddress((prev) => ({ ...prev, [name]: value }));
	};

	const handleCardInputChange = (event) => {
		const { name, value } = event.target;

		if (name === "number") {
			const digits = value.replace(/\D/g, "").slice(0, 16);
			const grouped = digits.replace(/(\d{4})(?=\d)/g, "$1 ").trim();
			setCardDetails((prev) => ({ ...prev, number: grouped }));
			return;
		}

		if (name === "expiry") {
			const digits = value.replace(/\D/g, "").slice(0, 4);
			let formatted = digits;
			if (digits.length >= 3) {
				formatted = `${digits.slice(0, 2)}/${digits.slice(2)}`;
			}
			setCardDetails((prev) => ({ ...prev, expiry: formatted }));
			return;
		}

		if (name === "cvc") {
			const digits = value.replace(/\D/g, "").slice(0, 4);
			setCardDetails((prev) => ({ ...prev, cvc: digits }));
			return;
		}

		setCardDetails((prev) => ({ ...prev, [name]: value }));
	};

	const handleApplyPromo = () => {
		const normalized = promoInput.trim().toUpperCase();
		if (!normalized) {
			setPromoFeedback("Enter a promo code to see potential savings.");
			setAppliedPromo(null);
			return;
		}

		const match = PROMO_CODES[normalized];
		if (match) {
			setAppliedPromo(match);
			setPromoFeedback(`${match.label} applied`);
		} else {
			setAppliedPromo(null);
			setPromoFeedback("Promo code not recognized. Please check the code and try again.");
		}
	};

	const validatePayload = () => {
		if (!cartItems.length) {
			setOrderError("Your cart is empty. Add items before checking out.");
			return false;
		}

		const requiredFields = [
			{ path: contactInfo.fullName, label: "Full name" },
			{ path: contactInfo.email, label: "Email" },
			{ path: contactInfo.phone, label: "Phone" },
			{ path: shippingAddress.line1, label: "Street address" },
			{ path: shippingAddress.city, label: "City" },
			{ path: shippingAddress.state, label: "State" },
			{ path: shippingAddress.postalCode, label: "Postal code" },
		];

		const missing = requiredFields
			.filter((entry) => !entry.path || !entry.path.toString().trim())
			.map((entry) => entry.label);

		if (missing.length) {
			setOrderError(`Please complete: ${missing.join(", ")}`);
			return false;
		}

		if (paymentMethod === "card") {
			const sanitizedNumber = cardDetails.number.replace(/\s+/g, "");
			const issues = [];

			if (!cardDetails.nameOnCard.trim()) {
				issues.push("Name on card");
			}
			if (sanitizedNumber.length < 12) {
				issues.push("Card number");
			}
			if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardDetails.expiry)) {
				issues.push("Expiry (MM/YY)");
			}
			if (!/^\d{3,4}$/.test(cardDetails.cvc)) {
				issues.push("CVC");
			}

			if (issues.length) {
				setOrderError(`Please review card details: ${issues.join(", ")}`);
				return false;
			}
		}

		setOrderError(null);
		return true;
	};

	const resetDraft = () => {
		setContactInfo(loadInitialContact(storageKeys.contact, authUser));
		setShippingAddress(loadInitialAddress(storageKeys.address));
		setDeliveryOption("standard");
		setPaymentMethod("card");
		setPromoInput("");
		setAppliedPromo(null);
		setCardDetails({ ...DEFAULT_CARD });

		const uniqueKeys = new Set(
			Object.values(storageKeys).filter(Boolean)
		);
		uniqueKeys.forEach((key) => writeStoredValue(key, null));
	};

	const handlePlaceOrder = async (event) => {
		event.preventDefault();
		if (isSubmitting) return;

		if (!validatePayload()) return;

		setIsSubmitting(true);

		const confirmationCode = generateConfirmationCode();
		const sanitizedNumber = paymentMethod === "card" ? cardDetails.number.replace(/\D/g, "") : "";
		const cardLast4 = sanitizedNumber ? sanitizedNumber.slice(-4) : null;
		const authUser = readAuthUser();

		try {
			// Get the store_id from the first cart item (assuming all items are from the same store)
			const storeId = cartItems[0]?.storeId;
			
			if (!storeId) {
				setOrderError("Unable to determine store for this order.");
				setIsSubmitting(false);
				return;
			}

			// Prepare order data for backend
			const orderData = {
				store_id: storeId,
				items: cartItems.map((item) => ({
					id: item.id,
					quantity: item.quantity || 1,
					price: Number(item.price) || 0,
					size: item.size || null,
					color: item.color || null,
				})),
				total_amount: estimatedTotal,
				delivery_address: `${shippingAddress.street}, ${shippingAddress.city}, ${shippingAddress.state} ${shippingAddress.postalCode}`,
				contact: {
					fullName: contactInfo.fullName,
					email: contactInfo.email,
					phone: contactInfo.phone,
				},
				payment_method: paymentMethod,
				card_last4: cardLast4,
			};

			// Send order to backend API
			const response = await api.post("/orders", orderData);

			if (response.data.success) {
				// Order created successfully in backend
				const selectedShipping = SHIPPING_OPTIONS.find((entry) => entry.id === deliveryOption) || SHIPPING_OPTIONS[0];
				const selectedPayment = PAYMENT_METHODS.find((entry) => entry.id === paymentMethod) || PAYMENT_METHODS[0];
				const placedAt = new Date();
				const etaHours = estimateDeliveryHours(selectedShipping.id);
				const estimatedArrival = new Date(placedAt.getTime() + etaHours * 60 * 60 * 1000);
				
				const orderRecord = {
					orderId: response.data.order?.id ?? null,
					code: confirmationCode,
					placedAt: placedAt.toISOString(),
					eta: estimatedArrival.toISOString(),
					status: "Processing",
					cmsStatus: response.data.order?.status ?? "pending",
					delivery: {
						id: selectedShipping.id,
						label: selectedShipping.label,
						description: selectedShipping.description,
						status: response.data.order?.delivery?.status ?? null,
					},
					payment: {
						id: selectedPayment.id,
						label: selectedPayment.label,
						cardLast4,
						status: response.data.order?.payment?.status ?? "pending",
						method: response.data.order?.payment?.method ?? selectedPayment.id,
					},
					contact: { ...contactInfo },
					shippingAddress: { ...shippingAddress, country: "United States" },
					items: cartItems.map((item) => ({
						id: item.id,
						name: item.name,
						quantity: item.quantity || 1,
						price: Number(item.price) || 0,
						image: item.image || null,
						storeName: item.storeName || null,
						size: item.size || null,
						color: item.color || null,
					})),
					totals: {
						subtotal,
						shipping: shippingCost,
						tax: taxEstimate,
						discount,
						total: estimatedTotal,
					},
					promoCode: appliedPromo?.code || null,
					trackable: selectedShipping.id !== "pickup",
					userId: authUser?.id ?? null,
					userEmail: (authUser?.email || contactInfo.email || "").trim().toLowerCase(),
				};

				// Still save to localStorage for order status page
				persistLatestOrder(orderRecord);
				appendOrderToAccountHistory(orderRecord, authUser);
				clearCart();
				setCartItems([]);
				setIsSubmitting(false);
				resetDraft();
				navigate("/order-status");
			} else {
				setOrderError(response.data.message || "Failed to create order.");
				setIsSubmitting(false);
			}
		} catch (error) {
			console.error("Order creation error:", error);
			setOrderError(error.response?.data?.message || "Failed to create order. Please try again.");
			setIsSubmitting(false);
		}
	};

	const handleNavigateBackToCart = () => {
		navigate("/cart");
	};

	const handleBrowseStores = () => {
		navigate("/");
	};

	return (
		<div className="checkout-page">
			<div className="checkout-hero">
				<div className="checkout-hero-text">
					<p className="eyebrow">Finalize your fit</p>
					<h1>Checkout</h1>
					<p className="muted">
						Confirm your delivery preferences and finalize payment details.
					</p>
				</div>

				<div className="checkout-progress">
					<div className="progress-step completed">Cart</div>
					<div className="progress-connector completed" />
					<div className="progress-step active">Details</div>
					<div className="progress-connector" />
					<div className="progress-step">Review</div>
				</div>
			</div>

			<div className="checkout-banner secure-banner">
				<span className="banner-icon">🔒</span>
				<div>
					<strong>Payment protected</strong>
					<p className="muted small">Card details are captured securely. Double-check your delivery preferences before placing the order.</p>
				</div>
			</div>

			{orderError && <div className="checkout-banner error-banner">{orderError}</div>}

			<div className="checkout-grid">
				<section className="checkout-main">
					<form onSubmit={handlePlaceOrder}>
						<div className="checkout-section">
							<div className="section-heading">
								<div>
									  <h2>Contact details</h2>
									  <p className="muted small">We will use this information for confirmations and delivery updates.</p>
								</div>
								<button type="button" className="link-btn" onClick={handleNavigateBackToCart}>
									← Back to cart
								</button>
							</div>

							<div className="form-grid two-col">
								<label className="form-field">
									<span>Full name</span>
									<input
										type="text"
										name="fullName"
										autoComplete="name"
										value={contactInfo.fullName}
										onChange={handleContactChange}
									/>
								</label>
								<label className="form-field">
									<span>Email</span>
									<input
										type="email"
										name="email"
										autoComplete="email"
										value={contactInfo.email}
										onChange={handleContactChange}
									/>
								</label>
								<label className="form-field">
									<span>Phone</span>
									<input
										type="tel"
										name="phone"
										autoComplete="tel"
										value={contactInfo.phone}
										onChange={handleContactChange}
									/>
								</label>
							</div>
						</div>

						<div className="checkout-section">
							<div className="section-heading">
								<div>
									  <h2>Shipping</h2>
									  <p className="muted small">Tell us where to send your order.</p>
								</div>
							</div>

							<div className="form-grid two-col">
								<label className="form-field full">
									<span>Street address</span>
									<input
										type="text"
										name="line1"
										autoComplete="address-line1"
										value={shippingAddress.line1}
										onChange={handleAddressChange}
									/>
								</label>
								<label className="form-field full">
									<span>Apartment, suite, etc.</span>
									<input
										type="text"
										name="line2"
										autoComplete="address-line2"
										value={shippingAddress.line2}
										onChange={handleAddressChange}
									/>
								</label>
								<label className="form-field">
									<span>City</span>
									<input
										type="text"
										name="city"
										autoComplete="address-level2"
										value={shippingAddress.city}
										onChange={handleAddressChange}
									/>
								</label>
								<label className="form-field">
									<span>State</span>
									<input
										type="text"
										name="state"
										autoComplete="address-level1"
										value={shippingAddress.state}
										onChange={handleAddressChange}
									/>
								</label>
								<label className="form-field">
									<span>Postal code</span>
									<input
										type="text"
										name="postalCode"
										autoComplete="postal-code"
										value={shippingAddress.postalCode}
										onChange={handleAddressChange}
									/>
								</label>
								<label className="form-field">
									<span>Country</span>
									<input type="text" value="United States" disabled />
								</label>
							</div>
						</div>

						<div className="checkout-section">
							<div className="section-heading">
								<div>
									  <h2>Delivery speed</h2>
									  <p className="muted small">Choose the option that suits your schedule.</p>
								</div>
							</div>

							<div className="option-list">
								{SHIPPING_OPTIONS.map((option) => {
									const selected = option.id === deliveryOption;
									return (
										<label key={option.id} className={`option-card ${selected ? "selected" : ""}`}>
											<input
												type="radio"
												name="deliveryOption"
												value={option.id}
												checked={selected}
												onChange={() => setDeliveryOption(option.id)}
											/>
											<div className="option-icon">{option.icon}</div>
											<div className="option-content">
												<div className="option-heading">
													<span>{option.label}</span>
													<span className="option-price">
														{option.id === "standard" && subtotal >= 75 ? "Free" : formatPrice(option.baseCost)}
													</span>
												</div>
												<p className="muted small">{option.description}</p>
												<p className="muted x-small">{option.detail}</p>
											</div>
										</label>
									);
								})}
							</div>
						</div>

						<div className="checkout-section">
							<div className="section-heading">
								<div>
									<h2>Payment method</h2>
									<p className="muted small">Choose how you would like to pay for this order.</p>
								</div>
							</div>

							<div className="option-list">
								{PAYMENT_METHODS.map((method) => {
									const selected = method.id === paymentMethod;
									const badgeText = method.id === "cod" ? "COD" : "Card";
									return (
										<label key={method.id} className={`option-card ${selected ? "selected" : ""}`}>
											<input
												type="radio"
												name="paymentMethod"
												value={method.id}
												checked={selected}
												onChange={() => setPaymentMethod(method.id)}
											/>
											<div className="option-icon">{method.icon}</div>
											<div className="option-content">
												<div className="option-heading">
													<span>{method.label}</span>
													<span className="method-pill">{badgeText}</span>
												</div>
												<p className="muted small">{method.caption}</p>
											</div>
										</label>
									);
								})}
							</div>

							{paymentMethod === "card" && (
								<div className="card-fields">
									<label className="form-field full">
										<span>Name on card</span>
										<input
											type="text"
											name="nameOnCard"
											autoComplete="cc-name"
											value={cardDetails.nameOnCard}
											onChange={handleCardInputChange}
											placeholder="Name as displayed"
										/>
									</label>
									<label className="form-field full">
										<span>Card number</span>
										<input
											type="text"
											name="number"
											inputMode="numeric"
											autoComplete="cc-number"
											value={cardDetails.number}
											onChange={handleCardInputChange}
											placeholder="1234 5678 9012 3456"
										/>
									</label>
									<label className="form-field">
										<span>Expiry (MM/YY)</span>
										<input
											type="text"
											name="expiry"
											inputMode="numeric"
											autoComplete="cc-exp"
											value={cardDetails.expiry}
											onChange={handleCardInputChange}
											placeholder="MM/YY"
										/>
									</label>
									<label className="form-field">
										<span>CVC</span>
										<input
											type="text"
											name="cvc"
											inputMode="numeric"
											autoComplete="cc-csc"
											value={cardDetails.cvc}
											onChange={handleCardInputChange}
											placeholder="123"
										/>
									</label>
								</div>
							)}
						</div>

						<div className="checkout-actions">
							<button type="submit" className="checkout-btn" disabled={isSubmitting || !cartItems.length}>
								{isSubmitting ? "Placing order..." : "Place order"}
							</button>
							<button type="button" className="secondary-btn" onClick={handleBrowseStores}>
								Keep browsing
							</button>
						</div>
					</form>
				</section>

				<aside className="checkout-sidebar">
					<div className="summary-card">
						<h3>Order summary</h3>
						<ul className="summary-items">
							{cartItems.length ? (
								cartItems.map((item) => (
									<li key={item.cartKey} className="summary-item">
										<div className="summary-thumb">
											{item.image ? (
												<img src={item.image} alt={item.name} loading="lazy" />
											) : (
												<div className="image-placeholder">{item.name?.[0] || ""}</div>
											)}
										</div>
										<div className="summary-details">
											<p className="summary-name">{item.name}</p>
											<p className="muted x-small">
												{item.storeName ? `${item.storeName} • ` : ""}
												Qty {item.quantity || 1}
												{item.size ? ` • ${item.size}` : ""}
												{item.color ? ` • ${item.color}` : ""}
											</p>
										</div>
										<div className="summary-price">{formatCartPrice(item.price)}</div>
									</li>
								))
							) : (
								<li className="empty-summary">
									<p>Your bag is empty.</p>
									<button type="button" className="link-btn" onClick={handleBrowseStores}>
										Explore stores
									</button>
								</li>
							)}
						</ul>

						<div className="summary-divider" />

						<div className="summary-row">
							<span>Subtotal</span>
							<span>{formatPrice(subtotal)}</span>
						</div>
						<div className="summary-row">
							<span>Shipping</span>
							<span>{shippingCost === 0 ? "Free" : formatPrice(shippingCost)}</span>
						</div>
						<div className="summary-row">
							<span>Tax estimate</span>
							<span>{formatPrice(taxEstimate)}</span>
						</div>
						{discount > 0 && (
							<div className="summary-row savings">
								<span>Promo savings</span>
								<span>-{formatPrice(discount)}</span>
							</div>
						)}
						<div className="summary-row total">
							<span>Estimated total</span>
							<span>{formatPrice(estimatedTotal)}</span>
						</div>
					</div>

					<div className="promo-card">
						<div className="section-heading">
							<h4>Apply promo</h4>
							{appliedPromo && <span className="pill-count">{appliedPromo.code}</span>}
						</div>
						<div className="promo-form">
							<input
								type="text"
								value={promoInput}
								onChange={(event) => setPromoInput(event.target.value)}
								placeholder="Enter promo code"
							/>
							<button type="button" onClick={handleApplyPromo}>
								Apply
							</button>
						</div>
						{promoFeedback && <p className="muted x-small">{promoFeedback}</p>}
					</div>
				</aside>
			</div>
		</div>
	);
}
