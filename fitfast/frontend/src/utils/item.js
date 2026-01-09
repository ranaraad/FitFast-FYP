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

const DEFAULT_GARMENT_TYPE = "t_shirt";

const normalizeGarmentKey = (value) => {
  if (!value && value !== 0) {
    return "";
  }

  return value
    .toString()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_+|_+$/g, "")
    .replace(/__+/g, "_");
};

export function inferGarmentType(item) {
  if (!item) {
    return DEFAULT_GARMENT_TYPE;
  }

  const explicit = normalizeGarmentKey(
    item.garment_type ||
      item.garmentType ||
      item.garmentTypeKey ||
      item.garmentCategory
  );

  if (explicit) {
    return explicit || DEFAULT_GARMENT_TYPE;
  }

  const categorySource = (() => {
    const category = item.category || item.category_name || item.category_slug;
    if (typeof category === "string") return category;
    if (category && typeof category === "object") {
      return category.slug || category.name || "";
    }
    return "";
  })()
    .toString()
    .toLowerCase();

  if (categorySource.includes("dress")) return "a_line_dress";
  if (categorySource.includes("jean")) return "regular_jeans";
  if (categorySource.includes("pant")) return "regular_pants";
  if (categorySource.includes("skirt")) return "a_line_skirt";
  if (categorySource.includes("coat") || categorySource.includes("jacket")) {
    return "bomber_jacket";
  }
  if (categorySource.includes("hoodie")) return "pullover_hoodie";
  if (categorySource.includes("sweater")) return "crewneck_sweater";
  if (categorySource.includes("short")) return "casual_shorts";

  return DEFAULT_GARMENT_TYPE;
}
