import { CATEGORY_REVALIDATE_TIME } from "@/config/cacheConfig";
import { cacheableFetcher } from "../cacheableFetcher";

// api/fetchCategories.ts
export async function fetchCategories(): Promise<CategoryNode[]> {
  const mainResponse = await cacheableFetcher<{ data: Category[] }>(
    "/categories",
    {
      next: { revalidate: CATEGORY_REVALIDATE_TIME },
    },
  );

  const mainCategories = mainResponse?.data || [];

  return Promise.all(
    mainCategories.map(async (main) => {
      const subResponse = await cacheableFetcher<{ data: Category[] }>(
        `/sub-categories/${main.id}`,
        { next: { revalidate: CATEGORY_REVALIDATE_TIME } },
      );

      const subCategories = await Promise.all(
        (subResponse?.data || []).map(async (sub) => {
          const superSubResponse = await cacheableFetcher<{ data: Category[] }>(
            `/sub-categories/${sub.id}`,
            { next: { revalidate: CATEGORY_REVALIDATE_TIME } },
          );

          const superSubs = (superSubResponse?.data || []).map((s) => ({
            id: s.id,
            name: s.name,
            slug: s.slug,
            count: s.products_count,
          }));

          return {
            id: sub.id,
            name: sub.name,
            slug: sub.slug,
            subCategories: superSubs.length ? superSubs : undefined,
          };
        }),
      );

      return {
        id: main.id,
        name: main.name,
        slug: main.slug,
        count: main.products_count,
        subCategories: subCategories.length ? subCategories : undefined,
      };
    }),
  );
}
