import styles from "./WishlistButton.module.css";

export default function WishlistButton({
  active = false,
  ariaLabel = "Toggle wishlist",
  className = "",
  onClick,
}) {
  const combinedClassName = [
    "wishlist-btn",
    styles.button,
    active ? styles.active : "",
    active ? "active" : "",
    className,
  ]
    .filter(Boolean)
    .join(" ");

  const handleClick = (event) => {
    event.stopPropagation();

    if (typeof onClick === "function") {
      onClick(event);
    }
  };

  return (
    <button
      type="button"
      className={combinedClassName}
      aria-label={ariaLabel}
      aria-pressed={active}
      onClick={handleClick}
    >
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill={active ? "currentColor" : "#ffffff"}
        stroke="currentColor"
        strokeWidth="1.8"
        xmlns="http://www.w3.org/2000/svg"
        className={styles.icon}
      >
        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
      </svg>
    </button>
  );
}
