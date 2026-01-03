const STORAGE_KEY_PREFIX = "fitfast_cart";
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
    console.error("Failed to read auth_user for cart scoping", err);
  }

  return `${STORAGE_KEY_PREFIX}_guest`;
}

function buildCartKey(entry) {
  const baseId = entry.id?.toString() || "item";
  const storeId = entry.storeId?.toString() || "store";
  const size = entry.size ? entry.size.toString() : "default";
  const color = entry.color ? entry.color.toString() : "default";

  return `${storeId}_${baseId}_${size}_${color}`;
}

function readStorage() {
  if (!isBrowser) return [];

  try {
    const storageKey = getStorageKey();
    const raw = window.localStorage.getItem(storageKey);
    const parsed = raw ? JSON.parse(raw) : [];
    if (!Array.isArray(parsed)) return [];

    return parsed.map((item) => ({
      ...item,
      cartKey: item.cartKey || buildCartKey(item),
    }));
  } catch (err) {
    console.error("Failed to parse cart from storage", err);
    return [];
  }
}

function writeStorage(items) {
  if (!isBrowser) return;

  try {
    const storageKey = getStorageKey();
    window.localStorage.setItem(storageKey, JSON.stringify(items));
    window.dispatchEvent(new Event("cart-updated"));
  } catch (err) {
    console.error("Failed to write cart to storage", err);
  }
}

function normalizeEntry(entry) {
  const normalizedQuantity = Number(entry.quantity) || 1;

  return {
    id: entry.id?.toString(),
    storeId: entry.storeId?.toString(),
    name: entry.name || "Cart item",
    price: entry.price ?? null,
    image: entry.image || null,
    size: entry.size || null,
    color: entry.color || null,
    storeName: entry.storeName || null,
    quantity: Math.max(1, normalizedQuantity),
    cartKey: null,
  };
}

function findCartItemIndex(items, target) {
  return items.findIndex(
    (item) =>
      item.id?.toString() === target.id?.toString() &&
      item.storeId?.toString() === target.storeId?.toString() &&
      (item.size || null) === (target.size || null) &&
      (item.color || null) === (target.color || null)
  );
}

export function getCart() {
  return readStorage();
}

export function getCartCount() {
  return readStorage().reduce((sum, item) => sum + (item.quantity || 0), 0);
}

export function addToCart(entry) {
  const cart = readStorage();
  const normalized = normalizeEntry(entry);
  const existingIndex = findCartItemIndex(cart, normalized);

  if (existingIndex !== -1) {
    const existing = cart[existingIndex];
    cart[existingIndex] = {
      ...existing,
      cartKey: existing.cartKey || buildCartKey(existing),
      quantity: (existing.quantity || 1) + normalized.quantity,
    };
  } else {
    cart.push({ ...normalized, cartKey: buildCartKey(normalized) });
  }

  writeStorage(cart);
  return { items: cart };
}

export function updateCartItemQuantity(cartKey, quantity) {
  const cart = readStorage();
  const normalizedQty = Math.max(1, Number(quantity) || 1);
  const index = cart.findIndex((item) => item.cartKey === cartKey);

  if (index !== -1) {
    const item = cart[index];
    cart[index] = {
      ...item,
      cartKey: item.cartKey || buildCartKey(item),
      quantity: normalizedQty,
    };
    writeStorage(cart);
  }

  return { items: cart };
}

export function removeFromCart(cartKey) {
  const cart = readStorage();
  const nextCart = cart.filter((item) => item.cartKey !== cartKey);
  writeStorage(nextCart);
  return { items: nextCart };
}

export function clearCart() {
  writeStorage([]);
  return { items: [] };
}