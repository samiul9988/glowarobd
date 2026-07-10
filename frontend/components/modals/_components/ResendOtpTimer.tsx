"use client";

import { useEffect, useState } from "react";

interface ResendOtpTimerProps {
  duration?: number; // default 40s
  onResend: () => void; // function to call when user clicks resend
}

export default function ResendOtpTimer({
  duration = 40,
  onResend,
}: ResendOtpTimerProps) {
  const [timeLeft, setTimeLeft] = useState(duration);
  const [isCounting, setIsCounting] = useState(true);

  useEffect(() => {
    if (!isCounting) return;

    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          setIsCounting(false);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [isCounting]);

  const handleResend = () => {
    setTimeLeft(duration);
    setIsCounting(true);
    onResend(); // Call your resend OTP API
  };

  return (
    <div className="text-sm text-site-gray-600 mt-2 flex items-center justify-end gap-1">
      {isCounting ? (
        <span>
          Resend OTP in{" "}
          <span className="font-semibold text-site-gray-900">{timeLeft}s</span>
        </span>
      ) : (
        <div>
          <span>Don't receive code? </span>
          <button
            type="button"
            onClick={handleResend}
            className="text-[#007AFF] font-medium hover:underline cursor-pointer"
          >
            Resend OTP
          </button>
        </div>
      )}
    </div>
  );
}
