"use client";

import { useEffect } from "react";

export default function Error({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => {
    // Log the error to an error reporting service
    console.error(error);
  }, [error]);

  return (
    <div className="h-screen flex flex-col items-center justify-center gap-2 md:gap-4">
      <h2>Something went wrong!</h2>
      <button
        className="bg-site-primary hover:bg-site-primary/95 text-white font-semibold py-2 px-4 rounded cursor-pointer transition-colors duration-300 ease-in-out"
        onClick={
          // Attempt to recover by trying to re-render the segment
          () => reset()
        }
      >
        Try again
      </button>
    </div>
  );
}
