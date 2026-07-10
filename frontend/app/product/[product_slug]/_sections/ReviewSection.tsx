"use client";

import Container from "@/components/Container";
import { apiBaseUrl, imageBaseHostUrl } from "@/config/apiConfig";
import { fetcher } from "@/lib/fetcher";
import { useSession } from "@/store/useAuthStore";
import { useCallback, useEffect, useState } from "react";
import Lightbox from "yet-another-react-lightbox";
import Download from "yet-another-react-lightbox/plugins/download";
import Fullscreen from "yet-another-react-lightbox/plugins/fullscreen";
import Thumbnails from "yet-another-react-lightbox/plugins/thumbnails";
import "yet-another-react-lightbox/plugins/thumbnails.css";
import Zoom from "yet-another-react-lightbox/plugins/zoom";
import "yet-another-react-lightbox/styles.css";
import PostAReview from "../_components/PostAReview";
import ShowReviewList from "../_components/ShowReviewList";
import { ReviewProgresser } from "../_components/ReviewsProgresser";

interface Review {
  id: string;
  name: string;
  avatar: string;
  timeAgo: string;
  rating: number;
  comment: string;
  images: string[];
}

interface ReviewSectionProps {
  product: ProductDetailType;
  reviews: ReviewsResponseType | null;
  businessSettings?: SettingsType[];
}

