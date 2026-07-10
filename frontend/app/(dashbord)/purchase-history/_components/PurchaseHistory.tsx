"use client";

import React, { useEffect, useState } from "react";
import { useSession } from "@/store/useAuthStore";
import { useToken } from "@/store/useTokenStore";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/axios";
import PurchaseHistoryCard from "./PurchaseHistoryCard";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Search } from "lucide-react";
import PurchaseHistorySkeleton from "@/components/skeleton/PurchaseHistorySkeleton";

const DEBOUNCE_MS = 500;

const PurchaseHistory = () => {
  const { user } = useSession();
  const { accessToken } = useToken();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [searchDebounced, setSearchDebounced] = useState("");

  const [filter, setFilter] = useState("all");

  // debounce effect:
  useEffect(() => {
    const handler = setTimeout(() => {
      const s = search.trim();
      if (s.length === 0) {
        setSearchDebounced("");
      } else if (s.length >= 8) {
        setSearchDebounced(s);
      }
    }, DEBOUNCE_MS);

    return () => clearTimeout(handler);
  }, [search]);

  const { data: res, isLoading } = useQuery({
    queryKey: ["get_purchase_history", user?.id, page, searchDebounced, filter],
    queryFn: async () => {
      const params: Record<string, string | number> = { page };

      if (searchDebounced && searchDebounced.length >= 8) {
        params.search = searchDebounced;
      }

      if (filter !== "all") {
        if (["pending", "delivered", "cancelled"].includes(filter)) {
          params.delivery_status = filter;
        } else if (["paid", "unpaid"].includes(filter)) {
          params.payment_status = filter;
        }
      }

      const { data } = await api.get(`/purchase-history/${user?.id}`, {
        headers: { Authorization: `Bearer ${accessToken}` },
        params,
      });

      return data as PurchaseHistoryResponse;
    },
    enabled: !!user?.id && !!accessToken,
  });

  if (!res || isLoading) {
    return <PurchaseHistorySkeleton />;
  }

  const purchaseData = res?.data || [];

  return (
    <div className="w-full space-y-4">
      {/* Search + Filter */}
      <div className="bg-site-gray-50 flex items-start justify-between gap-5 rounded-[10px] px-2 py-4 transition-colors md:p-6 lg:gap-10">
        {/* Search Input */}
        <div className="relative w-full max-w-[300px] flex-1">
          <div className="relative w-full">
            <input
              type="text"
              placeholder="Search by Order Code"
              className="focus:border-site-primary w-full rounded-[6px] border bg-white px-4 py-1.5 pr-10 focus:outline-none"
              value={search}
              onChange={(e) => {
                setPage(1);
                setSearch(e.target.value);
              }}
            />
            <Search
              className="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400"
              size={18}
            />
          </div>
          {/* hint when user typed but less than 8 chars */}
          {search.trim().length > 0 && search.trim().length < 8 && (
            <p className="mt-1 text-xs text-red-500">
              Type at least 8 characters to search.
            </p>
          )}
        </div>

        {/* Filter Select */}
        <div className="flex items-center gap-1 md:gap-3">
          <span className="text-site-gray-700 text-sm">Filter By: </span>
          <Select
            value={filter}
            onValueChange={(val) => {
              setPage(1);
              setFilter(val);
            }}
          >
            <SelectTrigger className="!h-[38px] w-[100px] !bg-white">
              <SelectValue placeholder="Filter" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="delivered">Delivered</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="paid">Paid</SelectItem>
              <SelectItem value="unpaid">Unpaid</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      {/* Data */}
      {purchaseData.length ? (
        <div className="bg-site-gray-50 rounded-[10px] transition-colors">
          {purchaseData.map((item) => (
            <PurchaseHistoryCard key={item.id} item={item} />
          ))}
        </div>
      ) : (
        <p className="text-site-gray-500 py-32 text-center">
          {search.trim().length > 0 && search.trim().length < 8
            ? "Type at least 8 characters to search."
            : searchDebounced || filter !== "all"
              ? "No orders match with your search or filter."
              : "You have no purchase history yet."}
        </p>
      )}

      {/* Pagination */}
      {purchaseData.length > 6 && (
        <div className="mt-8 flex justify-center gap-2">
          <button
            disabled={!res?.links?.prev}
            onClick={() => setPage((prev) => prev - 1)}
            className="bg-site-gray-50 hover:bg-site-gray-100 text-site-gray-700 cursor-pointer rounded-full px-4 py-2 text-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50"
          >
            Prev
          </button>

          <span className="bg-site-gray-100 text-site-gray-700 rounded-full px-4 py-2 text-sm">
            Page {res?.meta?.current_page} of {res?.meta?.last_page}
          </span>

          <button
            disabled={!res?.links?.next}
            onClick={() => setPage((prev) => prev + 1)}
            className="bg-site-gray-50 hover:bg-site-gray-100 text-site-gray-700 cursor-pointer rounded-full px-4 py-2 text-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
};

export default PurchaseHistory;
