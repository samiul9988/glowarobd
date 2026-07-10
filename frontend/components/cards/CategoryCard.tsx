"use client";

import { imageBaseHostUrl, imageBaseUrl } from "@/config/apiConfig";
import Image from "next/image";
import Link from "next/link";
import { useState } from "react";

interface Icon {
  app: string;
  mobile: string;
  web: string;
}
interface Props {
  id: number;
  name: string;
  slug: string;
  featured_icons: Icon;
}

const CategoryCard = ({ name, featured_icons, slug }: Props) => {
  const [imgSrc, setImgSrc] = useState(imageBaseUrl + featured_icons?.web);
  const fallback = "/images/placeholder.png";
  // console.log("imgSrc",featured_icon)
  return (
    <Link prefetch={false} href={"/category/" + slug}>
      <div className="flex flex-col items-center justify-center gap-2 rounded-[20px] py-1 text-center md:gap-3">
        <Image
          src={imgSrc}
          alt={name}
          width={197}
          height={250}
          className="h-auto max-w-full object-contain transition-transform duration-300 ease-in-out group-hover:scale-110"
          onError={() => setImgSrc(fallback)}
          loading="lazy"
          placeholder="blur"
          blurDataURL={fallback}
        />
        <h6 className="text-site-gray-800 font-inter line-clamp-2 text-sm font-medium md:leading-7 lg:text-[20px] lg:break-normal">
          {name}
        </h6>
      </div>
    </Link>
  );
};

export default CategoryCard;