const ReviewSection = ({
  product,
  reviews,
  businessSettings,
}: ReviewSectionProps) => {
  // Pagination states
  const [allReviews, setAllReviews] = useState<Review[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [hasMoreReviews, setHasMoreReviews] = useState(false);

  // UI states
  const [isLightboxOpen, setIsLightboxOpen] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [currentReviewImages, setCurrentReviewImages] = useState<string[]>([]);
  const [isReviewModalOpen, setIsReviewModalOpen] = useState(false);

  // get user
  const { user } = useSession();

  // get who can post a reveiws
  // const whoCanPostReviews = businessSettings?.find(
  //   (setting) => setting.type === "who_can_post_reviews"
  // );
  // let allRegstardCustomer = false;
  // let allRegstardBuyer = false;
  // let everyOneCanPostReviews = false;

  // switch (whoCanPostReviews?.value) {
  //   case "all_registered_customers":
  //     allRegstardCustomer = true;
  //     break;
  //   case "all_registered_buyers":
  //     allRegstardBuyer = true;
  //     break;
  //   case "everyone":
  //     everyOneCanPostReviews = true;
  //     break;
  // }

  // Transform API reviews to component format
  const transformApiReviews = useCallback(
    (reviewsData: ReviewType[]): Review[] => {
      return reviewsData.map((review: ReviewType) => {
        // Handle user_id which can be number or empty string
        const userId = review.user_id
          ? review.user_id.toString()
          : Math.random().toString();

        // Handle photos - API can have different structures
        let images: string[] = [];
        if (review.photos && Array.isArray(review.photos)) {
          images = review.photos
            .map((photo: any) => {
              if (typeof photo === "string") {
                return `${imageBaseHostUrl}${photo}`;
              } else if (photo && photo.path) {
                return `${imageBaseHostUrl}${photo.path}`;
              }
              return "";
            })
            .filter(Boolean);
        }

        return {
          id: userId,
          name: review.hide_username ? "Anonymous User" : review.user_name,
          avatar: review.avatar
            ? `${imageBaseHostUrl}${review.avatar}`
            : "/images/placeholder.png",
          timeAgo: review.time,
          rating: review.rating,
          comment: review.comment || "",
          images: images,
        };
      });
    },
    [],
  );

  // Initialize reviews from props on first load
  const reviewsData = reviews?.data || [];
  const totalReviews = reviews?.meta?.total || product.total_reviews;

  // Load more reviews from API
  const loadMoreReviews = useCallback(async () => {
    if (isLoadingMore || !hasMoreReviews) return;

    setIsLoadingMore(true);
    try {
      const nextPage = currentPage + 1;
      const moreReviewsData = await fetcher<ReviewsResponseType>(
        `/reviews/product/${product.id}?page=${nextPage}&per_page=5`,
        { baseUrl: apiBaseUrl },
      );

      if (moreReviewsData?.data && moreReviewsData.data.length > 0) {
        const newReviews = transformApiReviews(moreReviewsData.data);
        setAllReviews((prev) => [...prev, ...newReviews]);
        setCurrentPage(nextPage);

        // Check if there are more reviews to load
        const totalLoaded = allReviews.length + newReviews.length;
        const totalAvailable = moreReviewsData.meta?.total || totalReviews;
        setHasMoreReviews(totalLoaded < totalAvailable);
      } else {
        setHasMoreReviews(false);
      }
    } catch (error) {
      console.error("Error loading more reviews:", error);
    } finally {
      setIsLoadingMore(false);
    }
  }, [
    currentPage,
    isLoadingMore,
    hasMoreReviews,
    product.id,
    allReviews.length,
    transformApiReviews,
    totalReviews,
  ]);

  // Lightbox functions
  const openLightbox = (images: string[], startIndex: number) => {
    setCurrentReviewImages(images);
    setCurrentImageIndex(startIndex);
    setIsLightboxOpen(true);
  };

  // Render stars function
  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, index) => (
      <svg
        key={index}
        className={`h-3 w-3 sm:h-4 sm:w-4 ${index < rating ? "fill-current text-orange-400" : "text-site-gray-200 fill-current"}`}
        viewBox="0 0 20 20"
      >
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
      </svg>
    ));
  };

  useEffect(() => {
    if (reviewsData.length > 0 && allReviews.length === 0) {
      const initialReviews = transformApiReviews(reviewsData);
      setAllReviews(initialReviews);

      // Check if there are more reviews to load
      setHasMoreReviews(initialReviews.length < totalReviews);
    }
  }, [reviewsData, totalReviews, allReviews.length, transformApiReviews]);

  // Convert images array to lightbox format
  const lightboxSlides = currentReviewImages.map((image, index) => ({
    src: image,
    alt: `Review image ${index + 1}`,
    title: `Review Image ${index + 1}`,
    description: `User uploaded review image`,
  }));

  return (
    <section className="py-6 sm:py-8 md:py-[50px]" id="review_section">
      <Container>
        {/* Header */}
        <div className="mb-6 flex flex-row items-start justify-between gap-4 sm:mb-8 sm:items-center">
          <h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-[40px]">
            Rating & Reviews ({product.rating_count ?? 0})
          </h2>
          {product?.can_post_review && (
            <button
              onClick={() => {
                setIsReviewModalOpen(true);
              }}
              className="bg-site-gray-50 hover:bg-site-gray-100 flex cursor-pointer items-center gap-2 rounded-[10px] px-3 py-2 text-sm transition-colors duration-200 sm:px-4 sm:text-base"
            >
              <svg
                className="text-site-gray-900 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M12 4v16m8-8H4"
                />
              </svg>
              <span className="text-site-gray-900 font-medium">
                Write a Review
              </span>
            </button>
          )}
        </div>
        <ReviewProgresser
          avgRating={product?.rating}
          totalReview={product?.rating_count}
          ratingCount={product?.rating_counts}
          totalReviews={product?.total_reviews}
        />

        <ShowReviewList
          reviews={allReviews}
          totalReviews={totalReviews}
          isLoading={isLoadingMore}
          hasMore={hasMoreReviews}
          onLoadMore={loadMoreReviews}
          openLightbox={openLightbox}
          renderStars={renderStars}
        />

        {/* Review Images Lightbox */}
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

        <PostAReview
          isReviewModalOpen={isReviewModalOpen}
          setIsReviewModalOpen={setIsReviewModalOpen}
          product={product}
          businessSettings={businessSettings}
        />
      </Container>
    </section>
  );
};

export default ReviewSection;
