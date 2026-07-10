// "use client";

// import { useState } from "react";
// import { Controller, useForm } from "react-hook-form";
// import { zodResolver } from "@hookform/resolvers/zod";
// import * as z from "zod";
// import ReactSelect from "react-select";
// import {
//   useStatesByCountry,
//   useCitiesByState,
//   useAreasByCity,
//   useCreateShippingAddress,
//   useUpdateShippingAddress,
// } from "@/hooks/queries/useCheckout";
// import { Input } from "@/components/ui/input";
// import { Label } from "@/components/ui/label";
// import {
//   Select,
//   SelectContent,
//   SelectItem,
//   SelectTrigger,
//   SelectValue,
// } from "@/components/ui/select";
// import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
// import { ShippingAddress } from "@/lib/api/checkout";
// import Heading from "@/components/Heading";
// import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
// import { useAuthStore } from "@/store/useAuthStore";
// import { Checkbox } from "@/components/ui/checkbox";
// import BodyText from "@/components/BodyText";

// const addressSchema = z.object({
//   address: z.string().min(6, "Address must be at least 6 characters"),
//   phone: z.string().min(11, "Phone number must be at least 11 digits"),
//   postal_code: z.string().optional(),
//   address_type: z.enum(["Home", "Office", "Others"]),
//   state_id: z.number().min(1, "Please select a state"),
//   city_id: z.number().min(1, "Please select a city"),
//   area_id: z.number().min(1, "Please select an area"),
// });

// type AddressFormData = z.infer<typeof addressSchema>;

// // Helper function to map data to react-select options
// const mapOptions = (data: any[]) =>
//   data.map((item) => ({
//     value: item.id,
//     label: item.name,
//   }));

// // Custom styles for react-select to match existing design
// const customSelectStyles = {
//   control: (provided: any, state: any) => ({
//     ...provided,
//     border: "1px solid #E6E8EC", // border-site-gray-100
//     borderRadius: "8px", // rounded-sm
//     boxShadow: "none",
//     fontSize: "14px", // text-sm
//     "&:hover": {
//       border: "1px solid #E6E8EC",
//     },
//     ...(state.isFocused && {
//       border: "1px solid #E6E8EC",
//       boxShadow: "none",
//     }),
//   }),
//   valueContainer: (provided: any) => ({
//     ...provided,
//     padding: "0 12px",
//   }),
//   placeholder: (provided: any) => ({
//     ...provided,
//     color: "#9CA3AF",
//   }),
//   indicatorSeparator: () => ({
//     display: "none",
//   }),
//   dropdownIndicator: (provided: any) => ({
//     ...provided,
//     color: "#6B7280",
//     "&:hover": {
//       color: "#6B7280",
//     },
//     textSize: "14px",
//     padding: "13px 5px",
//   }),
//   menu: (provided: any) => ({
//     ...provided,
//     zIndex: 9999,
//   }),
//   option: (provided: any, state: any) => ({
//     ...provided,
//     padding: "6px 12px",
//     borderBottom: "1px solid #f2f2f2",
//     fontSize: "14px", // text-sm
//     backgroundColor: state.isSelected
//       ? "#F3F4F6"
//       : state.isFocused
//         ? "#fff"
//         : "white",
//     color: "#111827",
//     "&:hover": {
//       backgroundColor: "#ddd",
//     },
//   }),
//   menuList: (base: any) => ({
//     ...base,
//     padding: "2px",
//     maxHeight: "180px",
//     scrollbarWidth: "thin", // Firefox
//     "&::-webkit-scrollbar": {
//       width: "6px", // scrollbar thickness
//     },
//     "&::-webkit-scrollbar-thumb": {
//       backgroundColor: "#c4c4c4",
//       borderRadius: "4px",
//     },
//     "&::-webkit-scrollbar-thumb:hover": {
//       backgroundColor: "#999",
//     },
//   }),
// };

// interface AddressFormProps {
//   userId: number;
//   existingAddress?: ShippingAddress & { id: number };
//   onSuccess: () => void;
//   onCancel: () => void;
//   setAddAddressModal: (value: boolean) => void;
// }

// export default function AddressForm({
//   userId,
//   existingAddress,
//   onSuccess,
//   onCancel,
//   setAddAddressModal,
// }: AddressFormProps) {
//   const [selectedState, setSelectedState] = useState<number | null>(
//     existingAddress?.state_id || null
//   );
//   const [selectedCity, setSelectedCity] = useState<number | null>(
//     existingAddress?.city_id || null
//   );

//   const {
//     register,
//     handleSubmit,
//     control,
//     setValue,
//     watch,
//     formState: { errors },
//   } = useForm<AddressFormData>({
//     resolver: zodResolver(addressSchema),
//     defaultValues: existingAddress
//       ? {
//           address: existingAddress.address,
//           phone: existingAddress.phone,
//           postal_code: existingAddress.postal_code,
//           address_type: existingAddress.address_type,
//           state_id: existingAddress.state_id || 0,
//           city_id: existingAddress.city_id || 0,
//           area_id: existingAddress.area_id || 0,
//         }
//       : {
//           address_type: "Home",
//           state_id: 0,
//           city_id: 0,
//           area_id: 0,
//         },
//   });

