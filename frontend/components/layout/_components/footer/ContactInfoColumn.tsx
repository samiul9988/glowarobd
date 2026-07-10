"use client";

import { motion } from "framer-motion";
import Link from "next/link";

interface Props {
  contactAddress: string;
  contactPhone: string;
  contactEmail: string;
}

const ContactInfoColumn = ({ contactAddress, contactPhone, contactEmail }: Props) => {
  return (
    <motion.div
      className="space-y-4 lg:space-y-9"
      initial={{ opacity: 0, y: 50 }}
      whileInView={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 2 * 0.2 }}
      viewport={{ once: true }}
    >
      <h3 className="text-white text-2xl font-semibold">Contact info</h3>
      <div className="space-y-4">
        {/* Address */}
        <div className="space-y-1">
          <p className="text-site-primary-500 text-sm">Address</p>
          <p className="text-white text-base">{contactAddress}</p>
        </div>

        {/* Phone */}
        <div className="space-y-1">
          <p className="text-site-primary-500 text-sm">Phone</p>
          <div>
            <Link href={`tel:${contactPhone}`} className="block hover:underline text-white text-base" target="_blank">
              {contactPhone}
            </Link>
          </div>
        </div>

        {/* Email */}
        <div className="space-y-1">
          <p className="text-site-primary-500 text-sm">Email</p>
          <Link href={`mailto:${contactEmail}`} className="text-white text-base hover:underline" target="_blank">
            {contactEmail}
          </Link>
        </div>
      </div>
    </motion.div>
  );
};

export default ContactInfoColumn;
