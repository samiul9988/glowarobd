import { PencilIcon } from "@/components/icons/icon-library";
import { useSession } from "@/store/useAuthStore";

interface Props {
  address: {
    id: number;
    user_id: number;
    address: string;
    country_id: number;
    state_id: number; // division id
    city_id: number; // district id
    area_id: number;
    country_name: string;
    state_name: string;
    city_name: string;
    area_name: string;
    postal_code: string | null;
    phone: string;
    set_default: number; // 0 or 1
    location_available: boolean;
    lat: number;
    lang: number;
    type: "Home" | "Office" | "Other";
  };
}

const AddressCard = ({ address }: Props) => {
  const { user } = useSession();

  return (
    <div className="p-5 border border-site-gray-100 rounded-[10px]">
      <div className="flex items-center gap-1 mb-1">
        <span className="text-site-gray-900 font-semibold text-base leading-6">
          {user?.name}
        </span>
        <span className="text-site-gray-400 text-sm">({address.type})</span>
      </div>

      <div className="text-site-gray-400 text-sm space-y-0.5">
        <span className="block">
          {address.phone} | {user?.email}
        </span>
        <span className="block">
          {address.address}, {address.city_name}, {address.state_name},{" "}
          {address.country_name}
        </span>
      </div>

      {/* <AddressForm  /> */}

      <button className="flex items-center text-sm gap-1 font-semibold text-[#007AFF] mt-1 cursor-pointer">
        <PencilIcon /> Edit
      </button>
    </div>
  );
};

export default AddressCard;
