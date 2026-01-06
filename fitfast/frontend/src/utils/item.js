export function getItemId(item) {
  if (!item) {
    return null;
  }

  return item.id ?? item.item_id ?? item.slug ?? item.name ?? null;
}

export function getItemName(item) {
  if (!item) {
    return "";
  }

  return item.name ?? item.title ?? item.label ?? "Item";
}

export function getItemImage(item) {
  if (!item) {
    return "";
  }

  return (
    item.image_url ||
    item.image ||
    item.imagePath ||
    item.image_path ||
    item.primary_image_url ||
    item.primary_image?.image_path ||
    item.thumbnail ||
    item.thumbnail_url ||
    ""
  );
}

export function formatPrice(price, { currency = "USD", locale = "en-US" } = {}) {
  if (price === undefined || price === null || price === "") {
    return "";
  }

  const numericValue = Number(price);

  if (Number.isNaN(numericValue)) {
    return String(price);
  }

  try {
    return new Intl.NumberFormat(locale, {
      style: "currency",
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(numericValue);
  } catch {
    return `$${numericValue.toFixed(2)}`;
  }
}

export function getBestFitCopy(item) {
  if (!item) {
    return "";
  }

  if (item.best_fit_label) {
    return item.best_fit_label;
  }

  if (item.best_fit_description) {
    return item.best_fit_description;
  }

  if (item.best_fit_match || item.best_fit_match === 0) {
    const matchValue = Number(item.best_fit_match);

    if (!Number.isNaN(matchValue)) {
      return `Best Fit: ${Math.round(matchValue)}% Match!`;
    }

    return `Best Fit: ${item.best_fit_match}`;
  }

  if (item.bestFitLabel) {
    return item.bestFitLabel;
  }

  if (item.bestFitDescription) {
    return item.bestFitDescription;
  }

  if (item.bestFitMatch || item.bestFitMatch === 0) {
    const matchValue = Number(item.bestFitMatch);

    if (!Number.isNaN(matchValue)) {
      return `Best Fit: ${Math.round(matchValue)}% Match!`;
    }

    return `Best Fit: ${item.bestFitMatch}`;
  }

  return "Best Fit: Medium - 90% Match!";
}
