import React from "react";

const SupportTicketCard = () => {
  return (
    <div className="bg-site-gray-50 hover:bg-site-primary/15 transition-colors p-5 md:p-6 rounded-[10px] flex justify-between items-center gap-5 cursor-pointer">
      <div className="space-y-2">
        <div className="flex flex-col md:flex-row md:items-center gap-2">
          <span className="text-site-gray-700 text-sm font-bold">
            EMW20254DSA5
          </span>
          <span className="text-xs text-site-gray-400">28 Sep 2025</span>
        </div>
        <p className="text-sm text-site-gray-700 line-clamp-1">
          Face wash arrived damaged — need replacement
        </p>
      </div>
      <div className="space-x-2">
        <span className="badge badge-primary">Pending</span>
        <span className="badge badge-success">Completed</span>
      </div>
    </div>
  );
};

export default SupportTicketCard;
