"use client";

import { useState, useEffect } from "react";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import ReactSelect from "react-select";
import {
  useStatesByCountry,
  useCitiesByState,
  useAreasByCity,
  useCreateShippingAddress,
  useUpdateShippingAddress,
} from "@/hooks/queries/useCheckout";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ShippingAddress } from "@/lib/api/checkout";
import Heading from "@/components/Heading";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { useAuthStore } from "@/store/useAuthStore";
import { Checkbox } from "@/components/ui/checkbox";
import BodyText from "@/components/BodyText";

const phoneSchema = z
  .string()
  .trim()
  // only digits and optional leading +
  .refine((val) => /^[+0-9]+$/.test(val), {
    message: "Phone number must be numeric",
  })
  .superRefine((val, ctx) => {
    if (val.startsWith("+88") && val.length !== 14) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "Phone number must be exactly 14 digits with +88",
      });
    } else if (val.startsWith("88") && val.length !== 13) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "Phone number must be exactly 13 digits with 88",
      });
    } else if (val.startsWith("01") && val.length !== 11) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "Phone number must be exactly 11 digits",
      });
    } else if (
      !val.startsWith("+88") &&
      !val.startsWith("88") &&
      !val.startsWith("01")
    ) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: "Phone number must start with +88, 88, or 01",
      });
    }
  })
  // normalize stored value to 01XXXXXXXXX
  .transform((val) => {
    if (val.startsWith("+88")) return val.slice(3);
    if (val.startsWith("88")) return val.slice(2);
    return val;
  });

const addressSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters"),
  address: z.string().min(10, "Address must be at least 10 characters"),
  phone: phoneSchema,
  postal_code: z.string().optional(),
  address_type: z.enum(["Home", "Office", "Others"]),
  state_id: z.number().min(1, "Please select a state"),
  city_id: z.number().min(1, "Please select a city"),
  area_id: z.number().min(1, "Please select an area"),
});

type AddressFormData = z.infer<typeof addressSchema>;

// Helper function to map data to react-select options
const mapOptions = (data: any[]) =>
  data.map((item) => ({
    value: item.id,
    label: item.name,
  }));

// Custom styles for react-select to match existing design
const customSelectStyles = {
  control: (provided: any, state: any) => ({
    ...provided,
    border: "1px solid #E6E8EC", // border-site-gray-100
    borderRadius: "8px", // rounded-sm
    boxShadow: "none",
    paddingBlock:"4px",
    paddingInline:"8px",
    height:"42px",
    fontSize: "14px", // text-sm
    backgroundColor: "#FFFFFF",
    "&:hover": {
      border: "1px solid #E6E8EC",
    },
    ...(state.isFocused && {
      border: "1px solid #E6E8EC",
      boxShadow: "none",
    }),
  }),
  valueContainer: (provided: any) => ({
    ...provided,
    padding: "0 6px",
  }),
  placeholder: (provided: any) => ({
    ...provided,
    color: "#9CA3AF",
  }),
  indicatorSeparator: () => ({
    display: "none",
  }),
  dropdownIndicator: (provided: any) => ({
    ...provided,
    color: "#6B7280",
    "&:hover": {
      color: "#6B7280",
    },
    textSize: "14px",
  }),
  menu: (provided: any) => ({
    ...provided,
    zIndex: 9999,
  }),
  option: (provided: any, state: any) => ({
    ...provided,
    borderBottom: "1px solid #f2f2f2",
    fontSize: "14px", // text-sm
    backgroundColor: state.isSelected
      ? "#F3F4F6"
      : state.isFocused
        ? "#fff"
        : "white",
    color: "#111827",
    "&:hover": {
      backgroundColor: "#f6f6f6",
    },
  }),
  menuList: (base: any) => ({
    ...base,
    padding: "2px",
    maxHeight: "240px",
    scrollbarWidth: "thin", // Firefox
    "&::-webkit-scrollbar": {
      width: "6px", // scrollbar thickness
    },
    "&::-webkit-scrollbar-thumb": {
      backgroundColor: "#c4c4c4",
      borderRadius: "4px",
    },
    "&::-webkit-scrollbar-thumb:hover": {
      backgroundColor: "#999",
    },
  }),
};

interface AddressFormProps {
  userId: number | string;
  guestId: string | null;
  existingAddress?: ShippingAddress & { id: number };
  onSuccess: () => void;
  onCancel: () => void;
  setAddAddressModal: (value: boolean) => void;
}

