import Image from "next/image";
import { useState } from "react";

interface Props {
  src: string | null;
  alt: string;
  width: number;
  height: number;
  className?: string;
  fallback?: string;
}

const FALLBACK_IMAGE = "/images/placeholder.png";

export default function CustomImage({
  src,
  alt,
  width,
  height,
  className = "",
  fallback = FALLBACK_IMAGE,
}: Props) {
  const [error, setError] = useState(false);

  // Prevent empty string from being passed to <Image />
  const finalSrc = !src || src == "" ? fallback : src;

  return (
    <Image
      src={error ? fallback : finalSrc}
      alt={alt}
      width={width}
      height={height}
      className={className}
      loading="lazy"
      onError={() => setError(true)}
    />
  );
}
