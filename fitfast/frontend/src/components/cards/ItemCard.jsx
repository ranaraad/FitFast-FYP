import WishlistButton from "../buttons/WishlistButton";
import styles from "./ItemCard.module.css";
import { getItemImage, getItemName, formatPrice } from "../../utils/item";

export default function ItemCard({
  item,
  badgeContent,
  wishlisted = false,
  onWishlistToggle,
  onAddToCart,
  onClick,
  className = "",
  addToCartLabel = "Add to Cart",
  showAddToCart = true,
  showPrice = true,
}) {
  if (!item) {
    return null;
  }

  const name = getItemName(item);
  const image = getItemImage(item);
  const priceLabel = showPrice ? formatPrice(item.price) : "";

  const articleClassName = [
    "product-card-modern",
    styles.card,
    className,
  ]
    .filter(Boolean)
    .join(" ");

  const handleCardClick = () => {
    if (typeof onClick === "function") {
      onClick(item);
    }
  };

  const handleWishlistToggle = () => {
    if (typeof onWishlistToggle === "function") {
      onWishlistToggle(item);
    }
  };

  const handleAddToCart = () => {
    if (typeof onAddToCart === "function") {
      onAddToCart(item);
    }
  };

  return (
    <article className={articleClassName} onClick={handleCardClick}>
      <div className={`product-image-container ${styles.imageWrapper}`}>
        {image ? (
          <img src={image} alt={name} loading="lazy" />
        ) : (
          <div className={`image-placeholder ${styles.placeholder}`}>
            {name.slice(0, 1)}
          </div>
        )}

        {typeof onWishlistToggle === "function" && (
          <WishlistButton active={wishlisted} onClick={handleWishlistToggle} />
        )}

        {badgeContent && <div className="best-fit-badge">{badgeContent}</div>}
      </div>

      <div className={`product-info-modern ${styles.content}`}>
        <h3>{name}</h3>

        <div className={`product-footer ${styles.footer}`}>
          {showPrice && priceLabel && (
            <span className="price-modern">{priceLabel}</span>
          )}

          {showAddToCart && typeof onAddToCart === "function" && (
            <button
              type="button"
              className="add-to-cart-btn"
              onClick={(event) => {
                event.stopPropagation();
                handleAddToCart();
              }}
            >
              {addToCartLabel}
            </button>
          )}
        </div>
      </div>
    </article>
  );
}
