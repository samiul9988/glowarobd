import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import PurchaseHistory from "./_components/PurchaseHistory";
import LeftSidebar from "../_sections/LeftSidebar";

const purchaseHistoryPage = async () => {
  const userData = await getServerSession();

  return (
    <div className="pt-10 pb-20">
      <Container>
        <div className="flex flex-col lg:flex-row gap-10 lg:gap-[130px]">
          {/* Left Sidebar */}
          <LeftSidebar userData={userData} />

          {/* Purchase History List */}
          <PurchaseHistory />
        </div>
      </Container>
    </div>
  );
};

export default purchaseHistoryPage;
