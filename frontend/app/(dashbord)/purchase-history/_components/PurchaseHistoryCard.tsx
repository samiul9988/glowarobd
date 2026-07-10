import { Calendar, DollarSign, Package } from "lucide-react";
import Link from "next/link";

interface Props {
  item: PurchaseHistoryItem;
}

const PurchaseHistoryCard = ({ item }: Props) => {
  return (
    <Link
      href={"/purchase-history/" + item.id}
      className="border-site-gray-100 border-b-:last:border-b-0 flex items-center justify-between border-b px-2 py-3 md:px-6 md:py-5"
    >
      {/* Left side */}
      <div className="space-y-2">
        <div className="flex flex-col gap-2 md:flex-row md:items-center">
          <span className="text-site-gray-700 text-sm font-bold">
            {item.code}
          </span>
        </div>
        <div className="mt-2 flex w-full items-center gap-1 space-x-1 md:space-x-4 lg:gap-2">
          <span className="text-site-gray-600 flex flex-nowrap items-center gap-1 text-xs lg:text-sm">
            <Calendar size={16} /> {item.date}
          </span>
          {/* Delivery status */}
          {item.delivery_status === "pending" && (
            <span className="text-site-gray-600 flex flex-nowrap items-center gap-0.5 text-xs capitalize lg:text-sm">
              <Package size={16} /> {item.delivery_status}
            </span>
          )}
          {item.delivery_status === "delivered" && (
            <span className="text-site-gray-600 flex flex-nowrap items-center gap-0.5 text-xs capitalize lg:text-sm">
              <Package size={16} /> {item.delivery_status}
            </span>
          )}
          {item.delivery_status === "cancelled" && (
            <span className="text-site-gray-600 flex flex-nowrap items-center gap-0.5 text-xs capitalize lg:text-sm">
              <Package size={16} /> {item.delivery_status}
            </span>
          )}

          {/* Payment status */}
          <span className="text-site-gray-600 flex flex-nowrap items-center gap-0.5 text-xs capitalize lg:text-sm">
            <DollarSign size={16} /> {item.payment_status_string}
          </span>
        </div>
      </div>

      {/* Right side */}
      <div className="flex flex-col justify-end gap-1">
        <span className="text-site-gray-900 text-base font-bold">
          {item.grand_total}
        </span>
        {/* <p className="text-site-gray-400 text-right text-xs md:hidden">
          {item.date}
        </p> */}
        <span className="flex-end text-site-primary-600 mt-0.5 text-end text-sm font-normal underline md:mt-0">
          View Details
        </span>
      </div>
    </Link>
  );
};

export default PurchaseHistoryCard;
