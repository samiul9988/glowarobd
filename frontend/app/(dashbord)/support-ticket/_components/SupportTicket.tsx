import SupportServiceCard from "@/components/cards/SupportServiceCard";
import Heading from "@/components/Heading";
import { Plus } from "lucide-react";
import React from "react";
import SupportTicketCard from "./SupportTicketCard";

const SupportTicket = () => {
  return (
    <div className="w-full">
      <div className="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-3">
        <Heading variant="h5">Support Ticket</Heading>
        {/* Add New Ticket */}
        <button className="shrink-0 flex items-center gap-2 md:gap-4 py-3 px-4 md:px-10 bg-site-gray-50 hover:bg-site-gray-100 transition-colors text-site-gray-700 cursor-pointer rounded-[10px] text-sm">
          <Plus /> Create a Ticket
        </button>
      </div>

      {/* Support Ticket */}
      <div className="space-y-3">
        <SupportTicketCard />
        <SupportTicketCard />
        <SupportTicketCard />
        <SupportTicketCard />
      </div>
    </div>
  );
};

export default SupportTicket;
