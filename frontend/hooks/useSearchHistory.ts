"use client";

import { useEffect, useState } from "react";

interface SearchHistoryItem {
  term: string;
  timestamp: number;
}

const STORAGE_KEY = "glowaro_search_history";
const EXPIRATION_HOURS = 24;

export function useSearchHistory() {
  const [suggestions, setSuggestions] = useState<string[]>([]);

  // Load from localStorage on mount
  useEffect(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) return;

    try {
      const parsed: SearchHistoryItem[] = JSON.parse(stored);

      // Filter out expired terms (older than 24 hours)
      const validItems = parsed.filter(
        (item) =>
          Date.now() - item.timestamp < EXPIRATION_HOURS * 60 * 60 * 1000,
      );

      // Save filtered back to keep localStorage clean
      localStorage.setItem(STORAGE_KEY, JSON.stringify(validItems));

      setSuggestions(validItems.map((item) => item.term));
    } catch (err) {
      console.error("Failed to parse search history", err);
    }
  }, []);

  // Add new term
  const addSuggestion = (term: string) => {
    if (!term.trim()) return;

    const stored = localStorage.getItem(STORAGE_KEY);
    let items: SearchHistoryItem[] = stored ? JSON.parse(stored) : [];

    // Remove existing same term (case-insensitive)
    items = items.filter(
      (item) => item.term.toLowerCase() !== term.toLowerCase(),
    );

    // Add new term at top
    const newItem: SearchHistoryItem = { term, timestamp: Date.now() };
    items.unshift(newItem);

    // Keep only latest 5
    const trimmed = items.slice(0, 5);

    // Save and update state
    localStorage.setItem(STORAGE_KEY, JSON.stringify(trimmed));
    setSuggestions(trimmed.map((item) => item.term));
  };

  // Clear all
  const clearSuggestions = () => {
    localStorage.removeItem(STORAGE_KEY);
    setSuggestions([]);
  };

  return { suggestions, addSuggestion, clearSuggestions };
}
