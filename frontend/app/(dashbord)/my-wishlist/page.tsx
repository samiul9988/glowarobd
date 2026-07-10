import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import LeftSidebar from "../_sections/LeftSidebar";
import WishlistSection from "@/app/wishlist/_sections/WishlistSection";

const wishlist = async () => {
  const userData = await getServerSession();

  return (
    <section className="pt-10 pb-20">
      <Container>
        <div className="flex flex-col gap-10 md:flex-row lg:gap-[100px]">
          {/* Left Sidebar */}
          <LeftSidebar userData={userData} />

          <div className="w-full">
            <WishlistSection isDashboard={true} />
          </div>
        </div>
      </Container>
    </section>
  );
};

export default wishlist;
