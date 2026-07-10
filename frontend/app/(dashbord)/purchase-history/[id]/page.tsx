import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import LeftSidebar from "../../_sections/LeftSidebar";
import PurchaseDetailsCard from "./_components/PurchaseDetailsCard";

interface Props {
  params: Promise<{ id: string }>;
}

const PurchaseDetails = async ({ params }: Props) => {
  const { id } = await params;
  const userData = await getServerSession();

  return (
    <div className="pt-10 pb-20">
      <Container>
        <div className="flex flex-col gap-10 lg:flex-row lg:gap-[130px]">
          {/* Left Sidebar */}
          <LeftSidebar userData={userData} />
          <div className="w-full">
            {/* Purchase Details */}
            <PurchaseDetailsCard id={id} />
          </div>
        </div>
      </Container>
    </div>
  );
};

export default PurchaseDetails;
