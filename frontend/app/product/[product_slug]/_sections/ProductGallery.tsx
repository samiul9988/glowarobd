"use client";
import ShareModal from "@/components/ShareModal";
import { cn } from "@/lib/utils";
import { Heart } from "lucide-react";
import Image from "next/image";
import { useState } from "react";
import "swiper/css";
import "swiper/css/free-mode";
import "swiper/css/navigation";
import "swiper/css/thumbs";
import { FreeMode, Thumbs } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/react";
import Lightbox from "yet-another-react-lightbox";
import {
  Download,
  Fullscreen,
  Thumbnails,
  Zoom,
} from "yet-another-react-lightbox/plugins";
import "yet-another-react-lightbox/plugins/captions.css";
import "yet-another-react-lightbox/plugins/thumbnails.css";
import "yet-another-react-lightbox/styles.css";

interface ProductGalleryProps {
  images: string[];
  productTitle?: string;
  productDescription?: string;
  handleAddToWishlist: () => void;
  isProductWishListed: boolean;
}

const ProductGallery = ({
  images,
  productTitle = "Amazing Product",
  productDescription = "Check out this incredible product!",
  handleAddToWishlist,
  isProductWishListed,
}: ProductGalleryProps) => {
  const [thumbsSwiper, setThumbsSwiper] = useState<any>(null);
  const [isLightboxOpen, setIsLightboxOpen] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [isShareModalOpen, setIsShareModalOpen] = useState(false);

  // Use placeholder if no images available
  const displayImages = images && images.length > 0 ? images : ["/images/placeholder.png"];

  // Convert images array to lightbox format with enhanced data
  const lightboxSlides = displayImages?.map((image, index) => ({
    src: image,
    alt: `Product image ${index + 1}`,
    title: `Product Image ${index + 1}`,
    description: `High quality product image showing different angles and details`,
  }));
  return (
    <>
      <div className="productGallery__slider relative w-full lg:w-[560px]">
        {/* Action buttons */}
        <div className="absolute top-2 right-2 z-10 flex flex-col gap-2 md:top-4 md:right-4">
          <button
            onClick={handleAddToWishlist}
            className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-[10px] bg-white shadow-md transition-colors hover:bg-gray-50 md:h-11 md:w-11"
          >
            {/* heart icon */}
            <Heart
              className={cn(
                isProductWishListed
                  ? "fill-rose-500 text-rose-500"
                  : "text-site-gray-500",
              )}
            />
          </button>
          {/* share button */}
          <button
            onClick={() => setIsShareModalOpen(true)}
            className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-[10px] bg-white shadow-md transition-colors hover:bg-gray-50 md:h-11 md:w-11"
          >
            <svg
              className="h-5 w-5 text-gray-600 md:h-6 md:w-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"
              />
            </svg>
          </button>
        </div>

        {/* Main Swiper */}
        <div className="mb-3 md:mb-4">
          <Swiper
            spaceBetween={0}
            thumbs={{
              swiper:
                thumbsSwiper && !thumbsSwiper.destroyed ? thumbsSwiper : null,
            }}
            modules={[FreeMode, Thumbs]}
            className="main-swiper"
          >
            {displayImages?.map((image, index) => (
              <SwiperSlide key={index}>
                <div
                  className="border-site-gray-50 aspect-square cursor-pointer overflow-hidden rounded-lg border bg-gray-100"
                  onClick={() => {
                    setCurrentImageIndex(index);
                    setIsLightboxOpen(true);
                  }}
                >
                  <Image
                    src={image}
                    alt={`Product image ${index + 1}`}
                    width={560}
                    height={560}
                    className="h-full w-full object-contain"
                  />
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        </div>
        {/* Thumbs Swiper - Only show if more than 1 image */}
        {displayImages && displayImages.length > 1 && (
          <Swiper
            onSwiper={setThumbsSwiper}
            spaceBetween={8}
            slidesPerView={4}
            freeMode={true}
            watchSlidesProgress={true}
            grabCursor={true}
            centeredSlides={false}
            modules={[FreeMode, Thumbs]}
            className="thumbs-swiper select-none"
            breakpoints={{
              480: { slidesPerView: 4, spaceBetween: 8 },
              640: { slidesPerView: 5, spaceBetween: 10 },
              1024: { slidesPerView: 5, spaceBetween: 12 },
            }}
          >
            {displayImages.map((image, index) => (
              <SwiperSlide key={index} className="p-0.5">
                <div
                  onClick={() => {
                    setCurrentImageIndex(index);

                    if (thumbsSwiper && thumbsSwiper.controller?.control) {
                      thumbsSwiper.controller.control.slideTo(index);
                    }

                    if (thumbsSwiper && thumbsSwiper.slides?.[index]) {
                      thumbsSwiper.slideTo(index - 1 < 0 ? 0 : index - 1);
                    }
                  }}
                  className={cn(
                    "aspect-square cursor-pointer overflow-hidden rounded-lg border-2 bg-gray-100 transition-all",
                    currentImageIndex === index
                      ? "border-site-primary scale-[1.02] shadow-md"
                      : "hover:border-site-primary/60 border-transparent",
                  )}
                >
                  <Image
                    src={image}
                    alt={`Thumbnail ${index + 1}`}
                    width={100}
                    height={100}
                    className="h-full w-full object-contain"
                  />
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        )}

        {/* Advanced Lightbox with Plugins */}
        <Lightbox
          open={isLightboxOpen}
          close={() => setIsLightboxOpen(false)}
          slides={lightboxSlides}
          index={currentImageIndex}
          plugins={[Download, Fullscreen, Thumbnails, Zoom]}
          on={{
            view: ({ index }: { index: number }) => setCurrentImageIndex(index),
          }}
          animation={{ fade: 300 }}
          carousel={{ finite: true }}
          controller={{
            closeOnBackdropClick: true,
            closeOnPullDown: true,
            closeOnPullUp: true,
          }}
          zoom={{
            maxZoomPixelRatio: 3,
            zoomInMultiplier: 2,
            doubleTapDelay: 300,
            doubleClickDelay: 300,
            doubleClickMaxStops: 2,
            keyboardMoveDistance: 50,
            wheelZoomDistanceFactor: 100,
            pinchZoomDistanceFactor: 100,
            scrollToZoom: true,
          }}
          thumbnails={{
            position: "bottom",
            width: 102,
            height: 101,
            border: 2,
            borderRadius: 4,
            padding: 4,
            gap: 16,
            imageFit: "cover",
          }}
        />
      </div>

      {/* Share Modal */}
      <ShareModal
        isOpen={isShareModalOpen}
        onClose={() => setIsShareModalOpen(false)}
        title={productTitle}
        description={productDescription}
        image={displayImages?.[0]} // Use the first image as the share image
      />
    </>
  );
};

export default ProductGallery;
