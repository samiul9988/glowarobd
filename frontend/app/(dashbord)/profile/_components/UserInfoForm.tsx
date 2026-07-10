"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { ChevronDownIcon, Eye, EyeOff } from "lucide-react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";

import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import { Input } from "@/components/ui/input";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { api } from "@/lib/axios";
import { cn } from "@/lib/utils";
import { userInfoSchema } from "@/schema/userInfoSchema";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import toast from "react-hot-toast";
import PrimaryButton from "@/components/buttons/PrimaryButton";

const UserInfoForm = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [open, setOpen] = useState(false);
  const { accessToken } = useToken();
  const { user } = useSession();
  const queryClient = useQueryClient();

  // Get Global User Data
  const { data: userData, isLoading } = useQuery({
    queryKey: ["get_user_data"],
    queryFn: async () => {
      const res = await api.post(
        `/get-user-by-access_token`,
        {
          access_token: accessToken,
        },
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        },
      );
      return res.data as UserResponse;
    },

    enabled: !!accessToken,
  });

  // Update User Data
  const { mutate: updateUser, isPending } = useMutation({
    mutationKey: ["update_user_data"],
    mutationFn: async (data: {
      name: string;
      password: string | undefined;
      gender: string;
      dob: Date | undefined;
    }) => {
      const res = await api.post(
        `/profile/update`,
        {
          id: user?.id,
          name: data.name,
          password: data.password || "",
          gender: data.gender,
          dob: data.dob,
        },
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        },
      );

      return res.data as UserResponse;
    },
    onSuccess: () => {
      toast.success("Profile updated successfully!");
      queryClient.invalidateQueries({ queryKey: ["get_user_data"] });
    },
  });

  const form = useForm<z.infer<typeof userInfoSchema>>({
    resolver: zodResolver(userInfoSchema),
    defaultValues: {
      name: "",
      phone: "",
      email: "",
      gender: "",
      dob: undefined,
      password: "",
      confirmPassword: "",
    },
  });

  useEffect(() => {
    if (userData) {
      form.reset({
        name: userData.name ?? "",
        phone: userData.phone ?? "",
        email: userData.email ?? "",
        gender: userData.gender ?? "",
        dob: userData.date_of_birth
          ? new Date(userData.date_of_birth)
          : undefined,
        password: "",
        confirmPassword: "",
      });
    }
  }, [userData, form]);

  // Handle Submit form data
  function onSubmit(values: z.infer<typeof userInfoSchema>) {
    updateUser({
      name: values.name,
      password: values.password,
      gender: values.gender,
      dob: values.dob,
    });

    form.reset({
      ...values,
      phone: userData?.phone ?? "",
      email: userData?.email ?? "",
      password: "",
      confirmPassword: "",
    });
  }

  return (
    <div className="mt-10">
      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
          {/* Name & Phone */}
          <div className="flex flex-col items-start gap-4 md:flex-row">
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem className="w-full">
                  <FormLabel className="text-site-gray-400">Name</FormLabel>
                  <FormControl>
                    <Input
                      type="text"
                      placeholder="John Doe"
                      {...field}
                      className="site-input-field"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="phone"
              disabled
              render={({ field }) => (
                <FormItem className="w-full">
                  <FormLabel className="text-site-gray-400">Phone</FormLabel>
                  <FormControl>
                    <Input
                      readOnly
                      type="text"
                      placeholder="01XXXXXXXXX"
                      {...field}
                      className="site-input-field"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

         

          {/* Gender + Date of Birth */}
          <div className="grid md:grid-cols-3 grid-cols-1 gap-4 ">
            {/* Gender */}
             {/* Email */}
          <FormField
            control={form.control}
            name="email"
            disabled
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-site-gray-400" >Email</FormLabel>
                <FormControl>
                  <Input
                    readOnly
                    type="text"
                    placeholder="017xxxxxxxx"
                    {...field}
                    className="site-input-field"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
            <FormField
              control={form.control}
              name="gender"
              render={({ field }) => (
                <FormItem className="w-full">
                  <FormLabel className="text-site-gray-400">Gender</FormLabel>
                  <Select
                    onValueChange={field.onChange}
                    value={field.value || userData?.gender || ""}
                  >
                    <SelectTrigger className="!h-12 w-full !bg-white">
                      <SelectValue placeholder="Select gender" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectGroup>
                        <SelectLabel>Genders</SelectLabel>
                        <SelectItem value="male">Male</SelectItem>
                        <SelectItem value="female">Female</SelectItem>
                        <SelectItem value="other">Other</SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Date of Birth */}
            <FormField
              control={form.control}
              name="dob"
              render={({ field }) => (
                <FormItem className="w-full">
                  <FormLabel className="text-site-gray-400">
                    Date of Birth
                  </FormLabel>
                  <Popover open={open} onOpenChange={setOpen}>
                    <PopoverTrigger asChild>
                      <Button
                        variant="outline"
                        id="date"
                        className={cn(
                          "!h-12 w-full justify-between !bg-white font-normal",
                          field.value
                            ? "!text-site-gray-900"
                            : "!text-site-gray-300",
                        )}
                      >
                        {field.value
                          ? field.value.toLocaleDateString("en-GB", {
                              day: "2-digit",
                              month: "2-digit",
                              year: "numeric",
                            })
                          : "DD/MM/YYYY"}
                        <ChevronDownIcon />
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent
                      className="w-full max-w-[300px] overflow-hidden p-0"
                      align="start"
                    >
                      <Calendar
                        mode="single"
                        selected={field.value}
                        captionLayout="dropdown"
                        onSelect={(date) => {
                          field.onChange(date);
                          setOpen(false);
                        }}
                      />
                    </PopoverContent>
                  </Popover>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          <h3 className="text-2xl pt-6 font-bold">Change Password</h3>

          {/* Password + Confirm Password */}
          <div className="flex flex-col items-start gap-4 md:flex-row">
            <FormField
              control={form.control}
              name="password"
              render={({ field }) => (
                <FormItem className="w-full">
                  <FormLabel className="text-site-gray-400">Password</FormLabel>
                  <FormControl>
                    <div className="relative">
                      <Input
                        type={showPassword ? "text" : "password"}
                        placeholder="••••••"
                        {...field}
                        className="site-input-field"
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute top-3.5 right-3 text-gray-500"
                      >
                        {showPassword ? (
                          <EyeOff size={18} />
                        ) : (
                          <Eye size={18} />
                        )}
                      </button>
                    </div>
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="confirmPassword"
              render={({ field }) => (
                <FormItem className="w-full">
                  <FormLabel className="text-site-gray-400">
                    Confirm Password
                  </FormLabel>
                  <FormControl>
                    <div className="relative">
                      <Input
                        type={showConfirmPassword ? "text" : "password"}
                        placeholder="••••••"
                        {...field}
                        className="site-input-field"
                      />
                      <button
                        type="button"
                        onClick={() =>
                          setShowConfirmPassword(!showConfirmPassword)
                        }
                        className="absolute top-3.5 right-3 text-gray-500"
                      >
                        {showConfirmPassword ? (
                          <EyeOff size={18} />
                        ) : (
                          <Eye size={18} />
                        )}
                      </button>
                    </div>
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          </div>

          {/* Submit Button */}
          <div className="flex justify-end">
            <PrimaryButton
              type="submit"
              disabled={isLoading || isPending}
              className="max-w-[220px]"
            >
              {isPending ? "Saving Changes..." : "Save Changes"}
            </PrimaryButton>
          </div>
        </form>
      </Form>
    </div>
  );
};

export default UserInfoForm;
