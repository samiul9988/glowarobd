import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import LeftSidebar from "../_sections/LeftSidebar";
import Address from "./_components/Address";
import PointPurchases from "./_components/PointPurchases";
import YourGroup from "./_components/YourGroup";
import { metaData } from "@/metadata/staticMetaData";
import UserProfile from "./_components/UserProfile";

const Profile = async () => {
  const userData = await getServerSession();

  return (
    <section className="pt-10 pb-20">
      <Container>
        <div className="flex flex-col gap-10 md:flex-row lg:gap-[100px]">
          {/* Left Sidebar */}
          <LeftSidebar userData={userData} />

          <div className="w-full">
            {/* Points & Purchases */}
            <UserProfile userData={userData} />

            <PointPurchases />

            {/* Address */}
            <Address />

            {/* Your Group */}
            {/* <YourGroup /> */}
          </div>
        </div>
      </Container>
    </section>
  );
};

export default Profile;

export const metadata = {
  title: metaData.user.title,
  description: metaData.user.description,
};
