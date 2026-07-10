"use client";

import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";

interface Props {
  appStore: string;
  playStore: string;
}

const MyAccountColumn = ({ appStore, playStore }: Props) => {
  return (
    <motion.div
      className="space-y-5 lg:space-y-10"
      initial={{ opacity: 0, y: 50 }}
      whileInView={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 4 * 0.2 }}
      viewport={{ once: true }}
    >
      <div className="space-y-4 lg:space-y-9">
        <h3 className="text-white text-2xl font-semibold">My Account</h3>
        <ul className="space-y-1 lg:space-y-3">
          <li>
            <Link href="/signin" className="text-site-white-07 hover:underline" target="_blank">
              Login/Signup
            </Link>
          </li>
          <li>
            <Link href="/my-order" className="text-site-white-07 hover:underline" target="_blank">
              My Order
            </Link>
          </li>
          <li>
            <Link href="/order-history" className="text-site-white-07 hover:underline" target="_blank">
              Order History
            </Link>
          </li>
          <li>
            <Link href="/wishlist" className="text-site-white-07 hover:underline" target="_blank">
              Wishlist
            </Link>
          </li>
          <li>
            <Link href="/cart" className="text-site-white-07 hover:underline" target="_blank">
              Cart
            </Link>
          </li>
          <li>
            <Link href="/track-order" className="text-site-white-07 hover:underline" target="_blank">
              Track Order
            </Link>
          </li>
        </ul>
      </div>
      <div className="space-y-3 lg:space-y-6">
        <h4 className="text-white text-xl font-medium">Download our app</h4>
        <div className="flex items-center gap-3">
          <Link href={playStore} target="_blank">
            <Image src="/images/play-store.png" alt="play-store" width={118} height={40} />
          </Link>
          <Link href={appStore} target="_blank">
            <Image src="/images/apple-store.png" alt="apple-store" width={118} height={40} />
          </Link>
        </div>
      </div>
    </motion.div>
  );
};

export default MyAccountColumn;
