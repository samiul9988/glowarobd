export function parseHTMLToText(html: string): string {
  if (!html) return "";

  // If in a browser environment
  if (typeof window !== "undefined" && typeof document !== "undefined") {
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    return tempDiv.textContent || tempDiv.innerText || "";
  }

  // Fallback for SSR (Node.js)
  return html
    .replace(/<[^>]*>/g, "")
    .replace(/\s+/g, " ")
    .trim();
}
