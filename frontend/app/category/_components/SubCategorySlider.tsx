"use client";
import Container from "@/components/Container";
import { cn } from "@/lib/utils";
import Link from "next/link";
import "swiper/css";
import "swiper/css/free-mode";
import { Autoplay, FreeMode } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";

interface Props {
  subcategoryRes: Category[];
}

export default function SubCategorySlider({ subcategoryRes }: Props) {
  return (
    <div className="lg:hidden">
      <nav className="relative bg-white">
        <Container className="pt-3 md:pt-4">
          <ul className="relative flex justify-center gap-3">
            <Swiper
              modules={[Autoplay, FreeMode]}
              spaceBetween={12}
              slidesPerView="auto"
              freeMode={true}
              autoplay={false}
              breakpoints={{
                0: { spaceBetween: 6 },
                768: { spaceBetween: 12 },
              }}
              className="dynamic-swiper"
            >
              {subcategoryRes &&
                subcategoryRes.map((item) => (
                  <SwiperSlide key={item.id} className="group relative !w-auto">
                    <Link
                      scroll={true}
                      href={"/category/" + item.slug || "#"}
                      className={cn(
                        "hover:bg-site-primary-100/80 border-site-gray-100 inline-flex items-center gap-1 rounded-full border bg-[#F9F9F9] px-3 py-1.5 text-sm font-medium text-gray-800 transition duration-300",
                      )}
                    >
                      {item.name}
                    </Link>
                  </SwiperSlide>
                ))}
            </Swiper>
          </ul>
        </Container>
      </nav>
    </div>
  );
}
