"use client";

import {
  AboutUsIcon,
  ContactUsIcon,
  FacebookIcon,
  FaqIcon,
  InstagramIcon,
  PrivacyPolicyIcon,
  ReturnPolicyIcon,
  ShippingPolicyIcon,
  SupportIcon,
  TermsConditionsIcon,
  TiktokIcon,
  UserIcon,
  YoutubeIcon,
} from "@/components/icons/icon-library";
import { useScroll, useSpring, useTransform, useInView, useAnimation } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import { useRef, useEffect } from "react";

import Container from "@/components/Container";
import { motion } from "framer-motion";
import { parseHTMLToText } from "@/utils/parseHTMLToText";
import { FaFacebook, FaLinkedin, FaYoutube } from "react-icons/fa";
import { FaSquareInstagram } from "react-icons/fa6";
import CustomImage from "@/components/cards/CustomImage";
import Heading from "@/components/Heading";
import { Headphones, Truck } from "lucide-react";
import { FooterImageSection } from "./FooterImageSection";

interface Props {
  socialLinks: {
    facebookLink: string;
    tiktokLink: string;
    youtubeLink: string;
    instagramLink: string;
    showSocialLinks: boolean;
  };
  copyRightText: string;
  paymentMethodImg: string;
  aboutUs: string;
}


