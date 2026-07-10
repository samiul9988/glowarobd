"use client";

import { Dialog, DialogContent, DialogTitle } from "@/components/ui/dialog";
import { useAuthModalStore } from "@/store/useAuthModalStore";
import { useFormControl } from "@/store/useFormControl";
import { AnimatePresence, motion } from "framer-motion";
import Image from "next/image";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { ModalCloseIcon } from "../icons/icon-library";
import ForgotPasswordForm from "./_components/ForgotPasswordForm";
import LoginForm from "./_components/LoginForm";
import ResetPasswordForm from "./_components/ResetPasswordForm";
import SignupForm from "./_components/SignUpForm";
import VerifyOTPForm from "./_components/VerifyOTPForm";

const AuthModal = ({
  loginSideImage,
  signupSideImage,
}: {
  loginSideImage: string;
  signupSideImage: string;
}) => {
  const { isOpen, setOpen } = useAuthModalStore();
  const { isAuth, isLogin, isForgot, isVerifyOTP, isReset, resetForm } =
    useFormControl();

  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();

  const slideVariants = {
    initial: { x: 100, opacity: 0, position: "absolute", width: "100%" },
    animate: { x: 0, opacity: 1, position: "relative" },
    exit: { x: -100, opacity: 0, position: "absolute", width: "100%" },
  };

  const handleOpenChange = (open: boolean) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set("authModal", open ? "open" : "close");

    const newUrl = `${pathname}?${params.toString()}`;
    router.replace(newUrl, { scroll: false });
    setOpen(open);

    if (open === false) {
      resetForm();
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={handleOpenChange}>
      <DialogContent className="flex w-full !max-w-[96%] gap-0 rounded-[10px] bg-white p-0 lg:!max-w-[1320px] [&>button]:hidden">
        <DialogTitle hidden>Auth form</DialogTitle>

        {/* Left side banner */}
        <div className="flex-1 max-lg:hidden">
          <div className="h-full py-10 pl-10">
            <div className="relative h-full">
              {isLogin ? (
                <Image src={loginSideImage} alt="login" fill />
              ) : (
                <Image src={signupSideImage} alt="login" fill />
              )}
            </div>
          </div>
        </div>

        {/* Animated form container */}
        <motion.div
          layout
          className="relative flex-1 overflow-hidden"
          transition={{ duration: 0.4, ease: "easeInOut" }}
        >
          <AnimatePresence mode="sync">
            {isAuth && (
              <motion.div
                key={isLogin ? "login" : "signup"}
                variants={slideVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.4, ease: "easeInOut" }}
              >
                {isLogin ? <LoginForm /> : <SignupForm />}
              </motion.div>
            )}

            {isForgot && (
              <motion.div
                key="forgot"
                variants={slideVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.4, ease: "easeInOut" }}
              >
                <ForgotPasswordForm />
              </motion.div>
            )}

            {isVerifyOTP && (
              <motion.div
                key="verify"
                variants={slideVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.4, ease: "easeInOut" }}
              >
                <VerifyOTPForm />
              </motion.div>
            )}

            {isReset && (
              <motion.div
                key="reset"
                variants={slideVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.4, ease: "easeInOut" }}
              >
                <ResetPasswordForm />
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>

        {/* Close button */}
        <div
          className="absolute top-5 right-5 inline-block cursor-pointer"
          onClick={() => handleOpenChange(false)}
        >
          <ModalCloseIcon className="fill-site-gray-300 transition-colors hover:fill-red-400" />
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default AuthModal;
