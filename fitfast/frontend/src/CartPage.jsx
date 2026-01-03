import { useEffect, useMemo, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import {
  getCart,
  removeFromCart,
  updateCartItemQuantity,
} from "./cartStorage";

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
      {cartFeedback && <div className="cart-feedback">{cartFeedback}</div>}

      <div className="cart-hero">
        <div>
          <p className="eyebrow">Your curated picks</p>
          <h1>Shopping Cart</h1>
          <p className="muted">
            Review your wardrobe, adjust quantities, and head to checkout when
            you are ready.
          </p>
        </div>
        <div className="cart-hero-meta">
          <span className="pill-count">{cartItems.length} items</span>
          <button className="secondary-btn" onClick={handleContinueShopping}>
            Continue shopping
          </button>
        </div>
      </div>

      {!cartItems.length ? (
        <div className="empty-cart card">
          <div>
            <h3>Your cart is empty</h3>
            <p className="muted">
              Save your favorites and come back when you are ready to checkout.
            </p>
          </div>
          <div className="empty-cart-actions">
            <Link className="secondary-btn" to="/">
              Explore stores
            </Link>
            <button onClick={handleContinueShopping}>Browse items</button>
          </div>
        </div>
      ) : (
        <div className="cart-layout">
          <section className="cart-items">
            {cartItems.map((item) => (
              <article className="cart-item-card" key={item.cartKey}>
                <div className="cart-item-media">
                  {item.image ? (
                    <img src={item.image} alt={item.name} loading="lazy" />
                  ) : (
                    <div className="image-placeholder">{item.name?.[0] || ""}</div>
                  )}
                </div>

                <div className="cart-item-body">
                  <div className="cart-item-header">
                    <div>
                      <h3>{item.name}</h3>
                      {item.storeName && (
                        <p className="muted small">{item.storeName}</p>
                      )}
                      <div className="cart-item-meta">
                        {item.color && <span>Color: {item.color}</span>}
                        {item.size && <span>Size: {item.size}</span>}
                      </div>
                    </div>
                    <div className="price-modern">
                      {formatPrice(item.price)}
                    </div>
                  </div>

                  <div className="cart-item-actions">
                    <div className="quantity-control compact">
                      <button
                        className="quantity-btn"
                        onClick={() => handleQuantityChange(item.cartKey, -1)}
                        aria-label="Decrease quantity"
                        disabled={(item.quantity || 1) <= 1}
                      >
                        −
                      </button>
                      <span className="quantity-value">{item.quantity || 1}</span>
                      <button
                        className="quantity-btn"
                        onClick={() => handleQuantityChange(item.cartKey, 1)}
                        aria-label="Increase quantity"
                      >
                        +
                      </button>
                    </div>

                    <div className="cart-item-actions-secondary">
                      <button
                        className="link-btn"
                        type="button"
                        onClick={() => handleRemove(item.cartKey)}
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            ))}
          </section>

          <aside className="order-summary">
            <h3>Order Summary</h3>
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

            <button
              className="checkout-btn"
              type="button"
              onClick={handleCheckout}
              disabled={!cartItems.length}
            >
              Proceed to Checkout
            </button>
            <p className="muted small">Checkout gathers your delivery and payment details.</p>
          </aside>
        </div>
      )}
    </div>
  );
}