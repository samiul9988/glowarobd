"use client";

import { imageBaseHostUrl, imageBaseUrl } from "@/config/apiConfig";
import Image from "next/image";
import Link from "next/link";
import { useState } from "react";

interface Props {
    id: number;
    name: string;
    slug?: string;
    image: string;
    title?:string
}

const ShopByConcernCard = ({ name, image, slug, title }: Props) => {
  const [imgSrc, setImgSrc] = useState(imageBaseUrl + image);
  const fallback = "/images/placeholder.png";

  return (
    <Link prefetch={false} href={`/category/${slug ?? '#'}`} className="bg-gradient-to-b from-[#B375F2] to-[#E3D2F4] group block hover:shadow-lg transition-shadow duration-300 ease-in-out rounded-[12px] p-2 ">
      <div className="rounded-[12px] flex flex-col items-center justify-center gap-2  text-center">
        <Image
          src={imgSrc}
          alt={`Category ${name}`}
          width={197}
          height={213}
          className="rounded-sm object-cover max-w-full  h-[110px] w-full md:h-[220px]  transition-transform duration-300 ease-in-out group-hover:scale-95"
          onError={() => setImgSrc(fallback)}
          loading="lazy"
          placeholder="blur"
          blurDataURL={fallback}
        />
        <h6 className="text-site-secondary-800 font-bold font-inter line-clamp-2 text-sm  break-all md:leading-7 lg:text-base lg:break-normal">
          {title}
        </h6>
      </div>
    </Link>
  );
};

export default ShopByConcernCard;
