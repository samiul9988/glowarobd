import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import LeftSidebar from "../_sections/LeftSidebar";
import ProfileImageForm from "./_components/ProfileImageForm";
import ShippingAddress from "./_components/ShippingAddress";
import UserInfoForm from "./_components/UserInfoForm";
// import ShippingAddress from "./_components/ShippingAddress";

const ProfilePage = async () => {
  const userData = await getServerSession();

  return (
    <div className="pt-5 pb-20 md:pt-10">
      <Container>
        <div className="lg:max-w-[90%] flex flex-col gap-10 lg:flex-row lg:gap-[130px]">
          {/* Left Sidebar */}
          <LeftSidebar userData={userData} />

          <div className="w-full">
            {/* Profile Image */}
            <ProfileImageForm />

            {/* User Info */}
            <UserInfoForm />

            {/* Shipping Address */}
            <ShippingAddress />
            
          </div>
        </div>
      </Container>
    </div>
  );
};

export default ProfilePage;
