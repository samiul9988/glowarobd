"use client";

import Heading from "@/components/Heading";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useSession } from "@/store/useAuthStore";
import { useQueryClient } from "@tanstack/react-query";
import { useState } from "react";

export default function AuthConfirmation() {
  const [authConfirm, setAuthConfirm] = useState(true);
  const { user } = useSession();
  const { isOpen, setOpen } = useAuthModalStore();
  const userId = user?.id;

  if (userId) return null;

  const handleConfirm = () => {
    setAuthConfirm(false);
    setOpen(true);
  };
  return (
    <>
      <AlertDialog open={authConfirm} onOpenChange={setAuthConfirm}>
        {/* The Trigger is hidden, assuming it opens automatically or from an external click */}
        <AlertDialogTrigger asChild>
          <button className="cursor-pointer p-2 text-red-500 hover:text-red-700"></button>
        </AlertDialogTrigger>

        {/* Popup Content */}
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle className="mb-0 text-center text-base font-semibold tracking-wider">
              ⭐ Unlock Your Member Benefits!
            </AlertDialogTitle>
            <div className="mt-2 flex flex-col items-center justify-between">
              <Heading
                className="pb-1 text-center text-lg font-semibold tracking-wide"
                variant="h5"
              >
                Do you want to checkout as a guest?
              </Heading>
              {/* 3. Visible, Benefit-Focused Description */}
              <AlertDialogDescription className="text-center font-medium text-gray-700">
                Log In for faster checkout, and easy order tracking!
              </AlertDialogDescription>
            </div>
          </AlertDialogHeader>
          <div className="mt-4 flex items-center justify-center">
            <AlertDialogFooter className="flex w-full flex-col space-y-3 sm:flex-row-reverse sm:space-y-0 sm:space-x-4 sm:space-x-reverse">
              <div className="flex w-full items-center justify-center gap-2">
                <AlertDialogAction
                  className="w-[150px] cursor-pointer ring-0 outline-0 focus:ring-0 focus-visible:ring-0"
                  onClick={handleConfirm}
                >
                  Log In
                </AlertDialogAction>

                {/* Secondary Action: Guest Checkout button */}
                <AlertDialogCancel className="w-[150px] cursor-pointer ring-0 outline-0 hover:bg-gray-100 focus:ring-0 focus-visible:ring-0">
                  Continue as Guest
                </AlertDialogCancel>
              </div>
            </AlertDialogFooter>
          </div>
        </AlertDialogContent>
      </AlertDialog>
    </>
  );
}
