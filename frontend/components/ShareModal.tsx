"use client";
import { useEffect, useState } from "react";
import {
  FaFacebook,
  FaLinkedin,
  FaPinterest,
  FaReddit,
  FaTelegram,
  FaWhatsapp,
} from "react-icons/fa";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { X } from "lucide-react";

interface ShareModalProps {
  isOpen: boolean;
  onClose: () => void;
  url?: string;
  title?: string;
  description?: string;
  image?: string;
}

const ShareModal = ({
  isOpen,
  onClose,
  url = typeof window !== "undefined" ? window.location.href : "",
  title = "Check out this amazing product!",
  description = "I found this awesome product that I wanted to share with you.",
  image = "",
}: ShareModalProps) => {
  const [copied, setCopied] = useState(false);
  const [currentUrl, setCurrentUrl] = useState(url);

  useEffect(() => {
    if (typeof window !== "undefined") {
      setCurrentUrl(window.location.href);
    }
  }, []);

  const encodedUrl = encodeURIComponent(currentUrl);
  const encodedTitle = encodeURIComponent(title);
  const encodedDescription = encodeURIComponent(description);
  const encodedImage = encodeURIComponent(image);

  const shareOptions = [
    {
      name: "Facebook",
      icon: <FaFacebook  className="h-5 w-5"  />,
      url: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
      color: "bg-[#1877F2] hover:bg-[#166fe5]",
    },
    {
      name: "Twitter",
      icon: (
        <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
        </svg>
      ),
      url: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`,
      color: "bg-[#1DA1F2] hover:bg-[#1a91da]",
    },
    {
      name: "LinkedIn",
      icon: <FaLinkedin  className="h-5 w-5"  />,
      url: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
      color: "bg-[#0077B5] hover:bg-[#006396]",
    },
    {
      name: "WhatsApp",
      icon: <FaWhatsapp   className="h-5 w-5" />,
      url: `https://wa.me/?text=${encodedTitle}%20${encodedUrl}`,
      color: "bg-[#25D366] hover:bg-[#20ba5a]",
    },
    
   
  ];

  const handleShare = (url: string) => {
    window.open(url, "_blank", "noopener,noreferrer,width=600,height=400");
  };

  const copyToClipboard = async () => {
    try {
      await navigator.clipboard.writeText(currentUrl);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error("Failed to copy: ", err);
    }
  };

  const handleNativeShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: title,
          text: description,
          url: currentUrl,
        });
      } catch (err) {
        console.error("Error sharing:", err);
      }
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-md ">
        <DialogHeader>
          <DialogTitle className="text-center">Share this product</DialogTitle>
        </DialogHeader>
        {/* Content */}
        <div className="space-y-6 ">
          {/* Social Media Options */}
          <div className="flex flex-wrap items-center justify-center gap-2">
            {shareOptions.map((option) => (
              <Button
                key={option.name}
                onClick={() => handleShare(option.url)}
                className={`${option.color} flex h-auto cursor-pointer flex-col items-center gap-2 p-3 text-white transition-all duration-200 hover:scale-105 active:scale-95`}
                variant="default"
              >
                {option.icon}
                {/* <span className="text-xs font-medium">{option.name}</span> */}
              </Button>
            ))}
          </div>

          {/* Native Share (if supported) */}
          {/* {typeof navigator !== "undefined" && navigator.share && (
            <Button
              className="flex items-center gap-2"
              variant="default"
              onClick={handleNativeShare}
            >
              <svg
                className="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"
                />
              </svg>
              More sharing options
            </Button>
          )} */}

          {/* Copy Link */}
          <div className="max-w-full rounded-lg border border-gray-200 p-3">
            <div className="flex items-center justify-between gap-2">
              <div className="min-w-0 flex-1">
                <p className="mb-1 text-sm font-medium text-gray-900">
                  Copy link
                </p>
                <p className="text-sm text-gray-500">{currentUrl}</p>
              </div>
              <Button
                onClick={copyToClipboard}
                variant="outline"
                size="sm"
                className={
                  copied ? "bg-green-100 text-green-800 hover:bg-green-100" : ""
                }
              >
                {copied ? "Copied!" : "Copy"}
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default ShareModal;
