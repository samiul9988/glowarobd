"use client";

import { useTheme } from "next-themes";
import { useEffect, useState } from "react";
import { Sun, Moon } from "lucide-react";
import { Button } from "@/components/ui/button";

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();
  const [mounted, setMounted] = useState(false);

  // Ensure we're mounted before showing correct theme
  useEffect(() => {
    setMounted(true);
  }, []);

  if (!mounted) {
    // render a placeholder button (no icon) to avoid mismatch
    return (
      <Button variant="outline" size="icon" disabled>
        <span className="h-5 w-5" />
      </Button>
    );
  }

  return (
    <Button variant="outline" size="icon" onClick={() => setTheme(theme === "light" ? "dark" : "light")}>
      {theme === "light" ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
    </Button>
  );
}
