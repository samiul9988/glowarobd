import { Skeleton } from "@/components/ui/skeleton";

export default function AddressSkeleton() {
  return (
    <div className="bg-site-primary-50 p-4 lg:p-6 rounded-md space-y-4">
      <div className="flex items-center justify-between">
        <Skeleton className="h-5 w-24 rounded-md bg-site-gray-200" />
        <Skeleton className="h-4 w-20 rounded-md bg-site-gray-200" />
      </div>

      <div className="space-y-2 mt-4">
        <Skeleton className="h-4 w-32 rounded-md bg-site-gray-200" />
        <Skeleton className="h-3 w-48 rounded-md bg-site-gray-200" />
        <Skeleton className="h-3 w-64 rounded-md bg-site-gray-200" />
      </div>
    </div>
  );
}
