import { AnimatePresence, motion } from "framer-motion";
import { useEffect } from "react";
import SearchBar from "./SearchBar";
interface Props {
  showMobileSearchBar: boolean;
  setShowMobileSearchBar: (value: boolean) => void;
}

export default function MobileSearch({
  showMobileSearchBar,
  setShowMobileSearchBar,
}: Props) {
  // handle scroll
  useEffect(() => {
    const scrollY = window.scrollY;

    if (showMobileSearchBar) {
      //Stop Lenis globally (if exists)
      (window as any).lenis?.stop?.();

      // 🔒 Lock scroll
      document.body.style.position = "fixed";
      document.body.style.top = `-${scrollY}px`;
      document.body.style.width = "100%";
    } else {
      // 🔓 Unlock scroll
      const y = document.body.style.top;
      document.body.style.position = "";
      document.body.style.top = "";
      document.body.style.width = "";
      window.scrollTo(0, parseInt(y || "0") * -1);

      // Resume Lenis
      (window as any).lenis?.start?.();
    }

    return () => {
      document.body.style.position = "";
      document.body.style.top = "";
      document.body.style.width = "";
      (window as any).lenis?.start?.();
    };
  }, [showMobileSearchBar]);
  return (
    <>
      <AnimatePresence>
        {showMobileSearchBar && (
          <motion.div
            key="mobileSearch"
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "100vh", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{
              duration: 0.5,
              ease: [0.25, 0.1, 0.25, 1], // smooth ease
            }}
            className="fixed top-0 left-0 z-50 w-full overflow-hidden bg-gradient-to-b from-[#F3FAFF] to-[#FFFFFF] lg:hidden"
          >
            <SearchBar
              setShowMobileSearchBar={setShowMobileSearchBar}
              showMobileSearchBar={showMobileSearchBar}
            />
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