//   // Queries
//   const { data: statesData } = useStatesByCountry(18); // Bangladesh country ID
//   const { data: citiesData } = useCitiesByState(
//     selectedState!,
//     !!selectedState
//   );
//   const { data: areasData } = useAreasByCity(selectedCity!, !!selectedCity);

//   // Mutations
//   const createMutation = useCreateShippingAddress();
//   const updateMutation = useUpdateShippingAddress();

//   // handle address and cities
//   const states = statesData?.data || [];
//   const cities = citiesData?.data || [];
//   const areas = areasData?.data || [];

//   // submit address data
//   const onSubmit = (data: AddressFormData) => {
//     const addressData = {
//       ...data,
//       user_id: userId,
//       country_id: 18,
//     };

//     if (existingAddress) {
//       updateMutation.mutate(
//         { ...addressData, id: existingAddress.id },
//         { onSuccess }
//       );
//     } else {
//       createMutation.mutate(addressData, { onSuccess });
//       setAddAddressModal(false);
//     }
//   };

//   const { user } = useAuthStore();
//   const isLoading = createMutation.isPending || updateMutation.isPending;
//   const mapOptions = (items: any[]) =>
//     items.map((item) => ({ value: item.id, label: item.name }));
//   return (
//     <Card className="border-none shadow-none p-0">
//       <CardHeader className="p-0">
//         <Heading variant="h5" className="mb-5 font-normal">
//           {existingAddress ? "Edit Address" : "Delivery Address"}
//         </Heading>
//       </CardHeader>
//       <CardContent className="p-0">
//         <form onSubmit={handleSubmit(onSubmit)} className="space-y-5 ">
//           <div className="mb-6 ">
//             {/* select address type */}
//             <RadioGroup
//               onValueChange={(value) =>
//                 setValue("address_type", value as "Home" | "Office" | "Others")
//               }
//               defaultValue={watch("address_type")}
//               className="flex  gap-6 "
//             >
//               <div className="flex items-center space-x-2 cursor-pointer ">
//                 <RadioGroupItem
//                   value="Home"
//                   id="home"
//                   className={`
//                     h-5 cursor-pointer w-5 rounded-full border-2 border-site-gray-100
//                     data-[state=checked]:border-site-primary-500
//                     data-[state=checked]:text-site-primary-500
//                     [&>span>svg]:data-[state=checked]:fill-site-primary-500
//                     [&>span>svg]:data-[state=checked]:text-site-primary-500
//                      [&>span>svg]:h-3 [&>span>svg]:w-3

//                   `}
//                 />

//                 <Label htmlFor="home">Home</Label>
//               </div>

//               <div className="flex items-center space-x-2 cursor-pointer">
//                 <RadioGroupItem
//                   value="Office"
//                   id="office"
//                   className={`
//                     h-5 cursor-pointer w-5 rounded-full border-2 border-gray-400
//                     data-[state=checked]:border-site-primary-500
//                     data-[state=checked]:text-site-primary-500
//                     [&>span>svg]:data-[state=checked]:fill-site-primary-500
//                     [&>span>svg]:data-[state=checked]:text-site-primary-500
//                      [&>span>svg]:h-3 [&>span>svg]:w-3

//                   `}
//                 />
//                 <Label htmlFor="office">Office</Label>
//               </div>

//               <div className="flex items-center space-x-2 cursor-pointer">
//                 <RadioGroupItem
//                   value="Others"
//                   id="others"
//                   className={`
//                     h-5 cursor-pointer w-5 rounded-full border-2 border-gray-400
//                     data-[state=checked]:border-site-primary-500
//                     data-[state=checked]:text-site-primary-500
//                     [&>span>svg]:data-[state=checked]:fill-site-primary-500
//                     [&>span>svg]:data-[state=checked]:text-site-primary-500
//                      [&>span>svg]:h-3 [&>span>svg]:w-3

//                   `}
//                 />
//                 <Label htmlFor="others">Others</Label>
//               </div>
//             </RadioGroup>
//             {errors.address_type && (
//               <p className="text-sm text-red-500 mt-1">
//                 {errors.address_type.message}
//               </p>
//             )}
//           </div>
//           {/* Name & phone */}
//           <div className="grid grid-cols-1 md:grid-cols-2 gap-6  ">
//             <div>
//               <Label
//                 className="mb-1.5 block text-site-gray-400 text-sm "
//                 htmlFor="name"
//               >
//                 Name
//               </Label>
//               <Input
//                 id="name"
//                 defaultValue={user?.name}
//                 className="border border-site-gray-100 rounded-sm  h-12"
//                 placeholder="Enter Your Name"
//               />
//             </div>

//             <div>
//               <Label
//                 className="mb-1.5 block text-site-gray-400 text-sm "
//                 htmlFor="phone"
//               >
//                 Phone Number
//               </Label>
//               <Input
//                 id="phone"
//                 {...register("phone")}
//                 className="border border-site-gray-100 rounded-sm  h-12"
//                 placeholder="8801245789658"
//                 defaultValue={user?.phone}
//               />
//               {errors.phone && (
//                 <p className="text-sm text-red-500 mt-1">
//                   {errors.phone.message}
//                 </p>
//               )}
//             </div>
//           </div>

