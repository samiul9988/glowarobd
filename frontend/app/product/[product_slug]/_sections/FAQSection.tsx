"use client";

import Container from "@/components/Container";
import Image from "next/image";
import { useState } from "react";
import { FaMinus, FaPlus } from "react-icons/fa";
import { AnimatePresence, motion } from "framer-motion";
import { imageBaseHostUrl } from "@/config/apiConfig";

interface CustomFieldValue {
  title: string;
  description?: string;
  image?: string;
}

interface CustomField {
  banner?: string;
  type: string;
  value: CustomFieldValue[] | string;
}

interface FAQSectionProps {
  product?: ProductDetailType;
  faqs?: CustomField;
  className?: string;
}

const FAQSection: React.FC<FAQSectionProps> = ({
  product,
  faqs,
  className = "",
}) => {
  // --- Normalize Data ---
  const faqData: { id: string; question: string; answer: string }[] = (() => {
    const fieldSource =
      faqs?.value && Array.isArray(faqs.value)
        ? faqs.value
        : product?.custom_fields?.faqs?.value &&
            Array.isArray(product.custom_fields.faqs.value)
          ? product.custom_fields.faqs.value
          : [];

    return fieldSource.map((faq: CustomFieldValue, idx: number) => ({
      id: (idx + 1).toString(),
      question: faq.title,
      answer: faq.description || "",
    }));
  })();

  if (faqData.length === 0) return null;

  // --- Banner logic ---
  const bannerSrc =
    (faqs?.banner && `${imageBaseHostUrl}${faqs.banner}`) ||
    (product?.custom_fields?.faqs?.banner &&
      `${imageBaseHostUrl}${product.custom_fields.faqs.banner}`) ||
    "/images/faq-img.png";

  const [openItems, setOpenItems] = useState<Set<string>>(
    new Set([faqData[0]?.id]),
  );

  const toggleItem = (id: string) => {
    const newOpenItems = new Set(openItems);
    newOpenItems.has(id) ? newOpenItems.delete(id) : newOpenItems.add(id);
    setOpenItems(newOpenItems);
  };
  return (
    <Container>
      <div className={`w-full py-[50px] ${className}`}>
        <div className="flex flex-col items-center gap-6 lg:flex-row lg:gap-0">
          {/* Left Image Section */}
          <div className="flex w-full justify-center lg:w-[500px]">
            <Image
              src={bannerSrc}
              alt="FAQ Banner"
              width={500}
              height={560}
              className="object-cover"
              unoptimized
            />
          </div>

          {/* FAQ Section */}
          <div className="w-full lg:flex-1 lg:pl-[136px]">
            <h2 className="text-site-gray-900 mb-6 text-3xl font-bold tracking-wider lg:mb-10 lg:text-4xl xl:text-5xl">
              frequently asked questions
            </h2>

            {faqData.map((faq) => (
              <div
                key={faq.id}
                className={`bg-white ${
                  openItems.has(faq.id) ? "" : "border-site-gray-100 border-b"
                }`}
              >
                <button
                  onClick={() => toggleItem(faq.id)}
                  className="flex w-full cursor-pointer items-center justify-between py-3 text-left transition-colors duration-200"
                >
                  <span className="font-abigeta text-site-gray-900 pr-4 text-[23px] leading-[27px]">
                    {faq.question}
                  </span>
                  <div className="flex-shrink-0">
                    <div
                      className={`flex h-8 w-8 items-center justify-center rounded-full transition-all duration-200 ${
                        openItems.has(faq.id)
                          ? "bg-gray-100"
                          : "bg-site-gray-900"
                      }`}
                    >
                      {openItems.has(faq.id) ? (
                        <FaMinus className="text-site-gray-900" />
                      ) : (
                        <FaPlus className="text-white" />
                      )}
                    </div>
                  </div>
                </button>

                <AnimatePresence>
                  {openItems.has(faq.id) && (
                    <motion.div
                      className="border-site-gray-100 overflow-hidden border-b"
                      initial={{ opacity: 0, height: 0, marginTop: 0 }}
                      animate={{ opacity: 1, height: "auto", marginTop: 16 }}
                      exit={{ opacity: 0, height: 0, marginTop: 0 }}
                    >
                      <div className="pb-6">
                        <p className="text-base leading-relaxed text-gray-600">
                          {faq.answer}
                        </p>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            ))}
          </div>
        </div>
      </div>
    </Container>
  );
};

export default FAQSection;
