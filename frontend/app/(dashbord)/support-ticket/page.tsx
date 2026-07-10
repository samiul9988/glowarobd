import Container from "@/components/Container";
import { getServerSession } from "@/lib/getServerSession";
import LeftSidebar from "../_sections/LeftSidebar";
import SupportTicket from "./_components/SupportTicket";

const SupportTicketPage = async () => {
  const userData = await getServerSession();

  return (
    <div className="pt-10 pb-20">
      <Container>
        <div className="flex flex-col lg:flex-row gap-10 lg:gap-[130px]">
          {/* Left Sidebar */}
          <LeftSidebar userData={userData} />

          {/* Support Ticket */}
          <SupportTicket />
        </div>
      </Container>
    </div>
  );
};

export default SupportTicketPage;
