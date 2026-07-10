"use client";

import React from "react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: (failureCount, error: any) => {
        if (error?.response?.status && error.response.status < 500)
          return false;
        return failureCount < 2;
      },
      retryDelay: (attempt) => attempt * 1000, // 1s → 2s
      refetchOnWindowFocus: false,
      refetchOnReconnect: false,
      staleTime: 1000 * 60, // optional: 1 minute
      gcTime: 1000 * 60 * 60, // optional: garbage collect after 60 min
    },
  },
});

interface Props {
  children: React.ReactNode;
}

const ReactQueryProvider = ({ children }: Props) => {
  return (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  );
};

export default ReactQueryProvider;
