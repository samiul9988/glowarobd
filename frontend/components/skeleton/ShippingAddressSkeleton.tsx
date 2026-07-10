import { Skeleton } from "@/components/ui/skeleton";

const ShippingAddressSkeleton: React.FC = () => {
  return (
    <div className="p-5 border border-site-gray-100 rounded-[10px] space-y-3">
      <Skeleton className="h-5 w-1/3" /> {/* Name */}
      <Skeleton className="h-4 w-2/3" /> {/* Phone/Email */}
      <Skeleton className="h-4 w-full" /> {/* Address */}
      <div className="flex gap-3 mt-2">
        <Skeleton className="h-8 w-20 rounded" /> {/* Edit button */}
        <Skeleton className="h-8 w-24 rounded" /> {/* Delete button */}
      </div>
    </div>
  );
};

export default ShippingAddressSkeleton;
