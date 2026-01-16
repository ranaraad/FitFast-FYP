const STORAGE_KEY_PREFIX = "fitfast_wishlist";

const isBrowser = typeof window !== "undefined" && !!window.localStorage;

function getStorageKey() {
  if (!isBrowser) return STORAGE_KEY_PREFIX;

  try {
    const rawUser = window.localStorage.getItem("auth_user");
    const user = rawUser ? JSON.parse(rawUser) : null;

    if (user?.id) {
      return `${STORAGE_KEY_PREFIX}_${user.id}`;
    }
  } catch (err) {
    console.error("Failed to read auth_user for wishlist scoping", err);
  }

  return `${STORAGE_KEY_PREFIX}_guest`;
}

function readStorage() {
  if (!isBrowser) return [];
  try {
     const storageKey = getStorageKey();
    const raw = window.localStorage.getItem(storageKey);
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
    const storageKey = getStorageKey();
    window.localStorage.setItem(storageKey, JSON.stringify(items));
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

export function clearUserWishlistData(userId) {
  if (!isBrowser) return;

  try {
    // Clear the specific user's wishlist
    if (userId) {
      const userWishlistKey = `${STORAGE_KEY_PREFIX}_${userId}`;
      window.localStorage.removeItem(userWishlistKey);
    }

    // Also clear guest wishlist to ensure clean state
    const guestWishlistKey = `${STORAGE_KEY_PREFIX}_guest`;
    window.localStorage.removeItem(guestWishlistKey);
  } catch (err) {
    console.error("Failed to clear user wishlist data", err);
  }
}
