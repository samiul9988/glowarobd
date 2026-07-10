"use client";

import { Dialog, DialogContent, DialogTitle } from "@/components/ui/dialog";
import { useInfoModalStore } from "@/store/useInfoModalStore";
import { useRouter } from "next/navigation";
import { ModalCloseIcon } from "../icons/icon-library";
import { Check } from "lucide-react";

interface Props {
  open: boolean;
  onClose: () => void;
  title: string;
  messages: {
    title: string;
    video_url: string;
    offers: string[];
  };
}

const InfoModal = ({ messages, title }: Props) => {
  const { isOpen, setOpen } = useInfoModalStore();
  const router = useRouter();

  return (
    <Dialog open={isOpen} onOpenChange={(open) => setOpen(open)}>
      <DialogContent className="[&>button]:hidden !max-w-[96%] lg:!max-w-[500px] w-full bg-white p-0 flex gap-0 rounded-[10px] outline-none">
        <DialogTitle hidden>Info form</DialogTitle>
        <div className="p-6 md:p-10">
          <h2 className="text-2xl">{title}</h2>
          <p>{messages.title}</p>
          <ul className="space-y-1.5 mt-8">
            {messages.offers.map((offer, index) => (
              <li key={index} className="flex items-start gap-2">
                <Check className="w-4 h-4 text-green-500 flex-shrink-0 mt-1" />
                <span>{offer}</span>
              </li>
            ))}
          </ul>
        </div>

        <div
          className="absolute top-3 right-3 inline-block cursor-pointer"
          onClick={() => setOpen(false)}
        >
          <ModalCloseIcon className="fill-site-gray-300 hover:fill-red-400 transition-colors" />
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default InfoModal;
