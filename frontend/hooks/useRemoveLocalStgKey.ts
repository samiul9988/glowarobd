export function removeLocalStorageKey(keys: string | string[]) {
  if (typeof window === "undefined") return;

  try {
    if (Array.isArray(keys)) {
      keys.forEach((key) => localStorage.removeItem(key));
    } else {
      localStorage.removeItem(keys);
    }
  } catch (error) {
    console.error("Error removing localStorage key(s):", error);
  }
}
