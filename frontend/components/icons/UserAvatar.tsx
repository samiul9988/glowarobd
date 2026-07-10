import Image from "next/image";
import { useMemo, useState } from "react";

// UserAvatar component
// Props:
// - name: string (used to create fallback initial)
// - src: optional string (image URL)
// - size: number (px) default 40
// - className: extra class names
// - alt: alt text for image

export default function UserAvatar({ name = "?", src = "", size = 40, className = "", alt = "user avatar" }) {
    const [imgError, setImgError] = useState(false);

    // single-character initial (first non-space char)
    const initial = useMemo(() => {
        if (!name || typeof name !== "string") return "?";
        const ch = name.trim().charAt(0) || "?";
        return ch.toUpperCase();
    }, [name]);

    // deterministic background color from name
    const bg = useMemo(() => {
        const palette = [
            "bg-[#1CACE4]",
            "bg-[#EF5A28]",
            "bg-[#010101]",
        ];
        let code = 0;
        for (let i = 0; i < name.length; i++) code = (code * 31 + name.charCodeAt(i)) | 0;
        const idx = Math.abs(code) % palette.length;
        return palette[idx];
    }, [name]);

    const sizeClass = {
        24: "w-6 h-6 text-sm",
        28: "w-7 h-7 text-sm",
        32: "w-8 h-8 text-base",
        36: "w-9 h-9 text-base",
        40: "w-10 h-10 text-lg",
        48: "w-12 h-12 text-lg",
        56: "w-14 h-14 text-xl",
        64: "w-16 h-16 text-2xl",
    }[size] || `w-[${size}px] h-[${size}px] text-base`;

    const fontSize = size >= 56 ? "text-xl" : size >= 40 ? "text-lg" : "text-base";

    // If no src provided, show initial fallback
    const showFallback = !src || imgError;

    return (
        <div className={`inline-flex items-center justify-center overflow-hidden rounded-full ${className}`} style={{ width: size, height: size }}>
            {!showFallback ? (
                <Image
                    src={src}
                    alt={alt}
                    width={size}
                    height={size}
                    onError={() => setImgError(true)}
                    className="object-cover rounded-full"
                    unoptimized
                />
            ) : (
                <div
                    className={`flex items-center justify-center rounded-full ${bg} text-white select-none`}
                    style={{ width: size, height: size, fontSize: Math.floor(size * 0.45) }}
                    aria-hidden="true"
                >
                    {initial}
                </div>
            )}
        </div>
    );
}

// Example usage (copy into your React/Next page):
// <UserAvatar name="Md Shakil" src="/avatars/shakil.jpg" size={48} />
// <UserAvatar name="Tanzina" size={40} />
