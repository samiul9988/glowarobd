"use client";

import Heading from "@/components/Heading";
import { Plus, Trash2 } from "lucide-react";
import React, { useState } from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import { Controller, useForm } from "react-hook-form";
import { z } from "zod";
import ReactSelect from "react-select";

import { PencilIcon } from "@/components/icons/icon-library";
import ShippingAddressSkeleton from "@/components/skeleton/ShippingAddressSkeleton";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { api } from "@/lib/axios";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import toast from "react-hot-toast";

import * as ScrollArea from "@radix-ui/react-scroll-area";
import BodyText from "@/components/BodyText";
import { RiPhoneFill } from "react-icons/ri";
import { FaMapMarkerAlt } from "react-icons/fa";
import { Card, CardContent } from "@/components/ui/card";
import PrimaryButton from "@/components/buttons/PrimaryButton";
import SecondaryButton from "@/components/buttons/SecondaryButton";

// Custom styles for react-select to match existing design
const customSelectStyles = {
  control: (provided: any, state: any) => ({
    ...provided,
    border: "1px solid #E6E8EC",
    borderRadius: "8px",
    boxShadow: "none",
    paddingBlock: "4px",
    paddingInline: "8px",
    height: "42px",
    fontSize: "14px",
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
    fontSize: "14px",
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
    scrollbarWidth: "thin",
    "&::-webkit-scrollbar": {
      width: "6px",
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

// Zod schema for address form
const addressFormSchema = z.object({
  type: z.enum(["home", "office", "other"]),
  name: z.string().min(2, "Name must be at least 2 characters"),
  phone: z
    .string()
    .min(11, "Phone is Invalid")
    .max(20, "Phone is too long")
    .regex(
      /^[+0-9\s()-]+$/,
      "Phone must contain only digits and + - ( ) characters"
    ),
  address: z.string().min(5, "Please enter a full address"),
  state: z.string().min(1, "Please select a division"),
  city: z.string().min(1, "Please select a district"),
  area: z.string().min(1, "Please select an area"),
});

type AddressFormValues = z.infer<typeof addressFormSchema>;

// Helper function to map data to react-select options
const mapOptions = (data: any[]) =>
  data.map((item) => ({
    value: String(item.id),
    label: item.name,
  }));

const ShippingAddress: React.FC = () => {
  const [open, setOpen] = useState(false);
  const [divisionId, setDivisionId] = useState<string>("");
  const [districtId, setDistrictId] = useState<string>("");
  const [edit, setEdit] = useState(false);
  const [editId, setEditId] = useState<number | null>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { accessToken } = useToken();
  const { user } = useSession();
  const queryClient = useQueryClient();

  const form = useForm<AddressFormValues>({
    resolver: zodResolver(addressFormSchema),
    defaultValues: {
      type: "home",
      name: "",
      phone: "",
      address: "",
      state: "",
      city: "",
      area: "",
    },
  });

  // Get all Division
  const { data: divisions, isLoading: isDivisionsLoading } = useQuery({
    queryKey: ["get_divisions"],
    queryFn: async () => {
      const res = await api.get("/states-by-country/18");
      return res.data as DivisionResponse;
    },
  });

  // Get all city
  const { data: districts, isLoading: isDistrictsLoading } = useQuery({
    queryKey: ["get_cities", divisionId],
    queryFn: async () => {
      const res = await api.get(`/cities-by-state/${divisionId}`);
      return res.data as DistrictResponse;
    },
    enabled: !!divisionId,
  });

  // Get all area
  const { data: areas, isLoading: isAreasLoading } = useQuery({
    queryKey: ["get_areas", districtId],
    queryFn: async () => {
      const res = await api.get(`/areas-by-city/${districtId}`);
      return res.data as AreaResponse;
    },
    enabled: !!districtId,
  });

  interface ShippingAddressResponse {
    data: ShippingAddress[];
    status: number;
    success: boolean;
  }

  // Get all address
  const { data: shippingAddress, isLoading: isShippingAddressLoading } =
    useQuery({
      queryKey: ["get_address"],
      queryFn: async () => {
        const res = await api.get(`/user/shipping/address/${user?.id}`, {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        });
        return res.data as ShippingAddressResponse;
      },
    });

  // Add new address
  const { mutate: addNewAddress, isPending } = useMutation({
    mutationFn: async (data: AddressFormValues) => {
      const res = await api.post(
        "/user/shipping/create",
        {
          user_id: user?.id,
          address: data.address,
          country_id: 18,
          state_id: data.state,
          city_id: data.city,
          area_id: data.area,
          phone: data.phone,
          address_type: data.type,
          name: data.name,
        },
        {
          headers: { Authorization: `Bearer ${accessToken}` },
        }
      );
      return res.data;
    },
    onSuccess: (data) => {
      if (data.result) {
        toast.success(data.message);
        setOpen(false);
        form.reset();
        setEdit(false);
        setEditId(null);
      } else {
        toast.error(data.message);
      }
      queryClient.invalidateQueries({ queryKey: ["get_address"] });
    },
  });

  // Delete address
  const { mutate: deleteAddress } = useMutation({
    mutationFn: async (id: number) => {
      const res = await api.get(`/user/shipping/delete/${id}`, {
        headers: { Authorization: `Bearer ${accessToken}` },
      });
      return res.data;
    },
    onSuccess: (data) => {
      if (data.result) {
        toast.success(data.message);
      } else {
        toast.error(data.message);
      }
      queryClient.invalidateQueries({ queryKey: ["get_address"] });
    },
  });

  // Update shipping address
  const { mutate: updateShippingAddress, isPending: isUpdatePending } =
    useMutation({
      mutationFn: async (data: AddressFormValues & { id: number }) => {
        const res = await api.post(
          "/user/shipping/update",
          {
            user_id: user?.id,
            id: data.id,
            address: data.address,
            country_id: 18,
            state_id: data.state,
            city_id: data.city,
            area_id: data.area,
            phone: data.phone,
            address_type: data.type,
            name: data.name,
          },
          {
            headers: { Authorization: `Bearer ${accessToken}` },
          }
        );
        return res.data;
      },
      onSuccess: (data) => {
        if (data.result) {
          toast.success(data.message);
          setOpen(false);
          form.reset();
          setEdit(false);
          setEditId(null);
        } else {
          toast.error(data.message);
        }
        queryClient.invalidateQueries({ queryKey: ["get_address"] });
      },
    });

  // Handle edit
  const handleEditShippingAddress = (address: ShippingAddress) => {
    setEdit(true);
    setEditId(address.id);
    setOpen(true);

    form.setValue("type", address.type || "home");
    form.setValue("name", user?.name || "");
    form.setValue("phone", address.phone);
    form.setValue("address", address.address);
    form.setValue("state", String(address.state_id));
    form.setValue("city", String(address.city_id));
    form.setValue("area", String(address.area_id));

    setDivisionId(String(address.state_id));
    setDistrictId(String(address.city_id));
  };

  const onSubmit = (values: AddressFormValues) => {
    if (edit && editId) {
      updateShippingAddress({ ...values, id: editId });
    } else {
      addNewAddress(values);
    }
  };

  const handleDeleteShippingAddress = (id: number) => {
    deleteAddress(id);
    setOpen(false);
  };

  return (
    <div className="mt-16 ">
      <div className="flex items-center  md:items-center justify-between gap-3">
        <Heading variant="h5" className=""><span className="font-bold text-xl md:text-2xl">Shipping Address</span></Heading>
        {/* Add New Address */}
        <SecondaryButton
          onClick={() => setOpen(true)}
          className="max-w-[180px] text-base md:text-xl md:max-w-[220px]"
        >
          <Plus /> Add New Address
        </SecondaryButton>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
        {isShippingAddressLoading || !shippingAddress ? (
          <>
            {Array.from({ length: 4 }).map((_, index) => (
              <ShippingAddressSkeleton key={index} />
            ))}
          </>
        ) : shippingAddress?.data.length === 0 ? (
          <p className="text-center text-gray-500 col-span-full py-5 md:py-8">
            No shipping addresses found. Please add one.
          </p>
        ) : (
          shippingAddress.data.map((address) => (
            
            <Card  
              key={address.id}
              className="data-[state=checked]:border-site-primary-600 bg-site-gray-50 relative mb-2 rounded-[16px] shadow-none hover:bg-[#FBEDF5] data-[state=checked]:bg-[#FBEDF5] [state=checked]:border-[1.5px]"
            >
              <CardContent className="h-full p-4">
                <div className="flex h-full space-x-4">
                  <Label
                    htmlFor={`address-${address.id}`}
                    className="flex-1 cursor-pointer"
                  >
                    <div className="space-y-1">
                      <div className="flex items-center gap-1">
                        <BodyText
                          className="text-site-gray-800 mb-1 font-bold capitalize"
                          variant="one"
                        >
                          {address?.name}
                        </BodyText>
                      </div>
                      <BodyText
                        className="text-site-gray-700 flex items-center gap-1 font-normal data-[state=checked]:text-[#3080B5]"
                        variant="two"
                      >
                        <RiPhoneFill /> {address?.phone && address?.phone}
                      </BodyText>
                      <BodyText
                        variant="two"
                        className="text-site-gray-700 flex items-baseline gap-2 font-normal"
                      >
                        <FaMapMarkerAlt />
                        {address.area_name && `${address.area_name}, `}
                        {address.city_name && `${address.city_name}, `}
                        {address?.state_name}
                        {address.postal_code && `, ${address.postal_code}`}
                      </BodyText>
                    </div>
                  </Label>
                  <div className="flex flex-col items-end justify-between">
                    <div className="border-site-gray-200 bg-site-gray-100 text-site-gray-700 rounded-full border px-2 py-0.5 text-xs data-[state=checked]:border-[#FA045B] data-[state=checked]:bg-[#F8DCEB] data-[state=checked]:text-[#FA045B]">
                      {address.type || "Address"}
                    </div>
                    {/* Address actions */}
              <div className="flex items-center gap-3 mt-2">
                <button
                  onClick={() => handleEditShippingAddress(address)}
                  className="flex items-center text-sm gap-1 font-semibold text-[#007AFF] cursor-pointer"
                >
                  <PencilIcon /> Edit
                </button>
                <button
                  onClick={() => setDeleteId(address.id)}
                  className="flex items-center text-sm gap-1 font-semibold text-red-500 cursor-pointer"
                >
                  <Trash2 size={16} /> Delete
                </button>

                {/* Confirm popup */}
                <AlertDialog
                  open={deleteId === address.id}
                  onOpenChange={(open) => {
                    if (!open) setDeleteId(null);
                  }}
                >
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle hidden>Are you sure?</AlertDialogTitle>
                      <p>Are you sure?</p>
                      <AlertDialogDescription>
                        This action will delete your shipping address.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel className="cursor-pointer">
                        Close
                      </AlertDialogCancel>
                      <AlertDialogAction
                        onClick={() => handleDeleteShippingAddress(address.id)}
                        className="bg-site-primary hover:bg-site-primary/95 cursor-pointer"
                      >
                        Yes, Delete Address
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))
        )}
      </div>

      {/* Address Form Modal */}
      <Dialog
        open={open}
        onOpenChange={(val) => {
          setOpen(val);
          if (!val) {
            // Reset form and edit state when modal closes
            form.reset({
              type: "home",
              name: "",
              phone: "",
              address: "",
              state: "",
              city: "",
              area: "",
            });
            setEdit(false);
            setEditId(null);
            setDivisionId("");
            setDistrictId("");
          }
        }}
      >
        <DialogContent className="bg-site-gray-50 max-w-[95%]  md:max-w-[500px] md:p-4 w-full !max-h-[99vh] flex flex-col p-0">
          {/* Header */}
          <DialogHeader className="mb-0  pb-2 md:pb-5 border-b border-gray-200 flex-shrink-0">
            <DialogTitle hidden>
              {edit ? "Edit Address" : "Add New Address"}
            </DialogTitle>
            <Heading variant="h5" className="font-bold !text-xl !md:text-2xl">
              {edit ? "Edit Address" : "Add New Address"}
            </Heading>
          </DialogHeader>

          {/* Scrollable Form Body */}
          <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
            <ScrollArea.Viewport
              className="overflow-y-auto px-5 py-4 flex-1 space-y-6 h-[calc(100dvh-252px)] lg:h-auto"
              onWheel={(e) => e.stopPropagation()}
            >
              <form
                onSubmit={form.handleSubmit(onSubmit)}
                className="space-y-5"
              >
                {/* Address Type Radio */}
                <div className="mb-6">
                  <RadioGroup
                    onValueChange={(value) =>
                      form.setValue("type", value as "home" | "office" | "other")
                    }
                    value={form.watch("type") || "home"}
                    className="flex gap-6"
                  >
                    {["home", "office", "other"].map((type) => (
                      <div
                        key={type}
                        className="flex cursor-pointer items-center space-x-2"
                      >
                        <RadioGroupItem
                          value={type}
                          id={type.toLowerCase()}
                          className={`border-site-gray-100 data-[state=checked]:border-site-primary-500 data-[state=checked]:text-site-primary-500 [&>span>svg]:data-[state=checked]:fill-site-primary-500 [&>span>svg]:data-[state=checked]:text-site-primary-500 h-5 w-5 cursor-pointer border-2 [&>span>svg]:h-3 [&>span>svg]:w-3 rounded-full`}
                        />
                        <Label htmlFor={type.toLowerCase()} className="capitalize">
                          {type}
                        </Label>
                      </div>
                    ))}
                  </RadioGroup>
                  {form.formState.errors.type && (
                    <p className="mt-1 text-sm text-red-500">
                      Select an address type
                    </p>
                  )}
                </div>

                {/* Name & Phone */}
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
                      {...form.register("name")}
                      className="checkout-input"
                      placeholder="Enter Your Name"
                    />
                    {form.formState.errors.name && (
                      <p className="mt-1 text-sm text-red-500">
                        {form.formState.errors.name.message}
                      </p>
                    )}
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
                      {...form.register("phone")}
                      className="checkout-input"
                      placeholder="01780658***"
                    />
                    {form.formState.errors.phone && (
                      <p className="mt-1 text-sm text-red-500">
                        {form.formState.errors.phone.message}
                      </p>
                    )}
                  </div>
                </div>

                {/* Address input */}
                <div>
                  <Label
                    className="text-site-gray-400 mb-1.5 block text-sm"
                    htmlFor="address"
                  >
                    Address
                  </Label>
                  <input
                    id="address"
                    {...form.register("address")}
                    className="checkout-input"
                    placeholder="Street, house no, block, etc."
                  />
                  {form.formState.errors.address && (
                    <p className="mt-1 text-sm text-red-500">
                      {form.formState.errors.address.message}
                    </p>
                  )}
                </div>

                {/* State and City */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                  <div>
                    <Label
                      className="text-site-gray-400 mb-1.5 block text-sm"
                      htmlFor="state"
                    >
                      State/Division
                    </Label>
                    <Select
                      onValueChange={(val) => {
                        form.setValue("state", val);
                        setDivisionId(val);
                        form.setValue("city", "");
                        form.setValue("area", "");
                        setDistrictId("");
                      }}
                      value={form.watch("state")}
                    >
                      <SelectTrigger className="px-4 py-1 focus-visible:outline-none focus:ring-1 focus:ring-site-secondary-500 border-site-gray-100 border-[0.5px] h-[42px] text-base rounded-lg w-full">
                        <SelectValue placeholder="Select Division" />
                      </SelectTrigger>
                      <SelectContent>
                        {isDivisionsLoading && (
                          <p className="text-center text-sm">Loading...</p>
                        )}
                        {divisions?.data?.map((division) => (
                          <SelectItem
                            key={division.id}
                            value={String(division.id)}
                          >
                            {division.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {form.formState.errors.state && (
                      <p className="mt-1 text-sm text-red-500">
                        {form.formState.errors.state.message}
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
                      control={form.control}
                      name="city"
                      render={({ field }) => (
                        <ReactSelect
                          {...field}
                          options={mapOptions(districts?.data || [])}
                          value={
                            mapOptions(districts?.data || []).find(
                              (o) => o.value === field.value
                            ) || null
                          }
                          onChange={(option) => {
                            const cityId = option?.value || "";
                            field.onChange(cityId);
                            setDistrictId(cityId);
                            form.setValue("area", "");
                          }}
                          placeholder="Select city"
                          isDisabled={!divisionId}
                          isSearchable={true}
                          isClearable={true}
                          styles={customSelectStyles}
                        />
                      )}
                    />
                    {form.formState.errors.city && (
                      <p className="mt-1 text-sm text-red-500">
                        {form.formState.errors.city.message}
                      </p>
                    )}
                  </div>
                </div>

                {/* Area */}
                <div className="mb-5" onWheel={(e) => e.stopPropagation()}>
                  <Label
                    className="text-site-gray-400 mb-1.5 block text-sm"
                    htmlFor="area"
                  >
                    Area
                  </Label>
                  <Controller
                    control={form.control}
                    name="area"
                    render={({ field }) => (
                      <ReactSelect
                        {...field}
                        options={mapOptions(areas?.data || [])}
                        value={
                          mapOptions(areas?.data || []).find(
                            (o) => o.value === field.value
                          ) || null
                        }
                        onChange={(option) => field.onChange(option?.value || "")}
                        placeholder="Select area"
                        isDisabled={!districtId}
                        isSearchable={true}
                        isClearable={true}
                        styles={customSelectStyles}
                      />
                    )}
                  />
                  {form.formState.errors.area && (
                    <p className="mt-1 text-sm text-red-500">
                      {form.formState.errors.area.message}
                    </p>
                  )}
                </div>

                {/* Footer */}
                <DialogFooter className="flex-shrink-0 pt-5">
                  <button
                    type="submit"
                    disabled={isPending || isUpdatePending}
                    className="bg-site-secondary-600 font-bold hover:bg-site-secondary-500 block w-full cursor-pointer rounded-full px-10 py-1.5 text-center text-base text-white transition-colors duration-300 ease-in-out md:py-3 mx-auto lg:max-w-[80%]"
                  >
                    {edit
                      ? isUpdatePending
                        ? "Updating..."
                        : "Update"
                      : isPending
                        ? "Saving..."
                        : "Save the Address"}
                  </button>
                </DialogFooter>
              </form>
            </ScrollArea.Viewport>
            <ScrollArea.Scrollbar
              orientation="vertical"
              className="flex select-none touch-none p-0.5 bg-gray-200 rounded-full w-2"
            >
              <ScrollArea.Thumb className="flex-1 bg-gray-400 rounded-full" />
            </ScrollArea.Scrollbar>
          </ScrollArea.Root>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ShippingAddress;
