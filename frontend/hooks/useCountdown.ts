import { useEffect, useMemo, useRef, useState } from "react";

export interface TimeLeft {
  days: number;
  hours: number;
  minutes: number;
  seconds: number;
}

export const useCountdown = (endDate?: Date | string) => {
  const [timeLeft, setTimeLeft] = useState<TimeLeft>({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
  });

  // ✅ Memoize target date so it doesn’t recreate on every render
  const targetDate = useMemo(
    () =>
      endDate
        ? new Date(endDate).getTime()
        : Date.now() + 5 * 24 * 60 * 60 * 1000,
    [endDate],
  );

  // ✅ Keep a ref for mounted state (prevent updates on unmounted)
  const mountedRef = useRef(true);

  useEffect(() => {
    mountedRef.current = true;

    const updateCountdown = () => {
      const now = Date.now();
      const diff = targetDate - now;

      if (diff <= 0) {
        if (mountedRef.current)
          setTimeLeft({ days: 0, hours: 0, minutes: 0, seconds: 0 });
        return;
      }

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
      const minutes = Math.floor((diff / (1000 * 60)) % 60);
      const seconds = Math.floor((diff / 1000) % 60);

      if (mountedRef.current) setTimeLeft({ days, hours, minutes, seconds });
    };

    // Run once immediately
    updateCountdown();

    // Use one interval — with consistent cleanup
    const timerId = setInterval(updateCountdown, 1000);

    return () => {
      mountedRef.current = false;
      clearInterval(timerId);
    };
  }, [targetDate]);

  const isExpired =
    timeLeft.days === 0 &&
    timeLeft.hours === 0 &&
    timeLeft.minutes === 0 &&
    timeLeft.seconds === 0;

  return { timeLeft, isExpired };
};
