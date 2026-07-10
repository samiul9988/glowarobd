"use client";

import Container from "@/components/Container";
import { motion } from "framer-motion";
import Image from "next/image";

interface Props {
  copyRightText: string;
  paymentMethodImg: string;
}

const CopyRightSection = ({ copyRightText, paymentMethodImg }: Props) => {
  return (
    <Container className="py-5 flex flex-col lg:flex-row items-center justify-between gap-3 overflow-clip">
      <motion.p
        className="text-site-white-06 text-sm font-normal text-center md:text-left"
        initial={{ opacity: 0, y: 10 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, delay: 1 * 0.2 }}
        viewport={{ once: true, amount: 0 }}
      >
        {copyRightText}
      </motion.p>
      <motion.div
        className="flex items-center gap-4"
        initial={{ opacity: 0, y: 10 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, delay: 2 * 0.2 }}
        viewport={{ once: true, amount: 0 }}
      >
        <span className="text-site-white-06 text-sm font-normal shrink-0">pay with:</span>
        <div className="flex-1 shrink-0">
          <Image
            src={paymentMethodImg}
            alt="pay-with"
            width={0}
            height={0}
            sizes="100vw"
            className="w-full h-[20px] md:h-[30px] object-contain"
          />
        </div>
      </motion.div>
    </Container>
  );
};

export default CopyRightSection;
