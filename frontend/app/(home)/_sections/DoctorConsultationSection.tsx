"use client";

import { useState } from "react";
import Container from "@/components/Container";
import { motion } from "framer-motion";
import Image from "next/image";
import { GoArrowRight } from "react-icons/go";
import { IoStar } from "react-icons/io5";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { ModalCloseIcon } from "@/components/icons/icon-library";

const DoctorConsultationSection = () => {
  const [open, setOpen] = useState(false);

  return (
    <section className="pt-0 lg:pt-[100px]">
      <Container>
        <div className="relative h-full w-full rounded-[14px] bg-[url('/images/sections-bg/consult-bg.png')] bg-cover px-6 pt-8 md:px-20 md:pt-[60px] lg:py-[60px]">
          <div className="mb-8 w-full space-y-5 max-md:text-center md:mb-12 md:space-y-10 lg:mb-0 lg:max-w-[500px]">
            <div className="space-y-2">
              <h2 className="text-site-gray-900 text-[40px] leading-11 md:text-[52px] md:leading-14">
                Doctor&apos;s <br className="hidden lg:block" /> Skincare
                Consultation
              </h2>
              <p className="text-site-gray-900/50 text-sm leading-[22px] font-normal md:text-base md:leading-6">
                Get expert advice from certified doctors to solve your skin
                problems. Book your appointment, discuss treatments.
              </p>
            </div>

            {/* Book Appointment Button */}
            <button
              onClick={() => setOpen(true)}
              className="bg-site-primary-600 hover:bg-site-primary-600/90 group inline-flex cursor-pointer items-center gap-2 rounded-[10px] px-6 py-2.5 text-sm font-semibold text-white transition-colors duration-300 ease-in-out md:gap-4 md:px-10 md:py-4 md:text-base"
            >
              Book Appointment
              <GoArrowRight
                strokeWidth={0.9}
                className="h-5 w-5 transition-all group-hover:translate-x-1"
              />
            </button>
          </div>

          <div className="right-[62px] bottom-0 overflow-y-clip lg:absolute">
            {/* Doctor image */}
            <motion.div
              initial={{ y: 120 }}
              whileInView={{ y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: false, amount: 0.5 }}
            >
              <Image
                src="/images/doctor.png"
                alt="Doctor"
                height={530}
                width={530}
                loading="lazy"
                className="max-lg:mx-auto"
              />
            </motion.div>

            {/* Experienced badge */}
            <motion.div
              className="absolute bottom-0 left-0 inline-block w-[107px] translate-x-[15px] -translate-y-[20px] space-y-2 rounded-[10px] border border-white/10 bg-white/30 p-2.5 backdrop-blur-sm md:w-[137px] md:translate-x-[80px] md:-translate-y-[60px] lg:-translate-x-[40px] lg:-translate-y-1/2"
              initial={{ x: -120, opacity: 0 }}
              whileInView={{ x: 0, opacity: 1 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: false, amount: 0 }}
            >
              <div className="flex flex-col items-center justify-center">
                <span className="text-site-gray-700 text-xs font-bold md:text-sm">
                  17+ Years
                </span>
                <span className="text-site-gray-400 text-xs font-normal md:text-sm">
                  Experienced
                </span>
              </div>
              <div className="flex items-center justify-center gap-0.5">
                {Array(5)
                  .fill(null)
                  .map((_, index) => (
                    <IoStar key={index} className="text-[#FF8800]" size={16} />
                  ))}
              </div>
            </motion.div>
          </div>
        </div>
      </Container>

      {/* Popup Dialog */}
      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent className="max-w-sm rounded-xl [&>button]:hidden">
          <DialogHeader>
            <DialogTitle className="animate-bounce text-center text-xl md:text-3xl">
              Coming Soon 🚧
            </DialogTitle>
            <DialogDescription className="text-center text-gray-600">
              Doctor appointment booking feature is under development. Stay
              tuned!
            </DialogDescription>
          </DialogHeader>

          <DialogFooter className="flex justify-center">
            <Button
              onClick={() => setOpen(false)}
              className="bg-site-primary hover:bg-site-primary/90 mt-4 cursor-pointer"
            >
              Close
            </Button>
          </DialogFooter>

          {/* Close button */}
          <div
            className="absolute top-5 right-5 inline-block cursor-pointer"
            onClick={() => setOpen(false)}
          >
            <ModalCloseIcon className="fill-site-gray-300 transition-colors hover:fill-red-400" />
          </div>
        </DialogContent>
      </Dialog>
    </section>
  );
};

export default DoctorConsultationSection;
