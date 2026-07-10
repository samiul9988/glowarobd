"use client";

import { useState } from "react";
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
import { Trash2 } from "lucide-react";
import Heading from "@/components/Heading";

type DeleteAddressProps = {
  addressId: number;
  deleteAddressMutation: { mutate: (id: number) => void };
};

export function DeleteAddressButton({
  addressId,
  deleteAddressMutation,
}: DeleteAddressProps) {
  const [open, setOpen] = useState(false);

  const handleConfirm = () => {
    deleteAddressMutation.mutate(addressId);
    setOpen(false);
  };

  return (
        <AlertDialog open={open} onOpenChange={setOpen} >
        {/* Trigger Button */}
        <AlertDialogTrigger asChild>
            <button className=" hover:text-red-700 cursor-pointer ">
            <Trash2 className="h-4 w-4" />
            </button>
        </AlertDialogTrigger>

        {/* Popup Content */}
        <AlertDialogContent className="!max-w-[400px]">
            <AlertDialogHeader className="!justify-center !items-center">
                <AlertDialogTitle className="hidden"></AlertDialogTitle>
                <Heading className="" variant="h6">
                    Delete Address
                </Heading>
                <AlertDialogDescription>
                    Are you sure you want to delete this address?
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter className="flex w-full items-center !justify-center">
            <AlertDialogCancel className="cursor-pointer rounded-full">
                Cancel
            </AlertDialogCancel>
            <AlertDialogAction
                className="bg-red-500 cursor-pointer rounded-full"
                onClick={handleConfirm}
            >
                Delete Address
            </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
        </AlertDialog>
  );
}