const FooterWrapper = ({
  socialLinks,
  copyRightText,
  paymentMethodImg,
  aboutUs,
}: Props) => {
  const sectionRef = useRef(null);

  const aboutPlainText = parseHTMLToText(aboutUs);
  const copyRightPlainText = parseHTMLToText(copyRightText);

  const { scrollYProgress } = useScroll({
    target: sectionRef,
    offset: ["start end", "end start"],
  });

  const smoothProgress = useSpring(scrollYProgress, {
    stiffness: 80,
    damping: 20,
    mass: 0.5,
  });

  const translateY = useTransform(smoothProgress, [0, 1], [-750, 650]);
  const rotate = useTransform(
    smoothProgress,
    [0, 0.3, 0.6, 1],
    [25, 15, -10, 0],
  );

  // Social links data
  const socials = [
    {
      name: "facebook",
      icon: (
        <FaFacebook
          size={28}
          className="rounded-full  transition-all duration-200 ease-in-out hover:fill-white"
        />
      ),
      href: socialLinks.facebookLink,
    },
    {
      name: "tiktok",
      icon: (
        <TiktokIcon
          size={28}
          className="rounded-full  transition-all duration-200 ease-in-out hover:fill-white"
        />
      ),
      href: socialLinks.tiktokLink,
    },
    {
      name: "youtube",
      icon: (
        <FaYoutube
          size={28}
          className="rounded-full  transition-all duration-200 ease-in-out hover:fill-white"
        />
      ),
      href: socialLinks.youtubeLink,
    },
    {
      name: "instagram",
      icon: (
        <FaSquareInstagram
          size={28}
          className="rounded-full  transition-all duration-200 ease-in-out hover:fill-white"
        />
      ),
      href: socialLinks.instagramLink,
    },
    // {
    //   name: "Linkedin",
    //   icon: (
    //     <FaLinkedin
    //       size={28}
    //       className=" transition-all duration-200 ease-in-out hover:fill-white"
    //     />
    //   ),
    //   href: socialLinks.linkedin,
    // },
  ];

  return (
    <footer
      ref={sectionRef}
      className="bg-[#C3B3D3] pt-10 md:pt-[80px] relative w-full overflow-hidden max-md:mb-[50px]"
    >
    <Container className="mb-5">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-2">
            <div className="flex items-center gap-3 bg-[#FFFFFF80] p-2 rounded-sm">
                <div className="w-12 h-12 p-1 rounded-sm bg-site-secondary-500 flex items-center justify-center">
                  <Truck size={24} className="text-white"/>
                </div>
                <div className="flex flex-col gap-1">
                    <h4 className="text-base font-bold">Nation Wide Shipping</h4>
                    <p className="text-sm font-normal">Quick & safe Delivery</p>
                </div>
            </div>
            <div className="flex items-center gap-3 bg-[#FFFFFF80] p-2 rounded-sm">
                <div className="w-12 h-12 p-1 rounded-sm bg-site-secondary-500 flex items-center justify-center">
                  <Headphones size={24} className="text-white"/>
                </div>
                <div className="flex flex-col gap-1">
                    <h4 className="text-base font-bold">24/7 Support</h4>
                    <p className="text-sm font-normal">Support anytime, day night</p>
                </div>
            </div>
             <div className="flex items-center gap-3 bg-[#FFFFFF80] p-2 rounded-sm">
                <div className="w-12 h-12 p-1 rounded-sm bg-site-secondary-500 flex items-center justify-center">
                  <Truck size={24} className="text-white"/>
                </div>
                <div className="flex flex-col gap-1">
                    <h4 className="text-base font-bold">100% Authentic</h4>
                    <p className="text-sm font-normal">Trusted quality checked</p>
                </div>
            </div>
             <div className="flex items-center gap-3 bg-[#FFFFFF80] p-2 rounded-sm">
                <div className="w-12 h-12 p-1 rounded-sm bg-site-secondary-500 flex items-center justify-center">
                  <Truck size={24} className="text-white"/>
                </div>
                <div className="flex flex-col gap-1">
                    <h4 className="text-base font-bold">Order Tracking</h4>
                    <p className="text-sm font-normal">Track order in real time</p>
                </div>
            </div>
        </div>
    </Container>
      <Container className="relative">
        {/* Footer gift img */}
        {/* <GiftImgAnimation /> */}

        {/* Footer Top */}
        <div className="relative z-10 flex flex-col justify-between gap-8 md:gap-14 lg:flex-row lg:gap-0">
          {/* Left side */}
     

          {/* Right side */}
          <div className="w-full p-6 md:p-8 rounded-lg bg-[#FFFFFF80] gap-3 md:flex-row md:gap-6">
            <div className="grid grid-cols-2 gap-10 rounded-[10px] text-sm md:text-[22px] lg:grid-cols-4 lg:gap-3">
              {/* quick link */}
              <div className="space-y-4 max-lg:flex-1 md:space-y-6">
                <h5 className="text-site-primary-500 text-base font-medium">
                  {" "}
                  QUICK LINKS{" "}
                </h5>
                <ul className="flex flex-col items-start gap-4">
                  <li className="group inline-flex items-center">
                    <Link
                      href="/flash-deals"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Offers
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/wishlist"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Wishlist
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/blogs"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Blogs
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/cart"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      My Bag
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/my-orders"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      My Orders
                    </Link>
                  </li>
                </ul>
              </div>

              {/* Socials links */}
              {socialLinks.showSocialLinks && (
                <div className="space-y-4 rounded-[10px] md:space-y-6">
                  <h5 className="text-site-primary-500 text-base font-medium">
                    {" "}
                    SOCIALS{" "}
                  </h5>
                  <ul className="flex h-full w-full flex-col gap-3">
                    {socials.map(
                      (social, index) =>
                        social?.href &&
                        social?.icon && (
                          <li key={index}>
                            <Link
                              className="text-gray-800 flex items-center gap-3 text-[23px]"
                              href={social.href}
                              target="_blank"
                            >
                              {social.icon}
                              {social.name}
                            </Link>
                          </li>
                        ),
                    )}
                  </ul>
                </div>
              )}

              {/* Site Policies and links */}
              <div className="space-y-4 max-lg:flex-1 md:space-y-6">
                <h5 className="text-site-primary-500 text-base font-medium">
                  {" "}
                  TOP CATEGORIES{" "}
                </h5>
                <ul className="flex flex-col items-start gap-4">
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/returnpolicy"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Moisturizer
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/shipping-delivery"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Skincare
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/terms"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Hair Care
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/privacypolicy"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Sunscreen
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/privacypolicy"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      K-Beauty
                    </Link>
                  </li>
                </ul>
              </div>

              {/* Insight and Useful links */}
              <div className="space-y-4 max-lg:flex-1 md:space-y-6">
                <h5 className="text-site-primary-500 text-base font-medium">
                  {" "}
                  LEARN ABOUT{" "}
                </h5>
                <ul className="flex flex-col items-start gap-4">
                  <li className="group inline-flex items-center">
                    <Link
                      href="/pages/about-us"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      About Us
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="#"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Terms & Conditions
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="#"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Returns & Refunds
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/about-us"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Warranty Policy
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/privacy"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      Privacy Policy
                    </Link>
                  </li>
                  <li className="group inline-flex items-center">
                    <Link
                      href="/page/how-to-buy"
                      className="text-gray-800 flex items-center gap-3 text-sm font-medium transition-colors group-hover:text-gray-800 md:text-[22px]"
                    >
                      How to Buy
                    </Link>
                  </li>
                </ul>
              </div>
            </div>
          </div>

         
        </div>
        {/* Vertical divider */}
        {/* Copyright and Designed by text */}
        <div className="flex justify-between items-center  pt-4">
          <div
            className="text-[11px] font-normal !text-gray-800/50 md:text-sm"
            dangerouslySetInnerHTML={{ __html: copyRightPlainText }}
          ></div>
          <div>
             <div className="lg:max-w-[685px]">
              {paymentMethodImg && (
                <CustomImage
                  src={paymentMethodImg }
                  alt="payment-method"
                  height={500}
                  width={50}
                  className="mt-6 h-auto w-full rounded-sm object-contain md:rounded-[10px]"
                />
              )}
            </div>
          </div>
          {/* <div className="my-2 h-px w-full self-stretch bg-white/10 lg:hidden" /> */}
          {/* <span className="text-[11px] font-normal text-gray-800/50 md:text-sm">
            Design & Developed by{" "}
            <Link
              href="https://www.coder71.com"
              target="_blank"
              className="transition-colors hover:text-gray-800 hover:underline"
            >
              Coder71 Limited
            </Link>
          </span> */}
        </div>
      </Container>
      <FooterImageSection />

      {/* <Container className="relative">
      </Container> */}

      {/* Footer bottom images */}
      {/* <div className="absolute bottom-0 left-0 h-[100px] w-[200px] translate-y-5 transform md:h-[150px] md:w-[280px] lg:left-12 lg:block lg:h-[200px] lg:w-[450px]">
        <Image
          src="/images/footer/footer-product-group-2.png"
          alt="glowaro-products"
          fill
        />
      </div>

      <div className="absolute right-0 bottom-0 h-[100px] w-[200px] translate-y-5 transform md:h-[150px] md:w-[280px] lg:right-12 lg:block lg:h-[200px] lg:w-[450px]">
        <Image
          src="/images/footer/footer-product-group-1.png"
          alt="glowaro-products"
          fill
        />
      </div> */}

      {/* Single products Mobile view */}
      {/* <div className="absolute right-0 bottom-0 translate-x-[50px] translate-y-[50px] transform lg:hidden">
        <Image
          src="/images/footer/footer-product-1.png"
          alt="glowaro"
          height={0}
          width={110}
          priority
        />
      </div>

      <div className="absolute bottom-0 left-0 -translate-x-[50px] translate-y-[50px] transform lg:hidden">
        <Image
          src="/images/footer/footer-product-2.png"
          alt="glowaro"
          height={0}
          width={100}
          priority
        />
      </div> */}

      {/* Single products Desktop view */}
      {/* <motion.div
        className="absolute right-0 bottom-0 hidden transform lg:block lg:translate-x-[80px] lg:translate-y-[120px]"
        style={{ translateY, rotate }}
      >
        <Image
          src="/images/footer/footer-product-1.png"
          alt="glowaro"
          height={0}
          width={250}
          priority
        />
      </motion.div> */}

      {/* <motion.div
        className="absolute bottom-0 left-0 hidden transform lg:block lg:-translate-x-[55px] lg:translate-y-[75px]"
        style={{ translateY, rotate }}
      >
        <Image
          src="/images/footer/footer-product-2.png"
          alt="glowaro"
          height={0}
          width={170}
          priority
        />
      </motion.div> */}

      {/* Footer shadow */}
      {/* <Image
        src="/images/footer/footer-shadow.png"
        alt="glowaro"
        width={0}
        height={0}
        sizes="100vw"
        className="absolute bottom-0 left-0 w-full object-contain"
      /> */}
    </footer>
  );
};

export default FooterWrapper;
