import Container from "@/components/Container";
import UserAvatar from "@/components/icons/UserAvatar";
import { AnimatePresence, motion } from "framer-motion";
import Image from "next/image";

interface Review {
  id: string;
  name: string;
  avatar: string;
  timeAgo: string;
  rating: number;
  comment: string;
  images: string[];
}

interface ShowReviewListProps {
  reviews: Review[];
  totalReviews: number;
  isLoading: boolean;
  hasMore: boolean;
  onLoadMore: () => void;
  openLightbox: (images: string[], startIndex: number) => void;
  renderStars: (rating: number) => React.ReactNode;
}

export default function ShowReviewList({
  reviews,
  totalReviews,
  isLoading,
  hasMore,
  onLoadMore,
  openLightbox,
  renderStars,
}: ShowReviewListProps) {
  return (
    <>
      {/* Reviews List */}
      {reviews.length > 0 ? (
        <div>
          <AnimatePresence>
            {reviews.map((review, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -20 }}
                transition={{ duration: 0.3, delay: index * 0.1 }}
                className="border-site-gray-100 border-b bg-white py-4 sm:py-6"
              >
                {/* Mobile Layout */}
                <div className="block lg:hidden">
                  {/* User Info and Rating */}
                  <div className="mb-3 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <div className="relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-gray-200 sm:h-14 sm:w-14">
                        <Image
                          src={review.avatar}
                          alt={`${review.name} avatar`}
                          width={56}
                          height={56}
                          className="h-full w-full object-cover"
                          onError={(e) => {
                            const target = e.target as HTMLImageElement;
                            target.style.display = "none";
                            const parent = target.parentElement;
                            if (parent) {
                              parent.innerHTML = `<div class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-500 text-lg font-medium">${review.name.charAt(0)}</div>`;
                            }
                          }}
                        />
                      </div>
                      <div>
                        <h4 className="text-site-gray-900 line-clamp-1 text-base font-medium sm:text-lg">
                          {review.name}
                        </h4>
                        <p className="text-site-gray-400 text-xs sm:text-sm">
                          {review.timeAgo}
                        </p>
                      </div>
                    </div>
                    {/* Stars */}
                    <div className="flex items-center gap-1">
                      {renderStars(review.rating)}
                    </div>
                  </div>

                  {/* Comment */}
                  <p className="text-site-gray-600 mb-3 text-sm leading-relaxed sm:text-base">
                    {review.comment}
                  </p>

                  {/* Review Images */}
                  {review.images && review.images.length > 0 && (
                    <div className="flex gap-2 overflow-x-auto pb-1">
                      {review.images.map((image: string, imgIndex: number) => (
                        <div
                          key={imgIndex}
                          className="relative h-12 w-12 flex-shrink-0 cursor-pointer overflow-hidden rounded-lg bg-gray-200 transition-opacity hover:opacity-80 sm:h-16 sm:w-16"
                          onClick={() => openLightbox(review.images, imgIndex)}
                        >
                          <Image
                            src={image}
                            alt={`Review image ${imgIndex + 1}`}
                            width={64}
                            height={64}
                            className="h-full w-full object-cover"
                            onError={(e) => {
                              const target = e.target as HTMLImageElement;
                              target.style.display = "none";
                              const parent = target.parentElement;
                              if (parent) {
                                parent.innerHTML =
                                  '<div class="w-full h-full bg-gray-300 flex items-center justify-center"><svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" /></svg></div>';
                              }
                            }}
                          />
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                {/* Desktop Layout */}
                <div className="hidden gap-2 lg:flex lg:items-center lg:gap-16">
                  {/* Avatar and User Info */}
                  <div className="flex w-[280px] flex-shrink-0 items-center gap-4 xl:w-[380px] xl:gap-6">
                    <div className="relative h-16 w-16 overflow-hidden rounded-full bg-gray-200 xl:h-[76px] xl:w-[76px]">
                      <UserAvatar
                        name={review?.name}
                        src={review?.avatar}
                        alt={review?.name}
                        size={76}
                        className="h-full w-full object-cover"
                      />
                    </div>
                    <div className="min-w-0 flex-1">
                      <h4 className="text-site-gray-900 truncate text-lg font-medium xl:text-[23px]">
                        {review.name}
                      </h4>
                      <p className="text-site-gray-400 text-sm">
                        {review.timeAgo}
                      </p>
                    </div>
                  </div>

                  {/* Review Content */}
                  <div className="min-w-0 flex-1 lg:px-4">
                    {/* Review Images */}
                    {review.images && review.images.length > 0 && (
                      <div className="mb-4 flex flex-wrap gap-2">
                        {review.images.map(
                          (image: string, imgIndex: number) => (
                            <div
                              key={imgIndex}
                              className="group relative h-16 w-16 cursor-pointer overflow-hidden rounded-md bg-gray-200 transition-opacity hover:opacity-80"
                              onClick={() =>
                                openLightbox(review.images, imgIndex)
                              }
                            >
                              <Image
                                src={image}
                                alt={`Review image ${imgIndex + 1}`}
                                width={300}
                                height={300}
                                className="h-16 w-16 rounded-sm border object-cover"
                              />
                            </div>
                          ),
                        )}
                      </div>
                    )}

                    {/* Review comment */}
                    <p className="text-site-gray-600 text-base leading-relaxed">
                      {review.comment}
                    </p>
                  </div>

                  {/* Rating Stars */}
                  <div className="flex w-[120px] items-start justify-end pt-1 xl:w-[150px]">
                    <div className="flex items-center gap-1">
                      {renderStars(review.rating)}
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </AnimatePresence>
        </div>
      ) : (
        <section className="py-6 sm:py-8 md:py-[50px]">
          <Container>
            <div className="pb-6 text-center">
              <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                <svg
                  className="h-8 w-8 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                  />
                </svg>
              </div>
              <h3 className="mb-2 text-xl text-gray-900 md:text-2xl">
                No Reviews Yet
              </h3>
              <p className="text-gray-500">
                Be the first to review this product!
              </p>
            </div>
          </Container>
        </section>
      )}

      {/* <div className="h-[1px] w-full bg-site-gray-100 top-1/2   " /> */}

      {/* See More Button */}
      {hasMore && (
        <div className="flex items-center py-2">
          <hr className="border-site-gray-100 w-full" />
          <div className="shrink-0 text-center">
            <button
              onClick={onLoadMore}
              disabled={isLoading}
              className="border-site-gray-100 inline-flex cursor-pointer items-center gap-2 rounded-full border-1 px-4 py-1 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50 sm:gap-3 sm:px-6 sm:py-2 sm:text-base"
            >
              {isLoading ? (
                <>
                  <svg
                    className="h-4 w-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      className="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      strokeWidth="4"
                    ></circle>
                    <path
                      className="opacity-75"
                      fill="currentColor"
                      d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  <span>Loading...</span>
                </>
              ) : (
                <>
                  <span className="text-site-gray-700 font-semibold">
                    See More
                  </span>

                  <div className="hidden items-center sm:flex">
                    {reviews.slice(-3).map((review, index) => (
                      <div
                        key={index}
                        className="relative -ml-1 h-6 w-6 overflow-hidden rounded-full border-2 bg-gray-200 first:ml-0"
                      >
                        <Image
                          src={review?.avatar}
                          alt=""
                          width={24}
                          height={24}
                          className="h-full w-full object-cover"
                          onError={(e) => {
                            const target = e.target as HTMLImageElement;
                            target.style.display = "none";
                            const parent = target.parentElement;
                            if (parent) {
                              parent.innerHTML = `<div class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-400 text-xs font-medium">${review.name.charAt(0)}</div>`;
                            }
                          }}
                        />
                      </div>
                    ))}
                  </div>
                </>
              )}
            </button>
          </div>
          <hr className="border-site-gray-100 w-full" />
        </div>
      )}
    </>
  );
}
