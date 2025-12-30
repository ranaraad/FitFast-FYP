const STORAGE_KEY = "fitfast_wishlist";

const isBrowser = typeof window !== "undefined" && !!window.localStorage;

function readStorage() {
  if (!isBrowser) return [];
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch (err) {
    console.error("Failed to parse wishlist from storage", err);
    return [];
  }
}

function writeStorage(items) {
  if (!isBrowser) return;
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch (err) {
    console.error("Failed to write wishlist to storage", err);
  }
}

export function getWishlist() {
  return readStorage();
}

export function isItemWishlisted(items, itemId, storeId) {
  return items.some(
    (entry) =>
      entry.id?.toString() === itemId?.toString() &&
      entry.storeId?.toString() === storeId?.toString()
  );
}

export function toggleWishlistEntry(entry) {
  const wishlist = readStorage();
  const normalized = {
    id: entry.id?.toString(),
    storeId: entry.storeId?.toString(),
    name: entry.name || "Saved item",
    price: entry.price ?? null,
    image: entry.image || null,
    storeName: entry.storeName || null,
  };

  const existingIndex = wishlist.findIndex((item) =>
    isItemWishlisted([item], normalized.id, normalized.storeId)
  );

  let added;
  if (existingIndex !== -1) {
    wishlist.splice(existingIndex, 1);
    added = false;
  } else {
    wishlist.push({ ...normalized, addedAt: new Date().toISOString() });
    added = true;
  }

  writeStorage(wishlist);
  return { items: wishlist, added };
}
