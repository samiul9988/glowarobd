"use client";

import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { FaFacebookF, FaLinkedinIn, FaTwitter, FaYoutube } from "react-icons/fa";
import { TiSocialInstagram } from "react-icons/ti";

interface Props {
  aboutUsText: string;
  footerLogo: string;
  socialLinks: {
    facebook: string;
    instagram: string;
    linkedin: string;
    twitter: string;
    youtube: string;
  };
  showSocialLinks: boolean;
}

const AboutColumn = ({ aboutUsText, footerLogo, showSocialLinks, socialLinks }: Props) => {
  const socials = [
    {
      name: "facebook",
      icon: <FaFacebookF />,
      href: socialLinks.facebook,
    },
    {
      name: "twitter",
      icon: <FaTwitter />,
      href: socialLinks.twitter,
    },
    {
      name: "linkedin",
      icon: <FaLinkedinIn />,
      href: socialLinks.linkedin,
    },
    {
      name: "instagram",
      icon: <TiSocialInstagram size={20} />,
      href: socialLinks.instagram,
    },
    {
      name: "youtube",
      icon: <FaYoutube />,
      href: socialLinks.youtube,
    },
  ];

  return (
    <motion.div
      className="space-y-7 lg:space-y-14"
      initial={{ opacity: 0, y: 50 }}
      whileInView={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 1 * 0.2 }}
      viewport={{ once: true }}
    >
      <div className="space-y-4 lg:space-y-10">
        <Image src={footerLogo} alt="Mababur Shop Logo" width={243} height={47} loading="lazy" />

        <div className="text-site-white-07" dangerouslySetInnerHTML={{ __html: aboutUsText }}></div>
      </div>

      {showSocialLinks && (
        <div className="space-y-2 lg:space-y-4">
          <h4 className="text-lg font-semibold text-white">Share your love</h4>

          <ul className="flex items-center gap-1.5 lg:gap-3 flex-wrap">
            {socials.map(social =>
              social.href ? (
                <li key={social.name}>
                  <Link
                    href={social.href}
                    className="h-8 w-8 bg-site-white-06 inline-flex items-center justify-center rounded-full text-site-secondary-600"
                    target="_blank"
                    aria-label={social.name}
                  >
                    {social.icon}
                  </Link>
                </li>
              ) : null
            )}
          </ul>
        </div>
      )}
    </motion.div>
  );
};

export default AboutColumn;
