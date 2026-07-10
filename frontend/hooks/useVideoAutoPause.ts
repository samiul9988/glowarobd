"use client";

import { useEffect, useState } from "react";

/**
 * TikTok-style auto pause/play hook
 * - Scroll <50% → auto pause
 * - Only one video plays at a time
 * Returns a Map of video element to paused state
 */
export function useVideoAutoPause(selector: string = "video[data-autopause]") {
  const [pausedVideos, setPausedVideos] = useState<
    Map<HTMLVideoElement, boolean>
  >(new Map());

  useEffect(() => {
    const videos = Array.from(
      document.querySelectorAll<HTMLVideoElement>(selector),
    );
    if (!videos.length) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          const video = entry.target as HTMLVideoElement;

          if (entry.intersectionRatio < 0.5 && !video.paused) {
            video.pause();

            // Update state
            setPausedVideos((prev) => new Map(prev).set(video, true));
          }
        });
      },
      { threshold: [0, 0.5, 1] },
    );

    videos.forEach((video) => {
      observer.observe(video);
      // Initialize map
      setPausedVideos((prev) => new Map(prev).set(video, video.paused));
    });

    // Pause other videos when one plays
    const handlePlay = (e: Event) => {
      const playingVideo = e.target as HTMLVideoElement;
      videos.forEach((v) => {
        if (v !== playingVideo && !v.paused) {
          v.pause();
          setPausedVideos((prev) => new Map(prev).set(v, true));
        } else if (v === playingVideo) {
          setPausedVideos((prev) => new Map(prev).set(v, false));
        }
      });
    };

    videos.forEach((v) => v.addEventListener("play", handlePlay));

    // Listen for pause events as well
    const handlePause = (e: Event) => {
      const video = e.target as HTMLVideoElement;
      setPausedVideos((prev) => new Map(prev).set(video, true));
    };
    videos.forEach((v) => v.addEventListener("pause", handlePause));

    return () => {
      observer.disconnect();
      videos.forEach((v) => {
        v.removeEventListener("play", handlePlay);
        v.removeEventListener("pause", handlePause);
      });
    };
  }, [selector]);

  /**
   * Utility to check if a specific video is paused
   */
  const isPaused = (video: HTMLVideoElement | null) => {
    if (!video) return true;
    return pausedVideos.get(video) ?? true;
  };

  return { isPaused };
}
