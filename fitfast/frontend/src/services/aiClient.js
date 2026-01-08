import api from "../api";

const BASE_PATH = "/ai/users";

const extractMessage = (value) => {
  if (!value && value !== 0) {
    return "";
  }
  if (typeof value === "string") {
    return value;
  }
  if (Array.isArray(value)) {
    return value.map((entry) => extractMessage(entry)).filter(Boolean).join(" ");
  }
  if (typeof value === "object") {
    return (
      extractMessage(value.message) ||
      extractMessage(value.detail) ||
      extractMessage(value.msg) ||
      ""
    );
  }
  return String(value);
};

function normalizeError(error) {
  const response = error.response?.data;
  if (response) {
    const message = extractMessage(response.message || response.detail);
    if (message) {
      return message;
    }
  }
  return "Unable to reach the AI service. Please try again.";
}

export async function syncUserProfile(userId = "me") {
  try {
    const { data } = await api.post(`${BASE_PATH}/${userId}/sync`);
    return { data: data?.data ?? null, error: null };
  } catch (error) {
    return { data: null, error: normalizeError(error) };
  }
}

export async function getSizeRecommendation(userId = "me", { garmentType, itemId }) {
  try {
    const payload = { garmentType };
    if (itemId) {
      payload.itemId = typeof itemId === "string" ? itemId : String(itemId);
    }

    const { data } = await api.post(`${BASE_PATH}/${userId}/size`, payload);
    return { data: data?.data ?? null, error: null };
  } catch (error) {
    return { data: null, error: normalizeError(error) };
  }
}

export async function buildOutfitRecommendation(
  userId = "me",
  { startingItemId = null, style = null, maxItems = 4 } = {}
) {
  try {
    const payload = { maxItems };
    if (startingItemId) {
      payload.startingItemId =
        typeof startingItemId === "string" ? startingItemId : String(startingItemId);
    }
    if (style) {
      payload.style = typeof style === "string" ? style.trim() : String(style);
    }

    const { data } = await api.post(`${BASE_PATH}/${userId}/outfit`, payload);
    return { data: data?.data ?? null, error: null };
  } catch (error) {
    return { data: null, error: normalizeError(error) };
  }
}

export async function getPersonalizedRecommendations(userId = "me", limit = 6) {
  try {
    const { data } = await api.post(`${BASE_PATH}/${userId}/recommendations`, { limit });
    return { data: data?.data ?? null, error: null };
  } catch (error) {
    return { data: null, error: normalizeError(error) };
  }
}
