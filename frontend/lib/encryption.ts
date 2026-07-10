"use client";

const SALT = "glow_";

/**
 * Encrypts an ID (string or number) to a URL-safe Base64 string.
 * This is used to obfuscate order IDs in the URL.
 */
export function encryptId(id: string | number): string {
  const str = SALT + String(id);
  const encoded = typeof window !== "undefined"
    ? btoa(str)
    : Buffer.from(str).toString("base64");
  
  // Make it URL-safe
  return encoded
    .replace(/\+/g, "-")
    .replace(/\//g, "_")
    .replace(/=+$/, "");
}

/**
 * Decrypts an obfuscated ID back to its original value.
 */
export function decryptId(encryptedId: string): string {
  try {
    // Restore non-URL-safe characters
    let base64 = encryptedId.replace(/-/g, "+").replace(/_/g, "/");
    
    // Add padding if needed
    while (base64.length % 4) {
      base64 += "=";
    }
    
    const decoded = typeof window !== "undefined"
      ? atob(base64)
      : Buffer.from(base64, "base64").toString("utf8");
    
    return decoded.replace(SALT, "");
  } catch (e) {
    console.error("Decryption failed:", e);
    return encryptedId;
  }
}
