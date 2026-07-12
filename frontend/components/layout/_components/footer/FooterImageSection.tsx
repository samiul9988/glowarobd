import { useScroll, useSpring, useTransform, useInView, useAnimation } from "framer-motion";
import Image from "next/image";
import { useRef, useEffect } from "react";
import { motion } from "framer-motion";


export const FooterImageSection = () => {
  const footerImgRef = useRef(null);
  const isInView = useInView(footerImgRef, { once: false, amount: 0.3 });
  const logoControls = useAnimation();
  const overlayControls = useAnimation();
useEffect(() => {
  if (isInView) {
    // Animate logo: slide up into view
    logoControls.start({
      opacity: 1,
      y: -40,
      transition: {
        type: "spring",
        stiffness: 80,
        damping: 15,
        mass: 0.8,
        delay: 0.2,
      },
    });

    // Animate overlay image
    overlayControls.start({
      y: 70,
      opacity: 0.95,
      transition: {
        type: "spring",
        stiffness: 80,
        damping: 15,
        mass: 0.8,
        delay: 0.3,
      },
    });

  } else {
    // Proper reverse reset
    logoControls.start({
      opacity: 0,
      y: 80, // same as initial position
      transition: {
        type: "spring",
        stiffness: 80,
        damping: 15,
        mass: 0.8,
      },
    });

    overlayControls.start({
      y: 0,        // reset to original position
      opacity: 1,  // fully visible
      transition: {
        type: "spring",
        stiffness: 80,
        damping: 15,
        mass: 0.8,
      },
    });
  }
}, [isInView, logoControls, overlayControls]);

  return (
    <div
      ref={footerImgRef}
      className="relative h-[600px] overflow-hidden"
      style={{
        backgroundImage: "url('/images/footer-first.png')",
        backgroundSize: "cover",
        backgroundPosition: "center",
      }}
    >
      <div className="absolute inset-0 bg-gradient-to-b from-transparent to-black/20">
        {/* Second overlay image */}
        <motion.div
          className="absolute inset-0 z-[20]"
          initial={{ scale: 1, opacity: 1 }}
          animate={overlayControls}
        >
          <Image
            src="/images/footer-second.png"
            alt="glowaro"
            height={600}
            width={1200}
            priority
            className="h-full w-full object-cover"
          />
        </motion.div>

        {/* Logo - emerges from between the images */}
        <motion.div
          className="z-[10] absolute top-1/2 left-1/2 -translate-x-1/2  -translate-y-1/2"
          initial={{ opacity: 0, y: "100%" }}
          animate={logoControls}
        >
          <Image
            src="/images/footer-logo.png"
            alt="glowaro"
            className="h-full w-full object-contain min-w-[350px] "
            height={200}
            width={800}
            priority
          />
        </motion.div>
      </div>
    </div>
  );
};