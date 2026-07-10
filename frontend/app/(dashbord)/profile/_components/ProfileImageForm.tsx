"use client";

import React, { useRef, useState } from "react";
import Image from "next/image";
import { CloudUpload, Loader2, Trash } from "lucide-react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import toast from "react-hot-toast";
import { api } from "@/lib/axios";
import { useToken } from "@/store/useTokenStore";
import { useSession } from "@/store/useAuthStore";
import { convertToBase64 } from "@/utils/convertToBase64";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormMessage,
} from "@/components/ui/form";
import BodyText from "@/components/BodyText";
import { imageBaseHostUrl } from "@/config/apiConfig";

// Image validation schema
const imageSchema = z
  .any()
  .refine((file) => file?.length === 1, "You must select one image")
  .refine(
    (file) => file?.[0]?.size <= 6024 * 6024,
    "File size must be less than 5MB",
  )
  .refine(
    (file) => ["image/jpeg", "image/png"].includes(file?.[0]?.type),
    "Only JPEG or PNG files are allowed",
  );

const FormSchema = z.object({
  image: imageSchema,
});

type FormValues = z.infer<typeof FormSchema>;

interface UserResponse {
  id: number;
  name: string;
  avatar_original: string;
}

const ProfileImageForm = () => {
  const fileInputRef = useRef<HTMLInputElement | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const { accessToken } = useToken();
  const { user } = useSession();
  const queryClient = useQueryClient();

  const form = useForm<FormValues>({
    resolver: zodResolver(FormSchema),
  });

  // Fetch user data
  const { data: userData, isLoading } = useQuery({
    queryKey: ["get_user_data"],
    queryFn: async () => {
      const res = await api.post(
        `/get-user-by-access_token`,
        { access_token: accessToken },
        {
          headers: { Authorization: `Bearer ${accessToken}` },
        },
      );
      return res.data as UserResponse;
    },
    enabled: !!accessToken,
  });

  // Mutation for uploading image
  const { mutate: uploadProfileImage, isPending } = useMutation({
    mutationKey: ["update_profile_image"],
    mutationFn: async ({
      base64,
      fileName,
    }: {
      base64: string;
      fileName: string;
    }) => {
      const res = await api.post(
        `/profile/update-image`,
        {
          id: user?.id,
          filename: fileName,
          image: base64,
        },
        {
          headers: { Authorization: `Bearer ${accessToken}` },
        },
      );
      return res.data;
    },
    onSuccess: (data) => {
      toast.success(data?.message || "Profile image updated!");
      queryClient.invalidateQueries({ queryKey: ["get_user_data"] });
      resetForm();
    },
    onError: (error: any) => {
      toast.error(error?.response?.data?.message || "Image upload failed");
    },
  });

  // Reset form
  const resetForm = () => {
    setPreviewUrl(null);
    if (fileInputRef.current) fileInputRef.current.value = "";
    form.resetField("image");
  };

  // Handle file change + auto upload
  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const filesList = e.target.files as FileList;
    form.setValue("image", filesList);
    form.clearErrors("image");

    const validation = imageSchema.safeParse(filesList);
    if (!validation.success) {
      toast.error(validation.error.issues[0]?.message || "Image upload failed");
      resetForm();
      return;
    }

    // Set local preview
    const objectUrl = URL.createObjectURL(file);
    setPreviewUrl(objectUrl);

    // Convert to base64 + upload
    const { base64, fileName } = await convertToBase64(file);
    uploadProfileImage({ base64, fileName });
  };

  const imageSrc =
    previewUrl ||
    (userData?.avatar_original &&
      imageBaseHostUrl + userData.avatar_original) ||
    "/images/placeholder.png";

  return (
    <Form {...form}>
      <form className="relative flex items-center gap-6">
        {/* Profile Image */}
        <FormField
          control={form.control}
          name="image"
          render={() => (
            <FormItem className="relative">
              <FormControl>
                <div className="relative h-[118px] w-[118px] rounded-full bg-white">
                  <Image
                    src={imageSrc}
                    alt="Profile Image"
                    fill
                    className="aspect-square rounded-full object-cover"
                  />

                  {/* Loading overlay */}
                  {isPending && (
                    <div className="absolute inset-0 flex items-center justify-center bg-white/60">
                      <Loader2
                        className="text-site-primary-500 animate-spin"
                        size={26}
                      />
                    </div>
                  )}

                  {/* Delete button */}
                  {/* {imageSrc && (
                    <button
                      type="button"
                      onClick={handleDelete}
                      className="absolute right-1 bottom-1 grid h-9 w-9 place-content-center rounded-full border-2 border-white bg-[#FADADA] transition hover:bg-red-200"
                    >
                      <Trash
                        size={16}
                        className="text-[#E54545]"
                        strokeWidth={2}
                      />
                    </button>
                  )} */}

                  {/* Hidden file input */}
                  <input
                    type="file"
                    accept="image/jpeg,image/png"
                    ref={fileInputRef}
                    onChange={handleFileChange}
                    className="hidden"
                  />
                </div>
              </FormControl>

              {/* Styled form error message (from your previous version) */}
              <FormMessage className="absolute -bottom-6 text-[13px] text-red-500" />
            </FormItem>
          )}
        />

        {/* Upload Info & Button */}
        <div className="space-y-2.5">
          <BodyText variant="two" className="text-site-gray-400">
            File size: maximum 1 MB 
          </BodyText>

          <button
            type="button"
            disabled={isPending}
            onClick={() => fileInputRef.current?.click()}
            className={`rounded-full flex items-center gap-2  border px-5 md:px-10 py-[11px] transition-colors ${
              isPending
                ? "cursor-not-allowed opacity-70"
                : "border-site-primary-500 text-site-primary-500 hover:bg-site-primary-50"
            }`}
          >
            <CloudUpload size={20} />
            {isPending ? "Uploading..." : "Upload Photo"}
          </button>
        </div>
      </form>
    </Form>
  );
};

export default ProfileImageForm;
