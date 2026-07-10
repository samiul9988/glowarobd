import { ChevronDown } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState } from "react";
import * as ScrollArea from "@radix-ui/react-scroll-area";

const CategoriesAccordion = ({
  categories,
}: {
  categories: CategoryNode[];
}) => {
  const fullPathName = usePathname();
  const currentCategorySlug = fullPathName.split("/")[2];

  // Helper function to find category by slug and return path info
  const findCategoryBySlug = (
    slug: string,
  ): {
    found: boolean;
    categoryId: string;
    parentIds: string[];
  } => {
    for (const mainCat of categories) {
      // Check main category
      if (mainCat.slug === slug) {
        return {
          found: true,
          categoryId: String(mainCat.id),
          parentIds: [],
        };
      }

      // Check subcategories
      if (mainCat.subCategories) {
        for (const subCat of mainCat.subCategories) {
          if (subCat.slug === slug) {
            return {
              found: true,
              categoryId: String(subCat.id),
              parentIds: [String(mainCat.id)],
            };
          }

          // Check third-level categories
          if (subCat.subCategories) {
            for (const thirdCat of subCat.subCategories) {
              if (thirdCat.slug === slug) {
                return {
                  found: true,
                  categoryId: String(thirdCat.id),
                  parentIds: [String(mainCat.id), String(subCat.id)],
                };
              }
            }
          }
        }
      }
    }

    return { found: false, categoryId: "", parentIds: [] };
  };

  const currentCategoryInfo = findCategoryBySlug(currentCategorySlug || "");

  // Expanded items state - tracks which categories/subcategories are expanded
  const [expandedItems, setExpandedItems] = useState<Set<string>>(() => {
    const initialExpanded = new Set<string>();

    // If we found a category from URL, expand its parents
    if (currentCategoryInfo.found) {
      currentCategoryInfo.parentIds.forEach((id) => initialExpanded.add(id));
    } else if (categories.length > 0 && categories[0].subCategories) {
      // Otherwise expand first category
      initialExpanded.add(String(categories[0].id));
    }

    return initialExpanded;
  });

  const toggleExpansion = (
    itemId: string | number,
    isParent: boolean = false,
  ) => {
    const id = String(itemId);
    const newExpanded = new Set(expandedItems);

    if (isParent) {
      // For parent categories: close all other parents, toggle current
      const parentIds = categories.map((cat) => String(cat.id));

      if (newExpanded.has(id)) {
        // Close this parent
        newExpanded.delete(id);
      } else {
        // Close all other parents, open this one
        parentIds.forEach((parentId) => newExpanded.delete(parentId));
        newExpanded.add(id);
      }
    } else {
      // For subcategories: just toggle normally
      if (newExpanded.has(id)) {
        newExpanded.delete(id);
      } else {
        newExpanded.add(id);
      }
    }

    setExpandedItems(newExpanded);
  };

  // Helper function to check if an item or its children are selected based on URL slug
  const isItemOrChildSelected = (itemId: string | number): boolean => {
    const id = String(itemId);

    // Check if this item is the currently selected one from URL
    if (currentCategoryInfo.found && currentCategoryInfo.categoryId === id) {
      return true;
    }

    // Check if any child is selected
    for (const category of categories) {
      if (String(category.id) === id) {
        // Check if any subcategory matches the URL slug
        if (category.subCategories) {
          for (const sub of category.subCategories) {
            if (sub.slug === currentCategorySlug) return true;

            // Check third-level
            if (sub.subCategories) {
              for (const third of sub.subCategories) {
                if (third.slug === currentCategorySlug) return true;
              }
            }
          }
        }
      }

      // Check if this is a subcategory and any of its children match
      for (const sub of category.subCategories || []) {
        if (String(sub.id) === id && sub.subCategories) {
          for (const third of sub.subCategories) {
            if (third.slug === currentCategorySlug) return true;
          }
        }
      }
    }

    return false;
  };

  const isExpanded = (itemId: string | number) =>
    expandedItems.has(String(itemId));

  const isSelected = (itemId: string | number) => {
    return (
      currentCategoryInfo.found &&
      currentCategoryInfo.categoryId === String(itemId)
    );
  };
  return (
    <div className="rounded-2 w-full bg-white md:w-[220px]">
      <div className="">
        <ScrollArea.Root className="relative overflow-clip" data-lenis-ignore>
          <ScrollArea.Viewport
            className="rounded-2 max-h-[400px]"
            onWheel={(e) => e.stopPropagation()}
          >
            {categories.map((category: CategoryNode) => (
              <div key={category.id} className="pr-3">
                {/* Main Category Row */}
                <div
                  className="mb-2.5 flex cursor-pointer items-center md:mb-4"
                  onClick={() => {
                    if (category.subCategories) {
                      toggleExpansion(category.id, true);
                    }
                  }}
                >
                  {/* Radio Button for Main Category */}
                  <div className="flex flex-1 items-center">
                    <Link
                      prefetch={false}
                      href={`/category/${category?.slug}`}
                      className="inline-flex h-full w-full"
                    >
                      <div className="relative mr-3 cursor-pointer">
                        <div
                          className={`flex h-5 w-5 items-center justify-center rounded-full border-1 transition-colors ${
                            isItemOrChildSelected(category.id)
                              ? "border-site-secondary-500 bg-site-secondary-500"
                              : "border-site-gray-200 bg-white"
                          }`}
                        >
                          {isItemOrChildSelected(category.id) && (
                            <div className="bg-site-secondary-500 h-4 w-4 rounded-full border-2 border-white"></div>
                          )}
                        </div>
                      </div>
                      <span
                        className={`cursor-pointer text-sm transition-colors ${
                          isItemOrChildSelected(category.id)
                            ? "text-site-gray-900 font-semibold"
                            : "text-site-gray-500 font-normal"
                        }`}
                      >
                        {category.name}
                      </span>
                      {typeof category.products_count === "number" && (
                        <span className="rounded-full px-2.5 py-1 text-xs font-semibold transition-colors">
                          ({category.products_count})
                        </span>
                      )}
                    </Link>
                  </div>

                  {/* Count and Expand Button */}
                  <div className="flex items-center space-x-2 px-3 py-1">
                    {/* {typeof category.count === "number" && category.count > 0 && (
                            <span className="rounded-full px-2.5 py-1 text-xs font-semibold transition-colors">
                                {category.count}
                            </span>
                            )} */}
                    {category.subCategories && (
                      <ChevronDown
                        className={`h-4 w-4 text-gray-500 transition-transform duration-200 ${
                          isExpanded(category.id) ? "rotate-180" : ""
                        }`}
                      />
                    )}
                  </div>
                </div>

                {/* Subcategories */}
                {category.subCategories && isExpanded(category.id) && (
                  <div className="bg-white">
                    {category.subCategories.map((subCategory: CategoryNode) => (
                      <div key={subCategory.id}>
                        {/* Subcategory Row */}
                        <div
                          className="mb-4 flex cursor-pointer items-center pl-3.5 transition-colors"
                          onClick={() => {
                            if (subCategory.subCategories) {
                              toggleExpansion(subCategory.id);
                            }
                          }}
                        >
                          <Link
                            href={"/category/" + subCategory?.slug}
                            prefetch={false}
                            className="flex flex-1 items-center"
                            // onClick={(e) => {
                            //     e.stopPropagation();
                            //     handleItemSelection(subCategory.id);
                            // }}
                          >
                            <div className="relative mr-3">
                              <div
                                className={`flex h-5 w-5 items-center justify-center rounded-full border-1 transition-colors ${
                                  isItemOrChildSelected(subCategory.id)
                                    ? "border-site-secondary-500 bg-site-secondary-500"
                                    : "border-site-gray-200 bg-white"
                                }`}
                              >
                                {isItemOrChildSelected(subCategory.id) && (
                                  <div className="bg-site-secondary-500 h-4 w-4 rounded-full border-2 border-white"></div>
                                )}
                              </div>
                            </div>
                            <span
                              className={`text-sm transition-colors ${
                                isItemOrChildSelected(subCategory.id)
                                  ? "text-site-gray-900 font-semibold"
                                  : "text-site-gray-500 font-normal"
                              }`}
                            >
                              {subCategory.name}
                            </span>
                            {typeof subCategory.products_count === "number" && (
                              <span className="rounded-full px-2.5 py-1 text-xs font-semibold transition-colors">
                                ({subCategory.products_count})
                              </span>
                            )}
                          </Link>
                          {/* right side */}
                          <div className="flex items-center space-x-2">
                            {/* {typeof subCategory.count === "number" &&
                                    subCategory.count > 0 && (
                                        <span className="rounded-full px-2.5 py-1 text-xs font-semibold transition-colors">
                                        {subCategory.count}
                                        </span>
                                    )} */}

                            {subCategory.subCategories && (
                              <ChevronDown
                                className={`text-site-gray-400 h-4 w-4 transition-transform duration-200 ${
                                  isExpanded(subCategory.id) ? "rotate-180" : ""
                                }`}
                              />
                            )}
                          </div>
                        </div>

                        {/* Grandchildren */}
                        {subCategory.subCategories &&
                          isExpanded(subCategory.id) && (
                            <div className="bg-white">
                              {subCategory.subCategories.map(
                                (grandChild: CategoryNode) => (
                                  <Link
                                    key={grandChild.id}
                                    href={`/category/${grandChild?.slug}`}
                                    prefetch={false}
                                    className="mb-4 flex cursor-pointer items-center pl-7 transition-colors hover:bg-gray-50"
                                  >
                                    <div className="relative mr-3">
                                      <div
                                        className={`flex h-5 w-5 items-center justify-center rounded-full border-1 transition-colors ${
                                          isSelected(grandChild.id)
                                            ? "border-site-secondary-500 bg-site-secondary-500"
                                            : "border-site-gray-200 bg-white"
                                        }`}
                                      >
                                        {isSelected(grandChild.id) && (
                                          <div className="bg-site-secondary-500 h-4 w-4 rounded-full border-2 border-white"></div>
                                        )}
                                      </div>
                                    </div>
                                    <span
                                      className={`text-sm transition-colors ${
                                        isSelected(grandChild.id)
                                          ? "text-site-gray-900 font-semibold"
                                          : "text-site-gray-500 font-normal"
                                      }`}
                                    >
                                      {grandChild.name}
                                    </span>
                                    {typeof grandChild.products_count ===
                                      "number" && (
                                      <span className="rounded-full px-2.5 py-1 text-xs font-semibold transition-colors">
                                        ({grandChild.products_count})
                                      </span>
                                    )}

                                    {/* {typeof grandChild.count === "number" &&
                                            grandChild.count > 0 && (
                                                <span className="rounded-full px-2.5 py-1 text-xs font-semibold transition-colors">
                                                {grandChild.count}
                                                </span>
                                            )} */}
                                  </Link>
                                ),
                              )}
                            </div>
                          )}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </ScrollArea.Viewport>
          <ScrollArea.Scrollbar
            orientation="vertical"
            className="flex w-2 touch-none rounded-full bg-gray-200 p-0.5 select-none"
          >
            <ScrollArea.Thumb className="flex-1 rounded-full bg-gray-400" />
          </ScrollArea.Scrollbar>
        </ScrollArea.Root>
      </div>
    </div>
  );
};

export default CategoriesAccordion;
