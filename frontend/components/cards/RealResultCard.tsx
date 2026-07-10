"use client";

import { imageBaseHostUrl, imageBaseUrl } from "@/config/apiConfig";
import { useMediaQuery } from "@/hooks/useMediaQuery";
import { useVideoAutoPause } from "@/hooks/useVideoAutoPause";
import { cn } from "@/lib/utils";
import { useGlobalMute } from "@/store/useGlobalMute";
import { Play, Volume2, VolumeOff } from "lucide-react";
import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import ProductHorizontalSlider from "../sliders/ProductHorizontalSlider";
import { RiPlayFill, RiVideoFill } from "react-icons/ri";

interface Props {
  data: Video;
  isProductPage?: boolean;
  index: number;
}

const RealResultCard = ({ data, isProductPage, index }: Props) => {
  const videoRef = useRef<HTMLVideoElement | null>(null);
  const [isPlaying, setIsPlaying] = useState(false);
  // const [isMuted, setIsMuted] = useState(true);
  const [duration, setDuration] = useState(0);
  const [currentTime, setCurrentTime] = useState(0);
  const [progress, setProgress] = useState(0);
  const isMobile = useMediaQuery("(max-width: 768px)");
  const { isMuted, setIsMuted } = useGlobalMute();

  // Determine if file is a video
  const isVideo = data?.video_url?.toLowerCase().endsWith(".mp4");
  const { isPaused } = useVideoAutoPause();

  const hoverTimerRef = useRef<NodeJS.Timeout | null>(null);

  // Format seconds -> mm:ss
  const formatTime = (secs: number) => {
    if (!secs || isNaN(secs)) return "0:00";
    const minutes = Math.floor(secs / 60);
    const seconds = Math.floor(secs % 60)
      .toString()
      .padStart(2, "0");
    return `${minutes}:${seconds}`;
  };

  const handleClick = async () => {
    if (isVideo && videoRef.current) {
      const video = videoRef.current;

      if (video.paused) {
        try {
          await video.play();
          setIsPlaying(true);
        } catch (err) {
          // Force mute if autoplay blocked
          video.muted = true;
          setIsMuted();
          await video.play();
          setIsPlaying(true);
        }
      } else {
        video.pause();
        video.load();
        setIsPlaying(false);
      }
    }
  };

  // Delay play helper
  const safePlay = async () => {
    if (!videoRef.current) return;
    try {
      await videoRef.current.play();
      setIsPlaying(true);
    } catch (err) {
      // Force mute if autoplay blocked
      videoRef.current.muted = true;
      setIsMuted();
      try {
        await videoRef.current.play();
        setIsPlaying(true);
      } catch (err2) {
        console.warn("Autoplay blocked:", err2);
      }
    }
  };

  // Mouse hover start (start timer)
  const handleMouseEnter = () => {
    if (!isVideo || !videoRef.current) return;

    hoverTimerRef.current = setTimeout(() => {
      safePlay();
    }, 200); // 200ms second delay
  };

  // Mouse hover leave (cancel timer)
  const handleMouseLeave = () => {
    if (!isVideo || !videoRef.current) return;

    // Cancel hover delay
    if (hoverTimerRef.current) {
      clearTimeout(hoverTimerRef.current);
      hoverTimerRef.current = null;
    }

    // Stop video
    videoRef.current.pause();
    setIsPlaying(false);
    videoRef.current.load();
    videoRef.current.currentTime = 0;
    setProgress(0);
    setCurrentTime(0);
  };

  const handleTimeUpdate = () => {
    if (isVideo && videoRef.current) {
      const percent =
        (videoRef.current.currentTime / videoRef.current.duration) * 100;
      setProgress(percent);
      setCurrentTime(videoRef.current.currentTime);
    }
  };

  const toggleMute = () => {
    if (isVideo && videoRef.current) {
      videoRef.current.muted = !isMuted;
      setIsMuted();
    }
  };

  // Sync hook paused state with component
  useEffect(() => {
    const el = videoRef.current;
    if (el && isPaused(el)) {
      setIsPlaying(false);
      el.load();
    }
  }, [isPaused]);

  return (
    <div className="h-auto w-full space-y-4 overflow-hidden">
      <div
        className="group item-center relative flex w-full justify-center overflow-hidden rounded-[10px] border"
        onMouseEnter={isMobile ? undefined : handleMouseEnter}
        onMouseLeave={isMobile ? undefined : handleMouseLeave}
      >
        {/* Conditional Render */}
        {isVideo ? (
          <video
            data-autopause
            ref={videoRef}
            className="ratio aspect-[9/16] w-full object-cover"
            muted={isMuted}
            loop
            poster={`${imageBaseUrl}${data.thumbnail}`}
            playsInline
            onLoadedMetadata={(e) => setDuration(e.currentTarget.duration)}
            onTimeUpdate={handleTimeUpdate}
            onClick={isMobile ? handleClick : undefined}
          >
            <source src={`${imageBaseUrl}${data.video_url}`} type="video/mp4" />
          </video>
        ) : (
          <Image
            src={`${imageBaseHostUrl}${data.video_url}`}
            alt={data.title || "Thumbnail"}
            width={500}
            height={400}
            className="h-full max-h-[540px] w-full object-cover"
          />
        )}

        {/* Overlay Play Button for video */}
        {isVideo && !isPlaying && (
          <div
            className="absolute inset-0 flex items-center justify-center"
            onClick={isMobile ? handleClick : undefined}
          >
            <div className="bg-site-gray-900 flex h-[52px] w-[52px] cursor-pointer items-center justify-center rounded-full opacity-50 backdrop-blur-md">
              <Play className="fill-white text-white" />
            </div>
          </div>
        )}

        {/* Custom Progress + Time */}
        {/* {isVideo && duration > 0 && (
          <div
            className={cn(
              "pointer-events-none absolute right-2 bottom-1 left-2 flex items-center gap-2 text-xs text-white duration-300 md:bottom-2",
              isPlaying ? "opacity-100" : "opacity-0",
            )}
          >
            <div className="relative h-1 w-full overflow-hidden rounded bg-white/50">
              <div
                className="bg-site-primary-500 absolute top-0 left-0 h-1 rounded"
                style={{ width: `${progress}%` }}
              />
            </div>

            <span className="text-[11px] whitespace-nowrap">
              {formatTime(Math.max(duration - currentTime, 0))}
            </span>
          </div>
        )} */}

        {isVideo && duration > 0 && (
            <div className="absolute bottom-2 right-2 bg-white/90 px-3  py-1 rounded-full">
                 <span className="flex items-center gap-1 text-base font-medium whitespace-nowrap">
                    {isPlaying ? <RiVideoFill size={16}/> : <RiPlayFill size={16}/>}
                    {formatTime(Math.max(duration - currentTime, 0))}
                </span>
            </div>
        )}

        {/* Bottom-right buttons (only for video) */}
        {isVideo && (
          <div
            className={cn(
              "absolute left-2 bottom-2 flex flex-col gap-2 duration-300 ",
              isPlaying ? "" : "",
            )}
          >
            <button
              onClick={(e) => {
                e.stopPropagation();
                toggleMute();
              }}
              className="grid h-9 w-9 cursor-pointer place-content-center rounded-full bg-white/85 shadow-md transition hover:bg-gray-100"
            >
              {isMuted ? (
                <VolumeOff className="text-site-gray-900 h-4 w-4" />
              ) : (
                <Volume2 className="text-site-gray-900 h-4 w-4" />
              )}
            </button>

            {/* <button className="grid h-9 w-9 cursor-pointer place-content-center rounded-full bg-white/85 shadow-md transition hover:bg-gray-100">
              <Heart className="text-site-gray-900 h-4 w-4" />
            </button> */}
          </div>
        )}
      </div>
      <div>
        {data.title &&
            <h3 className="text-site-gray-800 text-base md:text-lg font-medium">{data.title}</h3>
        }
      </div>

      {/* Bottom Product Card */}
      {/* {data?.products && !isProductPage && (
        <ProductHorizontalSlider data={data} isProductPage={isProductPage} />
      )} */}
    </div>
  );
};

export default RealResultCard;