//           {/* address inbput */}
//           <div>
//             <Label
//               className="mb-1.5 block text-site-gray-400 text-sm"
//               htmlFor="address"
//             >
//               Address
//             </Label>
//             <Input
//               id="address"
//               {...register("address")}
//               className="border border-site-gray-100 rounded-sm  h-12"
//               placeholder="5th Floor, 2/9 Ave. Mirpur DOHS, Dhaka "
//             />
//             {errors.address && (
//               <p className="text-sm text-red-500 mt-1">
//                 {errors.address.message}
//               </p>
//             )}
//           </div>

//           {/* state and  */}
//           <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
//             <div>
//               <Label
//                 className="mb-1.5 block text-site-gray-400 text-sm"
//                 htmlFor="state"
//               >
//                 State/Division
//               </Label>
//               <Select
//                 onValueChange={(value) => {
//                   const stateId = parseInt(value);
//                   setSelectedState(stateId);
//                   setValue("state_id", stateId);
//                   setSelectedCity(null);
//                   setValue("city_id", 0);
//                   setValue("area_id", 0);
//                 }}
//                 defaultValue={selectedState?.toString()}
//               >
//                 <SelectTrigger className="border border-site-gray-100 rounded-sm  h-12">
//                   <SelectValue placeholder="Select state" />
//                 </SelectTrigger>
//                 <SelectContent>
//                   {states.map((state: any) => (
//                     <SelectItem key={state.id} value={state.id.toString()}>
//                       {state.name}
//                     </SelectItem>
//                   ))}
//                 </SelectContent>
//               </Select>
//               {errors.state_id && (
//                 <p className="text-sm text-red-500 mt-1">
//                   {errors.state_id.message}
//                 </p>
//               )}
//             </div>

//             <div onWheel={(e) => e.stopPropagation()}>
//               <Label
//                 className="mb-1.5 block text-site-gray-400 text-sm"
//                 htmlFor="city"
//               >
//                 City
//               </Label>
//               <Controller
//                 control={control}
//                 name="city_id"
//                 render={({ field }) => (
//                   <ReactSelect
//                     {...field}
//                     options={mapOptions(cities)}
//                     value={
//                       mapOptions(cities).find((o) => o.value === field.value) ||
//                       null
//                     }
//                     onChange={(option) => {
//                       const cityId = option?.value || 0;
//                       field.onChange(cityId);
//                       setSelectedCity(cityId);
//                       setValue("area_id", 0);
//                     }}
//                     placeholder="Select city"
//                     isDisabled={!selectedState}
//                     isSearchable={true}
//                     isClearable={true}
//                     styles={customSelectStyles}
//                   />
//                 )}
//               />

//               {errors.city_id && (
//                 <p className="text-sm text-red-500 mt-1">
//                   {errors.city_id.message}
//                 </p>
//               )}
//             </div>
//           </div>

//           {/* get areas */}
//           <div className="mb-5" onWheel={(e) => e.stopPropagation()}>
//             <Label
//               className="mb-1.5 block text-site-gray-400 text-sm"
//               htmlFor="area"
//             >
//               Area
//             </Label>
//             <Controller
//               control={control}
//               name="area_id"
//               render={({ field }) => (
//                 <ReactSelect
//                   {...field}
//                   options={mapOptions(areas)}
//                   value={
//                     mapOptions(areas).find((o) => o.value === field.value) ||
//                     null
//                   }
//                   onChange={(option) => field.onChange(option?.value || 0)}
//                   placeholder="Select area"
//                   isDisabled={!selectedCity}
//                   isSearchable={true}
//                   isClearable={true}
//                   styles={customSelectStyles}
//                 />
//               )}
//             />
//             {errors.area_id && (
//               <p className="text-sm text-red-500 mt-1">
//                 {errors.area_id.message}
//               </p>
//             )}
//           </div>

//           {/* default address */}
//           <div className="flex items-center space-x-2">
//             <Checkbox id="savea_default_address" className="w-5 h-5" />
//             <Label htmlFor="savea_default_address">
//               <BodyText className="text-[#243752]" variant="two">
//                 {" "}
//                 Save as Default Address
//               </BodyText>
//             </Label>
//           </div>

//           {/* submit button */}
//           <div className="flex gap-3 pt-4">
//             <button
//               type="submit"
//               disabled={isLoading}
//               className="block cursor-pointer  w-full bg-site-gray-700 text-white py-2 md:py-4 px-10 text-center rounded-[10px] text-base font-semibold hover:bg-site-gray-900 transition-colors duration-300 ease-in-out"
//             >
//               {isLoading
//                 ? "Saving..."
//                 : existingAddress
//                   ? "Update"
//                   : "Save the Address"}
//             </button>
//           </div>
//         </form>
//       </CardContent>
//     </Card>
//   );
// }
