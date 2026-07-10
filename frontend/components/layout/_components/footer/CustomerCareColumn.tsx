"use client";

import { motion } from "framer-motion";
import Link from "next/link";

interface Props {
  labels: string[];
  links: string[];
}

const CustomerCareColumn = ({ labels, links }: Props) => {
  return (
    <motion.div
      className="space-y-4 lg:space-y-9"
      initial={{ opacity: 0, y: 50 }}
      whileInView={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 3 * 0.2 }}
      viewport={{ once: true }}
    >
      <h3 className="text-white text-2xl font-semibold">Customer Care</h3>
      <ul className="space-y-1 lg:space-y-3">
        {labels.map((label, index) => (
          <li key={index}>
            <Link href={links[index]} className="text-site-white-07 hover:underline" target="_blank">
              {label}
            </Link>
          </li>
        ))}
      </ul>
    </motion.div>
  );
};

export default CustomerCareColumn;