export default function AddressForm({
  userId,
  guestId,
  existingAddress,
  onSuccess,
  onCancel,
  setAddAddressModal,
}: AddressFormProps) {
  const [selectedState, setSelectedState] = useState<number | null>(
    existingAddress?.state_id || null,
  );
  const [selectedCity, setSelectedCity] = useState<number | null>(
    existingAddress?.city_id || null,
  );

  const {
    register,
    handleSubmit,
    control,
    setValue,
    watch,
    reset,
    formState: { errors },
  } = useForm<AddressFormData>({
    resolver: zodResolver(addressSchema),
    defaultValues: existingAddress
      ? {
          name: existingAddress?.name,
          address: existingAddress.address,
          phone: existingAddress.phone,
          postal_code: existingAddress.postal_code,
          address_type: existingAddress.address_type,
          state_id: existingAddress.state_id || 0,
          city_id: existingAddress.city_id || 0,
          area_id: existingAddress.area_id || 0,
        }
      : {
          name: "",
          address_type: "Home",
          state_id: 0,
          city_id: 0,
          area_id: 0,
        },
  });

  // Queries
  const { data: statesData } = useStatesByCountry(18); // Bangladesh country ID
  const { data: citiesData } = useCitiesByState(
    selectedState!,
    !!selectedState,
  );
  const { data: areasData } = useAreasByCity(selectedCity!, !!selectedCity);

  // Mutations
  const createMutation = useCreateShippingAddress();
  const updateMutation = useUpdateShippingAddress();

  // handle address and cities
  const states = statesData?.data || [];
  const cities = citiesData?.data || [];
  const areas = areasData?.data || [];

  // Reset form when existingAddress changes (for edit mode)
  useEffect(() => {
    if (existingAddress) {
      const addressType =
        existingAddress.address_type || existingAddress.type || "Home";

      reset({
        address: existingAddress.address,
        phone: existingAddress.phone,
        postal_code: existingAddress.postal_code || "",
        address_type: addressType as "Home" | "Office" | "Others",
        state_id: existingAddress.state_id || 0,
        city_id: existingAddress.city_id || 0,
        area_id: existingAddress.area_id || 0,
      });
      setSelectedState(existingAddress.state_id || null);
      setSelectedCity(existingAddress.city_id || null);
    }
  }, [existingAddress, reset]);

  // Initialize form values when editing and data is loaded
  useEffect(() => {
    if (existingAddress && cities.length > 0 && selectedState) {
      // Ensure city is set when cities data is loaded
      if (existingAddress.city_id && !selectedCity) {
        setSelectedCity(existingAddress.city_id);
        setValue("city_id", existingAddress.city_id);
      }
    }
  }, [cities, existingAddress, selectedState, selectedCity, setValue]);

  useEffect(() => {
    if (existingAddress && areas.length > 0 && selectedCity) {
      // Ensure area is set when areas data is loaded
      if (existingAddress.area_id) {
        setValue("area_id", existingAddress.area_id);
      }
    }
  }, [areas, existingAddress, selectedCity, setValue]);

  // submit address data
  const onSubmit = (data: AddressFormData) => {
    const addressData = {
      ...data,
      country_id: 18,
      type: data.address_type,
      user_id: userId || guestId || 0,
    };

    if (existingAddress) {
      updateMutation.mutate(
        { ...addressData, id: existingAddress.id },
        {
          onSuccess: () => {
            onSuccess();
            setAddAddressModal(false);
          },
          onError: (error) => {},
        },
      );
    } else {
      createMutation.mutate(addressData, {
        onSuccess: () => {
          onSuccess();
          setAddAddressModal(false);
        },
      });
    }
  };

  const { user } = useAuthStore();
  const isLoading = createMutation.isPending || updateMutation.isPending;
  const mapOptions = (items: any[]) =>
    items.map((item) => ({ value: item.id, label: item.name }));
  return (
    <Card className="border-none p-0 shadow-none bg-transparent">
      <CardHeader className="p-0">
        <Heading variant="h6" className="mb-3 font-bold">
          {existingAddress ? "Edit Address" : "Delivery Address"}
        </Heading>
      </CardHeader>
      <CardContent className="p-0">
        <form
          onSubmit={handleSubmit(onSubmit, (errors) => {})}
          className="max-h-[80vh] space-y-5 overflow-y-scroll md:overflow-y-visible px-2 py-3"
        >
          <div className="mb-6">
            {/* select address type */}
            <RadioGroup
              onValueChange={(value) =>
                setValue("address_type", value as "Home" | "Office" | "Others")
              }
              value={watch("address_type") || "Home"}
              className="flex gap-6"
            >
              {["Home", "Office", "Others"].map((type) => (
                <div
                  key={type}
                  className="flex cursor-pointer items-center space-x-2"
                >
                  <RadioGroupItem
                    value={type}
                    id={type.toLowerCase()}
                    className={`border-site-gray-100 data-[state=checked]:border-site-primary-500 data-[state=checked]:text-site-primary-500 [&>span>svg]:data-[state=checked]:fill-site-primary-500 [&>span>svg]:data-[state=checked]:text-site-primary-500 h-5 w-5 cursor-pointer  border-2 [&>span>svg]:h-3 [&>span>svg]:w-3 rounded-full`}
                  />
                  <Label htmlFor={type.toLowerCase()}>{type}</Label>
                </div>
              ))}
            </RadioGroup>
            {errors.address_type && (
              <p className="mt-1 text-sm text-red-500">
                Select an address type
              </p>
            )}
          </div>
          {/* Name & phone */}
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <Label
                className="text-site-gray-400 mb-1.5 block text-sm"
                htmlFor="name"
              >
                Name
              </Label>
              <input
                id="name"
                {...register("name")}
                defaultValue={user?.name}
                className="checkout-input "
                placeholder="Enter Your Name"
              />
            </div>

            <div>
              <Label
                className="text-site-gray-400 mb-1.5 block text-sm"
                htmlFor="phone"
              >
                Phone Number
              </Label>
              <input
                id="phone"
                {...register("phone")}
                className="checkout-input "
                placeholder="01780658***"
                defaultValue={user?.phone}
              />
              {errors.phone && (
                <p className="mt-1 text-sm text-red-500">
                  {errors.phone.message}
                </p>
              )}
            </div>
          </div>

          {/* address inbput */}
          <div>
            <Label
              className="text-site-gray-400 mb-1.5 block text-sm"
              htmlFor="address"
            >
              Address
            </Label>
            <input
              id="address"
              {...register("address")}
              className="checkout-input "
              placeholder="Street, house no, block, etc. "
            />
            {errors.address && (
              <p className="mt-1 text-sm text-red-500">
                {errors.address.message}
              </p>
            )}
          </div>

          {/* state and  */}
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <Label
                className="text-site-gray-400 mb-1.5 block text-sm"
                htmlFor="state"
              >
                State/Division
              </Label>
              <Select
                onValueChange={(value) => {
                  const stateId = parseInt(value);
                  setSelectedState(stateId);
                  setValue("state_id", stateId);
                  setSelectedCity(null);
                  setValue("city_id", 0);
                  setValue("area_id", 0);
                }}
                value={selectedState?.toString()}
              >
                <SelectTrigger className="px-4 py-1 focus-visible:outline-none focus:ring-1 focus:ring-site-secondary-500 border-site-gray-100 border-[0.5px] h-[42px] text-base  rounded-lg w-full">
                  <SelectValue placeholder="Select Division" />
                </SelectTrigger>
                <SelectContent>
                  {states.map((state: any) => (
                    <SelectItem key={state.id} value={state.id.toString()}>
                      {state.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {errors.state_id && (
                <p className="mt-1 text-sm text-red-500">
                  {errors.state_id.message}
                </p>
              )}
            </div>

            <div onWheel={(e) => e.stopPropagation()}>
              <Label
                className="text-site-gray-400 mb-1.5 block text-sm"
                htmlFor="city"
              >
                City
              </Label>
              <Controller
                control={control}
                name="city_id"
                render={({ field }) => (
                  <ReactSelect
                    {...field}
                    options={mapOptions(cities)}
                    value={
                      mapOptions(cities).find((o) => o.value === field.value) ||
                      null
                    }
                    onChange={(option) => {
                      const cityId = option?.value || 0;
                      field.onChange(cityId);
                      setSelectedCity(cityId);
                      setValue("area_id", 0);
                    }}
                    placeholder="Select city"
                    isDisabled={!selectedState}
                    isSearchable={true}
                    isClearable={true}
                    styles={customSelectStyles}
                  />
                )}
              />

              {errors.city_id && (
                <p className="mt-1 text-sm text-red-500">
                  {errors.city_id.message}
                </p>
              )}
            </div>
          </div>

          {/* get areas */}
          <div className="mb-5" onWheel={(e) => e.stopPropagation()}>
            <Label
              className="text-site-gray-400 mb-1.5 block text-sm"
              htmlFor="area"
            >
              Area
            </Label>
            <Controller
              control={control}
              name="area_id"
              render={({ field }) => (
                <ReactSelect
                  {...field}
                  options={mapOptions(areas)}
                  value={
                    mapOptions(areas).find((o) => o.value === field.value) ||
                    null
                  }
                  onChange={(option) => field.onChange(option?.value || 0)}
                  placeholder="Select area"
                  isDisabled={!selectedCity}
                  isSearchable={true}
                  isClearable={true}
                  styles={customSelectStyles}
                />
              )}
            />
            {errors.area_id && (
              <p className="mt-1 text-sm text-red-500">
                {errors.area_id.message}
              </p>
            )}
          </div>

          {/* default address */}
          {/* <div className="flex items-center space-x-2">
            <Checkbox id="savea_default_address" className="h-5 w-5 " />
            <Label htmlFor="savea_default_address">
              <BodyText className="text-[#243752]" variant="two">
                {" "}
                Save as Default Address
              </BodyText>
            </Label>
          </div> */}

          {/* submit button */}
          <div className="flex gap-3 pt-5">
            <button
              type="submit"
              disabled={isLoading}
              className="bg-site-secondary-600  font-bold hover:bg-site-secondary-500 block w-full cursor-pointer rounded-full px-10 py-1.5 text-center text-base  text-white transition-colors duration-300 ease-in-out md:py-3 mx-auto lg:max-w-[80%]"
            >
              {isLoading
                ? "Saving..."
                : existingAddress
                  ? "Update"
                  : "Save the Address"}
            </button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
