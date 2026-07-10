"use client";

import Image from "next/image";
import { useState } from "react";

interface CategoryImageProps {
  src: string;
  alt: string;
  className?: string;
}

export default function CategoryImage({
  src,
  alt,
  className,
}: CategoryImageProps) {
  const [imgSrc, setImgSrc] = useState(src);

  return (
    <Image
      src={imgSrc || "/images/placeholder.png"}
      alt={alt}
      className={className}
      height={120}
      width={140}
      onError={() => setImgSrc("/images/placeholder.png")}
    />
  );
}
