import { useCallback, useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api";

const ORDER_STATUS_STORAGE = "fitfast_recent_order";
const isBrowser = typeof window !== "undefined";

function writeLatestOrder(payload, { emit = false } = {}) {
	if (!isBrowser) return;

	try {
		window.localStorage.setItem(ORDER_STATUS_STORAGE, JSON.stringify(payload));
		if (emit) {
			window.dispatchEvent(new Event("fitfast-order-updated"));
		}
	} catch (err) {
		console.error("Failed to persist latest order", err);
	}
}

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

	const timeline = stages.map((stage, index) => {
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

	const backendStatus = (order.delivery && order.delivery.status) || order.cmsStatus || order.status;

	if (!backendStatus) {
		return timeline;
	}

	const normalizedStatus = backendStatus.toString().toLowerCase();
	const statusToStage = {
		pending: "confirmed",
		confirmed: "confirmed",
		processing: "preparing",
		shipped: "dispatched",
		in_transit: "dispatched",
		"in transit": "dispatched",
		out_for_delivery: "out-for-delivery",
		"out for delivery": "out-for-delivery",
		delivered: "delivered",
		failed: "delivered",
	};

	const targetStageId = statusToStage[normalizedStatus];
	if (!targetStageId) {
		return timeline;
	}

	const targetIndex = timeline.findIndex((stage) => stage.id === targetStageId);
	if (targetIndex === -1) {
		return timeline;
	}

	return timeline.map((stage, index) => {
		if (index < targetIndex) {
			return { ...stage, state: "completed" };
		}
		if (index === targetIndex) {
			return {
				...stage,
				state: normalizedStatus === "delivered" ? "completed" : "current",
			};
		}
		return { ...stage, state: normalizedStatus === "failed" ? "current" : "upcoming" };
	});
}

function formatDeliveryStatus(status) {
	if (!status) return "";
	const label = status.toString().trim().replace(/_/g, " ");
	return label.charAt(0).toUpperCase() + label.slice(1);
}

function formatPaymentMethodLabel(method) {
	if (!method) return "Payment";
	const normalized = method.toString().trim().toLowerCase();
	switch (normalized) {
		case "cash":
		case "cod":
			return "Cash on delivery";
		case "card":
		case "credit_card":
			return "Card payment";
		case "paypal":
			return "PayPal";
		default:
			return formatDeliveryStatus(normalized);
	}
}

function mergeOrderSnapshot(localOrder, backendOrder) {
	if (!backendOrder || typeof backendOrder !== "object") {
		return localOrder;
	}

	const localItems = Array.isArray(localOrder?.items) ? localOrder.items : [];
	const normalizedItems = Array.isArray(backendOrder.items) && backendOrder.items.length
		? backendOrder.items.map((item, index) => {
			const fallback = localItems[index] || {};
			const quantity = Number(item.quantity ?? fallback.quantity ?? 1) || 1;
			const unitPrice = Number(item.unit_price ?? item.price ?? fallback.price ?? 0) || 0;
			return {
				id: item.id ?? fallback.id ?? `${backendOrder.id || "order"}-item-${index + 1}`,
				name: item.name ?? fallback.name ?? "Ordered item",
				quantity,
				price: unitPrice,
				total: Number(item.total ?? quantity * unitPrice),
				image: item.image_url ?? fallback.image ?? null,
				size: item.size ?? item.selected_size ?? fallback.size ?? null,
				color: item.color ?? item.selected_color ?? fallback.color ?? null,
			};
		})
		: localItems;

	const calculatedSubtotal = normalizedItems.reduce((sum, entry) => sum + Number(entry.total ?? entry.price ?? 0), 0);

	const localTotals = localOrder?.totals || {};
	const normalizedTotals = {
		...localTotals,
		subtotal: localTotals.subtotal ?? calculatedSubtotal,
		total: Number(backendOrder.total_amount ?? localTotals.total ?? calculatedSubtotal),
	};

	const backendDelivery = backendOrder.delivery || {};
	const backendDeliveryStatus = backendDelivery.status && backendDelivery.status !== "N/A"
		? backendDelivery.status
		: backendOrder.status;
	const delivery = {
		...(localOrder?.delivery || {}),
		label: backendDelivery.label ?? localOrder?.delivery?.label ?? null,
		description: backendDelivery.description ?? localOrder?.delivery?.description ?? null,
		status: backendDeliveryStatus ?? localOrder?.delivery?.status ?? null,
		address: backendDelivery.address ?? localOrder?.delivery?.address ?? null,
	};

	const backendPayment = backendOrder.payment || {};
	const payment = {
		...(localOrder?.payment || {}),
		status: backendPayment.status ?? localOrder?.payment?.status ?? null,
		method: backendPayment.method ?? localOrder?.payment?.method ?? null,
	};

	if (!payment.label) {
		payment.label = formatPaymentMethodLabel(payment.method);
	}

	const backendStatusRaw = backendDeliveryStatus || backendOrder.status || null;
	const backendStatusLabel = backendStatusRaw ? formatDeliveryStatus(backendStatusRaw) : null;

	const merged = {
		...localOrder,
		orderId: backendOrder.id ?? localOrder?.orderId ?? null,
		cmsStatus: backendStatusRaw ?? localOrder?.cmsStatus ?? null,
		status: backendStatusLabel || localOrder?.status || "Processing",
		items: normalizedItems,
		totals: normalizedTotals,
		delivery,
		payment,
		lastBackendSync: new Date().toISOString(),
	};

	if (!merged.placedAt) {
		merged.placedAt = backendOrder.created_at ?? new Date().toISOString();
	}

	return merged;
}

export default function OrderStatusPage() {
	const navigate = useNavigate();
	const [order, setOrder] = useState(null);
	const [loading, setLoading] = useState(true);
	const [isSyncing, setIsSyncing] = useState(false);
	const [syncError, setSyncError] = useState(null);

	const refreshFromBackend = useCallback(
		async (orderId, snapshot) => {
			if (!orderId) return;
			setIsSyncing(true);
			setSyncError(null);

			try {
				const response = await api.get(`/orders/${orderId}`);
				if (response.data?.success && response.data?.order) {
					setOrder((prev) => {
						const base = snapshot ?? prev ?? {};
						const merged = mergeOrderSnapshot(base, response.data.order);
						writeLatestOrder(merged, { emit: true });
						return merged;
					});
				}
			} catch (error) {
				console.error("Order sync error", error);
				setSyncError(error.response?.data?.message || "Unable to sync latest order status.");
			} finally {
				setIsSyncing(false);
			}
		},
		[]
	);

	useEffect(() => {
		const syncLatest = () => {
			const snapshot = readLatestOrder();
			setOrder(snapshot);
			setLoading(false);

			if (snapshot?.orderId) {
				const lastSyncTime = snapshot.lastBackendSync
					? new Date(snapshot.lastBackendSync).getTime()
					: 0;
				if (!lastSyncTime || Date.now() - lastSyncTime > 5000) {
					refreshFromBackend(snapshot.orderId, snapshot);
				}
			}
		};

		syncLatest();
		window.addEventListener("storage", syncLatest);
		window.addEventListener("fitfast-order-updated", syncLatest);

		return () => {
			window.removeEventListener("storage", syncLatest);
			window.removeEventListener("fitfast-order-updated", syncLatest);
		};
	}, [refreshFromBackend]);

	useEffect(() => {
		if (!order?.orderId) return undefined;
		if (order?.delivery?.status === "delivered") return undefined;

		const interval = setInterval(() => {
			refreshFromBackend(order.orderId, order);
		}, 60000);

		return () => clearInterval(interval);
	}, [order, refreshFromBackend]);

	const handleManualRefresh = useCallback(() => {
		if (order?.orderId) {
			refreshFromBackend(order.orderId, order);
		} else {
			const snapshot = readLatestOrder();
			setOrder(snapshot);
		}
	}, [order, refreshFromBackend]);

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

	const currentStage = timeline.find((stage) => stage.state === "current") || timeline[timeline.length - 1];
	const deliveryStatusLabel = order
		? formatDeliveryStatus(order.delivery?.status || order.cmsStatus)
		: "";
	const statusHeading = deliveryStatusLabel || currentStage?.label || "On the way";
	const lastSyncedAt = order?.lastBackendSync ? new Date(order.lastBackendSync) : null;
	const deliveryStatusNote = order
		? formatDeliveryStatus(order.delivery?.status || order.cmsStatus)
		: null;
	const paymentStatusNote = order?.payment?.status ? formatDeliveryStatus(order.payment.status) : null;

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

	return (
		<div className="order-status-page">
			<div className="order-status-hero">
				<div>
					<p className="eyebrow">Order tracking</p>
					<h1>Status: {statusHeading}</h1>
					<p className="muted">Order {order.code} • Placed {formatDateTime(order.placedAt)}</p>
					{lastSyncedAt ? (
						<p className="muted x-small">Synced {lastSyncedAt.toLocaleString()}</p>
					) : null}
				</div>
				<div className="order-status-meta">
					<div className="progress-circle">
						<span>{progressPercent}%</span>
					</div>
					<div className="eta-meta">
						<p className="muted x-small">Estimated arrival</p>
						<strong>{formatDate(order.eta)}</strong>
					</div>
					<div className="sync-meta">
						<button
							type="button"
							className="secondary-btn"
							onClick={handleManualRefresh}
							disabled={isSyncing}
						>
							{isSyncing ? "Syncing..." : "Refresh status"}
						</button>
						{syncError ? (
							<p className="muted x-small" style={{ color: "#b91c1c" }}>
								{syncError}
							</p>
						) : null}
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
							{deliveryStatusNote ? (
								<div className="detail-row">
									<span>Status</span>
									<strong>{deliveryStatusNote}</strong>
								</div>
							) : null}
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
									{paymentStatusNote ? ` • ${paymentStatusNote}` : ""}
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
