"use client";
import { AnimatePresence, motion } from "framer-motion";
import { useState } from "react";
import Container from "../../../../components/Container";

interface TabData {
  id: string;
  label: string;
  show_section?: boolean;
  icon: React.ReactNode;
  content: React.ReactNode;
}

interface ProductDescriptionTabsProps {
  className?: string;
  product: ProductDetailType;
}

const ProductDescriptionTabs: React.FC<ProductDescriptionTabsProps> = ({
  className = "",
  product,
}) => {
  const [activeTab, setActiveTab] = useState("description");

  const tabs: TabData[] = [
    {
      id: "description",
      label: "Description",
      show_section: Boolean(
        product?.description && product?.description !== "",
      ),
      icon: (
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <g clipPath="url(#clip0_541_495)">
            <path
              d="M3 16.5H15C15.5523 16.5 16 16.9477 16 17.5C16 18.0128 15.614 18.4355 15.1166 18.4933L15 18.5H3C2.44772 18.5 2 18.0523 2 17.5C2 16.9872 2.38604 16.5645 2.88338 16.5067L3 16.5ZM3 12.5H21C21.5523 12.5 22 12.9477 22 13.5C22 14.0128 21.614 14.4355 21.1166 14.4933L21 14.5H3C2.44772 14.5 2 14.0523 2 13.5C2 12.9872 2.38604 12.5645 2.88338 12.5067L3 12.5ZM3 8.5H21C21.5523 8.5 22 8.94772 22 9.5C22 10.0128 21.614 10.4355 21.1166 10.4933L21 10.5H3C2.44772 10.5 2 10.0523 2 9.5C2 8.98716 2.38604 8.56449 2.88338 8.50673L3 8.5ZM3 4.5H21C21.5523 4.5 22 4.94772 22 5.5C22 6.01284 21.614 6.43551 21.1166 6.49327L21 6.5H3C2.44772 6.5 2 6.05228 2 5.5C2 4.98716 2.38604 4.56449 2.88338 4.50673L3 4.5Z"
              fill="currentColor"
            />
          </g>
          <defs>
            <clipPath id="clip0_541_495">
              <rect width="24" height="24" fill="currentColor" />
            </clipPath>
          </defs>
        </svg>
      ),
      content: (
        <div className="space-y-6">
          <div
            className="prose prose-gray max-w-none leading-relaxed text-gray-700"
            dangerouslySetInnerHTML={{ __html: product?.description }}
          />
        </div>
      ),
    },
    {
      id: "ingredients",
      label: "Ingredients",
      show_section: Boolean(
        product?.custom_fields?.ingredients?.value &&
          typeof product.custom_fields.ingredients.value === "string" &&
          product.custom_fields.ingredients.value.trim() !== "",
      ),
      icon: (
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M19.6582 7.05C19.6582 7.62201 19.5778 8.17549 19.4275 8.7H21.2329C22.2089 8.7 23 9.48126 23 10.445V15.85C23 19.7989 19.7584 23 15.7595 23C14.0611 23 12.4994 22.4226 11.2645 21.4557L9.94526 22.7583C9.61904 23.0805 9.09007 23.0805 8.76381 22.7583C8.43755 22.4361 8.43755 21.9139 8.76381 21.5917L10.0829 20.2889C9.64327 19.7414 9.28316 19.1288 9.01976 18.4681C8.58801 18.5546 8.14114 18.6 7.68354 18.6C3.99232 18.6 1 15.6451 1 12V7.065C1 6.14545 1.75488 5.4 2.68608 5.4H7.63565C8.36296 2.86065 10.7272 1 13.5316 1H18.0532C18.9396 1 19.6582 1.70963 19.6582 2.585V7.05ZM9.32347 5.60015C11.2111 6.07036 12.7794 7.33364 13.6436 9.01015C14.3128 8.80848 15.0234 8.7 15.7595 8.7H17.6634C17.8723 8.19046 17.9873 7.6335 17.9873 7.05V2.65H13.5316C11.5852 2.65 9.93033 3.88242 9.32347 5.60015ZM12.4563 20.2787C13.3805 20.952 14.5229 21.35 15.7595 21.35C18.8355 21.35 21.3291 18.8875 21.3291 15.85V10.445C21.3291 10.3926 21.286 10.35 21.2329 10.35H15.7595C12.6835 10.35 10.1899 12.8125 10.1899 15.85C10.1899 17.0712 10.5929 18.1994 11.2748 19.1121L16.0043 14.4417C16.3306 14.1195 16.8596 14.1195 17.1858 14.4417C17.512 14.7639 17.512 15.2861 17.1858 15.6083L12.4563 20.2787ZM8.51899 15.85C8.51899 15.1984 8.60725 14.5672 8.77262 13.9674L5.70027 10.9333C5.374 10.6111 5.374 10.0889 5.70027 9.76664C6.02653 9.44446 6.5555 9.44446 6.88176 9.76664L9.46365 12.3163C10.0946 11.2224 11.0095 10.309 12.1093 9.67374C11.266 8.11254 9.60045 7.05 7.68354 7.05H2.68608C2.68244 7.05 2.68042 7.05084 2.68042 7.05084C2.68042 7.05084 2.67731 7.05244 2.67533 7.05439C2.67336 7.05635 2.67173 7.05942 2.67173 7.05942C2.67173 7.05942 2.67089 7.06141 2.67089 7.065V12C2.67089 14.7338 4.91513 16.95 7.68354 16.95C7.99374 16.95 8.29734 16.9222 8.59195 16.8689C8.54387 16.5361 8.51899 16.196 8.51899 15.85Z"
            fill="currentColor"
          />
        </svg>
      ),
      content: (
        <div className="space-y-6">
          {/* Key Ingredients */}
          {product?.custom_fields?.key_ingredient?.value &&
            Array.isArray(product.custom_fields.key_ingredient.value) && (
              <div>
                <h4 className="mb-4 font-semibold text-gray-900">
                  Key Ingredients
                </h4>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                  {product.custom_fields.key_ingredient.value.map(
                    (ingredient: CustomFieldValue, index: number) => (
                      <div key={index} className="rounded-lg bg-gray-50 p-4">
                        <h5 className="mb-2 font-medium text-gray-800">
                          {ingredient.title}
                        </h5>
                      </div>
                    ),
                  )}
                </div>
              </div>
            )}

          {/* Full Ingredients List */}
          {product?.custom_fields?.ingredients?.value &&
            typeof product.custom_fields.ingredients.value === "string" && (
              <div>
                <h4 className="mb-4 font-semibold text-gray-900">
                  Full Ingredients List
                </h4>
                <div
                  className="rounded-lg bg-gray-50 p-4 leading-relaxed text-gray-700"
                  dangerouslySetInnerHTML={{
                    __html: product.custom_fields.ingredients.value,
                  }}
                />
              </div>
            )}
        </div>
      ),
    },
    {
      id: "how-to-use",
      label: "How to use",
      show_section: Boolean(
        product?.custom_fields?.how_to_use?.value &&
          typeof product.custom_fields.how_to_use.value === "string" &&
          product.custom_fields.how_to_use.value.trim() !== "",
      ),
      icon: (
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M12 1C5.93167 1 1 5.93167 1 12C1 18.0683 5.93167 23 12 23C18.0683 23 23 18.0683 23 12C23 5.93167 18.0683 1 12 1ZM12 21.1667C6.94 21.1667 2.83333 17.06 2.83333 12C2.83333 6.94 6.94 2.83333 12 2.83333C17.06 2.83333 21.1667 6.94 21.1667 12C21.1667 17.06 17.06 21.1667 12 21.1667Z"
            fill="currentColor"
          />
          <path
            d="M12.7883 5.67503C11.0283 5.23503 9.06659 6.35336 8.53493 8.0767C8.36993 8.5717 8.64493 9.08503 9.12159 9.23169C9.59826 9.37836 10.1116 9.12169 10.2766 8.62669C10.5333 7.82003 11.5233 7.2517 12.3666 7.45336C13.1916 7.65503 13.8149 8.62669 13.6499 9.47003C13.5033 10.3134 12.6049 10.9917 11.7433 10.8817C11.4866 10.845 11.2116 10.9367 11.0283 11.1017C10.8266 11.285 10.7166 11.5234 10.7166 11.7984L10.6983 14.6584C10.6983 15.1717 11.1016 15.575 11.6149 15.575C12.1283 15.575 12.5316 15.1717 12.5316 14.6584L12.5499 12.66C13.9616 12.385 15.2083 11.23 15.4649 9.78169C15.7949 8.00336 14.5666 6.11503 12.7883 5.67503Z"
            fill="currentColor"
          />
          <path
            d="M11.6516 16.3816H11.6332C11.1382 16.3816 10.7349 16.8033 10.7166 17.2983C10.7166 17.3166 10.7166 17.4816 10.7166 17.4999C10.7166 17.9949 11.1199 18.3249 11.6149 18.3433H11.6332C12.1282 18.3433 12.5316 17.8849 12.5499 17.3899C12.5499 17.3716 12.5499 17.2616 12.5499 17.2433C12.5316 16.7483 12.1466 16.3816 11.6516 16.3816Z"
            fill="currentColor"
          />
        </svg>
      ),
      content: (
        <div className="space-y-6">
          {product?.custom_fields?.how_to_use?.value &&
            typeof product.custom_fields.how_to_use.value === "string" && (
              <div
                className="prose prose-gray max-w-none leading-relaxed text-gray-700"
                dangerouslySetInnerHTML={{
                  __html: product.custom_fields.how_to_use.value,
                }}
              />
              // <div>{product.custom_fields.how_to_use.value}</div>
            )}
        </div>
      ),
    },
  ];

  // Filter tabs to only show those with content and show_section is true
  const visibleTabs = tabs.filter((tab) => tab.show_section && tab.content);

  // Set the first visible tab as active if current active tab is not visible
  const activeTabExists = visibleTabs.some((tab) => tab.id === activeTab);
  const currentActiveTab = activeTabExists
    ? activeTab
    : visibleTabs[0]?.id || "description";

  if (product?.description === null) {
    return null;
  }

  return (
    <div>
        {visibleTabs?.length && 
         <hr className="h-[1px] mb-6 md:mb-10 bg-site-gray-100 w-full"/>
        }
        <div
            className={` w-full overflow-hidden rounded-[10px]  ${className}`}
        >
            {/* Tab Navigation */}
            <div className="mb-6">
            <div className="w-fit rounded-full bg-site-gray-50 hide-scrollbar relative  flex  overflow-x-auto ">
                {visibleTabs.map((tab) => (
                <motion.button
                    key={tab.id}
                    onClick={() => setActiveTab(tab.id)}
                    className={`rounded-full relative flex cursor-pointer items-center gap-2  px-10 py-2 text-sm font-medium whitespace-nowrap ${
                    currentActiveTab === tab.id
                        ? "z-10 text-white"
                        : "text-site-gray-500 hover:text-gray-800"
                    }`}
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    transition={{ duration: 0.3 }}
                >
                    {currentActiveTab === tab.id && (
                    <motion.div
                        layoutId="activeTab"
                        className="bg-site-gray-900 absolute inset-0 rounded-full shadow-sm"
                        initial={false}
                        transition={{
                        type: "tween",
                        stiffness: 300,
                        damping: 30,
                        }}
                    />
                    )}
                    {/* <motion.div
                    className="relative z-10"
                    animate={{
                        scale: currentActiveTab === tab.id ? 1.05 : 1,
                    }}
                    transition={{ duration: 0.3 }}
                    >
                    {tab.icon}
                    </motion.div> */}
                    <span className="relative z-20">{tab.label}</span>
                </motion.button>
                ))}
            </div>
            </div>

            {/* Tab Content */}
            <div className="tab-content-details relative overflow-hidden bg-white ">
            <AnimatePresence mode="wait">
                <motion.div
                key={currentActiveTab}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -20 }}
                transition={{
                    duration: 0.4,
                    ease: [0.4, 0.0, 0.2, 1],
                }}
                className="w-full"
                >
                {visibleTabs.find((tab) => tab.id === currentActiveTab)?.content}
                </motion.div>
            </AnimatePresence>
            </div>
        </div>
    </div>
  );
};

export default ProductDescriptionTabs;
