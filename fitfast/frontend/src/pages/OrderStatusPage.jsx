import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";

const ORDER_STATUS_STORAGE = "fitfast_recent_order";
const isBrowser = typeof window !== "undefined";

function readLatestOrder() {
	if (!isBrowser) return null;

	try {
		const raw = window.localStorage.getItem(ORDER_STATUS_STORAGE);
		return raw ? JSON.parse(raw) : null;
	} catch (err) {
		console.error("Failed to read latest order", err);
		return null;
	}
}

function formatCurrency(value) {
	const numeric = Number(value) || 0;
	return `$${numeric.toFixed(2)}`;
}

function formatDateTime(iso) {
	if (!iso) return "";
	const date = new Date(iso);
	return date.toLocaleString(undefined, {
		weekday: "short",
		month: "short",
		day: "numeric",
		hour: "2-digit",
		minute: "2-digit",
	});
}

function formatDate(iso) {
	if (!iso) return "";
	const date = new Date(iso);
	return date.toLocaleDateString(undefined, {
		weekday: "long",
		month: "long",
		day: "numeric",
	});
}

function hoursBetween(fromIso, toDate = new Date()) {
	if (!fromIso) return 0;
	const from = new Date(fromIso);
	return (toDate.getTime() - from.getTime()) / (1000 * 60 * 60);
}

function buildTimeline(order) {
	if (!order) return [];

	const totalHours = Math.max(
		1,
		(new Date(order.eta).getTime() - new Date(order.placedAt).getTime()) /
			(1000 * 60 * 60)
	);
	const elapsedHours = Math.max(0, hoursBetween(order.placedAt));
	const checkpoints = [
		0,
		Math.min(totalHours * 0.2, 4),
		totalHours * 0.5,
		totalHours * 0.8,
		totalHours,
	];

	const isPickup = order.delivery?.id === "pickup";

	const stages = [
		{
			id: "confirmed",
			label: "Order confirmed",
			detail: "We received your order and payment details.",
		},
		{
			id: "preparing",
			label: "Preparing items",
			detail: "Our team is assembling your curated pieces.",
		},
		{
			id: "dispatched",
			label: isPickup ? "Ready for pickup" : "Dispatched",
			detail: isPickup
				? "Your order is ready at the selected location."
				: "Parcel has left the warehouse and is with the courier.",
		},
		{
			id: "out-for-delivery",
			label: isPickup ? "Awaiting pickup" : "Out for delivery",
			detail: isPickup
				? "Stop by with a valid ID to collect your order."
				: "Driver is on the way to your address.",
		},
		{
			id: "delivered",
			label: isPickup ? "Picked up" : "Delivered",
			detail: isPickup
				? "Enjoy your new look. Thanks for choosing FitFast!"
				: "Package will arrive soon. Thanks for choosing FitFast!",
		},
	];

	let activeIndex = stages.findIndex((_, idx) => elapsedHours < checkpoints[idx]);
	if (activeIndex === -1) activeIndex = stages.length - 1;

	return stages.map((stage, index) => {
		const threshold = checkpoints[index];
		const prevThreshold = index === 0 ? 0 : checkpoints[index - 1];
		let state = "upcoming";

		if (elapsedHours >= threshold) {
			state = "completed";
		} else if (elapsedHours >= prevThreshold || index === activeIndex) {
			state = "current";
		}

		const expectedAt = new Date(new Date(order.placedAt).getTime() + threshold * 60 * 60 * 1000);

		return {
			...stage,
			state,
			expectedAt,
		};
	});
}

