import { apiBaseUrl } from "@/config/apiConfig";
import { REVALIDATE_TIME } from "@/config/cacheConfig";
import { fetcher } from "@/lib/fetcher";
import Container from "../Container";
import AuthModal from "../modals/AuthModal";
import Logo from "./_components/Logo";
import MobileMenu from "./_components/MobileMenu";
import { NavItems } from "./_components/NavItems";
import SearchBar from "./_components/SearchBar";
import UserActions from "./_components/UserAction";
import HeaderWrapper from "./HeaderWrapper";
import { cacheableFetcher } from "@/lib/cacheableFetcher";

const Header = async () => {
  // Mega menu
  const settingsData = await cacheableFetcher<
    ApiResponseType<BusinessDataType[]>
  >("/business-settings", {
    revalidate: 300,
  });

  const data =
    settingsData &&
    settingsData.data.filter((item) => item.type === "customs_menu_71")[0]
      .value;
  const menu: NavItemsType[] = data && JSON.parse(data);

  // Header logo
  const logo =
    settingsData?.data.filter((item) => item.type === "header_logo")[0]
      .image_url ?? "";

  // All categories and sub-categories
  const res = await fetcher<{ data: Categories[] }>("/categories", {
    baseUrl: apiBaseUrl,
    next: { revalidate: REVALIDATE_TIME },
  });

  // App Download links
  const appStoreLink =
    settingsData?.data.filter((item) => item.type === "app_store_link")[0]
      .value ?? "";

  const playStoreLink =
    settingsData?.data.filter((item) => item.type === "play_store_link")[0]
      .value ?? "/images/login-banner.png";

  const loginSideImage =
    settingsData?.data.filter((item) => item.type === "user_login_banner")?.[0]
      ?.image_url || "/images/login-banner.png";
  const signupSideImage =
    settingsData?.data.filter(
      (item) => item.type === "user_registration_banner",
    )?.[0]?.image_url ?? "";

  const categoriesWithSubs =
    res &&
    res.data &&
    (await Promise.all(
      res.data.map(async (category) => {
        const subRes = await cacheableFetcher<{ data: Categories[] }>(
          `/sub-categories/${category.id}`,
          { baseUrl: apiBaseUrl, revalidate: 210 },
        );

        return {
          ...category,
          subCategories: subRes?.data || [],
        };
      }),
    ));

  return (
    <HeaderWrapper>
      <div className="border-site-secondary-100 flex items-center justify-between gap-3 rounded-full border-b bg-[#EFE6F8] px-3 py-2 shadow-[0px_1px_15px_0px_#00000010] lg:px-4 lg:py-3">
        <div className="flex w-full items-center gap-4 md:w-fit">
          {/* Mobile menu */}
          <MobileMenu
            logo={logo}
            categories={categoriesWithSubs}
            appLinks={{ appStoreLink, playStoreLink }}
          />

          {/* Logo */}
          <Logo />
        </div>

        {/* Search bar */}
        <SearchBar className="hidden lg:block" />

        {/* User actions */}
        <UserActions />
      </div>

      {/* Navigation */}
      <Container>
        <SearchBar className="hidden max-w-full pb-3.5 lg:hidden" />
      </Container>

      {/* Nav items */}
      <div className="bg-white">
        <NavItems menu={menu} categories={categoriesWithSubs} />
      </div>

      {/* Auth Modal */}
      {/* <AuthModal
        loginSideImage={loginSideImage}
        signupSideImage={signupSideImage}
      /> */}
    </HeaderWrapper>
  );
};

export default Header;
