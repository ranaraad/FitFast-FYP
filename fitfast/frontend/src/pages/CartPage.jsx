import { useEffect, useMemo, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import {
  getCart,
  removeFromCart,
  updateCartItemQuantity,
} from "../cartStorage";

export default function CartPage() {
  const navigate = useNavigate();
  const [cartItems, setCartItems] = useState(() => getCart());
  const [cartFeedback, setCartFeedback] = useState("");

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
    if (!cartFeedback) return;

    const timeout = setTimeout(() => setCartFeedback(""), 2500);
    return () => clearTimeout(timeout);
  }, [cartFeedback]);

  const formatPrice = (price) => {
    if (price === null || price === undefined) return "";
    const numeric = Number(price);

    if (Number.isNaN(numeric)) return price;
    return `$${numeric.toFixed(2)}`;
  };

  const subtotal = useMemo(
    () =>
      cartItems.reduce((sum, item) => {
        const price = Number(item.price) || 0;
        return sum + price * (item.quantity || 1);
      }, 0),
    [cartItems]
  );

  const shippingEstimate = useMemo(() => {
    if (!cartItems.length) return 0;
    return subtotal >= 75 ? 0 : 8;
  }, [cartItems.length, subtotal]);

  const estimatedTotal = subtotal + shippingEstimate;

  const handleQuantityChange = (cartKey, delta) => {
    const target = cartItems.find((item) => item.cartKey === cartKey);
    if (!target) return;

    const nextQty = Math.max(1, (target.quantity || 1) + delta);
    const { items } = updateCartItemQuantity(cartKey, nextQty);
    setCartItems([...items]);
    setCartFeedback("Updated quantity");
  };

  const handleRemove = (cartKey) => {
    const { items } = removeFromCart(cartKey);
    setCartItems([...items]);
    setCartFeedback("Removed from cart");
  };

  const handleContinueShopping = () => {
    if (cartItems[0]?.storeId) {
      navigate(`/stores/${cartItems[0].storeId}`);
    } else {
      navigate("/");
    }
  };

  const handleCheckout = () => {
    navigate("/checkout");
  };

  return (
    <div className="cart-page">
      {cartFeedback ? <div className="cart-toast">{cartFeedback}</div> : null}

      <div className="cart-hero">
        <div className="cart-hero-copy">
          <p className="eyebrow">Your curated picks</p>
          <h1>Shopping Cart</h1>
          <p className="muted">
            Review your wardrobe, adjust quantities, and head to checkout when you are ready.
          </p>
        </div>
        <div className="cart-hero-meta">
          <div className="cart-hero-badge">
            <span>{cartItems.length || "No"}</span>
            <small>items in bag</small>
          </div>
          <button type="button" className="ghost-btn" onClick={handleContinueShopping}>
            Continue shopping
          </button>
        </div>
      </div>

      {!cartItems.length ? (
        <div className="empty-cart">
          <div className="empty-cart-visual">
            <div className="hanger" />
            <div className="shopping-bag">
              <span>FitFast</span>
            </div>
            <div className="floating-tag">New arrivals daily</div>
          </div>
          <div className="empty-cart-copy">
            <p className="eyebrow">Looks like it is empty</p>
            <h3>Your wardrobe is waiting</h3>
            <p className="muted">
              Discover this season&apos;s essentials, save your favorite fits, and return when you are ready to checkout.
            </p>
            <div className="empty-cart-actions">
              <Link className="primary-btn" to="/">
                Explore stores
              </Link>
              <button type="button" className="ghost-btn" onClick={handleContinueShopping}>
                Browse items
              </button>
            </div>
          </div>
        </div>
      ) : (
        <div className="cart-layout">
          <section className="cart-items">
            {cartItems.map((item) => (
              <article className="cart-item-card" key={item.cartKey}>
                <div className="cart-item-media">
                  {item.storeId && item.id ? (
                    <Link
                      to={`/stores/${item.storeId}/product/${item.id}`}
                      className="cart-item-link"
                      aria-label={`View ${item.name || "item"}`}
                    >
                      {item.image ? (
                        <img src={item.image} alt={item.name} loading="lazy" />
                      ) : (
                        <div className="image-placeholder">{item.name?.[0] || ""}</div>
                      )}
                    </Link>
                  ) : item.image ? (
                    <img src={item.image} alt={item.name} loading="lazy" />
                  ) : (
                    <div className="image-placeholder">{item.name?.[0] || ""}</div>
                  )}
                </div>

                <div className="cart-item-body">
                  <div className="cart-item-header">
                    <div>
                      <h3>
                        {item.storeId && item.id ? (
                          <Link
                            to={`/stores/${item.storeId}/product/${item.id}`}
                            className="cart-item-name-link"
                          >
                            {item.name}
                          </Link>
                        ) : (
                          item.name
                        )}
                      </h3>
                      {item.storeName ? <p className="cart-item-brand">{item.storeName}</p> : null}
                      <div className="cart-item-meta">
                        {item.color ? <span>Color: {item.color}</span> : null}
                        {item.size ? <span>Size: {item.size}</span> : null}
                      </div>
                    </div>
                    <span className="price-tag">{formatPrice(item.price)}</span>
                  </div>

                  <div className="cart-item-actions">
                    <div className="quantity-control compact">
                      <button
                        className="quantity-btn"
                        onClick={() => handleQuantityChange(item.cartKey, -1)}
                        aria-label="Decrease quantity"
                        type="button"
                        disabled={(item.quantity || 1) <= 1}
                      >
                        −
                      </button>
                      <span className="quantity-value">{item.quantity || 1}</span>
                      <button
                        className="quantity-btn"
                        onClick={() => handleQuantityChange(item.cartKey, 1)}
                        aria-label="Increase quantity"
                        type="button"
                      >
                        +
                      </button>
                    </div>

                    <div className="cart-item-actions-secondary">
                      <button
                        className="trash-btn"
                        type="button"
                        onClick={() => handleRemove(item.cartKey)}
                        aria-label="Remove from cart"
                        title="Remove item"
                      >
                        <svg
                          width="18"
                          height="18"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                          <line x1="10" y1="11" x2="10" y2="17"></line>
                          <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            ))}
          </section>

          <aside className="order-summary">
            <div className="summary-heading">
              <h3>Order summary</h3>
              <p className="muted x-small">Taxes calculated at checkout</p>
            </div>
            <div className="summary-row">
              <span>Subtotal</span>
              <span>{formatPrice(subtotal)}</span>
            </div>
            <div className="summary-row">
              <span>Estimated shipping</span>
              <span>{shippingEstimate === 0 ? "Free" : formatPrice(shippingEstimate)}</span>
            </div>
            <div className="summary-row total">
              <span>Estimated total</span>
              <span>{formatPrice(estimatedTotal)}</span>
            </div>

            <button className="checkout-btn" type="button" onClick={handleCheckout}>
              Proceed to Checkout
            </button>
            <p className="shipping-note">Complimentary delivery when you spend $75 or more.</p>
          </aside>
        </div>
      )}
    </div>
  );
}