export default function OrderStatusPage() {
	const navigate = useNavigate();
	const [order, setOrder] = useState(null);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		const syncLatest = () => {
			setOrder(readLatestOrder());
			setLoading(false);
		};

		syncLatest();
		window.addEventListener("storage", syncLatest);
		window.addEventListener("fitfast-order-updated", syncLatest);

		return () => {
			window.removeEventListener("storage", syncLatest);
			window.removeEventListener("fitfast-order-updated", syncLatest);
		};
	}, []);

	const timeline = useMemo(() => buildTimeline(order), [order]);

	const progressPercent = useMemo(() => {
		if (!order) return 0;
		const totalHours = Math.max(
			1,
			(new Date(order.eta).getTime() - new Date(order.placedAt).getTime()) /
				(1000 * 60 * 60)
		);
		const elapsed = Math.max(0, hoursBetween(order.placedAt));
		return Math.min(100, Math.round((elapsed / totalHours) * 100));
	}, [order]);

	const mapSource = useMemo(() => {
		if (!order) return null;
		const { line1, city, state, postalCode } = order.shippingAddress || {};
		const query = [line1, city, state, postalCode].filter(Boolean).join(", ") || "New York";
		return `https://maps.google.com/maps?q=${encodeURIComponent(query)}&t=&z=12&ie=UTF8&iwloc=&output=embed`;
	}, [order]);

	const handleBackHome = () => {
		navigate("/");
	};

	const handleViewStores = () => {
		navigate("/stores/1");
	};

	if (loading) {
		return <div className="order-status-page">Loading latest order...</div>;
	}

	if (!order) {
		return (
			<div className="order-status-page">
				<div className="order-status-empty">
					<h2>No recent order found</h2>
					<p className="muted">Place an order to track delivery progress here.</p>
					<div className="order-status-empty-actions">
						<button type="button" onClick={handleBackHome}>
							Return home
						</button>
					</div>
				</div>
			</div>
		);
	}

	const currentStage = timeline.find((stage) => stage.state === "current") || timeline[timeline.length - 1];

	return (
		<div className="order-status-page">
			<div className="order-status-hero">
				<div>
					<p className="eyebrow">Order tracking</p>
					<h1>Status: {currentStage?.label || "On the way"}</h1>
					<p className="muted">Order {order.code} • Placed {formatDateTime(order.placedAt)}</p>
				</div>
				<div className="order-status-meta">
					<div className="progress-circle">
						<span>{progressPercent}%</span>
					</div>
					<div className="eta-meta">
						<p className="muted x-small">Estimated arrival</p>
						<strong>{formatDate(order.eta)}</strong>
					</div>
				</div>
			</div>

			<div className="order-status-grid">
				<section className="order-progress">
					<div className="progress-bar">
						<div className="progress-bar-fill" style={{ width: `${progressPercent}%` }} />
					</div>
					<ul className="status-list">
						{timeline.map((stage) => (
							<li key={stage.id} className={`status-item ${stage.state}`}>
								<div className="status-marker" />
								<div>
									<div className="status-heading">
										<strong>{stage.label}</strong>
										<span className="muted x-small">{formatDateTime(stage.expectedAt)}</span>
									</div>
									<p className="muted x-small">{stage.detail}</p>
								</div>
							</li>
							))}
					</ul>
				</section>

				<aside className="order-status-aside">
					<div className="map-card">
						<h3>Live route</h3>
						<p className="muted x-small">Courier en route to {order.shippingAddress?.city || "your area"}</p>
						<div className="map-frame">
							{mapSource ? (
								<iframe title="Delivery route" src={mapSource} loading="lazy" allowFullScreen />
							) : (
								<div className="map-placeholder">Map preview unavailable</div>
							)}
						</div>
					</div>

					<div className="order-details-card">
						<h3>Delivery details</h3>
						<div className="detail-row">
							<span>Method</span>
							<strong>{order.delivery?.label}</strong>
						</div>
						<div className="detail-row">
							<span>Destination</span>
							<p className="muted x-small">
								{[order.shippingAddress?.line1, order.shippingAddress?.line2]
									.filter(Boolean)
									.join(", ")}
								<br />
								{[order.shippingAddress?.city, order.shippingAddress?.state, order.shippingAddress?.postalCode]
									.filter(Boolean)
									.join(", ")}
							</p>
						</div>
						<div className="detail-row">
							<span>Contact</span>
							<p className="muted x-small">
								{order.contact?.fullName}
								{order.contact?.phone ? ` • ${order.contact.phone}` : ""}
								<br />
								{order.contact?.email}
							</p>
						</div>
						<div className="detail-row">
							<span>Payment</span>
							<p className="muted x-small">
								{order.payment?.label}
								{order.payment?.cardLast4 ? ` ending in ${order.payment.cardLast4}` : ""}
							</p>
						</div>
					</div>

					<div className="order-summary-card">
						<h3>Order summary</h3>
						<ul className="summary-items">
							{order.items?.map((item, idx) => (
								<li key={`${item.id}-${idx}`} className="summary-item">
									<div className="summary-thumb">
										{item.image ? (
											<img src={item.image} alt={item.name} loading="lazy" />
										) : (
											<div className="image-placeholder">{item.name?.[0] || ""}</div>
										)}
									</div>
									<div>
										<p className="summary-name">{item.name}</p>
										<p className="muted x-small">
											Qty {item.quantity}
											{item.size ? ` • ${item.size}` : ""}
											{item.color ? ` • ${item.color}` : ""}
										</p>
									</div>
									<strong>{formatCurrency(item.price)}</strong>
								</li>
							))}
						</ul>

						<div className="summary-divider" />
						<div className="summary-row">
							<span>Subtotal</span>
							<span>{formatCurrency(order.totals?.subtotal)}</span>
						</div>
						<div className="summary-row">
							<span>Shipping</span>
							<span>{order.totals?.shipping === 0 ? "Free" : formatCurrency(order.totals?.shipping)}</span>
						</div>
						<div className="summary-row">
							<span>Tax</span>
							<span>{formatCurrency(order.totals?.tax)}</span>
						</div>
						{order.totals?.discount ? (
							<div className="summary-row savings">
								<span>Discount</span>
								<span>-{formatCurrency(order.totals.discount)}</span>
							</div>
						) : null}
						<div className="summary-row total">
							<span>Total</span>
							<span>{formatCurrency(order.totals?.total)}</span>
						</div>
					</div>

					<div className="order-status-actions">
						<button type="button" onClick={handleBackHome}>
							Continue shopping
						</button>
						<button type="button" className="secondary-btn" onClick={handleViewStores}>
							View stores
						</button>
					</div>
				</aside>
			</div>
		</div>
	);
}
