import { apiBaseUrl, imageBaseHostUrl } from "@/config/apiConfig";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { zodResolver } from "@hookform/resolvers/zod";
import { AnimatePresence, motion } from "framer-motion";
import Image from "next/image";
import { Dispatch, SetStateAction, useEffect, useRef, useState } from "react";
import { useForm } from "react-hook-form";
import toast from "react-hot-toast";
import { z } from "zod";

// Zod schema for form validation
const reviewSchema = z.object({
  rating: z.number().min(1, "Please select a rating").max(5),
  comment: z.string().min(10, "Review must be at least 10 characters long"),
  name: z.string().min(2, "Name must be at least 2 characters long"),
  email: z.string().email({ message: "Please enter a valid email address" }),
  phone: z.string().optional(),
  hide_username: z.boolean(),
});

type ReviewFormData = z.infer<typeof reviewSchema>;

export default function PostAReview({
  isReviewModalOpen,
  setIsReviewModalOpen,
  product,
  businessSettings,
}: {
  isReviewModalOpen: boolean;
  setIsReviewModalOpen: Dispatch<SetStateAction<boolean>>;
  product?: any;
  businessSettings?: SettingsType[];
}) {
  const { user } = useSession();
  const { accessToken } = useToken();
  const [selectedRating, setSelectedRating] = useState(0);
  const [hoverRating, setHoverRating] = useState(0);
  const [uploadedImages, setUploadedImages] = useState<File[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Business settings
  const canImageUpload = businessSettings?.find(
    (setting) => setting.type === "reviews_image_upload"
  );
  const maxImageUpload = businessSettings?.find(
    (setting) => setting.type === "reviews_max_image"
  );
  const onlyRegistardUser = businessSettings?.find(
    (setting) => setting.type === "reviews_image_upload_only_user"
  );
  const whoCanPostReviews = businessSettings?.find(
    (setting) => setting.type === "who_can_post_reviews"
  );

  const isImageUploadEnabled = canImageUpload?.value === "on";
  const maxImages = parseInt(maxImageUpload?.value || "5");

  const reviewPermission = whoCanPostReviews?.value || "all_registered_buyers";
  const isOnlyRegistardUserCanImageUpload = onlyRegistardUser?.value === "on";
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
    setValue,
    watch,
  } = useForm<ReviewFormData>({
    resolver: zodResolver(reviewSchema),
    defaultValues: {
      name: user?.name || "",
      email: user?.email || "",
      phone: user?.phone || "",
      hide_username: false,
      rating: 0,
      comment: "",
    },
  });

  // Watch hide_username to update form state (for future use)
  // const hideUsername = watch("hide_username");

  // Update form values when user data changes
  useEffect(() => {
    if (user) {
      setValue("name", user.name || "");
      setValue("email", user.email || "");
      setValue("phone", user.phone || "");
    }
  }, [user, setValue]);

  // Handle image upload
  const handleImageUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = event.target.files;
    if (files && isImageUploadEnabled) {
      const filesArray = Array.from(files);
      const newImages = [...uploadedImages, ...filesArray].slice(0, maxImages);
      setUploadedImages(newImages);
    }
  };

  // Remove uploaded image
  const removeImage = (index: number) => {
    setUploadedImages((prev) => prev.filter((_, i) => i !== index));
  };

  // Handle rating selection
  const handleRatingClick = (rating: number) => {
    setSelectedRating(rating);
    setValue("rating", rating);
  };

  // Reset form and close modal
  const resetAndClose = () => {
    reset();
    setSelectedRating(0);
    setHoverRating(0);
    setUploadedImages([]);
    setIsReviewModalOpen(false);
  };

  // Submit review
  const onSubmit = async (data: ReviewFormData) => {
    if (!user) {
      toast.error("Please login to submit a review");
      return;
    }

    // Check review permission
    if (reviewPermission === "verified_buyers_only") {
      // You might want to add a check here for verified buyers
      // For now, we'll allow all registered users
    }

    setIsSubmitting(true);

    try {
      const formData = new FormData();

      // Add form fields
      formData.append("product_id", product.id.toString());
      formData.append("user_id", user.id.toString());
      formData.append("name", data.name);
      formData.append("email", data.email);
      formData.append("phone", data.phone || "");
      formData.append("rating", data.rating.toString());
      formData.append("comment", data.comment);
      formData.append("hide_username", data.hide_username ? "1" : "0");

      // Add images only if upload is enabled
      if (isImageUploadEnabled && uploadedImages.length > 0) {
        uploadedImages.forEach((image) => {
          formData.append("photos[]", image);
        });
      }

      const response = await fetch(`${apiBaseUrl}/reviews/submit`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
        body: formData,
      });

      const responseData = await response.json();

      if (response.ok && responseData.result === true) {
        toast.success("Review submitted successfully!");
        resetAndClose();
        // Optionally refresh the page or update the reviews list
        // window.location.reload();
      } else {
        toast.error(responseData.message || "Failed to submit review");
      }
    } catch (error) {
      toast.error("An error occurred while submitting your review");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <>
      {/* Write Review Modal */}
      {isReviewModalOpen && (
        <AnimatePresence>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-gray-900/40 backdrop-blur-xs flex items-center justify-center px-2 md:p-4 z-50"
            onClick={resetAndClose}
          >
            <motion.div
              initial={{ scale: 0.95, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.95, opacity: 0 }}
              transition={{ duration: 0.2 }}
              className="bg-white rounded-md px-3 py-6 md:p-6 w-full max-w-[653px] max-h-[90vh] overflow-y-auto scroll-thin"
              onClick={(e) => e.stopPropagation()}
              onWheel={(e) => e.stopPropagation()}
            >
              <form onSubmit={handleSubmit(onSubmit)}>
                {/* Modal Header */}
                <div className="flex items-center justify-between mb-6">
                  <h3 className="text-xl font-semibold text-gray-900">
                    Write a Review
                  </h3>
                  <button
                    type="button"
                    onClick={resetAndClose}
                    className="p-2 hover:bg-gray-100 rounded-full transition-colors"
                  >
                    <svg
                      className="w-5 h-5 text-gray-500"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>

                {/* Product Info */}
                <div className="flex items-center gap-3 mb-6 p-3 bg-gray-50 rounded-lg">
                  <div className="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden">
                    <Image
                      src={
                        product?.photos?.[0]
                          ? `${imageBaseHostUrl}${product.photos[0].path}`
                          : "/images/placeholder.png"
                      }
                      alt={product?.name || "Product"}
                      width={48}
                      height={48}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <div className="flex-1 min-w-0">
                    <h4 className="font-medium text-gray-900 truncate">
                      {product?.name}
                    </h4>
                    <p className="text-sm text-gray-500">
                      {product?.brand?.name}
                    </p>
                  </div>
                </div>

                {/* Rating Section */}
                <div className="mb-6">
                  <label className="block text-sm font-medium text-gray-700 mb-3">
                    Your Rating *
                  </label>
                  <div className="flex items-center gap-0">
                    {[1, 2, 3, 4, 5].map((star) => (
                      <button
                        key={star}
                        type="button"
                        className="p-0.5 hover:scale-110 transition-transform"
                        onClick={() => handleRatingClick(star)}
                        onMouseEnter={() => setHoverRating(star)}
                        onMouseLeave={() => setHoverRating(0)}
                      >
                        <svg
                          className={`w-8 h-8 transition-colors ${
                            star <= (hoverRating || selectedRating)
                              ? "text-yellow-400"
                              : "text-gray-300"
                          }`}
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      </button>
                    ))}
                  </div>
                  {errors.rating && (
                    <p className="text-red-500 text-sm mt-1">
                      {errors.rating.message}
                    </p>
                  )}
                </div>

                {/* Review Comment */}
                <div className="mb-6">
                  <label
                    htmlFor="reviewComment"
                    className="block text-sm font-medium text-gray-700 mb-2"
                  >
                    Your Review *
                  </label>
                  <textarea
                    id="reviewComment"
                    rows={4}
                    placeholder="Share your experience with this product..."
                    className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-colors resize-none ${
                      errors.comment ? "border-red-500" : "border-gray-300"
                    }`}
                    {...register("comment")}
                  />
                  {errors.comment && (
                    <p className="text-red-500 text-sm mt-1">
                      {errors.comment.message}
                    </p>
                  )}
                </div>

                {/* Photo Upload - Only show if enabled */}
                {(isImageUploadEnabled ||
                  (isOnlyRegistardUserCanImageUpload && user?.id)) && (
                  <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Add Photos (Optional)
                    </label>
                    <div
                      className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
                      onClick={() => fileInputRef.current?.click()}
                    >
                      <svg
                        className="w-8 h-8 text-gray-400 mx-auto mb-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        />
                      </svg>
                      <p className="text-sm text-gray-500">
                        Click to upload photos
                      </p>
                      <p className="text-xs text-gray-400 mt-1">
                        PNG, JPG up to 5MB each (Max {maxImages} photos)
                      </p>
                    </div>
                    <input
                      ref={fileInputRef}
                      type="file"
                      multiple
                      accept="image/*"
                      onChange={handleImageUpload}
                      className="hidden"
                    />

                    {/* Display uploaded images */}
                    {uploadedImages.length > 0 && (
                      <div className="flex gap-2 mt-3 flex-wrap">
                        {uploadedImages.map((image, index) => (
                          <div key={index} className="relative">
                            <img
                              src={URL.createObjectURL(image)}
                              alt={`Upload ${index + 1}`}
                              className="w-16 h-16 object-cover rounded-lg"
                            />
                            <button
                              type="button"
                              onClick={() => removeImage(index)}
                              className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
                            >
                              ×
                            </button>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                )}

                {/* User Info */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                  <div>
                    <label
                      htmlFor="reviewerName"
                      className="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Your Name *
                    </label>
                    <input
                      type="text"
                      id="reviewerName"
                      placeholder="Enter your name"
                      className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-colors ${
                        errors.name ? "border-red-500" : "border-gray-300"
                      }`}
                      value={user?.name || ""}
                      {...register("name")}
                    />
                    {errors.name && (
                      <p className="text-red-500 text-sm mt-1">
                        {errors.name.message}
                      </p>
                    )}
                  </div>
                  <div>
                    <label
                      htmlFor="reviewerEmail"
                      className="block text-sm font-medium text-gray-700 mb-2"
                    >
                      Email *
                    </label>
                    <input
                      type="email"
                      id="reviewerEmail"
                      placeholder="Enter your email"
                      className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-colors ${
                        errors.email ? "border-red-500" : "border-gray-300"
                      }`}
                      value={user?.email || ""}
                      {...register("email")}
                    />
                    {errors.email && (
                      <p className="text-red-500 text-sm mt-1">
                        {errors.email.message}
                      </p>
                    )}
                  </div>
                </div>

                {/* Phone (Optional) */}
                <div className="mb-6">
                  <label
                    htmlFor="reviewerPhone"
                    className="block text-sm font-medium text-gray-700 mb-2"
                  >
                    Phone (Optional)
                  </label>
                  <input
                    type="tel"
                    id="reviewerPhone"
                    placeholder="Enter your phone number"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-colors"
                    {...register("phone")}
                    value={user?.phone || ""}
                  />
                </div>

                {/* Privacy Options */}
                <div className="mb-6">
                  <label className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      {...register("hide_username")}
                    />
                    <span className="text-sm text-gray-600">
                      Keep my name anonymous
                    </span>
                  </label>
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                  <button
                    type="button"
                    onClick={resetAndClose}
                    className="cursor-pointer flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                    disabled={isSubmitting}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={isSubmitting}
                    className="flex-1 bg-gray-800 text-white py-3 px-6 rounded-lg font-medium hover:bg-gray-900 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {isSubmitting ? "Submitting..." : "Submit Review"}
                  </button>
                </div>
              </form>
            </motion.div>
          </motion.div>
        </AnimatePresence>
      )}
    </>
  );
}
