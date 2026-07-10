import { imageBaseHostUrl } from "@/config/apiConfig";
import { cacheableFetcher } from "@/lib/cacheableFetcher";
import Image from "next/image";
import Link from "next/link";
import CategoryImage from "./_components/CategoryImage";

const CategoryWrapperMobile = async () => {
  // Fetch main categories
  const res = await cacheableFetcher<{ data: Categories[] }>("/categories", {
    revalidate: 150,
  });

  if (!res || res.data.length === 0) {
    return <p>No categories found</p>;
  }

  // Fetch sub-categories for each category dynamically
  const categoriesWithSubs = await Promise.all(
    res.data.map(async (category) => {
      const subRes = await cacheableFetcher<{ data: Categories[] }>(
        `/sub-categories/${category.id}`,
        { revalidate: 150 },
      );

      return {
        ...category,
        subCategories: subRes?.data || [],
      };
    }),
  );

  // Filter out categories that have no sub-categories
  const filteredCategories = categoriesWithSubs.filter(
    (cat) => cat.subCategories.length > 0,
  );

  if (filteredCategories.length === 0) {
    return (
      <p className="text-site-gray-500 text-sm">
        No categories with sub-categories found
      </p>
    );
  }

  return (
    <div className="mb-10 space-y-8">
      {filteredCategories.map((cat) => (
        <div key={cat.id} className="space-y-2.5">
          {/* Page banner image */}
          <div className="w-full">
            <Image
              src={imageBaseHostUrl + cat.page_banners.mobile}
              alt={cat.name}
              width={0}
              height={0}
              sizes="100vw"
              className="h-auto w-full object-contain"
              loading="lazy"
            />
          </div>

          {cat.subCategories.length > 0 ? (
            <div className="mt-2 grid grid-cols-4 gap-2">
              {cat.subCategories.map((sub) => (
                <Link
                  href={`/category/${sub.slug}`}
                  key={sub.id}
                  className="flex flex-col items-center justify-center gap-2 rounded-[10px] p-1 py-2.5"
                  style={{ backgroundColor: cat.child_bg_color || "#000" }}
                >
                  <CategoryImage
                    src={imageBaseHostUrl + sub.icons.mobile}
                    alt={sub.name}
                    className="h-[48px] w-[48px]"
                  />
                  <p className="text-center text-[10px] leading-3 font-medium text-white">
                    {sub.name}
                  </p>
                </Link>
              ))}
            </div>
          ) : (
            <p className="text-site-gray-500 text-sm">No sub-categories</p>
          )}
        </div>
      ))}
    </div>
  );
};

export default CategoryWrapperMobile;
